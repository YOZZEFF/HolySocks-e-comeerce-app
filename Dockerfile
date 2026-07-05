# =============================================================================
# Laravel on Railway — FrankenPHP
# =============================================================================
# Local testing:
#   docker build -t wearvaback .
#   docker run -e PORT=8080 -e APP_ENV=production -e APP_KEY=<your-key> -p 8080:8080 wearvaback
#
# Railway auto-detects this Dockerfile. No Start Command needed.
# Set environment variables (APP_KEY, DB_*, etc.) in the Railway dashboard.
#
# If config:cache freezes build-time env vars, remove the caching lines below
# and Laravel will read Railway's runtime env vars instead.
# =============================================================================

FROM dunglas/frankenphp:php8.4

RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

RUN apt-get update && apt-get install -y unzip libzip-dev && \
    docker-php-ext-install zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY Caddyfile /etc/caddy/Caddyfile

COPY --chown=www-data:www-data docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

COPY --chown=www-data:www-data . .

RUN composer install --no-dev --optimize-autoloader --no-interaction && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

RUN mkdir -p storage/logs storage/framework/views storage/framework/cache storage/framework/sessions && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

ENV DOCUMENT_ROOT=/app/public

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["frankenphp", "run"]
