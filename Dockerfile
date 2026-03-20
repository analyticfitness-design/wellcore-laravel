FROM dunglas/frankenphp:latest-php8.4

# Install PHP extensions
RUN install-php-extensions pdo_mysql mbstring bcmath gd intl opcache redis

# Set working directory
WORKDIR /app

# Copy composer files first for cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application
COPY . .

# Run post-install scripts
RUN composer run-script post-autoload-dump

# Build frontend assets
RUN npm ci && npm run build && rm -rf node_modules

# Storage and cache setup
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Create storage symlink
RUN php artisan storage:link || true

EXPOSE 8080

ENTRYPOINT ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
