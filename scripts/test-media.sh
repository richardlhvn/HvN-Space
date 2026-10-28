#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p plugin/media-assets
fixture_path=plugin/media-assets/.test-fixture.png
trap 'rm -f "$fixture_path"' EXIT
python3 - <<'PY'
import base64
from pathlib import Path
Path('plugin/media-assets/.test-fixture.png').write_bytes(base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5xkAAAAASUVORK5CYII='))
PY
chmod 755 plugin/media-assets
chmod 644 "$fixture_path"
docker compose run --rm -e WORDPRESS_TABLE_PREFIX=ag_media_test_ cli wp eval-file /scripts/bootstrap.php --skip-wordpress
docker compose run --rm -e WORDPRESS_TABLE_PREFIX=ag_media_test_ cli wp eval-file /scripts/install.php
docker compose run --rm -e WORDPRESS_TABLE_PREFIX=ag_media_test_ -v "$PWD/tests:/tests:ro" cli wp eval-file /tests/media-sync.php
