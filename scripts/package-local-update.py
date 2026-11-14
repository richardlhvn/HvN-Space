#!/usr/bin/env python3
"""Package only reviewed source and public instructions, never local state."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
import hashlib

root = Path(__file__).resolve().parent.parent
out = root / 'dist'
out.mkdir(exist_ok=True)
name = 'tunetofond_local_update_Core_0.12.0_Theme_0.6.0.zip'
files = []
for folder in ['plugin', 'theme', 'scripts']:
    files.extend(p for p in (root / folder).rglob('*') if p.is_file()
                 and not any(part.startswith('.') or part == '__pycache__' for part in p.relative_to(root).parts)
                 and p.suffix.lower() not in {'.xlsx', '.xlsm', '.sql', '.zip', '.pyc'})
files.extend(root / p for p in ['LICENSE', 'downloads/README_TUNETOFOND_UPDATE.md',
    'docs/TUNETOFOND_REBRAND.md', 'docs/CANONICAL_RESEARCH_SOURCE.md',
    'downloads/tunetofond-desktop.png', 'downloads/tunetofond-mobile.png'])
with ZipFile(out / name, 'w', ZIP_DEFLATED) as archive:
    for p in sorted(files):
        target = 'README_TUNETOFOND_UPDATE.md' if p.name == 'README_TUNETOFOND_UPDATE.md' else p.relative_to(root).as_posix()
        archive.write(p, target)
digest = hashlib.sha256((out / name).read_bytes()).hexdigest()
(out / (name + '.sha256')).write_text(f'{digest}  {name}\n')
print(f'Built {name}: {len(files)} files; SHA-256 {digest}')
