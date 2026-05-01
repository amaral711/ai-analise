FROM php:8.4-fpm-alpine

# System deps
RUN apk add --no-cache \
    nginx supervisor nodejs npm \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev libxml2-dev icu-dev oniguruma-dev \
    autoconf g++ make

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache && \
    pecl install redis && docker-php-ext-enable redis && \
    apk del autoconf g++ make

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

COPY docker/nginx/railway.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/railway.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
