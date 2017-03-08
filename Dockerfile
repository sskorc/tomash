FROM php:7-cli

ENV COMPOSER_VERSION 1.2.0

RUN apt-get update \
    && apt-get install -y git libssl-dev zlib1g-dev libicu-dev g++ \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && pecl install xdebug \
    && echo zend_extension=xdebug.so > /usr/local/etc/php/conf.d/xdebug.ini \
    && docker-php-ext-install zip mbstring intl opcache pdo pdo_mysql

RUN curl https://getcomposer.org/download/$COMPOSER_VERSION/composer.phar -o /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer

ADD docker/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/tomash
