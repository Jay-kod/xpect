FROM composer:2

COPY docker/entrypoint.sh /usr/local/bin/xpect-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/xpect-entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["sh", "/usr/local/bin/xpect-entrypoint"]
