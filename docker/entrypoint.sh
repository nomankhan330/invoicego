#!/bin/bash
set -e

# Default port to 80 if not set (Render sets $PORT dynamically, e.g. 10000)
export PORT=${PORT:-80}

# Update Apache port configuration
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure storage directories exist with correct permissions
mkdir -p /var/www/html/storage/framework/{sessions,views,cache} /var/www/html/storage/logs /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate app key if missing
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is missing. Generating key..."
    php artisan key:generate --force || true
fi

# Run optimizations
echo "Optimizing Laravel configuration and routes..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations if RUN_MIGRATIONS is true
if [ "$RUN_MIGRATIONS" = "true" ] || [ "$RUN_MIGRATIONS" = "1" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
fi

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground

