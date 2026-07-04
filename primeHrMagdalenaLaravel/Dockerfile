FROM node:20-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

FROM php:8.4-cli
WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev unzip zip libxml2-dev libcurl4-openssl-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring curl xml zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000
CMD php artisan migrate --force && php artisan optimize && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
