#!/bin/bash
set -e

if [ ! -d /var/www/html/vendor ]; then
  echo "vendor/ missing, running composer install..."
  composer install --no-interaction --no-progress --working-dir=/var/www/html
fi

exec "$@"
