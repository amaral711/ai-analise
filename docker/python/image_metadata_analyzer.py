import io

from PIL import Image

_AI_KEYWORDS = [
    "stable diffusion", "midjourney", "dall-e", "dalle", "openai",
    "ai generated", "ai-generated", "artificial intelligence",
    "steps:", "cfg scale", "sampler", "model hash",
    "dreamshaper", "flux", "comfyui", "automatic1111",
    "novelai", "invokeai", "adobe firefly", "leonardo ai",
    "canva ai", "bing image creator", "ideogram", "playground ai",
]


def check_ai_metadata(image_bytes: bytes) -> dict:
    """
    Inspeciona metadados da imagem em busca de indicadores de geração por IA.
    Cobre: chunks PNG (tEXt/iTXt/zTXt), EXIF tag Software (305).
    Retorna encontrado=True + lista de indicadores se algo for detectado.
    """
    try:
        img = Image.open(io.BytesIO(image_bytes))
    except Exception:
        return {"encontrado": False, "score": None, "indicadores": []}

    indicadores = []

    # PNG text chunks
    for key, value in img.info.items():
        if not isinstance(value, str):
            continue
        combined = (key + " " + value).lower()
        for kw in _AI_KEYWORDS:
            if kw in combined:
                indicadores.append(f"{key}: {value[:120].strip()}")
                break

    # EXIF tag 305 = Software
    try:
        exif = img._getexif() or {}
        software = exif.get(305, "")
        if isinstance(software, str) and software:
            sw_lower = software.lower()
            for kw in _AI_KEYWORDS:
                if kw in sw_lower:
                    indicadores.append(f"Software EXIF: {software[:120].strip()}")
                    break
    except Exception:
        pass

    encontrado = len(indicadores) > 0
    return {
        "encontrado": encontrado,
        "score": 1.0 if encontrado else None,
        "indicadores": indicadores,
    }
