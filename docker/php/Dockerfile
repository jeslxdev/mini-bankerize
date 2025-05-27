FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    zip \
    git \
    curl \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
