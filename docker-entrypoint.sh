#!/bin/sh
set -e

echo "Starting temporary Redis server for boot tasks..."
redis-server --daemonize yes
sleep 1

# Ensure framework directories exist in case a host volume mount hides them
mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Cache optimizations for production
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configuration, routes, and views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Ensure storage is linked
echo "Linking storage..."
php artisan storage:link --force

# Always run database migrations since this is a monolithic container
echo "Running database migrations..."
php artisan migrate --force

echo "Stopping temporary Redis server..."
redis-cli shutdown
sleep 1

# Pass control to Supervisor
exec "$@"
