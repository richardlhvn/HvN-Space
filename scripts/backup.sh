#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
umask 077
mkdir -p .runtime/backups
backup_path=".runtime/backups/wordpress-$(date -u +%Y%m%d-%H%M%S)-$$.sql"
trap 'rm -f "$backup_path.partial"' EXIT
docker compose exec -T db sh -c 'exec mariadb-dump --single-transaction --skip-lock-tables -u "$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' > "$backup_path.partial"
test -s "$backup_path.partial"
mv "$backup_path.partial" "$backup_path"
printf 'Database backup saved privately to %s\n' "$backup_path"
