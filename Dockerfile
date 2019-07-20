#
# Composer Depencies
#
FROM composer as vendor

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

#
# PHP7
#
FROM php:7.3-alpine

RUN apk add autoconf
RUN apk add alpine-sdk
RUN pecl install swoole && \
    docker-php-ext-enable swoole

RUN docker-php-ext-install opcache && \
    touch /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.interned_strings_buffer=64" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=50000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.revalidate_freq=8" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.huge_code_pages=1" >> /usr/local/etc/php/conf.d/opcache.ini

RUN apk del autoconf
RUN rm -rf /var/cache/apk/*

RUN mkdir -p /app
COPY . /app
COPY --from=vendor /app/vendor /app/vendor

WORKDIR /app

EXPOSE 9500

CMD ["/usr/local/bin/php", "/app/bin/server.php"]