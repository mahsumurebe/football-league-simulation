#!/bin/sh
set -e

echo "Starting application setup..."

# Wait for PostgreSQL to be ready
echo "Waiting for PostgreSQL..."
until nc -z postgres 5432; do
    echo "PostgreSQL is unavailable - sleeping"
    sleep 2
done

echo "PostgreSQL is up - executing commands"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo "Migrations completed successfully"
else
    echo "Migration failed with exit code $?"
    exit 1
fi

# Seed database if needed
if [ "$SEED_DATABASE" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
else
    echo "Skipping database seeding (set SEED_DATABASE=true to enable)"
fi

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Remove Vite hot file if exists (forces use of built assets in production)
if [ -f /var/www/html/public/hot ]; then
    echo "Removing public/hot file to use built assets..."
    rm -f /var/www/html/public/hot
fi

# Optimize for production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Set permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "Application setup completed!"

# Execute the main command
exec "$@"
