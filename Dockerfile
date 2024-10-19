FROM ghcr.io/roadrunner-server/roadrunner:2024 as roadrunner
FROM php:8.3-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions @composer-2 zip intl sockets protobuf

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

EXPOSE 8080/tcp

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1