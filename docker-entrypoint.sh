#!/bin/sh
set -e

echo "Current working directory: $(pwd)"
echo "Contents of current directory:"
ls -la

echo "Contents of .env file before processing:"
cat .env

# Check if .env file exists and is valid
if [ ! -f .env ] || ! grep -q '^APP_KEY=' .env; then
    echo ".env file is missing or invalid. Creating from .env.example..."
    cp .env.example .env
    php artisan key:generate --no-interaction --force
fi

echo "Contents of .env file after processing:"
cat .env

# Install Composer dependencies if vendor directory is empty
if [ -z "$(ls -A vendor)" ]; then
    echo "Vendor directory is empty. Installing dependencies..."
    composer install --no-scripts
fi

# Generate optimized autoload files
composer dump-autoload --optimize

# Run database migrations
php artisan migrate --force

# Start PHP-FPM
exec php-fpm