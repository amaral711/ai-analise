FROM php:8.4-fpm

# System deps
RUN apt-get update && apt-get install -y \
    nginx supervisor python3 python3-pip python3-venv \
    nodejs npm \
    libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev \
    zip unzip curl git \
    && docker-php-ext-configure gd \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Python virtualenv + dependências
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
COPY docker/python/requirements.txt /tmp/requirements.txt
RUN pip install --no-cache-dir torch --index-url https://download.pytorch.org/whl/cpu \
    && pip install --no-cache-dir -r /tmp/requirements.txt

# Pré-baixa o modelo HuggingFace na imagem (evita download em runtime)
ENV HF_HOME=/opt/models
RUN python3 -c "from transformers import pipeline; pipeline('image-classification', model='umm-maybe/AI-image-detector', device=-1)"

# Fix nginx: remove includes de sites-enabled e o site padrão do Debian
RUN sed -i '/sites-enabled/d' /etc/nginx/nginx.conf \
    && rm -f /etc/nginx/sites-enabled/default

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# PHP deps (layer separado para cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# JS deps (layer separado para cache)
COPY package.json package-lock.json ./
RUN npm ci

# Copia todo o código fonte
COPY . .

# Build do frontend (vendor já existe, Ziggy resolve)
RUN npm run build

# Finaliza o PHP
RUN composer dump-autoload --optimize

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

COPY docker/nginx/railway.conf /etc/nginx/conf.d/laravel.conf
COPY docker/supervisor/railway.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
