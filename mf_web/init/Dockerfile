FROM sebp/lighttpd

RUN apk --update add php81 php81-cgi php81-phar php81-mbstring php81-session php81-pdo php81-pdo_mysql

RUN ln /usr/bin/php81 /opt/php
ENV PATH="/opt:$PATH"

COPY php_cgi.ini /etc/php81/conf.d/
COPY --chmod=300 install-composer.sh /opt/
RUN /opt/install-composer.sh
RUN rm /opt/install-composer.sh
RUN mv /composer.phar /opt/composer

RUN chown lighttpd:lighttpd /var/log/lighttpd

WORKDIR /var/www/localhost/htdocs/

CMD ["start.sh"]