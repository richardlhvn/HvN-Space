<?php
// Read-only preservation fingerprint. Contains hashes/IDs, never credentials.
global $wpdb;
$records = [];
foreach ($wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type IN ('artist','song','attachment') ORDER BY ID", ARRAY_A) as $p) {
    $terms = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->term_relationships} WHERE object_id=%d ORDER BY term_taxonomy_id", $p['ID']), ARRAY_A);
    $records[$p['ID']] = hash('sha256', serialize([$p, get_post_meta($p['ID']), $terms]));
}
$options = [];
foreach (['home','siteurl','blog_public','permalink_structure','ag_public_catalog_scope','stylesheet','template','theme_mods_afterglow'] as $key) { $options[$key] = get_option($key); }
WP_CLI::line(wp_json_encode(['records'=>$records,'options'=>$options], JSON_PRETTY_PRINT));
