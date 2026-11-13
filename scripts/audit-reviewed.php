<?php
// Read-only, nonsecret evidence for before/after enrichment comparisons.
global $wpdb;
// Audit what is stored even while a newly designated source is unavailable.
// The active visibility keys identify this staging selection, not proof that
// a new canonical snapshot has already been imported.
$scope = ag_public_catalog_scope();
$targets = array_fill_keys($scope['keys'] ?? [], true);
if (!$targets) {
    foreach ($wpdb->get_col("SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key='_ag_reviewed_payload_hash'") as $id) { $targets[get_post_meta($id, '_ag_handoff_key', true)] = true; }
}
$unrelated = []; $existing = []; $artists = 0; $songs = 0; $players = 0;
foreach (get_posts(['post_type' => ['artist', 'song'], 'post_status' => ['publish', 'draft', 'private', 'pending', 'future', 'trash'], 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC']) as $p) {
    $key = get_post_meta($p->ID, '_ag_handoff_key', true);
    if ($p->post_type === 'artist') { $artists++; } else { $songs++; if (get_post_meta($p->ID, '_ag_spotify', true)) { $players++; } }
    if (isset($targets[$key])) { $existing[$key] = ['id' => $p->ID, 'slug' => $p->post_name, 'status' => $p->post_status]; continue; }
    $relations = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->term_relationships} WHERE object_id=%d ORDER BY term_taxonomy_id", $p->ID), ARRAY_A);
    $unrelated[$p->ID] = hash('sha256', wp_json_encode([$p, get_post_meta($p->ID), $relations]));
}
$unique = []; $selections = 0;
foreach (get_posts(['post_type' => 'artist', 'post_status' => 'publish', 'numberposts' => -1]) as $p) {
    foreach (ag_related_ids($p->ID, 'recommended') as $song) { $unique[$song] = true; $selections++; }
}
$media = [];
foreach ($existing as $key => $row) {
    if (str_ends_with($key, '/profile')) { $media[$key] = ['thumbnail' => get_post_thumbnail_id($row['id']), 'credit' => ag_media_credit($row['id'])]; }
}
$pages = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type='page' ORDER BY ID", ARRAY_A);
$options = [];
foreach (['home','siteurl','blogname','blogdescription','permalink_structure','timezone_string','show_on_front','page_on_front','page_for_posts','wp_page_for_privacy_policy','blog_public'] as $key) { $options[$key] = get_option($key); }
WP_CLI::line(wp_json_encode(['artist_posts' => $artists, 'song_posts_including_legacy' => $songs, 'selected_songs' => count($unique), 'curated_selections' => $selections, 'song_posts_with_tracks' => $players, 'noindex' => !(bool)get_option('blog_public'), 'target_records' => $existing, 'unrelated_hashes' => $unrelated, 'target_media' => $media, 'site_pages_hash' => hash('sha256', wp_json_encode($pages)), 'site_options' => $options, 'public_keys' => $scope['keys'] ?? []], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
