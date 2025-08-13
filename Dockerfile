FROM php:8.3-apache

WORKDIR /var/www/html

COPY web/ /var/www/html/

RUN php -v && ldd --version

EXPOSE 80