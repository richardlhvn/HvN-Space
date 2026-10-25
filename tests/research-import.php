<?php
// Use only a disposable validation namespace, never the user's live database.
global $wpdb;
if ($wpdb->prefix !== 'ag_research_test_') { throw new RuntimeException('A disposable test namespace is required.'); }
$GLOBALS['ag_research_checks'] = 0;
function ag_research_assert($value, $message) {
    if (!$value) { throw new RuntimeException($message); }
    $GLOBALS['ag_research_checks']++;
}
try {
    wp_set_current_user(0);
    ag_research_assert(is_wp_error(ag_import_astrid_research()), 'Unauthenticated users must not import.');
    wp_set_current_user(get_user_by('login', 'editor')->ID);
    $artist = get_page_by_path('astrid-s', OBJECT, 'artist');
    $song = get_page_by_path('hurts-so-good', OBJECT, 'song');
    wp_update_post(['ID' => $artist->ID, 'post_content' => 'Retain my researched artist biography.']);
    wp_update_post(['ID' => $song->ID, 'post_content' => 'Retain my song notes.']);
    update_post_meta($artist->ID, '_ag_recommended', '12345');
    $before_artist = get_post($artist->ID)->to_array();
    $before_meta = get_post_meta($artist->ID);
    $before_song = get_post($song->ID)->to_array();
    // Same title by a different artist must not be selected.
    $other = get_page_by_path('sara-kays', OBJECT, 'artist');
    $collision = wp_insert_post(['post_type' => 'song', 'post_title' => 'Breathe', 'post_status' => 'publish']);
    update_post_meta($collision, '_ag_artist_id', (string)$other->ID);
    $published = (int)wp_count_posts('song')->publish;
    $result = ag_import_astrid_research();
    ag_research_assert(!is_wp_error($result), 'Import must succeed for administrators.');
    ag_research_assert(count($result['created']) === 9, 'Only the nine missing songs should be created.');
    ag_research_assert(count($result['reused']) === 2, 'Existing artist and song should be reused.');
    ag_research_assert(get_post($artist->ID)->to_array() === $before_artist && get_post_meta($artist->ID) === $before_meta, 'Existing artist text and metadata must be unchanged.');
    ag_research_assert(get_post($song->ID)->to_array() === $before_song, 'Existing song content must be unchanged.');
    ag_research_assert((int)wp_count_posts('song')->publish === $published, 'Import must not publish anything.');
    foreach ($result['created'] as $id) {
        ag_research_assert(get_post_status($id) === 'draft' && get_post_meta($id, '_ag_demo', true) === '1', 'New records must be draft and noindex.');
        ag_research_assert(get_post_meta($id, '_ag_artist_id', true) === (string)$artist->ID, 'Drafts must link to the correct artist.');
        ag_research_assert(!get_post_meta($id, '_ag_sources', true) && !get_post_meta($id, '_ag_spotify', true) && !get_post_meta($id, '_ag_release', true), 'Unverified facts and citations must remain empty.');
    }
    $breathe = ag_astrid_find_song('breathe', 'Breathe', $artist->ID);
    ag_research_assert($breathe !== $collision, 'A different artist’s same-titled track must remain separate.');
    wp_update_post(['ID' => $result['created'][0], 'post_title' => 'My edited title', 'post_content' => 'Keep this edit.']);
    wp_update_post(['ID' => $artist->ID, 'post_name' => 'my-edited-artist-slug']);
    wp_trash_post($result['created'][1]);
    $again = ag_import_astrid_research();
    ag_research_assert(!is_wp_error($again) && count($again['created']) === 0, 'Repeat import must not duplicate renamed or trashed records.');
    ag_research_assert(get_post($result['created'][0])->post_content === 'Keep this edit.', 'Repeat import must preserve user edits.');
    ag_research_assert(get_post_status($result['created'][1]) === 'trash', 'Repeat import must not restore trashed records.');
    add_option('ag_astrid_import_lock', time());
    ag_research_assert(is_wp_error(ag_import_astrid_research()), 'Concurrent imports must be rejected.');
    delete_option('ag_astrid_import_lock');
    WP_CLI::success($GLOBALS['ag_research_checks'] . ' research import checks passed.');
} finally {
    $tables = $wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like('ag_research_test_') . '%'));
    foreach ($tables as $table) {
        if (!preg_match('/\Aag_research_test_[a-z0-9_]+\z/', $table)) { throw new RuntimeException('Unexpected test table name.'); }
        $wpdb->query('DROP TABLE `' . $table . '`');
    }
}
