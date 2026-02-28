#!/usr/bin/env bash
set -e

cd /app

# Install vendors if not present
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
  echo ">> vendor/ not found; running composer install..."
  composer install \
    --no-dev --prefer-dist --no-progress --no-interaction \
    --optimize-autoloader
fi

# Optionally cache configs if APP_KEY etc. are present
if [ -n "${APP_ENV}" ] && [ -n "${APP_KEY}" ]; then
  php artisan config:cache || true
  php artisan route:cache || true
fi

/usr/bin/supervisord -c /etc/supervisor/supervisord.conf &

# Hand off to Bitnami's original entrypoint
exec /opt/bitnami/scripts/laravel/entrypoint.sh /opt/bitnami/scripts/laravel/run.sh
