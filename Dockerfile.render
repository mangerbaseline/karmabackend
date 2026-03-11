# Use PHP 8.3 FPM as base
FROM php:8.3-fpm

# Install system dependencies and Nginx
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    git unzip zip curl ca-certificates \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for MySQL and Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip gd opcache

# Copy Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Setup Nginx configuration
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
# Note: If docker/nginx/default.conf doesn't exist, we will create a basic one.

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port 10000 (Render's default)
EXPOSE 10000

# Start script
COPY render-entrypoint.sh /usr/local/bin/render-entrypoint.sh
RUN chmod +x /usr/local/bin/render-entrypoint.sh

CMD ["/usr/local/bin/render-entrypoint.sh"]
