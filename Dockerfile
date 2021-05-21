FROM php:7.2

RUN apt-get update -yqq && \
    apt-get install git zip unzip -yqq > /dev/null

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer
