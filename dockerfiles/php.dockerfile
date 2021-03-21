FROM php:7.4-fpm-alpine

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html