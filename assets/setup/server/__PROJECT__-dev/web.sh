#!/bin/bash

cd /app
composer.phar install
unset APP_ENV
bin/console > /dev/null
php-fpm84 -D && sudo nginx
cd -
