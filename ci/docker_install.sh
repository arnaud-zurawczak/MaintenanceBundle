#!/bin/bash

[[ ! -e /.dockerenv ]] && exit 0

set -xe

apt-get update -yqq
apt-get install git zip unzip -yqq

pecl install "xdebug-$XDEBUG" && docker-php-ext-enable xdebug
