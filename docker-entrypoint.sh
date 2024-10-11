#!/bin/sh
set -e

# Check if .env file exists, if not, copy from .env.example
if [ ! -f .env ]; then
    echo ".env file not found. Copying from .env.example..."
    cp .env.example .env
fi

# Update .env file with environment variables
env | while IFS='=' read -r key value; do
    if grep -q "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
        echo "${key}=${value}" >> .env
    fi
done

# Generate app key if not set
php artisan key:generate --no-interaction --force

# Run database migrations
php artisan migrate --force

# Start PHP-FPM
exec php-fpm