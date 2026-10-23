#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
docker compose run --rm -v "$PWD/tests:/tests:ro" cli wp eval-file /tests/editorial.php
