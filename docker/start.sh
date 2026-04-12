#!/bin/bash
set -e

# Resolve nix nginx path
NGINX_PATH=$(nix-store -q $(which nginx))

# Generate nginx.conf from template
sed "s|__NGINX_PATH__|${NGINX_PATH}|g" /app/docker/nginx.template.conf > /app/nginx.conf

# Fix storage permissions
chmod -R ugo+rw /app/storage 2>/dev/null || true

# Run migrations
php artisan migrate --force 2>&1 || true

# Seed if tables are empty
php artisan db:seed --class=WishSeeder --force 2>&1 || true
php artisan db:seed --class=CountrySeeder --force 2>&1 || true
php artisan db:seed --class=BudgetSeeder --force 2>&1 || true

# Publish Filament assets
php artisan filament:assets 2>&1 || true

# Cache config (AFTER assets)
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true
php artisan view:cache 2>&1 || true

# Start queue worker in background
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &

# Start php-fpm and nginx
php-fpm -y /assets/php-fpm.conf &
sleep 1
nginx -c /app/nginx.conf
