# Build with
# docker buildx build --platform linux/arm64,linux/amd64 -t ghcr.io/digital-craftsman-de/cqs-routing-php-8.5:latest ./.docker/php-8.5 --push

FROM php:8.5-fpm-alpine

RUN apk add --update \
    autoconf \
    g++ \
    automake \
    make \
    gnupg \
    libzip \
    libzip-dev \
    util-linux-dev \
    linux-headers \
    libuuid \
    $PHPIZE_DEPS \
    libtool \
    unzip

RUN curl -L --output /usr/bin/pie https://github.com/php/pie/releases/download/1.3.1/pie.phar \
  && chmod +x /usr/bin/pie

# Debugging
RUN pie install xdebug/xdebug

# Ids package
RUN pie install pecl/uuid

# Install composer
RUN curl -s https://getcomposer.org/installer | php
RUN alias composer='php composer.phar'
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD php.ini /etc/php/conf.d/
ADD php.ini /etc/php/cli/conf.d/
ADD php.ini /usr/local/etc/php
ADD php-fpm.conf /etc/php/php-fpm.d/
ADD docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/

WORKDIR /var/www/html

CMD ["php-fpm", "-F"]
