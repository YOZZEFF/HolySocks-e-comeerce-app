#!/bin/sh
set -e

php artisan config:clear

php artisan migrate --force
php artisan db:seed --class=RealDataSeeder --force

php artisan config:cache

exec "$@"
