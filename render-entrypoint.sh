#!/usr/bin/env bash
set -e

# Change Nginx to listen on port 10000 (Render default)
sed -i 's/listen 80;/listen 10000;/g' /etc/nginx/sites-available/default

# Change fastcgi_pass to localhost since PHP-FPM is in the same container
sed -i 's/fastcgi_pass app:9000;/fastcgi_pass 127.0.0.1:9000;/g' /etc/nginx/sites-available/default

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
