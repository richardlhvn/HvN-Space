#!/usr/bin/env bash
# Run only on the fresh Hostinger destination, from a private extracted package.
set -euo pipefail
umask 077
tf_mode=check
tf_wp_root=''
while (($#)); do
  case "$1" in
    --check) tf_mode=check; shift ;;
    --apply) tf_mode=apply; shift ;;
    --wp-root) tf_wp_root="${2:?Supply the destination WordPress directory}"; shift 2 ;;
    *) printf 'Unknown argument: %s\n' "$1" >&2; exit 1 ;;
  esac
done
[[ -n "$tf_wp_root" ]] || { echo 'Usage: bash restore-hostinger.sh --check|--apply --wp-root /path/to/public_html' >&2; exit 1; }
for tf_cmd in wp php sha256sum realpath; do command -v "$tf_cmd" >/dev/null || { printf 'Required command missing: %s\n' "$tf_cmd" >&2; exit 1; }; done
php -r 'exit(PHP_VERSION_ID>=80100?0:1);' || { echo 'PHP 8.1 or newer is required.' >&2; exit 1; }
export TF_MIGRATION_DIR
TF_MIGRATION_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
tf_wp_root="$(realpath "$tf_wp_root")"
[[ -f "$tf_wp_root/wp-load.php" ]] || { echo 'WordPress installation not found.' >&2; exit 1; }
case "$TF_MIGRATION_DIR/" in "$tf_wp_root/"*) echo 'Keep the migration package outside public_html.' >&2; exit 1;; esac
(cd "$TF_MIGRATION_DIR" && sha256sum -c SHA256SUMS >/dev/null)
tf_wp=(wp --path="$tf_wp_root" --skip-plugins --skip-themes)
tf_ready="$("${tf_wp[@]}" eval-file "$TF_MIGRATION_DIR/tools/preflight.php")"
if [[ "$tf_ready" == ALREADY_APPLIED ]]; then echo 'This exact cloud migration is already applied. Existing site retained.'; exit 0; fi
[[ "$tf_ready" == READY ]] || { echo 'Destination preflight did not pass.' >&2; exit 1; }
echo 'Destination is a fresh tunetofond.com WordPress installation. Archive checksums verified.'
php -r '$m=json_decode(file_get_contents($argv[1]),true); printf("Cloud source: %d artists / %d stored songs; public selection stays ten artists / 128 songs.\n",$m["expected"]["post_counts"]["artist"],$m["expected"]["post_counts"]["song"]);' "$TF_MIGRATION_DIR/manifest.json"
if [[ "$tf_mode" == check ]]; then echo 'Read-only check complete. Nothing imported.'; exit 0; fi
read -r -p 'New production administrator username [tunetofond-owner]: ' TF_OWNER_LOGIN
export TF_OWNER_LOGIN="${TF_OWNER_LOGIN:-tunetofond-owner}"
read -r -p 'Your real administrator email (private account setting): ' TF_OWNER_EMAIL
export TF_OWNER_EMAIL
php -r '$m=json_decode(file_get_contents($argv[1]),true); $u=getenv("TF_OWNER_LOGIN"); if(!preg_match("/\A[A-Za-z0-9_.-]+\z/",$u)||in_array($u,$m["source_user_logins"],true)||!filter_var(getenv("TF_OWNER_EMAIL"),FILTER_VALIDATE_EMAIL)){fwrite(STDERR,"Invalid owner details or username belongs to the cloud account. Nothing imported.\n"); exit(1);}' "$TF_MIGRATION_DIR/manifest.json"
tf_backup="$TF_MIGRATION_DIR/destination-backup-$(date -u +%Y%m%d-%H%M%S)"
mkdir -m 700 "$tf_backup"
export TF_OLD_PREFIX
TF_OLD_PREFIX="$("${tf_wp[@]}" db prefix)"
tf_config="$("${tf_wp[@]}" config path)"
cp "$tf_config" "$tf_backup/wp-config.php"
"${tf_wp[@]}" db export "$tf_backup/before.sql" --single-transaction --skip-lock-tables --no-tablespaces=1
tf_mutated=0
tf_restore_on_failure() {
  tf_status=$?
  if ((tf_status == 0)); then tf_status=1; fi
  trap - ERR INT TERM
  if ((tf_mutated)); then
    echo 'Migration failed; restoring the destination configuration and database.' >&2
    cp "$tf_backup/wp-config.php" "$tf_config"
    if [[ "$TF_OLD_PREFIX" != "$tf_source_prefix" ]]; then
      "${tf_wp[@]}" eval-file "$TF_MIGRATION_DIR/tools/rollback-new-tables.php" || echo 'Review newly introduced tables before retrying.' >&2
    fi
    if ! "${tf_wp[@]}" db import "$tf_backup/before.sql"; then echo "Automatic rollback failed. Private backup: $tf_backup" >&2; fi
    "${tf_wp[@]}" cache flush >/dev/null 2>&1 || true
  fi
  echo 'The cloud source was not changed. Review the private backup before retrying.' >&2
  exit "${tf_status:-1}"
}
trap tf_restore_on_failure ERR INT TERM
tf_source_prefix="$(php -r '$m=json_decode(file_get_contents($argv[1]),true);if(!preg_match("/\A[A-Za-z0-9_]+\z/",$m["prefix"]))exit(1);echo $m["prefix"];' "$TF_MIGRATION_DIR/manifest.json")"
# Copy only custom code and media; retain the host's WordPress core/configuration.
mkdir -p "$tf_wp_root/wp-content/plugins" "$tf_wp_root/wp-content/themes" "$tf_wp_root/wp-content/uploads"
cp -R "$TF_MIGRATION_DIR/wp-content/plugins/." "$tf_wp_root/wp-content/plugins/"
cp -R "$TF_MIGRATION_DIR/wp-content/themes/." "$tf_wp_root/wp-content/themes/"
cp -R "$TF_MIGRATION_DIR/wp-content/uploads/." "$tf_wp_root/wp-content/uploads/"
find "$tf_wp_root/wp-content/plugins/afterglow-core" "$tf_wp_root/wp-content/themes/afterglow" "$tf_wp_root/wp-content/uploads" -type d -exec chmod 755 {} +
find "$tf_wp_root/wp-content/plugins/afterglow-core" "$tf_wp_root/wp-content/themes/afterglow" "$tf_wp_root/wp-content/uploads" -type f -exec chmod 644 {} +
tf_mutated=1
"${tf_wp[@]}" db import "$TF_MIGRATION_DIR/database.sql"
"${tf_wp[@]}" config set table_prefix "$tf_source_prefix" --type=variable
"${tf_wp[@]}" cache flush
"${tf_wp[@]}" search-replace 'http://localhost:8080' 'https://tunetofond.com' --all-tables-with-prefix --skip-columns=guid --precise --report-changed-only
"${tf_wp[@]}" option update home 'https://tunetofond.com'
"${tf_wp[@]}" option update siteurl 'https://tunetofond.com'
"${tf_wp[@]}" option update blog_public 0
"${tf_wp[@]}" core update-db
"${tf_wp[@]}" eval-file "$TF_MIGRATION_DIR/tools/verify.php"
export TF_OWNER_CREDENTIALS="$tf_backup/production-admin.json"
"${tf_wp[@]}" eval-file "$TF_MIGRATION_DIR/tools/create-owner.php"
"${tf_wp[@]}" config set DISALLOW_FILE_EDIT true --raw
wp --path="$tf_wp_root" rewrite flush --hard
"${tf_wp[@]}" cache flush
"${tf_wp[@]}" eval-file "$TF_MIGRATION_DIR/tools/verify.php"
tf_migration_id="$(php -r '$m=json_decode(file_get_contents($argv[1]),true);echo $m["migration_id"];' "$TF_MIGRATION_DIR/manifest.json")"
"${tf_wp[@]}" option update tf_cloud_migration_id "$tf_migration_id"
trap - ERR INT TERM
printf '\nMigration complete. Search indexing remains disabled.\nPrivate owner credentials: %s\nPrivate destination backup: %s\n' "$TF_OWNER_CREDENTIALS" "$tf_backup"
echo 'Log in with the new production account. Keep the archive and credential file outside public_html.'
