#!/usr/bin/env python3
"""
Detector de imagens geradas ou manipuladas por IA.
4 camadas: EXIF, ruído por região, FFT, iluminação.
Suporta detecção de imagens híbridas (foto real + elementos gerados).
"""

import sys
import os
import warnings
warnings.filterwarnings("ignore")

import cv2
import numpy as np
from PIL import Image
from PIL.ExifTags import TAGS
from scipy.stats import pearsonr

# ---------------------------------------------------------------------------
# Pesos do score final (devem somar 1.0)
# Iluminação ocupa o slot "ML" por ser a métrica mais discriminativa sem modelo
# ---------------------------------------------------------------------------
WEIGHT_LIGHTING = 0.45
WEIGHT_EXIF     = 0.25
WEIGHT_NOISE    = 0.20
WEIGHT_FFT      = 0.10

# ---------------------------------------------------------------------------
# Limiares configuráveis
# ---------------------------------------------------------------------------

# EXIF — campos esperados em imagens reais de câmera
EXIF_CRITICAL_FIELDS = [
    "Make", "Model", "DateTime", "GPSInfo", "ISOSpeedRatings",
    "FocalLength", "ExposureTime", "FNumber", "Flash",
    "MeteringMode", "WhiteBalance", "LightSource",
]

# RUÍDO — CV abaixo deste valor indica uniformidade artificial
NOISE_CV_THRESHOLD = 0.08

# FFT — limites de energia esperados em altas frequências para fotos reais
FFT_HIGH_FREQ_MIN_RATIO = 0.20  # abaixo = imagem excessivamente suave
FFT_DIFFUSION_THRESHOLD = 0.30  # acima  = artefato de modelo de difusão

# ILUMINAÇÃO — correlação de Pearson acima deste valor entre faixas = suspeito
LIGHTING_CORRELATION_THRESHOLD = 0.90

# DIVERGÊNCIA — diferença de pontos que caracteriza detectores divergentes
DIVERGENCE_THRESHOLD = 40

# Score mínimo de um único detector para forçar veredicto SUSPEITO
HIGH_ALERT_THRESHOLD = 85

# ---------------------------------------------------------------------------
# Camada 1: Metadados EXIF
# ---------------------------------------------------------------------------

def analyze_exif(image_path: str) -> dict:
    """
    Penaliza ausência de campos EXIF críticos de câmera.
    Imagens geradas por IA costumam não ter Make/Model/ISO/GPS/FocalLength.
    """
    found, missing = [], []

    try:
        img = Image.open(image_path)
        raw_exif = img._getexif() or {}
        exif_by_name = {TAGS.get(k, k): v for k, v in raw_exif.items()}
    except Exception:
        exif_by_name = {}

    for field in EXIF_CRITICAL_FIELDS:
        if field in exif_by_name and exif_by_name[field] not in (None, "", b""):
            found.append(field)
        else:
            missing.append(field)

    presence_ratio = len(found) / len(EXIF_CRITICAL_FIELDS)
    score = int((1 - presence_ratio) * 100)

    return {
        "found_count": len(found),
        "total_critical": len(EXIF_CRITICAL_FIELDS),
        "found_fields": found,
        "missing_fields": missing,
        "score": score,
    }


# ---------------------------------------------------------------------------
# Camada 2: Análise de ruído por região (grid 3×3)
# ---------------------------------------------------------------------------

def analyze_noise(img_gray: np.ndarray) -> dict:
    """
    Mede o ruído de 9 regiões via Laplaciano e calcula o coeficiente de variação.
    CV muito baixo = ruído uniforme demais = típico de geração sintética.
    Bom para detectar colagens onde a região gerada tem textura diferente.
    """
    h, w = img_gray.shape
    cell_h, cell_w = h // 3, w // 3
    noise_levels = []

    for row in range(3):
        for col in range(3):
            region = img_gray[
                row * cell_h:(row + 1) * cell_h,
                col * cell_w:(col + 1) * cell_w,
            ].astype(np.float64)
            lap = cv2.Laplacian(region, cv2.CV_64F)
            noise_levels.append(float(np.std(lap)))

    mean_noise = np.mean(noise_levels)
    std_noise  = np.std(noise_levels)
    cv = (std_noise / mean_noise) if mean_noise > 1e-9 else 0.0

    # CV baixo → ruído uniforme → score alto de suspeita
    if cv >= NOISE_CV_THRESHOLD:
        score = 0
    else:
        score = int((1 - cv / NOISE_CV_THRESHOLD) * 100)

    return {
        "region_noise_levels": [round(n, 4) for n in noise_levels],
        "mean_noise": round(mean_noise, 4),
        "coefficient_of_variation": round(cv, 4),
        "threshold": NOISE_CV_THRESHOLD,
        "score": score,
    }


# ---------------------------------------------------------------------------
# Camada 3: Análise de frequência via FFT
# ---------------------------------------------------------------------------

def analyze_fft(img_gray: np.ndarray) -> dict:
    """
    Modelos de difusão (SD, Midjourney) produzem padrões específicos na
    distribuição de energia espectral: ou excessivamente suaves (pouca alta-freq)
    ou com artefatos em bandas específicas (muita alta-freq).
    Peso menor (0.10) pois é pouco discriminativo em imagens comprimidas/editadas.
    """
    f = np.fft.fft2(img_gray.astype(np.float64))
    fshift = np.fft.fftshift(f)
    magnitude = np.abs(fshift) ** 2

    h, w = magnitude.shape
    cy, cx = h // 2, w // 2
    radius = int(min(h, w) * 0.25)  # fronteira: 25% da menor dimensão

    Y, X = np.ogrid[:h, :w]
    dist = np.sqrt((X - cx) ** 2 + (Y - cy) ** 2)

    energy_low  = float(magnitude[dist <= radius].sum())
    energy_high = float(magnitude[dist > radius].sum())
    total       = energy_low + energy_high
    high_ratio  = energy_high / total if total > 0 else 0.0

    diffusion_detected = (
        high_ratio > FFT_DIFFUSION_THRESHOLD or
        high_ratio < FFT_HIGH_FREQ_MIN_RATIO
    )

    if high_ratio < FFT_HIGH_FREQ_MIN_RATIO:
        score = int((1 - high_ratio / FFT_HIGH_FREQ_MIN_RATIO) * 80)
    elif high_ratio > FFT_DIFFUSION_THRESHOLD:
        excess = (high_ratio - FFT_DIFFUSION_THRESHOLD) / (1 - FFT_DIFFUSION_THRESHOLD)
        score = int(excess * 80)
    else:
        score = 0

    return {
        "high_freq_ratio": round(high_ratio * 100, 2),
        "diffusion_pattern_detected": diffusion_detected,
        "score": score,
    }


# ---------------------------------------------------------------------------
# Camada 4: Inconsistência de iluminação entre regiões
# ---------------------------------------------------------------------------

def analyze_lighting(img_gray: np.ndarray) -> dict:
    """
    Correlação alta de histogramas de luminância entre faixas horizontais distintas
    indica iluminação sintética uniforme — padrão de modelos generativos.
    Ocupa o slot de maior peso (0.45) por ser a métrica mais discriminativa
    sem depender de um classificador ML externo.
    """
    h = img_gray.shape[0]
    bands = [
        img_gray[: h // 3],
        img_gray[h // 3 : 2 * h // 3],
        img_gray[2 * h // 3 :],
    ]

    histograms = []
    for band in bands:
        hist, _ = np.histogram(band.flatten(), bins=256, range=(0, 256))
        histograms.append(hist.astype(np.float64))

    def safe_pearson(a: np.ndarray, b: np.ndarray) -> float:
        if np.std(a) < 1e-9 or np.std(b) < 1e-9:
            return 1.0
        r, _ = pearsonr(a, b)
        return float(r)

    corr_top_mid = safe_pearson(histograms[0], histograms[1])
    corr_mid_bot = safe_pearson(histograms[1], histograms[2])

    suspicious_pairs = sum([
        corr_top_mid > LIGHTING_CORRELATION_THRESHOLD,
        corr_mid_bot > LIGHTING_CORRELATION_THRESHOLD,
    ])
    score = int((suspicious_pairs / 2) * 100)

    return {
        "correlation_top_mid": round(corr_top_mid, 4),
        "correlation_mid_bottom": round(corr_mid_bot, 4),
        "threshold": LIGHTING_CORRELATION_THRESHOLD,
        "score": score,
    }


# ---------------------------------------------------------------------------
# Lógica de divergência e score ponderado
# ---------------------------------------------------------------------------

def compute_verdict(exif: dict, noise: dict, fft: dict, lighting: dict) -> dict:
    """
    Aplica pesos e detecta divergência entre detectores.

    Regra de imagem híbrida: quando detectores divergem >= DIVERGENCE_THRESHOLD
    pontos, a média simples mascara o sinal. Em vez disso:
    - Se qualquer detector atingiu HIGH_ALERT_THRESHOLD, o veredicto é SUSPEITO.
    - O detector de score mais alto é apontado como responsável pelo veredicto.
    """
    scores = {
        "EXIF":      exif["score"],
        "Ruído":     noise["score"],
        "FFT":       fft["score"],
        "Iluminação": lighting["score"],
    }

    weighted = round(
        scores["Iluminação"] * WEIGHT_LIGHTING +
        scores["EXIF"]       * WEIGHT_EXIF     +
        scores["Ruído"]      * WEIGHT_NOISE     +
        scores["FFT"]        * WEIGHT_FFT,
        1,
    )

    max_score   = max(scores.values())
    min_score   = min(scores.values())
    spread      = max_score - min_score
    divergent   = spread >= DIVERGENCE_THRESHOLD
    high_alert  = max_score >= HIGH_ALERT_THRESHOLD
    leading_det = max(scores, key=scores.__getitem__)

    # Veredicto final
    if high_alert or weighted >= 70:
        if divergent:
            verdict = "IMAGEM HÍBRIDA PROVÁVEL"
        else:
            verdict = "ALTA SUSPEITA DE GERAÇÃO/MANIPULAÇÃO POR IA"
    elif weighted >= 40:
        verdict = "SUSPEITA MODERADA — REVISÃO MANUAL RECOMENDADA"
    else:
        verdict = "BAIXA SUSPEITA — PROVAVELMENTE IMAGEM REAL"

    return {
        "weighted_score": weighted,
        "scores": scores,
        "divergent": divergent,
        "spread": spread,
        "high_alert": high_alert,
        "leading_detector": leading_det,
        "verdict": verdict,
    }


# ---------------------------------------------------------------------------
# Relatório
# ---------------------------------------------------------------------------

def print_report(
    image_path: str,
    exif: dict,
    noise: dict,
    fft: dict,
    lighting: dict,
    result: dict,
) -> None:
    print("\n=== RELATÓRIO DE ANÁLISE ===")
    print(f"Arquivo: {os.path.basename(image_path)}")

    print("\n[EXIF]")
    print(f"  Campos encontrados: {exif['found_count']}/{exif['total_critical']}")
    if exif["missing_fields"]:
        print(f"  Ausentes: {', '.join(exif['missing_fields'])}")
    if exif["found_fields"]:
        print(f"  Presentes: {', '.join(exif['found_fields'])}")
    print(f"  Score: {exif['score']}/100 suspeito")

    print("\n[RUÍDO POR REGIÃO]")
    print(f"  Coeficiente de variação: {noise['coefficient_of_variation']} (limiar: {noise['threshold']})")
    print(f"  Score: {noise['score']}/100 suspeito")

    print("\n[FREQUÊNCIA FFT]")
    print(f"  Energia em altas frequências: {fft['high_freq_ratio']}%")
    print(f"  Padrão de difusão detectado: {'SIM' if fft['diffusion_pattern_detected'] else 'NÃO'}")
    print(f"  Score: {fft['score']}/100 suspeito")

    print("\n[ILUMINAÇÃO]")
    print(f"  Correlação topo-meio: {lighting['correlation_top_mid']}")
    print(f"  Correlação meio-baixo: {lighting['correlation_mid_bottom']}")
    print(f"  Score: {lighting['score']}/100 suspeito")

    print("\n[DIVERGÊNCIA]")
    if result["divergent"]:
        print(f"  Detectores divergentes: SIM (diferença: {result['spread']} pontos)")
        reason = "score >= 85 — evidência forte isolada" if result["high_alert"] else "maior score individual"
        print(f"  Detector que puxou o veredicto: {result['leading_detector']} "
              f"(score {result['scores'][result['leading_detector']]}) — {reason}")
    else:
        print(f"  Detectores divergentes: NÃO (diferença: {result['spread']} pontos)")

    print("\n===========================")
    print(f"SCORE PONDERADO: {result['weighted_score']}/100")
    print(f"VEREDICTO: {result['verdict']}")
    print("===========================\n")


# ---------------------------------------------------------------------------
# Entrypoint
# ---------------------------------------------------------------------------

def main() -> None:
    if len(sys.argv) < 2:
        print("Uso: python analyze_image.py <caminho/para/imagem>")
        sys.exit(1)

    image_path = sys.argv[1]

    if not os.path.isfile(image_path):
        print(f"Erro: arquivo não encontrado — '{image_path}'")
        sys.exit(1)

    img_bgr = cv2.imread(image_path)
    if img_bgr is None:
        print(
            f"Erro: não foi possível abrir '{image_path}'. "
            "Formatos suportados: JPEG, PNG, BMP, TIFF, WEBP."
        )
        sys.exit(1)

    img_gray = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2GRAY)

    if img_gray.shape[0] < 9 or img_gray.shape[1] < 9:
        print("Erro: imagem muito pequena para análise (mínimo 9×9 pixels).")
        sys.exit(1)

    exif_result     = analyze_exif(image_path)
    noise_result    = analyze_noise(img_gray)
    fft_result      = analyze_fft(img_gray)
    lighting_result = analyze_lighting(img_gray)
    verdict_result  = compute_verdict(exif_result, noise_result, fft_result, lighting_result)

    print_report(image_path, exif_result, noise_result, fft_result, lighting_result, verdict_result)


if __name__ == "__main__":
    main()
