from transformers import pipeline

MODEL_NAME = "Detecting-ai/pt-ai-detector"

_classifier = None


def get_classifier():
    global _classifier
    if _classifier is None:
        print(f"Carregando modelo {MODEL_NAME}...")
        _classifier = pipeline("text-classification", model=MODEL_NAME, device=-1)
        print("Modelo detecting-ai carregado.")
    return _classifier


def _is_ai_label(label: str) -> bool:
    label = label.lower()
    return any(k in label for k in ("ai", "artificial", "generated", "machine", "fake", "label_1"))


def analyze(text: str) -> dict:
    result = get_classifier()(text, truncation=True, max_length=512)[0]
    label  = result["label"]
    score  = result["score"]

    ai_score = score if _is_ai_label(label) else 1.0 - score

    if score >= 0.85:
        confianca = "alta"
    elif score >= 0.65:
        confianca = "media"
    else:
        confianca = "baixa"

    return {
        "ai_score": round(ai_score, 3),
        "model":    MODEL_NAME,
        "confianca": confianca,
    }
