FROM python:3.11-slim

WORKDIR /app

# torch CPU-only primeiro (menor imagem)
RUN pip install --no-cache-dir torch --index-url https://download.pytorch.org/whl/cpu

COPY docker/python/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY docker/python/app.py .

# Diretório de cache dos modelos HuggingFace (montado como volume)
ENV HF_HOME=/cache/huggingface
ENV TRANSFORMERS_CACHE=/cache/huggingface

EXPOSE 8001

CMD sh -c "uvicorn app:app --host 0.0.0.0 --port ${PORT:-8001}"
