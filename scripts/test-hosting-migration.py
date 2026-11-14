#!/usr/bin/env python3
"""Exercise a private transfer against an isolated database and WordPress root."""
import argparse, hashlib, json, os, re, shutil, subprocess, tarfile, tempfile, uuid
from pathlib import Path

ROOT=Path(__file__).resolve().parents[1]
parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('--package',type=Path,required=True);args=parser.parse_args()
os.umask(0o077)
scratch=Path(tempfile.mkdtemp(prefix='hosting-migration-test-',dir=ROOT/'.runtime'))
site=scratch/'html';site.mkdir();package=scratch/'package';package.mkdir()
dbname='ag_hosting_migration_test_'+uuid.uuid4().hex[:10]
assert re.fullmatch(r'ag_hosting_migration_test_[0-9a-f]{10}',dbname)
checks=0;created=False

def call(command,**kwargs):return subprocess.run(command,cwd=ROOT,check=True,capture_output=True,text=True,**kwargs).stdout.strip()
def admin(sql):return call(['docker','compose','exec','-T','db','sh','-c','exec mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" --execute="$1"','--',sql])
base=['docker','compose','run','--rm','--user',f'{os.getuid()}:{os.getgid()}','-e','WORDPRESS_DB_NAME='+dbname,'-e','WORDPRESS_TABLE_PREFIX=dest_','-e','WP_CLI_CACHE_DIR=/tmp/tf-hosting-cache','-v',f'{site}:/migration-test-site','-v',f'{package}:/migration']
def wp(*argv):return call(base+['cli','wp','--path=/migration-test-site','--skip-plugins','--skip-themes',*argv])
def installer(mode='--check',owner_input=None,success=True):
    result=subprocess.run(base+['--entrypoint','bash','cli','/migration/restore-hostinger.sh',mode,'--wp-root','/migration-test-site'],cwd=ROOT,input=owner_input,capture_output=True,text=True)
    (scratch/'restore.log').write_text(result.stdout+result.stderr)
    if success and result.returncode:raise RuntimeError(result.stdout+result.stderr)
    if not success and result.returncode==0:raise AssertionError('An unsafe migration unexpectedly succeeded.')
    return result.stdout+result.stderr
def check(ok,message):
    global checks
    if not ok:raise AssertionError(message)
    checks+=1
def snapshot():return wp('eval','require "/migration/tools/fingerprint.php";WP_CLI::line(hash("sha256",serialize(tf_hosting_fingerprint())));')
def reseal():
    sums=[]
    for name in names:sums.append(hashlib.sha256((package/name).read_bytes()).hexdigest()+'  '+name)
    (package/'SHA256SUMS').write_text('\n'.join(sums)+'\n')

try:
    # Copy only listed payload files, never backups/credentials from another test.
    names=[line.split('  ',1)[1] for line in (args.package/'SHA256SUMS').read_text().splitlines()]
    for name in names:
        assert not Path(name).is_absolute() and '..' not in Path(name).parts
        dest=package/name;dest.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(args.package/name,dest)
    shutil.copyfile(args.package/'SHA256SUMS',package/'SHA256SUMS')
    stream=subprocess.Popen(['docker','compose','exec','-T','wordpress','tar','-C','/var/www/html','--exclude=wp-content/plugins/afterglow-core','--exclude=wp-content/themes/afterglow','--exclude=wp-content/uploads','--exclude=wp-content/cache','-cf','-','.'],cwd=ROOT,stdout=subprocess.PIPE)
    with tarfile.open(fileobj=stream.stdout,mode='r|') as tar:tar.extractall(site,filter='data')
    if stream.wait()!=0:raise RuntimeError('Core fixture copy failed.')
    admin(f"CREATE DATABASE `{dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci; GRANT ALL PRIVILEGES ON `{dbname}`.* TO 'wordpress'@'%';");created=True
    call(base+['--entrypoint','sh','cli','-c','exec wp --path=/migration-test-site core install --url=https://tunetofond.com --title="Fresh destination" --admin_user=destination-admin --admin_email=destination@example.test --admin_password="$AFTERGLOW_ADMIN_PASSWORD" --skip-email'])
    pristine_config=(site/'wp-config.php').read_bytes()
    wp('option','update','home','https://different.example.test')
    before=snapshot();installer(success=False);check(snapshot()==before,'Wrong-domain guard preserves destination.');wp('option','update','home','https://tunetofond.com')
    before=snapshot();check('Nothing imported' in installer() and snapshot()==before,'Dry run preserves all destination post/config fingerprints.')
    style=package/'wp-content/themes/afterglow/style.css';old=style.read_bytes();style.write_bytes(old+b'\n')
    installer(success=False);check(snapshot()==before,'A corrupt payload fails before writes.');style.write_bytes(old)
    log=installer('--apply','production-owner-test\nowner-migration@example.test\n')
    check('1419 complete post/meta/relationship fingerprints' in log,'Full post/meta/source/taxonomy preservation verification passes.')
    check(wp('db','prefix')=='wp_','Source prefix replaces the fresh destination prefix safely.')
    credentials=sorted(package.glob('destination-backup-*/production-admin.json'))[-1]
    relative='/migration/'+credentials.relative_to(package).as_posix()
    result=wp('eval',f'$c=json_decode(file_get_contents({json.dumps(relative)}),true);$owner=get_user_by("login",$c["username"]);$old=get_user_by("login","editor");$ok=$owner&&user_can($owner,"manage_options")&&wp_check_password($c["password"],$owner->user_pass,$owner->ID)&&!wp_check_password(getenv("AFTERGLOW_ADMIN_PASSWORD"),$old->user_pass,$old->ID);WP_CLI::line($ok?"CREDENTIALS_OK":"FAIL");')
    check(result=='CREDENTIALS_OK' and credentials.stat().st_mode&0o777==0o600,'New private production login works; the cloud password does not.')
    check(wp('option','get','blog_public')=='0','Indexing stays disabled.')
    check((site/'wp-content/themes/afterglow/style.css').stat().st_mode&0o777==0o644,'Public assets have readable production permissions.')
    later=wp('post','create','--post_type=page','--post_status=draft','--post_title=Later owner work','--porcelain')
    before=snapshot();check('already applied' in installer('--apply').lower() and snapshot()==before,'Repeat does not overwrite later owner work.')
    wp('option','delete','tf_cloud_migration_id');installer(success=False);check(snapshot()==before,'A different/repeated source cannot overwrite an existing catalog.')
    # Return to the original prefix: introduced tables must be treated as collisions.
    (site/'wp-config.php').write_bytes(pristine_config)
    before=snapshot();installer(success=False);check(snapshot()==before,'Existing foreign-prefix tables are protected.')
    manifest=json.loads((package/'manifest.json').read_text())
    admin('USE `'+dbname+'`; '+''.join('DROP TABLE `'+t+'`;' for t in manifest['tables']))
    # Inject a proof mismatch after import to exercise actual database/config rollback.
    original_manifest=(package/'manifest.json').read_text();first=next(iter(manifest['expected']['records']));manifest['expected']['records'][first]='0'*64
    (package/'manifest.json').write_text(json.dumps(manifest));reseal();before=snapshot()
    log=installer('--apply','rollback-owner-test\nrollback-owner@example.test\n',success=False)
    check('restoring the destination' in log and snapshot()==before,'A failure after import rolls back destination posts, metadata and settings.')
    check((site/'wp-config.php').read_bytes()==pristine_config and wp('db','prefix')=='dest_','Rollback restores the original configuration and prefix.')
    tables=json.loads(wp('eval','global $wpdb;WP_CLI::line(wp_json_encode($wpdb->get_col("SHOW TABLES")));'))
    check(not any(t.startswith('wp_') for t in tables),'Rollback removes only newly introduced source tables.')
    (package/'manifest.json').write_text(original_manifest);reseal()
    check('1419 complete post/meta/relationship fingerprints' in installer('--apply','retry-owner-test\nretry-owner@example.test\n'),'A rolled-back transfer can be retried successfully.')
    print(f'PASS: {checks} isolated hosting migration checks, including 1419-record preservation, safe repeat, credential rotation and complete rollback.')
finally:
    if created:
        admin(f"DROP DATABASE `{dbname}`; REVOKE ALL PRIVILEGES ON `{dbname}`.* FROM 'wordpress'@'%';")
    shutil.rmtree(scratch)
