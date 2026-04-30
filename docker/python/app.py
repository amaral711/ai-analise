import io
from contextlib import asynccontextmanager

from fastapi import FastAPI, File, HTTPException, UploadFile
from PIL import Image
from pydantic import BaseModel
from transformers import pipeline

MODEL = "umm-maybe/AI-image-detector"

ALLOWED_EXTENSIONS = {"jpg", "jpeg", "png", "webp"}

classifier = None


@asynccontextmanager
async def lifespan(app: FastAPI):
    global classifier
    print(f"Carregando modelo {MODEL}...")
    classifier = pipeline("image-classification", model=MODEL, device=-1)
    print("Modelo carregado.")
    yield


app = FastAPI(lifespan=lifespan)


# ─── Helpers ──────────────────────────────────────────────────────────────────

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
