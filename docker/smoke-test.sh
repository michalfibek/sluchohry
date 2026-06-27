#!/bin/bash
# Quick sanity pass after a PHP version bump: hits a handful of key routes
# and reports any new Fatal/Warning/Notice lines logged since it started.
set -u
BASE_URL="${BASE_URL:-http://localhost:8080}"
# Pass COOKIE_JAR=/path/to/cookies.txt (from a prior login) to test as an authenticated user.
COOKIE_OPT=""
[ -n "${COOKIE_JAR:-}" ] && COOKIE_OPT="-b $COOKIE_JAR"
LOG_FILE="$(dirname "$0")/../log/error.log"
MARKER_LINE=$(wc -l < "$LOG_FILE" 2>/dev/null || echo 0)

echo "== Checking routes against $BASE_URL =="
for path in / /admin/ /profile/ /rating/default/1 /game/melodic-cubes/ /game/pexeso/ /game/note-steps/ /game/faders/; do
  code=$(curl -s $COOKIE_OPT -o /dev/null -w '%{http_code}' "$BASE_URL$path")
  printf '%-30s %s\n' "$path" "$code"
done

echo
echo "== New error.log entries since this run started =="
tail -n +"$((MARKER_LINE + 1))" "$LOG_FILE" 2>/dev/null || echo "(no log file yet)"
