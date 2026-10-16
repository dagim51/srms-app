#!/bin/bash
set -e

# Entrypoint script for SRMS production container
# Handles initialization, migrations, and service startup

echo "Starting SRMS container..."

# Wait for database to be ready
echo "Waiting for database..."
until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}" -d "${DB_DATABASE:-ethio_real_estate}"; do
    echo "Database not ready, waiting..."
    sleep 2
done
echo "Database is ready!"

# Wait for Redis to be ready
echo "Waiting for Redis..."
until redis-cli -h "${REDIS_HOST:-redis}" -p "${REDIS_PORT:-6379}" ping | grep -q PONG; do
    echo "Redis not ready, waiting..."
    sleep 2
done
echo "Redis is ready!"

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Clear and cache configuration
echo "Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Create storage link if not exists
if [ ! -L public/storage ]; then
    php artisan storage:link
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start Supervisor (manages PHP-FPM, queue workers, cron)
echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf