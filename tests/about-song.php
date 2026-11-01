<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_about_test_') { throw new RuntimeException('Disposable namespace required.'); }
$checks = 0;
$assert = function ($ok, $message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } $checks++; };
try {
    wp_set_current_user(0);
    $assert(is_wp_error(ag_sync_about_songs(false)), 'Anonymous migrations must be rejected.');
    wp_set_current_user(get_user_by('login', 'editor')->ID);
    $packs = ag_handoff_packs(); $ids = []; $before = [];
    foreach (['astrid-s' => 'i-dont-know-why', 'clara-mae' => 'taped-up-heart-feat-clara-mae'] as $slug => $song_slug) {
        $pack = $packs[$slug]; $artist = wp_insert_post(['post_type' => 'artist', 'post_title' => $pack['artist']['name'], 'post_status' => 'publish', 'post_name' => $slug]);
        $record = array_column($pack['songs'], null, 'slug')[$song_slug];
        foreach (ag_about_fields() as $field) { unset($record[$field]); }
        $id = ag_handoff_save(0, $pack, $record, false, $artist, true); $ids[$slug] = $id;
        update_post_meta($id, '_ag_handoff_baseline', ag_handoff_state($id));
        $before[$id] = [get_post($id)->to_array(), get_post_meta($id)];
        $assert(ag_handoff_can_update($id, ag_handoff_key($slug, $record)), 'Empty optional fields must preserve historical baselines.');
    }
    $dry = ag_sync_about_songs();
    $assert(count($dry['added']) === 2 && !metadata_exists('post', $ids['astrid-s'], '_ag_about_song'), 'Dry run must propose two additions without writing.');
    $result = ag_sync_about_songs(false);
    $assert(count($result['added']) === 2, 'Both sourced background additions must map to existing songs.');
    foreach ($ids as $slug => $id) {
        $after = get_post_meta($id); $after['_ag_handoff_baseline'] = $before[$id][1]['_ag_handoff_baseline'];
        foreach (ag_about_fields() as $field) { unset($after['_ag_' . $field]); }
        $assert($after === $before[$id][1] && get_post($id)->to_array() === $before[$id][0], 'Migration must preserve every old field, source, ID, slug, text and status.');
        $about = ag_about_song($id);
        $assert($about['text'] && $about['attribution'] === 'artist_statement' && in_array('creative-process', $about['types'], true), 'Collaboration/process facts need no lyric meaning.');
        $assert($about['sources'] && ag_handoff_can_update($id, ag_meta('handoff_key', $id)), 'Sources and protected import baseline remain usable.');
    }
    $repeat = ag_sync_about_songs(false);
    $assert(!$repeat['added'] && count($repeat['preserved']) === 2, 'Repeat migration must not rewrite data.');
    $id = $ids['astrid-s'];
    $artist = (int) ag_meta('artist_id', $id);
    update_post_meta($artist, '_ag_handoff_key', 'astrid-s/profile');
    update_post_meta($artist, '_ag_source_registry', $packs['astrid-s']['sources']);
    delete_post_meta($id, '_ag_source_registry');
    $assert(ag_about_song($id)['text'] !== '', 'Early imports resolve their registry from the matching artist scope.');
    update_post_meta($artist, '_ag_handoff_key', 'other-artist/profile');
    $assert(ag_about_song($id)['text'] === '', 'Bare source IDs from a different artist must never resolve.');
    update_post_meta($artist, '_ag_handoff_key', 'astrid-s/profile');
    update_post_meta($id, '_ag_about_song', 'My reviewed creation story.');
    ag_sync_about_songs(false);
    $assert(ag_meta('about_song', $id) === 'My reviewed creation story.' && !ag_handoff_can_update($id, ag_meta('handoff_key', $id)), 'Owner-reviewed About the Song blocks later overwrite.');
    update_post_meta($id, '_ag_about_song', '');
    $assert(!ag_about_song($id)['text'], 'An explicit blank must hide the section and suppress legacy fallback.');
    foreach (ag_about_fields() as $field) { delete_post_meta($id, '_ag_' . $field); }
    wp_update_post(['ID' => $id, 'post_content' => 'Owner’s revised editor notes.']);
    $assert(!ag_sync_about_songs(false)['added'], 'Edited editor content must be preserved by additive migration.');
    update_post_meta($id, '_ag_artist_comment', 'A sourced account of writing this song.');
    update_post_meta($id, '_ag_meaning_type', 'sourced');
    $assert(ag_about_song($id)['origin'] === 'legacy_statement', 'Existing artist statements map without copying/rewriting them.');
    update_post_meta($id, '_ag_sources', '');
    $assert(!ag_about_song($id)['text'], 'An uncited legacy statement must not become an attributed story.');
    update_post_meta($id, '_ag_about_song', 'A new unreferenced factual story.');
    update_post_meta($id, '_ag_about_song_attribution', 'artist_statement');
    $assert(!ag_about_song($id)['text'], 'New factual stories without citations remain hidden.');
    update_post_meta($id, '_ag_about_song_source_urls', 'https://example.com/interview');
    update_post_meta($id, '_ag_about_song_attribution', 'mixed');
    update_post_meta($id, '_ag_about_song', "The artist described a relationship.\n\nThe producer described recording the demo.");
    $about = ag_about_song($id);
    $assert($about['attribution'] === 'mixed' && substr_count($about['text'], 'The producer') === 1, 'Meaning and production details coexist without being collapsed into artist intent.');
    $sample = $packs['astrid-s']['songs'][0];
    foreach ([
        ['about_song' => 'Fact', 'about_song_type' => ['made-up'], 'about_song_attribution' => 'artist_statement'],
        ['about_song' => 'Fact', 'about_song_source_ids' => ['not-in-registry'], 'about_song_attribution' => 'artist_statement'],
        ['about_song' => 'Fact', 'about_song_source_urls' => ['http://example.com/'], 'about_song_attribution' => 'third_party_reporting'],
        ['about_song' => 'Fact', 'about_song_attribution' => 'artist_statement'],
    ] as $bad) {
        $rejected = false; try { ag_about_validate($sample + $bad, $packs['astrid-s']); } catch (RuntimeException $e) { $rejected = true; }
        $assert($rejected, 'Malformed or unsupported optional import fields must be rejected.');
    }
    // Exercise the real editor save hook without writing to the local catalog.
    $_POST = ['ag_nonce' => wp_create_nonce('ag_save_details'), 'ag' => []];
    foreach (ag_field_definitions('song') as $key => $field) { $_POST['ag'][$key] = get_post_meta($id, '_ag_' . $key, true); }
    $_POST['ag']['about_song'] = 'A reviewed creation story.';
    $_POST['ag']['about_song_type'] = '["creative-process","collaboration"]';
    $_POST['ag']['about_song_source_ids'] = '[]';
    $_POST['ag']['about_song_source_urls'] = "https://example.com/interview\nhttp://invalid.example/source";
    $_POST['ag']['about_song_attribution'] = 'third_party_reporting';
    wp_update_post(['ID' => $id, 'post_title' => get_the_title($id)]);
    $_POST = [];
    $assert(ag_about_song($id)['text'] === 'A reviewed creation story.', 'The ordinary editor save hook must save an explicit narrative.');
    $assert(ag_about_values(ag_meta('about_song_type', $id)) === ['creative-process', 'collaboration'], 'Spreadsheet-style JSON arrays normalize for editor metadata.');
    $assert(ag_meta('about_song_source_urls', $id) === 'https://example.com/interview', 'Editor source fields keep HTTPS links and reject insecure ones.');
    $assert(ag_about_song($id)['attribution'] === 'third_party_reporting', 'A reported background must never be relabeled as artist intent.');
    WP_CLI::success($checks . ' About the Song migration/schema/protection checks passed.');
} finally {
    foreach ($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like('ag_about_test_') . '%')) as $table) {
        if (!preg_match('/\Aag_about_test_[a-z0-9_]+\z/', $table)) { throw new RuntimeException('Unexpected table.'); }
        $wpdb->query('DROP TABLE `' . $table . '`');
    }
}
