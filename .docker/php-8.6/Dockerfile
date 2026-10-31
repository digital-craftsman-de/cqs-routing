# Build with
# docker buildx build --platform linux/arm64,linux/amd64 -t ghcr.io/digital-craftsman-de/cqs-routing-php-8.6:latest ./.docker/php-8.6 --push

# TODO: Switch to php:8.6-fpm-alpine once PHP 8.6 is released.
FROM php:8.6-rc-fpm-alpine

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
     unzip \
     oniguruma-dev

# mbstring is no longer bundled in the official PHP 8.6 image
RUN docker-php-ext-install mbstring

RUN curl -L --output /usr/bin/pie https://github.com/php/pie/releases/download/1.5.5/pie.phar \
  && chmod +x /usr/bin/pie

# Debugging
# TODO: Switch to stable xdebug/xdebug once a stable version supports PHP 8.6.
RUN pie install xdebug/xdebug:^3.6@alpha

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
