import io

import numpy as np
from fastapi import FastAPI, File, HTTPException, UploadFile
from PIL import Image
from pydantic import BaseModel
from transformers import pipeline

import bert_detector
import detecting_ai

IMAGE_MODEL      = "umm-maybe/AI-image-detector"
ALLOWED_IMG_EXTS = {"jpg", "jpeg", "png", "webp"}

_image_classifier = None


def get_image_classifier():
    global _image_classifier
    if _image_classifier is None:
        print(f"Carregando modelo de imagem {IMAGE_MODEL}...")
        _image_classifier = pipeline("image-classification", model=IMAGE_MODEL, device=-1)
        print("Modelo de imagem carregado.")
    return _image_classifier


app = FastAPI()


@app.on_event("startup")
def preload_models():
    get_image_classifier()
    detecting_ai.get_classifier()
    bert_detector.get_bert_model()


# ─── Image helpers ────────────────────────────────────────────────────────────

def _is_ai_image_label(label: str) -> bool:
    label = label.lower()
    return any(k in label for k in ("artificial", "fake", "generated", "ai"))


def _classify_image(image_bytes: bytes) -> dict:
    img    = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    result = get_image_classifier()(img)[0]
    label  = result["label"]
    score  = result["score"]

    ai_score = score if _is_ai_image_label(label) else 1.0 - score

    return {"ai_score": round(ai_score, 3), "model": IMAGE_MODEL}


# ─── Schemas ──────────────────────────────────────────────────────────────────

class TextRequest(BaseModel):
    text:  str
    model: str = "detecting_ai"


# ─── Endpoints ────────────────────────────────────────────────────────────────

@app.get("/health")
def health():
    return {"status": "ok"}


@app.post("/detect/image")
async def detect_image(file: UploadFile = File(...)):
    filename = file.filename or ""
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else ""

    if ext not in ALLOWED_IMG_EXTS:
        raise HTTPException(
            status_code=400,
            detail=f"Formato não suportado. Envie: {', '.join(ALLOWED_IMG_EXTS)}",
        )

    content = await file.read()

    try:
        result = _classify_image(content)
    except Exception as e:
        raise HTTPException(status_code=422, detail=f"Erro ao processar imagem: {str(e)}")

    result["filename"] = filename
    return result


@app.post("/detect/text")
def detect_text(body: TextRequest):
    if not body.text or not body.text.strip():
        raise HTTPException(status_code=400, detail="Texto não pode ser vazio.")

    try:
        if body.model == "bert":
            result = bert_detector.analyze(body.text)
        else:
            result = detecting_ai.analyze(body.text)
    except Exception as e:
        raise HTTPException(status_code=422, detail=f"Erro ao processar texto: {str(e)}")

    return result
