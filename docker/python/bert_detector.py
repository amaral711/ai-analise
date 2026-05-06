import math

import numpy as np
import torch
from transformers import AutoModelForMaskedLM, AutoTokenizer

MODEL_NAME = "neuralmind/bert-large-portuguese-cased"

_tokenizer = None
_model     = None


def get_bert_model():
    global _tokenizer, _model
    if _tokenizer is None:
        print(f"Carregando modelo BERT {MODEL_NAME}...")
        _tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
        _model     = AutoModelForMaskedLM.from_pretrained(MODEL_NAME)
        _model.eval()
        print("Modelo BERT carregado.")
    return _tokenizer, _model


def _sentence_pseudo_perplexity(sentence: str) -> float | None:
    tokenizer, model = get_bert_model()

    tokens = tokenizer.encode(sentence, add_special_tokens=True, truncation=True, max_length=512)
    if len(tokens) <= 2:
        return None

    log_probs = []

    for i in range(1, len(tokens) - 1):
        masked = list(tokens)
        masked[i] = tokenizer.mask_token_id

        input_ids = torch.tensor([masked])

        with torch.no_grad():
            logits   = model(input_ids).logits[0, i]
            log_prob = torch.log_softmax(logits, dim=-1)[tokens[i]].item()
            log_probs.append(log_prob)

    if not log_probs:
        return None

    return math.exp(-sum(log_probs) / len(log_probs))


def analyze(text: str) -> dict:
    sentences = [s.strip() for s in text.split(".") if len(s.strip()) >= 10]

    if len(sentences) < 3:
        return {
            "ai_score":  0.5,
            "model":     MODEL_NAME,
            "confianca": "baixa",
            "veredicto": "Inconclusivo",
            "warning":   "texto muito curto",
        }

    perplexities = [p for s in sentences if (p := _sentence_pseudo_perplexity(s)) is not None]

    if not perplexities:
        return {
            "ai_score":  0.5,
            "model":     MODEL_NAME,
            "confianca": "baixa",
            "veredicto": "Inconclusivo",
            "warning":   "texto muito curto",
        }

    avg_perp   = float(np.mean(perplexities))
    burstiness = float(np.std(perplexities))

    if avg_perp < 50 and burstiness < 10:
        ai_prob   = 0.90
        confianca = "alta"
    elif avg_perp < 80 and burstiness < 15:
        ai_prob   = 0.65
        confianca = "media"
    else:
        ai_prob   = 0.20
        confianca = "media"

    return {
        "ai_score":       round(ai_prob, 2),
        "model":          MODEL_NAME,
        "confianca":      confianca,
        "veredicto":      "IA" if ai_prob >= 0.5 else "Humano",
        "avg_perplexity": round(avg_perp, 2),
        "burstiness":     round(burstiness, 2),
    }
