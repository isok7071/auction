FROM php:8.5-fpm-alpine

WORKDIR /var/www/html

RUN set -eux; \
    apk add --no-cache \
        git \
        icu-libs \
        libzip \
        oniguruma \
        unzip; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev; \
    docker-php-ext-install -j"$(nproc)" \
        intl \
        pcntl \
        pdo_mysql \
        zip; \
    apk del --no-network .build-deps

RUN git config --system --add safe.directory /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-fordewind.ini
COPY docker/php/entrypoint.sh /usr/local/bin/fordewind-entrypoint
RUN chmod +x /usr/local/bin/fordewind-entrypoint

ENTRYPOINT ["fordewind-entrypoint"]
CMD ["php-fpm"]
