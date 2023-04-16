FROM sebp/lighttpd

RUN apk --update add php81-cgi

COPY php.ini /etc/php81

RUN chown lighttpd:lighttpd /var/log/lighttpd

CMD ["start.sh"]