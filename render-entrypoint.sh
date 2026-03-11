#!/usr/bin/env bash
set -e

# Change Nginx to listen on port 10000 (Render default)
sed -i 's/listen 80;/listen 10000;/g' /etc/nginx/sites-available/default

# Change fastcgi_pass to localhost since PHP-FPM is in the same container
sed -i 's/fastcgi_pass app:9000;/fastcgi_pass 127.0.0.1:9000;/g' /etc/nginx/sites-available/default

# Clear cached config/routes to ensure fresh env vars are read
cd /var/www/html
php artisan config:clear 2>&1 || true
php artisan route:clear 2>&1 || true
php artisan view:clear 2>&1 || true

# Generate APP_KEY if not set or invalid
if [ -z "$APP_KEY" ] || [ ${#APP_KEY} -lt 10 ]; then
    echo "[Render] APP_KEY not set or too short, generating..."
    php artisan key:generate --force --no-interaction 2>&1 || true
fi

# Debug: show key length (not the key itself)
php -r "echo '[Render] APP_KEY length: ' . strlen(env('APP_KEY', getenv('APP_KEY') ?: '')) . PHP_EOL;" 2>&1 || true

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
