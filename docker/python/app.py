import io
import os
import tempfile
from contextlib import asynccontextmanager

import librosa
import numpy as np
from fastapi import FastAPI, File, HTTPException, UploadFile
from PIL import Image
from transformers import pipeline

MODEL = "umm-maybe/AI-image-detector"
AUDIO_MODEL = "MelodyMachine/Deepfake-audio-detection-V2"

ALLOWED_EXTENSIONS = {"jpg", "jpeg", "png", "webp"}
ALLOWED_AUDIO_EXTENSIONS = {"mp3", "wav", "ogg", "m4a", "aac"}

classifier = None
audio_classifier = None


@asynccontextmanager
async def lifespan(app: FastAPI):
    global classifier, audio_classifier
    print(f"Carregando modelo {MODEL}...")
    classifier = pipeline("image-classification", model=MODEL, device=-1)
    print("Modelo de imagem carregado.")

    print(f"Carregando modelo {AUDIO_MODEL}...")
    audio_classifier = pipeline("audio-classification", model=AUDIO_MODEL, device=-1)
    print("Modelo de áudio carregado.")
    yield


app = FastAPI(lifespan=lifespan)


# ─── Image helpers ────────────────────────────────────────────────────────────

def is_ai_label(label: str) -> bool:
    label = label.lower()
    return any(k in label for k in ("artificial", "fake", "generated", "ai"))


def classify_image(image_bytes: bytes) -> dict:
    img    = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    result = classifier(img)[0]
    label  = result["label"]
    score  = result["score"]

    ai_score = score if is_ai_label(label) else 1.0 - score

    return {
        "ai_score": round(ai_score, 3),
        "model":    MODEL,
    }


# ─── Audio helpers ────────────────────────────────────────────────────────────

def is_ai_audio_label(label: str) -> bool:
    label = label.lower()
    return any(k in label for k in ("fake", "spoof", "generated", "synthetic", "ai", "tts"))


def classify_audio(audio_bytes: bytes, filename: str) -> dict:
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else "wav"

    with tempfile.NamedTemporaryFile(suffix=f".{ext}", delete=False) as tmp:
        tmp.write(audio_bytes)
        tmp_path = tmp.name

    try:
        audio_array, _ = librosa.load(tmp_path, sr=16000, mono=True)
    finally:
        os.unlink(tmp_path)

    result = audio_classifier({"array": audio_array, "sampling_rate": 16000})[0]
    label  = result["label"]
    score  = result["score"]

    ai_score = score if is_ai_audio_label(label) else 1.0 - score

    return {
        "ai_score": round(ai_score, 3),
        "model":    AUDIO_MODEL,
    }


# ─── Endpoints ────────────────────────────────────────────────────────────────

@app.get("/health")
def health():
    return {"status": "ok", "model": MODEL}


@app.post("/detect/image")
async def detect_image(file: UploadFile = File(...)):
    filename = file.filename or ""
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else ""

    if ext not in ALLOWED_EXTENSIONS:
        raise HTTPException(
            status_code=400,
            detail=f"Formato não suportado. Envie: {', '.join(ALLOWED_EXTENSIONS)}",
        )

    content = await file.read()

    try:
        result = classify_image(content)
    except Exception as e:
        raise HTTPException(status_code=422, detail=f"Erro ao processar imagem: {str(e)}")

    result["filename"] = filename
    return result


@app.post("/detect/audio")
async def detect_audio(file: UploadFile = File(...)):
    filename = file.filename or ""
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else ""

    if ext not in ALLOWED_AUDIO_EXTENSIONS:
        raise HTTPException(
            status_code=400,
            detail=f"Formato não suportado. Envie: {', '.join(ALLOWED_AUDIO_EXTENSIONS)}",
        )

    content = await file.read()

    try:
        result = classify_audio(content, filename)
    except Exception as e:
        raise HTTPException(status_code=422, detail=f"Erro ao processar áudio: {str(e)}")

    result["filename"] = filename
    return result
