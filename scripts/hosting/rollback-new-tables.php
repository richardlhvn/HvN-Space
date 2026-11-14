<?php
// Called only after restoring the original config on a failed fresh-site transfer.
global $wpdb;
$m=json_decode(file_get_contents(getenv('TF_MIGRATION_DIR').'/manifest.json'),true,512,JSON_THROW_ON_ERROR);
if ($wpdb->prefix !== getenv('TF_OLD_PREFIX') || $wpdb->prefix === $m['prefix']) { throw new RuntimeException('Rollback prefix guard failed.'); }
foreach ($m['tables'] as $table) {
    if (!preg_match('/\A[a-zA-Z0-9_]+\z/',$table) || !str_starts_with($table,$m['prefix'])) { throw new RuntimeException('Rollback table guard failed.'); }
    if ($wpdb->query('DROP TABLE IF EXISTS `'.$table.'`') === false) { throw new RuntimeException('Could not remove a newly introduced migration table.'); }
}
