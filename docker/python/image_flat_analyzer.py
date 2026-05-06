import cv2
import numpy as np


def is_flat_design(img_array: np.ndarray) -> bool:
    """
    Detecta se a imagem é flat design / ilustração educacional.
    Critérios: baixa variação de cor por bloco + alta densidade de bordas retas.
    """
    hsv = cv2.cvtColor(img_array, cv2.COLOR_BGR2HSV)
    saturacao = hsv[:, :, 1]

    h, w = saturacao.shape
    block_size = h // 8
    if block_size == 0:
        return False

    variacoes = []
    medias_sat = []
    for i in range(0, h - block_size, block_size):
        for j in range(0, w - block_size, block_size):
            bloco = saturacao[i:i+block_size, j:j+block_size]
            variacoes.append(np.std(bloco))
            medias_sat.append(bloco.mean())

    variacao_media   = np.mean(variacoes)
    saturacao_media  = saturacao.mean()
    uniformidade_sat = np.std(medias_sat)  # baixo = sat uniforme entre regiões = flat design

    # uniformidade_sat < 30 exclui fotos reais (sat varia ~44 entre blocos)
    # flat design IA tem sat consistente entre regiões (~17)
    return saturacao_media > 15 and variacao_media < 50 and uniformidade_sat < 30


def analisar_flat_design(img_array: np.ndarray) -> float:
    """
    Retorna score 0.0-1.0 de probabilidade de ser flat design gerado por IA.
    """
    gray = cv2.cvtColor(img_array, cv2.COLOR_BGR2GRAY)
    hsv  = cv2.cvtColor(img_array, cv2.COLOR_BGR2HSV)
    h, w = gray.shape

    # --- Métrica 1: Uniformidade de cor por bloco ---
    block_size = max(h // 16, 8)
    variacoes_cor = []
    for i in range(0, h - block_size, block_size):
        for j in range(0, w - block_size, block_size):
            bloco = gray[i:i+block_size, j:j+block_size]
            variacoes_cor.append(np.std(bloco))
    # 120 ao invés de 80: flat design com texto/ícones tem variação natural de bloco ~48
    score_cor = 1.0 - min(np.mean(variacoes_cor) / 120.0, 1.0)

    # --- Métrica 2: Densidade de texto (bordas verticais e horizontais regulares) ---
    edges = cv2.Canny(gray, 50, 150)
    linhas_h = cv2.reduce(edges, 1, cv2.REDUCE_AVG).flatten()
    linhas_v = cv2.reduce(edges, 0, cv2.REDUCE_AVG).flatten()
    # 70 ao invés de 50: projeções de borda em flat design com texto têm std ~21
    regularidade_h = 1.0 - min(np.std(linhas_h) / 70.0, 1.0)
    regularidade_v = 1.0 - min(np.std(linhas_v) / 70.0, 1.0)
    score_texto = (regularidade_h + regularidade_v) / 2.0

    # --- Métrica 3: Regularidade de bordas em macro (imagem reduzida) ---
    small = cv2.resize(gray, (64, 64))
    edges_small = cv2.Canny(small, 30, 100)
    densidade_bordas = edges_small.mean() / 255.0
    score_macro = 1.0 - abs(densidade_bordas - 0.3) / 0.3
    score_macro = max(0.0, min(score_macro, 1.0))

    # --- Métrica 4: Uniformidade de saturação entre regiões ---
    saturacao = hsv[:, :, 1]
    bloco_s = max(h // 8, 8)
    saturacoes_medias = []
    for i in range(0, h - bloco_s, bloco_s):
        for j in range(0, w - bloco_s, bloco_s):
            bloco = saturacao[i:i+bloco_s, j:j+bloco_s]
            saturacoes_medias.append(bloco.mean())
    score_saturacao = 1.0 - min(np.std(saturacoes_medias) / 60.0, 1.0)

    score_final = (
        score_cor       * 0.15 +
        score_texto     * 0.20 +
        score_macro     * 0.45 +
        score_saturacao * 0.20
    )

    return round(min(score_final, 1.0), 4)
