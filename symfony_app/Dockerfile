FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev libzip-dev zip \
    && docker-php-ext-install intl pdo pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

WORKDIR /var/www/html

EXPOSE 8000
