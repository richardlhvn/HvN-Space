#!/usr/bin/env python3
"""Build a source-only local update with its protected tag migration."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
import re, hashlib
root=Path(__file__).resolve().parent.parent
core=re.search(r'Version:\s*(\S+)',(root/'plugin/afterglow-core.php').read_text()).group(1)
theme=re.search(r'^Version:\s*(\S+)',(root/'theme/style.css').read_text(),re.M).group(1)
name=f'tunetofond_tag_sync_Core_{core}_Theme_{theme}.zip'
out=root/'dist';out.mkdir(exist_ok=True)
with ZipFile(out/name,'w',ZIP_DEFLATED) as z:
    for folder in ['plugin','theme']:
        for p in sorted((root/folder).rglob('*')):
            if p.is_file() and not any(x.startswith('.') or x=='__pycache__' for x in p.relative_to(root).parts) and p.suffix.lower() not in {'.xlsx','.xlsm','.sql','.zip','.pyc'}:
                z.write(p,p.relative_to(root).as_posix())
    for script in ['migrate-tags.php','migrate-branding.php','backup.sh']:
        z.write(root/'scripts'/script,'scripts/'+script)
    for doc in ['TAG_SYNC.md','tag-sync-original-tags-2026-11-14.json']:
        z.write(root/'docs'/doc,'docs/'+doc)
    z.write(root/'downloads/README_TAG_SYNC_UPDATE.md','README.md')
    z.write(root/'LICENSE','LICENSE')
    for p in sorted((root/'downloads').glob('tag-sync-*.png')):z.write(p,'previews/'+p.name)
digest=hashlib.sha256((out/name).read_bytes()).hexdigest()
(out/(name+'.sha256')).write_text(f'{digest}  {name}\n')
print(f'Built {name}; SHA-256 {digest}')
