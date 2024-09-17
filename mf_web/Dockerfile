FROM php:8.3-apache

RUN apt-get update

# COPY php_cgi.ini /etc/php81/conf.d/

# Install Composer
WORKDIR /usr/bin
COPY install-composer.sh .
RUN ./install-composer.sh
RUN rm install-composer.sh
RUN ln composer.phar composer

WORKDIR /var/www/html/