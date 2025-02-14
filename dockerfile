FROM php:8.1.10-fpm

# Install MySQL extension
RUN docker-php-ext-install mysqli pdo_mysql
