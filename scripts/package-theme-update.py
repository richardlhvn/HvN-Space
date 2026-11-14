#!/usr/bin/env python3
"""Build a theme-folder update without databases, credentials or research imports."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
import re
import hashlib
root=Path(__file__).resolve().parent.parent
version=re.search(r'^Version:\s*(\S+)',(root/'theme/style.css').read_text(),re.M).group(1)
name=f'tunetofond_theme_update_{version}.zip'
out=root/'dist';out.mkdir(exist_ok=True)
with ZipFile(out/name,'w',ZIP_DEFLATED) as archive:
    for path in sorted((root/'theme').rglob('*')):
        if path.is_file() and not any(p.startswith('.') for p in path.relative_to(root).parts):
            archive.write(path,path.relative_to(root).as_posix())
    archive.write(root/'LICENSE','LICENSE')
    archive.write(root/f'downloads/README_THEME_{version}.md','README.md')
    for path in sorted((root/'downloads').glob('reader-cleanup-*.png')):
        archive.write(path,'previews/'+path.name)
digest=hashlib.sha256((out/name).read_bytes()).hexdigest()
(out/(name+'.sha256')).write_text(f'{digest}  {name}\n')
print(f'Built {name}; SHA-256 {digest}')
