FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Print directory contents before copying
RUN echo "Contents of /var/www before copying:" && ls -la /var/www

# Copy existing application directory contents
COPY . /var/www

# Print directory contents after copying
RUN echo "Contents of /var/www after copying:" && ls -la /var/www

# Copy .env.example to .env
COPY .env.example .env

# print the .env file contents
RUN cat .env

# Print .env file contents
RUN echo "Contents of .env file:" && cat .env

# Install dependencies
RUN composer install --no-scripts --no-autoloader

# Print vendor directory contents
RUN echo "Contents of vendor directory:" && ls -la vendor

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Change ownership
RUN chown -R www-data:www-data /var/www

# Create a script to run migrations and start PHP-FPM
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Print final directory contents
RUN echo "Final contents of /var/www:" && ls -la /var/www

# Expose port 9000 and set the entrypoint
EXPOSE 9000
ENTRYPOINT ["docker-entrypoint.sh"]