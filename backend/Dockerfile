FROM php:8.4-fpm
# Use fpm and Nginx

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# opcache: keeps compiled PHP in memory between requests
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
    && docker-php-ext-install pdo_mysql zip bcmath opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

# no dev dependencies (phpunit etc.) and an optimized class map
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000