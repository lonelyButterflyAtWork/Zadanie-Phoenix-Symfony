FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev libzip-dev zip \
    && docker-php-ext-install intl pdo pdo_pgsql opcache

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html/

RUN composer install --no-interaction --prefer-dist

RUN chown -R www-data:www-data var/ \
    && chmod -R 775 var/

COPY apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
