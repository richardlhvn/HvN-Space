#!/usr/bin/env python3
"""Package the combined emotion/genre source update, never local runtime state."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
import re
import hashlib
root=Path(__file__).resolve().parent.parent
core=re.search(r'Version:\s*(\S+)',(root/'plugin/afterglow-core.php').read_text()).group(1)
theme=re.search(r'^Version:\s*(\S+)',(root/'theme/style.css').read_text(),re.M).group(1)
name=f'tunetofond_mood_update_Core_{core}_Theme_{theme}.zip'
out=root/'dist';out.mkdir(exist_ok=True)
with ZipFile(out/name,'w',ZIP_DEFLATED) as z:
    for folder in ['plugin','theme']:
        for p in sorted((root/folder).rglob('*')):
            if p.is_file() and not any(part.startswith('.') or part=='__pycache__' for part in p.relative_to(root).parts) and p.suffix.lower() not in {'.xlsx','.xlsm','.sql','.zip','.pyc'}:
                z.write(p,p.relative_to(root).as_posix())
    z.write(root/'LICENSE','LICENSE')
    z.write(root/'downloads/README_MOOD_GROUPS_UPDATE.md','README.md')
    for file in ['MOOD_GROUPS.md','GENRE_GROUPS.md']:
        z.write(root/'docs'/file,'docs/'+file)
    for p in sorted((root/'downloads').glob('mood-groups-*.png')):
        z.write(p,'previews/'+p.name)
digest=hashlib.sha256((out/name).read_bytes()).hexdigest()
(out/(name+'.sha256')).write_text(f'{digest}  {name}\n')
print(f'Built {name}; SHA-256 {digest}')
