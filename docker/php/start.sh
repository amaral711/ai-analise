#!/bin/bash
set -e

cd /var/www/html

echo "→ Caching config/routes/views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "→ Running migrations..."
php artisan migrate --force

echo "→ Linking storage..."
php artisan storage:link --force

echo "→ Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
