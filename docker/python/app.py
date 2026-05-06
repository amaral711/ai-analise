import io

import numpy as np
from fastapi import FastAPI, File, HTTPException, UploadFile
from PIL import Image
from pydantic import BaseModel
from transformers import pipeline

MODEL = "umm-maybe/AI-image-detector"
TEXT_MODEL = "Detecting-ai/pt-ai-detector"

ALLOWED_EXTENSIONS = {"jpg", "jpeg", "png", "webp"}

classifier = None
text_classifier = None


def get_image_classifier():
    global classifier
    if classifier is None:
        print(f"Carregando modelo {MODEL}...")
        classifier = pipeline("image-classification", model=MODEL, device=-1)
        print("Modelo de imagem carregado.")
    return classifier


def get_text_classifier():
    global text_classifier
    if text_classifier is None:
        print(f"Carregando modelo {TEXT_MODEL}...")
        text_classifier = pipeline("text-classification", model=TEXT_MODEL, device=-1)
        print("Modelo de texto carregado.")
    return text_classifier


app = FastAPI()


@app.on_event("startup")
def preload_models():
    get_image_classifier()
    get_text_classifier()


# ─── Image helpers ────────────────────────────────────────────────────────────

def is_ai_label(label: str) -> bool:
    label = label.lower()
    return any(k in label for k in ("artificial", "fake", "generated", "ai"))


def classify_image(image_bytes: bytes) -> dict:
    img    = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    result = get_image_classifier()(img)[0]
    label  = result["label"]
    score  = result["score"]

    ai_score = score if is_ai_label(label) else 1.0 - score

    return {
        "ai_score": round(ai_score, 3),
        "model":    MODEL,
    }


# ─── Text helpers ─────────────────────────────────────────────────────────────

class TextRequest(BaseModel):
    text: str


def is_ai_text_label(label: str) -> bool:
    label = label.lower()
    return any(k in label for k in ("ai", "artificial", "generated", "machine", "fake", "label_1"))


def classify_text(text: str) -> dict:
    result = get_text_classifier()(text, truncation=True, max_length=512)[0]
    label  = result["label"]
    score  = result["score"]

    ai_score = score if is_ai_text_label(label) else 1.0 - score

    return {
        "ai_score": round(ai_score, 3),
        "model":    TEXT_MODEL,
    }


# ─── Endpoints ────────────────────────────────────────────────────────────────

@app.get("/health")
def health():
    return {"status": "ok"}


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


@app.post("/detect/text")
async def detect_text(body: TextRequest):
    if not body.text or not body.text.strip():
        raise HTTPException(status_code=400, detail="Texto não pode ser vazio.")

    try:
        result = classify_text(body.text)
    except Exception as e:
        raise HTTPException(status_code=422, detail=f"Erro ao processar texto: {str(e)}")

    return result
