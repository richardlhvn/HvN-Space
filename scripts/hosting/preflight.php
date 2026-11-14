<?php
global $wpdb;
$manifest = json_decode(file_get_contents(getenv('TF_MIGRATION_DIR').'/manifest.json'),true,512,JSON_THROW_ON_ERROR);
if (($manifest['schema'] ?? '') !== 'tunetofond.hosting-migration.v1' || $manifest['target_url'] !== 'https://tunetofond.com') { throw new RuntimeException('Invalid migration manifest.'); }
if (!in_array(wp_parse_url(get_option('home'),PHP_URL_HOST),['tunetofond.com','www.tunetofond.com'],true)) { throw new RuntimeException('This is not the tunetofond.com destination. No changes made.'); }
if (get_option('tf_cloud_migration_id') === $manifest['migration_id']) { WP_CLI::line('ALREADY_APPLIED'); return; }
$existing = $wpdb->get_results("SELECT post_type,post_name FROM {$wpdb->posts} WHERE post_type IN ('artist','song','post','page','attachment')",ARRAY_A);
foreach ($existing as $post) {
    if (!in_array($post['post_type'],['post','page'],true) || !in_array($post['post_name'],['hello-world','sample-page','privacy-policy'],true)) { throw new RuntimeException('Destination contains existing content. This installer only accepts a fresh WordPress site; no content was replaced.'); }
}
if ($wpdb->prefix !== $manifest['prefix']) {
    foreach ($manifest['tables'] as $table) { if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($table))) === $table) { throw new RuntimeException('Source table names already exist in this database. No changes made.'); } }
}
if (!get_role('administrator')) { throw new RuntimeException('Destination administrator role missing.'); }
WP_CLI::line('READY');
