#!/bin/bash

[[ ! -e /.dockerenv ]] && exit 0

set -xe

apt-get update -yqq
apt-get install git zip unzip -yqq

pecl install "xdebug-$XDEBUG" && docker-php-ext-enable xdebug

cp "docker/php.ini" "$PHP_INI_DIR/conf.d/php-custom.ini"
