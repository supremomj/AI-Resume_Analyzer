#!/bin/bash
set -e

# Create .env from environment variables if it doesn't exist or is empty
if [ ! -s ".env" ]; then
    env | grep -E '^(APP_|DB_|AI_|JSEARCH_|MAIL_|CACHE_|SESSION_|LOG_|QUEUE_)' > .env 2>/dev/null || true
fi

# Cache config and routes for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link || true

# Start Apache
apache2-foreground
