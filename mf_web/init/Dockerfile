FROM sebp/lighttpd

RUN apk --update add php8 php8-cgi php8-openssl php8-phar php8-mbstring php8-curl php8-intl php8-tokenizer

COPY php.ini /etc/php8
COPY --chmod=300 install-composer.sh /opt/
RUN /opt/install-composer.sh
RUN rm /opt/install-composer.sh
RUN mv /composer.phar /opt/composer
ENV PATH="/opt:$PATH"

RUN chown lighttpd:lighttpd /var/log/lighttpd

WORKDIR /var/www/localhost/htdocs/

CMD ["start.sh"]