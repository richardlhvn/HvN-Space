#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
if [ ! -f .env ]; then
  umask 077
  node --input-type=module <<'JS' > .env
import { randomBytes } from 'node:crypto';
for (const key of ['DB_PASSWORD', 'DB_ROOT_PASSWORD', 'AFTERGLOW_ADMIN_PASSWORD']) console.log(`${key}=${randomBytes(24).toString('hex')}`);
JS
fi
docker compose up -d --wait db wordpress
for attempt in {1..40}; do
  if docker compose exec -T wordpress test -f /var/www/html/wp-load.php; then break; fi
  sleep 1
done
docker compose run --rm cli wp eval-file /scripts/bootstrap.php --skip-wordpress
docker compose run --rm cli wp eval-file /scripts/install.php
docker compose run --rm cli wp rewrite flush
printf '\nAfterglow is running on local port %s. Admin user: editor.\nThe generated admin password is stored in .env (never commit it).\n' "${PORT:-8080}"
