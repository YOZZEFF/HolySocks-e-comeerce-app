#!/bin/sh
set -e

php artisan config:clear

php artisan migrate --force || echo "Warning: Some migrations could not be applied (tables may already exist). Continuing..."
php artisan db:seed --class=RealDataSeeder --force

php artisan config:cache

exec "$@"
