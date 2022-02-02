FROM php:8.1-apache

RUN docker-php-ext-install pdo_mysql
RUN a2enmod rewrite

COPY . /var/www
COPY ./public /var/www/html

RUN chmod -R 777 /var/www/storage/logs
RUN chmod -R 777 /var/www/storage/framework
