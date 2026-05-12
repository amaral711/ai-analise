FROM python:3.11-slim

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends ffmpeg && rm -rf /var/lib/apt/lists/*

# torch CPU-only primeiro (menor imagem)
RUN pip install --no-cache-dir torch --index-url https://download.pytorch.org/whl/cpu

COPY docker/python/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY docker/python/app.py .
COPY docker/python/bert_detector.py .
COPY docker/python/detecting_ai.py .
COPY docker/python/image_fft_analyzer.py .
COPY docker/python/image_style_analyzer.py .
COPY docker/python/image_flat_analyzer.py .
COPY docker/python/image_metadata_analyzer.py .
COPY docker/python/analyze_image.py .

# Diretório de cache dos modelos HuggingFace (montado como volume)
ENV HF_HOME=/cache/huggingface
ENV TRANSFORMERS_CACHE=/cache/huggingface

EXPOSE 8001

CMD sh -c "uvicorn app:app --host 0.0.0.0 --port ${PORT:-8001}"
