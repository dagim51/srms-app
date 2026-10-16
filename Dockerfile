# syntax=docker/dockerfile:1
################################################################################
# Production Dockerfile for SRMS - PHP-FPM with Nginx
################################################################################

# Base image: PHP 8.3 FPM
FROM php:8.4-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    # Core dependencies
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    # For GD
    libgd-dev \
    # For Redis
    libssl-dev \
    # For intl
    libicu-dev \
    # For re2c (intl dependency)
    re2c \
    # For Supervisor
    supervisor \
    # For cron
    cron \
    # Cleanup
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    zip \
    gd \
    bcmath \
    intl \
    opcache \
    pcntl \
    sockets \
    # Install Redis extension via PECL
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "opcache.enable=1" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.memory_consumption=256" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.interned_strings_buffer=16" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.max_accelerated_files=20000" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.validate_timestamps=0" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "opcache.save_comments=1" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo "memory_limit=512M" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "upload_max_filesize=50M" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "post_max_size=50M" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "max_execution_time=300" >> "$PHP_INI_DIR/conf.d/custom.ini"

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies (without dev dependencies)
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

# Copy application files
COPY . .

# Create necessary directories and set permissions
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy Supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy cron configuration
COPY docker/cron/laravel-schedule /etc/cron.d/laravel-schedule
RUN chmod 0644 /etc/cron.d/laravel-schedule && crontab /etc/cron.d/laravel-schedule

# Create entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Use entrypoint
ENTRYPOINT ["entrypoint.sh"]

# Default command
CMD ["php-fpm"]