#!/bin/bash
set -e

cd /var/www/html

# app/config/config.local.neon is gitignored (local dev keeps its own real copy
# on disk), so a fresh checkout baked into the production image never has one.
# Generate it from environment variables on every container start instead.
cat > app/config/config.local.neon <<NEON
parameters:

database:
	dsn: 'mysql:host=${DB_HOST:?DB_HOST is required};dbname=${DB_NAME:-sluchohry}'
	user: ${DB_USER:-sluchohry}
	password: ${DB_PASSWORD:?DB_PASSWORD is required}
	options:
		lazy: yes
NEON

exec "$@"
