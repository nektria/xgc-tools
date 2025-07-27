#!/bin/bash

cd /app
composer.phar install
unset APP_ENV
bin/console > /dev/null
bin/console messenger:consume --time-limit=3600 -v -b messenger.bus.default --all
cd -
