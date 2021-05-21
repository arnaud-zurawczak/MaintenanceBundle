#!/bin/bash

[[ ! -e /.dockerenv ]] && exit 0

set -xe

apt-get update -yqq
apt-get install git zip unzip -yqq

pecl install xdebug-2.8.1 && docker-php-ext-enable xdebug
