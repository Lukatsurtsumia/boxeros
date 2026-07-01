# BoxerOS — production image (Laravel 12, nginx + php-fpm)
# Reliable Dockerfile build (replaces Nixpacks, which mis-generates the nginx config).

# ---- Stage 1: compile front-end assets (Vite + Tailwind) ----
FROM node:22-alpine AS assets
WORKDIR /build
COPY . .
RUN npm install && npm run build

# ---- Stage 2: application runtime (nginx + php-fpm, serves /app/public on :80) ----
FROM webdevops/php-nginx:8.3-alpine

ENV WEB_DOCUMENT_ROOT=/app/public \
    COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

# Application source
COPY --chown=application:application . /app

# Compiled assets from stage 1 (overwrites the source public/build)
COPY --from=assets --chown=application:application /build/public/build /app/public/build

# PHP dependencies (production only) + writable Laravel dirs
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
 && chown -R application:application /app/storage /app/bootstrap/cache
