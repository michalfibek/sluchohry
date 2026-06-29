#!/bin/bash
set -e

cd /var/www/html

mkdir -p sessions log uploads
chmod 777 sessions log

needs_install=0
if [ ! -d vendor ]; then
  needs_install=1
elif [ -f vendor/composer/platform_check.php ] && ! php vendor/composer/platform_check.php; then
  # vendor/ was built for a different PHP version (bind-mounted across rebuilds)
  needs_install=1
fi

if [ "$needs_install" = "1" ]; then
  echo "Installing composer dependencies for PHP $(php -r 'echo PHP_VERSION;')..."
  rm -rf vendor
  composer install --no-interaction --no-progress
fi

exec "$@"
