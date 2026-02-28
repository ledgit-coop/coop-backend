#!/bin/bash
skip_migrations=${LARAVEL_SKIP_DATABASE:-false}

# Install dependencies
if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

# Cache laravel components
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate key
php artisan key:generate --ansi

if [ $skip_migrations != 'true' ]; then
    # Run migrations
    php artisan migrate
fi

# Run bitnami laravel setup
. /opt/bitnami/scripts/php/setup.sh
. /opt/bitnami/scripts/laravel/setup.sh
. /post-init.sh
. /opt/bitnami/scripts/laravel/run.sh
