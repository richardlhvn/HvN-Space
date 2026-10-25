#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
docker compose run --rm -e WORDPRESS_TABLE_PREFIX=ag_handoff_test_ cli wp eval-file /scripts/bootstrap.php --skip-wordpress
docker compose run --rm -e WORDPRESS_TABLE_PREFIX=ag_handoff_test_ cli wp eval-file /scripts/install.php
docker compose run --rm -e WORDPRESS_TABLE_PREFIX=ag_handoff_test_ -v "$PWD/tests:/tests:ro" cli wp eval-file /tests/handoff-import.php
