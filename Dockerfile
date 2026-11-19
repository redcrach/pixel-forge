# syntax=docker/dockerfile:1

# One image runs every role (web, queue, scheduler, Reverb); only the command changes.
# serversideup/php: PHP-FPM + NGINX on port 8080 as unprivileged www-data, with Laravel automations,
# health check scripts, and pdo_pgsql / redis / pcntl already installed.
ARG PHP_IMAGE=serversideup/php:8.4-fpm-nginx
ARG NODE_IMAGE=node:22-trixie-slim

FROM ${NODE_IMAGE} AS node

############################################
# Base: PHP plus what the app needs on top
############################################
FROM ${PHP_IMAGE} AS base

USER root
# bcmath: exact decimal math for Pixelforge rates (App\Dto\Pricing\RatesDto)
# intl: number and date formatting in the Filament admin panel
RUN install-php-extensions bcmath intl
USER www-data

############################################
# Node, for building and serving the front-end
############################################
# The Vite build runs `php artisan wayfinder:generate`, so Node has to live next to PHP, not in its own image.
FROM base AS base-with-node

USER root
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx
ENV npm_config_cache=/tmp/.npm
USER www-data

############################################
# Development: code is mounted, www-data takes your UID/GID so files stay yours
############################################
FROM base-with-node AS development

ARG USER_ID=1000
ARG GROUP_ID=1000

USER root
RUN docker-php-serversideup-set-id www-data ${USER_ID}:${GROUP_ID} \
    && docker-php-serversideup-set-file-permissions --owner ${USER_ID}:${GROUP_ID}
USER www-data

############################################
# Build: production dependencies and compiled assets
############################################
FROM base-with-node AS build

# Dependencies first, so these layers stay cached until the lock files change
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-autoloader --no-scripts --prefer-dist

COPY --chown=www-data:www-data package.json package-lock.json ./
RUN npm ci

COPY --chown=www-data:www-data . .
RUN composer dump-autoload --no-dev --optimize

# VITE_* values are compiled into the public JavaScript: never pass a secret here.
ARG VITE_APP_NAME="Pixelforge Studio"
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT=443
ARG VITE_REVERB_SCHEME=https
RUN npm run build \
    && rm -rf node_modules

############################################
# Production: code baked in, no Node, OPcache on
############################################
FROM base AS production

ENV PHP_OPCACHE_ENABLE=1

COPY --chown=www-data:www-data --from=build /var/www/html /var/www/html
