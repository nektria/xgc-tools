FROM eu.gcr.io/nektria/php:8.4
USER root
WORKDIR /app
RUN unset APP_ENV

ENV PATH ~/.composer/vendor/bin:$PATH

RUN apk add php-yaml

RUN rm -i -f /usr/bin/php
RUN ln -sf /usr/bin/php84 /usr/bin/php

RUN composer.phar global require phpstan/phpstan phpstan/phpstan-mockery phpstan/phpstan-phpunit phpstan/phpstan-strict-rules phpstan/phpstan-symfony phpstan/phpstan-doctrine shipmonk/phpstan-rules spaze/phpstan-disallowed-calls ergebnis/phpstan-rules
RUN composer.phar global require friendsofphp/php-cs-fixer:dev-master
RUN composer.phar global require squizlabs/php_codesniffer
RUN composer.phar global require phpunit/phpunit

ADD server/__PROJECT__-dev/php.ini /etc/php84/conf.d/nektria_custom.ini
COPY . /app

RUN composer.phar install
RUN bin/console debug:setup:assets -q

ENV PHP_CS_FIXER_IGNORE_ENV=1
CMD sh server/__PROJECT__-dev/web.sh
