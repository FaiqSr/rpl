#!/bin/sh
set -e

# Default working dir
cd /var/www/html || exit 1

if [ "$START_MODE" = "serve" ]; then
  echo "Starting Laravel built-in server on 0.0.0.0:8000"
  # Try to generate app key if missing
  if [ -f artisan ]; then
    php artisan key:generate --force || true
  fi
  exec php artisan serve --host=0.0.0.0 --port=8000
else
  echo "Starting php-fpm"
  exec php-fpm
fi
