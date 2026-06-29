#!/bin/bash
set -e

DUMP=/docker-entrypoint-initdb.d-dump/dump.sql.gz

# Skip if placeholder /dev/null was mounted (no DB_DUMP_FILE set)
if [ ! -s "$DUMP" ]; then
  echo "No dump file mounted, skipping import."
  exit 0
fi

echo "Importing database dump into ${MYSQL_DATABASE}..."
zcat "$DUMP" | mysql -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}"
echo "Dump import complete."
