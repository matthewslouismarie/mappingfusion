FROM php:8.3-apache

RUN apt-get update

# Install Composer
WORKDIR /usr/bin
COPY install-composer.sh .
RUN ./install-composer.sh
RUN rm install-composer.sh
RUN ln composer.phar composer

# Enable Apache Rewrite module
RUN a2enmod rewrite

# Enable PDO
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html/