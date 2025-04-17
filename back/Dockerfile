FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libicu-dev zip libzip-dev \
    && docker-php-ext-install intl zip pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
