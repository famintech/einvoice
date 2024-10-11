#!/bin/sh
set -e

# Update .env file with environment variables
env | while IFS='=' read -r key value; do
    sed -i "s#^${key}=.*#${key}=${value}#" .env
done

# Generate app key if not set
php artisan key:generate --no-interaction --force

# Run database migrations
php artisan migrate --force

# Start PHP-FPM
exec php-fpm