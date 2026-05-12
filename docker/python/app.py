import io

import cv2
import numpy as np
from fastapi import FastAPI, File, HTTPException, UploadFile
from PIL import Image
from pydantic import BaseModel
from transformers import pipeline

import bert_detector
import detecting_ai
import image_fft_analyzer
from image_flat_analyzer import analisar_flat_design, is_flat_design
from image_metadata_analyzer import check_ai_metadata
from image_style_analyzer import analisar_estilo_cartoon, is_cartoon

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
    print("Analisador FFT de imagem pronto.")


# ─── Image helpers ────────────────────────────────────────────────────────────

def _is_ai_image_label(label: str) -> bool:
    label = label.lower()
    return any(k in label for k in ("artificial", "fake", "generated", "ai"))


def _confidence(score: float) -> str:
    if score >= 0.80 or score <= 0.20:
        return "alta"
    if score >= 0.65 or score <= 0.35:
        return "media"
    return "baixa"


def _verdict(score: float) -> str:
    if score >= 0.70:
        return "IA"
    if score <= 0.40:
        return "Humano"
    return "Inconclusivo"


def _classify_image(image_bytes: bytes) -> dict:
    meta = check_ai_metadata(image_bytes)

    img_pil = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    result  = get_image_classifier()(img_pil)[0]
    label   = result["label"]
    score   = result["score"]

    score_modelo = round(score if _is_ai_image_label(label) else 1.0 - score, 4)
    score_fft    = image_fft_analyzer.analyze_fft(image_bytes)

    nparr = np.frombuffer(image_bytes, np.uint8)
    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

    if img_cv is not None and is_cartoon(img_cv):
        score_estilo = analisar_estilo_cartoon(img_cv)
        score_flat   = None
        # modelo e FFT são pouco confiáveis para cartoon — estilo domina
        score_final  = round((score_modelo * 0.05) + (score_fft * 0.05) + (score_estilo * 0.90), 4)
        modo         = "cartoon"
    elif img_cv is not None and is_flat_design(img_cv):
        score_estilo = None
        score_flat   = analisar_flat_design(img_cv)
        score_final  = round((score_modelo * 0.10) + (score_fft * 0.15) + (score_flat * 0.75), 4)
        modo         = "flat_design"
    else:
        score_estilo = None
        score_flat   = None
        if score_modelo >= 0.70:
            # Classificador já concluiu: FFT é ruído para imagens editadas/comprimidas
            score_final = score_modelo
        elif score_modelo <= 0.40:
            # Classificador já concluiu o contrário: idem
            score_final = score_modelo
        else:
            # Zona inconclusiva: FFT entra como desempate
            score_final = round((score_modelo * 0.55) + (score_fft * 0.45), 4)
        modo = "fotorrealista"

    if meta["encontrado"]:
        score_final = max(score_final, 0.95)
        print(f"[DEBUG META] indicadores={meta['indicadores']} score_override={score_final}", flush=True)

    aviso = None
    if score_modelo >= 0.70 and score_fft < 0.40:
        aviso = "FFT divergente ignorado — classificador concluiu com alta confiança"
    elif abs(score_modelo - score_fft) > 0.5:
        aviso = "Detectores divergentes: resultado pode ser impreciso"

    return {
        "modelo_usado":     "umm-maybe+fft+style+flat+meta",
        "veredicto":        _verdict(score_final),
        "probabilidade_ia": score_final,
        "confianca":        _confidence(score_final),
        "detalhes": {
            "score_modelo":        score_modelo,
            "score_fft":           score_fft,
            "score_estilo":        score_estilo,
            "score_flat":          score_flat,
            "modo_detectado":      modo,
            "metadata_ia":         meta["encontrado"],
            "metadata_indicadores": meta["indicadores"] if meta["encontrado"] else [],
        },
        "aviso": aviso,
    }


# ─── Schemas ──────────────────────────────────────────────────────────────────

class TextRequest(BaseModel):
    text:  str
    model: str = "detecting_ai"


# ─── Endpoints ────────────────────────────────────────────────────────────────

@app.get("/health")
def health():
    return {"status": "ok"}


@app.get("/models")
def list_models():
    return [
        {
            "id":        "detecting_ai",
            "nome":      "Detecting-AI (pt)",
            "descricao": "Detector de texto em português treinado para identificar conteúdo gerado por IA",
        },
        {
            "id":        "bert",
            "nome":      "BERT Português (pseudo-perplexidade)",
            "descricao": "Análise via pseudo-perplexidade com BERT para detectar padrões estatísticos de IA",
        },
        {
            "id":        "image_detector",
            "nome":      "umm-maybe + FFT + Style + Flat",
            "descricao": "Classificador base + análise de frequência + análise de estilo cartoon + análise de flat design. Detecta fotorrealismo, cartoon e ilustrações educacionais gerados por IA.",
        },
    ]


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
    except ValueError as e:
        raise HTTPException(status_code=422, detail=str(e))
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
