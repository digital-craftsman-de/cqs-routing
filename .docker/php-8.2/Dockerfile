# Build with
# docker buildx build --platform linux/arm64,linux/amd64 -t ghcr.io/digital-craftsman-de/cqrs-php-8.2:latest ./.docker/php-8.2 --push

FROM php:8.2-fpm-alpine

RUN apk add --update \
    autoconf \
    g++ \
    make \
    linux-headers \
    curl

# Add php extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync

# Debugging
RUN install-php-extensions xdebug

# Ids package
RUN install-php-extensions uuid

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    ln -s $(composer config --global home) /root/composer
ENV PATH=$PATH:/root/composer/vendor/bin COMPOSER_ALLOW_SUPERUSER=1

ADD php.ini /etc/php/conf.d/
ADD php.ini /etc/php/cli/conf.d/
ADD php.ini /usr/local/etc/php
ADD php-fpm.conf /etc/php/php-fpm.d/
ADD docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/

WORKDIR /var/www/html

CMD ["php-fpm", "-F"]
