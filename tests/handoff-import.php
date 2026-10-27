<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_handoff_test_') { throw new RuntimeException('A disposable namespace is required.'); }
$GLOBALS['ag_handoff_checks'] = 0;
function ag_handoff_assert($value, $message) { if (!$value) { throw new RuntimeException($message); } $GLOBALS['ag_handoff_checks']++; }
try {
    wp_set_current_user(0); ag_handoff_assert(is_wp_error(ag_apply_handoffs()), 'Anonymous imports must be rejected.');
    wp_set_current_user(get_user_by('login', 'editor')->ID);
    ag_import_astrid_research();
    $old_sara = get_page_by_path('sara-kays', OBJECT, 'artist')->ID;
    $old_hurts = get_page_by_path('hurts-so-good', OBJECT, 'song')->ID;
    $seed_content = get_post($old_sara)->post_content;
    wp_update_post(['ID' => $old_sara, 'post_content' => 'An edit made before the first research import.']);
    ag_handoff_assert(!ag_handoff_can_update($old_sara, 'sara-kays/profile'), 'Pre-existing edits must be protected before the first import.');
    wp_update_post(['ID' => $old_sara, 'post_content' => $seed_content]);
    ag_handoff_assert(ag_handoff_can_update($old_sara, 'sara-kays/profile'), 'Restored starter must match its portable baseline.');
    $result = ag_apply_handoffs(true);
    ag_handoff_assert(!is_wp_error($result), 'Import must succeed.');
    ag_handoff_assert(count($result['created']) + count($result['updated']) === 110 && !$result['preserved'], 'All 110 untouched targets must import.');
    ag_handoff_assert(ag_handoff_find('sara-kays') === $old_sara, 'Existing artist URL/ID must remain stable.');
    foreach (ag_handoff_packs() as $slug => $pack) {
        $artist_id = ag_handoff_find($slug);
        ag_handoff_assert(get_post_status($artist_id) === 'publish', 'Artist must appear on preview site.');
        ag_handoff_assert(count(explode(',', ag_meta('recommended', $artist_id))) === 10, 'Artist must recommend ten tracks.');
        ag_handoff_assert(str_contains(preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags(get_post($artist_id)->post_content), ENT_QUOTES, 'UTF-8')), preg_replace('/\s+/u', ' ', $pack['artist']['biography'])), 'Biography must be populated.');
        foreach ($pack['songs'] as $song) {
            $id = ag_handoff_find($slug, $song, $artist_id);
            ag_handoff_assert($id && ag_meta('artist_id', $id) === (string)$artist_id, 'Every track must link to its artist.');
            ag_handoff_assert(ag_meta('spotify', $id) === $song['spotify_url'], 'Official recording URL must be mapped.');
            ag_handoff_assert(ag_meta('demo', $id) === '1' && get_post_status($id) === 'publish', 'Preview pages must keep noindex review status.');
            ag_handoff_assert(ag_meta('credits', $id) === implode(', ', $song['artist_credits']), 'Collaborator credits must be retained.');
        }
    }
    $clara = ag_handoff_find('clara-mae');
    ag_handoff_assert(ag_meta('website', $clara) === '' && !str_contains(ag_meta('sources', $clara), 'claramaemusic.com'), 'The unrelated former artist domain must not become a public website or source link.');
    $mokita = ag_handoff_find('mokita');
    $mokita_pack = ag_handoff_packs()['mokita'];
    $unknown = ag_handoff_find('mokita', $mokita_pack['songs'][0], $mokita);
    ag_handoff_assert(ag_meta('artist_comment', $unknown) === '' && str_contains(get_post($unknown)->post_content, 'not yet been added'), 'Missing interpretation must remain missing.');
    $astrid = ag_handoff_find('astrid-s');
    $pack = ag_handoff_packs()['astrid-s']; $by_slug = array_column($pack['songs'], null, 'slug');
    ag_handoff_assert(ag_handoff_find('astrid-s', $by_slug['hurts-so-good'], $astrid) === $old_hurts, 'Existing song URL/ID must remain stable.');
    ag_handoff_assert(ag_meta('release', $old_hurts) === '' && str_contains(get_post($old_hurts)->post_content, '2016 (year precision)'), 'Year-only dates must not acquire a fabricated day.');
    $favorite = ag_handoff_find('astrid-s', $by_slug['favorite-part-of-me'], $astrid);
    ag_handoff_assert(ag_meta('release', $favorite) === '' && str_contains(get_post($favorite)->post_content, '2019-09 (month precision)'), 'Month-only dates must retain their precision.');
    $transcript = ag_handoff_find('astrid-s', $by_slug['its-ok-if-you-forget-me'], $astrid);
    ag_handoff_assert(ag_meta('artist_comment', $transcript) === '' && ag_meta('meaning_type', $transcript) === 'editorial', 'Unconfirmed automatic transcript must not become an artist statement.');
    $again = ag_apply_handoffs(true);
    ag_handoff_assert(!$again['created'] && !$again['preserved'] && count($again['updated']) === 110, 'Repeat imports must be safe and duplicate-free.');
    wp_update_post(['ID' => $old_sara, 'post_content' => 'My revised biography.']);
    wp_trash_post($favorite);
    $after = ag_apply_handoffs(true);
    ag_handoff_assert(in_array('sara-kays/profile', $after['preserved'], true) && get_post($old_sara)->post_content === 'My revised biography.', 'Author edits must survive later imports.');
    ag_handoff_assert(get_post_status($favorite) === 'trash' && !$after['created'], 'Deleted records must not be resurrected or duplicated.');
    WP_CLI::success($GLOBALS['ag_handoff_checks'] . ' supplied-research import checks passed.');
} finally {
    foreach ($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like('ag_handoff_test_') . '%')) as $table) {
        if (!preg_match('/\Aag_handoff_test_[a-z0-9_]+\z/', $table)) { throw new RuntimeException('Unexpected table name.'); }
        $wpdb->query('DROP TABLE `' . $table . '`');
    }
}
