#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
docker compose run --rm -e WORDPRESS_TABLE_PREFIX=ag_canonical_setup_test_ cli wp eval-file /scripts/bootstrap.php --skip-wordpress
docker compose run --rm -e WORDPRESS_TABLE_PREFIX=ag_canonical_setup_test_ cli wp eval-file /scripts/install-canonical.php
docker compose run --rm -e WORDPRESS_TABLE_PREFIX=ag_canonical_setup_test_ cli wp eval-file /scripts/install-canonical.php
docker compose run --rm -e WORDPRESS_TABLE_PREFIX=ag_canonical_setup_test_ -v "$PWD/tests:/tests:ro" cli wp eval-file /tests/canonical-setup.php
