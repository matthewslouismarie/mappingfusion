FROM php:8.3-apache

RUN apt update

# Install Composer
RUN apt install git -y
WORKDIR /usr/bin
COPY install-composer.sh .
RUN ./install-composer.sh
RUN rm install-composer.sh
RUN ln composer.phar composer

# Enable Apache Rewrite module
RUN a2enmod rewrite

# Enable PDO
RUN docker-php-ext-install pdo pdo_mysql

# Enable PHP GD
RUN apt install libpng-dev -y
RUN docker-php-ext-install gd

WORKDIR /var/www/html/