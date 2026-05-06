import numpy as np
import cv2


def analyze_fft(image_bytes: bytes) -> float:
    nparr = np.frombuffer(image_bytes, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_GRAYSCALE)

    if img is None:
        raise ValueError("Imagem inválida ou corrompida")

    f = np.fft.fft2(img)
    fshift = np.fft.fftshift(f)
    magnitude = 20 * np.log(np.abs(fshift) + 1)

    h, w = magnitude.shape
    cy, cx = h // 2, w // 2

    alta_freq  = magnitude[cy - 10:cy + 10, cx - 10:cx + 10].mean()
    baixa_freq = magnitude[:10, :10].mean()

    ratio    = alta_freq / (baixa_freq + 1e-8)
    score_ia = round(1.0 - min(ratio / 3.0, 1.0), 4)

    return score_ia
