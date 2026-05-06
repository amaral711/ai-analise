import cv2
import numpy as np


def is_cartoon(img_array: np.ndarray) -> bool:
    hsv = cv2.cvtColor(img_array, cv2.COLOR_BGR2HSV)
    saturacao_media = hsv[:, :, 1].mean()
    cores_unicas = len(np.unique(img_array.reshape(-1, 3), axis=0))
    return saturacao_media > 100 and cores_unicas < 50000


def analisar_estilo_cartoon(img_array: np.ndarray) -> float:
    gray = cv2.cvtColor(img_array, cv2.COLOR_BGR2GRAY)

    # Uniformidade das regiões de preenchimento (fill areas)
    # IA gera fills planos e uniformes; máscaramos as bordas e medimos o std restante
    edges = cv2.Canny(gray, 50, 150)
    mascara_fill = edges == 0
    if mascara_fill.sum() > 0:
        # 120 em vez de 60: AI moderna (DALL-E 3, Midjourney) gera cartoon com shading — std natural ~60-80
        score_fill = 1.0 - min(np.std(gray[mascara_fill]) / 120.0, 1.0)
    else:
        score_fill = 0.5

    # Simetria horizontal — IA tende a ser mais simétrica
    h, w = gray.shape
    esquerda = gray[:, :w // 2]
    direita = np.fliplr(gray[:, w // 2:])
    min_w = min(esquerda.shape[1], direita.shape[1])
    simetria = 1.0 - (
        np.mean(np.abs(
            esquerda[:, :min_w].astype(float) - direita[:, :min_w].astype(float)
        )) / 255.0
    )

    # Concentração de paleta de cores — IA usa paletas discretas e limitadas
    # Quantiza hue em 16 bins e mede entropia; entropia baixa = paleta concentrada = mais IA
    small = cv2.resize(img_array, (128, 128))
    hsv_small = cv2.cvtColor(small, cv2.COLOR_BGR2HSV)
    hue_q = (hsv_small[:, :, 0] // 16).flatten()
    hist = np.bincount(hue_q, minlength=16).astype(float)
    hist_norm = hist / hist.sum()
    entropy = -np.sum(hist_norm[hist_norm > 0] * np.log2(hist_norm[hist_norm > 0]))
    score_paleta = 1.0 - min(entropy / 4.0, 1.0)

    return round(min((score_fill * 0.45) + (simetria * 0.30) + (score_paleta * 0.25), 1.0), 4)
