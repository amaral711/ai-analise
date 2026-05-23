import bert_detector
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

app = FastAPI()


@app.on_event("startup")
def preload_models():
    bert_detector.get_bert_model()


class TextRequest(BaseModel):
    text: str


@app.get("/health")
def health():
    return {"status": "ok"}


@app.post("/detect/text")
def detect_text(body: TextRequest):
    if not body.text or not body.text.strip():
        raise HTTPException(status_code=400, detail="Texto não pode ser vazio.")

    try:
        result = bert_detector.analyze(body.text)
    except Exception as e:
        raise HTTPException(status_code=422, detail=f"Erro ao processar texto: {str(e)}")

    return result
