FROM php:7.2

RUN apt update -yqq && \
    apt install git zip unzip -yqq > /dev/null

RUN pecl install xdebug-2.8.1 > /dev/null && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

COPY "docker/php.ini" "$PHP_INI_DIR/conf.d/php-custom.ini"
