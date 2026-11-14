#!/usr/bin/env python3
"""Build a private database/media migration, never a public repository artifact."""
import argparse, datetime, hashlib, json, os, shutil, subprocess, tarfile, uuid
from pathlib import Path

ROOT=Path(__file__).resolve().parents[1]
def run(args, **kwargs):return subprocess.run(args,cwd=ROOT,check=True,**kwargs)
def manifest():
    return json.loads(run(['docker','compose','run','--rm','cli','wp','eval-file','/scripts/hosting/source-manifest.php'],stdout=subprocess.PIPE,text=True).stdout)

def build(out):
    os.umask(0o077);out.mkdir(parents=True,exist_ok=True)
    stamp=datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%d-%H%M%S')
    name=f'tunetofond-cloud-migration-{stamp}'
    work=ROOT/'.runtime'/name;work.mkdir(mode=0o700)
    m=manifest();m['migration_id']=uuid.uuid4().hex;m['created_utc']=stamp
    m['code_commit']=run(['git','rev-parse','HEAD'],stdout=subprocess.PIPE,text=True).stdout.strip()
    (work/'manifest.json').write_text(json.dumps(m,ensure_ascii=False,indent=2)+'\n')
    for folder,slug,parent in [('plugin','afterglow-core','plugins'),('theme','afterglow','themes')]:
        shutil.copytree(ROOT/folder,work/'wp-content'/parent/slug)
    (work/'wp-content/uploads').mkdir(parents=True)
    # Capture all uploads, including generated sizes, preserving attachment paths.
    stream=subprocess.Popen(['docker','compose','exec','-T','wordpress','tar','-C','/var/www/html/wp-content','-cf','-','uploads'],cwd=ROOT,stdout=subprocess.PIPE)
    with tarfile.open(fileobj=stream.stdout,mode='r|') as tar:
        for item in tar:
            path=Path(item.name)
            if item.issym() or item.islnk() or path.is_absolute() or '..' in path.parts or path.parts[0]!='uploads':raise RuntimeError('Unsafe upload path.')
            tar.extract(item,work/'wp-content',filter='data')
    if stream.wait()!=0:raise RuntimeError('Uploads capture failed.')
    dump='exec mariadb-dump --single-transaction --skip-lock-tables --add-drop-table --no-tablespaces -u "$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE" "$@"'
    raw=work/'database.raw.sql'
    with raw.open('wb') as handle:run(['docker','compose','exec','-T','db','sh','-c',dump,'--',*m['tables']],stdout=handle)
    # MariaDB 11 adds a client-specific sandbox directive incompatible with some hosts.
    sql=raw.read_bytes().splitlines(keepends=True)
    if sql and b'enable the sandbox mode' in sql[0]:sql=sql[1:]
    (work/'database.sql').write_bytes(b''.join(sql));raw.unlink()
    tools=work/'tools';tools.mkdir()
    for file in ['fingerprint.php','preflight.php','create-owner.php','verify.php','rollback-new-tables.php']:shutil.copyfile(ROOT/'scripts/hosting'/file,tools/file)
    shutil.copyfile(ROOT/'scripts/hosting/restore-hostinger.sh',work/'restore-hostinger.sh')
    shutil.copyfile(ROOT/'docs/HOSTINGER_CLOUD_MIGRATION.md',work/'README.md')
    if manifest()['expected']!=m['expected']:raise RuntimeError('Cloud content changed during export. Regenerate the package.')
    sums=[]
    for file in sorted(work.rglob('*')):
        if file.is_symlink():raise RuntimeError('Symlinks are not permitted.')
        if file.is_file():sums.append(hashlib.sha256(file.read_bytes()).hexdigest()+'  '+file.relative_to(work).as_posix())
    (work/'SHA256SUMS').write_text('\n'.join(sums)+'\n')
    archive=out/(name+'.tar.gz')
    with tarfile.open(archive,'w:gz') as tar:tar.add(work,arcname=name)
    digest=hashlib.sha256(archive.read_bytes()).hexdigest()
    archive.with_suffix('.gz.sha256').write_text(digest+'  '+archive.name+'\n')
    print(json.dumps({'archive':str(archive),'private_work_dir':str(work),'bytes':archive.stat().st_size,'sha256':digest,'preserved_posts':len(m['expected']['records']),'post_counts':m['expected']['post_counts']},indent=2))
if __name__=='__main__':
    parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('--output',type=Path,required=True);args=parser.parse_args();build(args.output)
