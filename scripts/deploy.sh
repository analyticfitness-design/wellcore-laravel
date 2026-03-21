#!/bin/bash
# WellCore Fitness — Production Deploy Script
# Usage: bash scripts/deploy.sh
# Make executable: chmod +x scripts/deploy.sh
set -e

echo "🚀 WellCore Deployment Starting..."

# Pull latest code
git pull origin main

# Install PHP dependencies (no dev)
composer install --no-dev --optimize-autoloader --no-interaction

# Install and build frontend
npm ci
npm run build

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run migrations (safe — no destructive migrations)
php artisan migrate --force

# Clear old cache
php artisan cache:clear

# Restart queue workers if running
php artisan queue:restart 2>/dev/null || true

# Set permissions
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment complete!"
echo "📊 $(php artisan --version)"
