#!/bin/sh

set -eu

if [ "$(id -u)" = "0" ]; then
    mkdir -p \
        /var/www/html/storage/framework/cache/data \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/testing \
        /var/www/html/storage/framework/views \
        /var/www/html/bootstrap/cache
    mkdir -p /tmp/composer-cache
    # These are local-development volumes shared by PHP-FPM and host-UID CLI commands.
    chmod -R a+rwX \
        /var/www/html/storage/framework \
        /var/www/html/bootstrap/cache
    chmod -R a+rwX /tmp/composer-cache
fi

exec "$@"
