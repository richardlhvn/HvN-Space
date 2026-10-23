#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
python3 - <<'PY'
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
out = Path('dist')
out.mkdir(exist_ok=True)
for source, slug in [('theme','afterglow'), ('plugin','afterglow-core')]:
    with ZipFile(out / f'{slug}.zip', 'w', ZIP_DEFLATED) as z:
        for path in sorted(Path(source).rglob('*')):
            if path.is_file():
                z.write(path, str(Path(slug) / path.relative_to(source)))
        z.write('LICENSE', f'{slug}/LICENSE')
    print(f'Built {out / (slug + ".zip")}')
PY
