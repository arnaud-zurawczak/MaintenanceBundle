FROM php:7.2 as php72

RUN apt update -yqq && \
    apt install git zip unzip -yqq > /dev/null

ARG XDEBUG_VERSION
RUN pecl install "xdebug-$XDEBUG_VERSION" > /dev/null && docker-php-ext-enable xdebug

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

COPY "php.ini" "$PHP_INI_DIR/conf.d/php-custom.ini"

RUN apt-get install -y zlib1g-dev libicu-dev g++ > /dev/null \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \

WORKDIR /app

FROM php:7.4 as php74

RUN apt update -yqq && \
    apt install git zip unzip -yqq > /dev/null

ARG XDEBUG_VERSION
RUN pecl install "xdebug-$XDEBUG_VERSION" > /dev/null && docker-php-ext-enable xdebug

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

COPY "php.ini" "$PHP_INI_DIR/conf.d/php-custom.ini"

RUN apt-get install -y zlib1g-dev libicu-dev g++ > /dev/null \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

WORKDIR /app

FROM php:8.0 as php80

RUN apt update -yqq && \
    apt install git zip unzip -yqq > /dev/null

ARG XDEBUG_VERSION
RUN pecl install "xdebug-$XDEBUG_VERSION" > /dev/null && docker-php-ext-enable xdebug

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

COPY "php.ini" "$PHP_INI_DIR/conf.d/php-custom.ini"

RUN apt-get install -y zlib1g-dev libicu-dev g++ > /dev/null \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

WORKDIR /app
