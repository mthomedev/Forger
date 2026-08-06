FROM php:8.4-fpm

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# opcache: keeps compiled PHP in memory between requests
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libfreetype6-dev \
        ffmpeg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql zip bcmath opcache gd exif \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

# no dev dependencies (phpunit etc.) and an optimized class map
RUN composer install --no-dev --optimize-autoloader

# Render routes traffic to the port exposed via $PORT (see render.yaml startCommand)
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
