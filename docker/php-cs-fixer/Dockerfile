ARG PHP_VERSION=8.2
ARG SYMFONY_VERSION=^5.4

FROM php:${PHP_VERSION}-alpine

ARG SYMFONY_VERSION=^5.4

RUN apk add --no-cache libzip-dev git && docker-php-ext-install zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json /tmp/composer.json

RUN cd /tmp && \
    composer global require --no-progress --no-scripts --no-plugins symfony/flex && \
    composer global config --no-plugins allow-plugins.symfony/flex true && \
    composer config extra.symfony.require "${SYMFONY_VERSION}" && \
    composer update --no-install --prefer-stable && \
    composer install --no-interaction --no-progress --no-scripts --no-plugins

WORKDIR /app

ENTRYPOINT ["/tmp/vendor/bin/php-cs-fixer"]
