<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_reviewed_test_') { throw new RuntimeException('Disposable namespace required.'); }
$checks = 0;
$assert = function ($ok, $message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } $checks++; };
$snapshot = function ($ids) use ($wpdb) {
    $out = [];
    foreach ($ids as $id) {
        $out[$id] = [get_post($id, ARRAY_A), get_post_meta($id), $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->term_relationships} WHERE object_id=%d ORDER BY term_taxonomy_id", $id), ARRAY_A)];
    }
    return $out;
};
try {
    wp_set_current_user(0);
    $assert(is_wp_error(ag_import_reviewed()), 'Anonymous imports must be rejected.');
    wp_set_current_user(get_user_by('login', 'editor')->ID);
    $data = ag_reviewed_data();
    ag_reviewed_validate($data);
    $assert(count($data['packs']) === 10 && $data['provenance']['approval_count'] === 128, 'Exact approval scope.');
    $assert(is_wp_error(ag_apply_handoffs(true)), 'Superseded historical import must be blocked.');
    $assert(!(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ('artist','song')"), 'Bootstrap must not import other artists.');
    $fixture = wp_insert_post(['post_type' => 'song', 'post_title' => 'Unrelated preservation fixture', 'post_status' => 'draft']);
    update_post_meta($fixture, '_ag_fixture', 'Preserve this unrelated draft.');
    $untouched = [$fixture];
    $before = $snapshot($untouched);
    $post_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type <> 'revision'");
    $dry = ag_import_reviewed(true);
    $assert(!is_wp_error($dry) && count($dry['created']) === 138 && !$dry['updated'] && !$dry['conflicts'], 'Fresh dry run must find only ten artists and 128 songs.');
    $assert($post_count === (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type <> 'revision'") && !$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_ag_reviewed_payload_hash'"), 'Dry run must not write posts or review metadata.');
    foreach (['unapproved', 'duplicate', 'reference', 'scope', 'recording'] as $case) {
        $bad = $data;
        if ($case === 'unapproved') { $bad['packs']['astrid-s']['songs'][0]['review']['decision'] = 'not_reviewed'; }
        if ($case === 'duplicate') { $bad['packs']['astrid-s']['songs'][] = $bad['packs']['astrid-s']['songs'][0]; }
        if ($case === 'reference') { $bad['packs']['astrid-s']['songs'][0]['about_song_source_ids'][] = 'nonexistent'; }
        if ($case === 'scope') { $bad['packs']['rosie'] = $bad['packs']['astrid-s']; }
        if ($case === 'recording') { $bad['packs']['astrid-s']['songs'][1]['spotify_url'] = $bad['packs']['astrid-s']['songs'][0]['spotify_url']; }
        $assert(is_wp_error(ag_import_reviewed(false, true, $bad)), 'Reject malformed/unauthorized batch: ' . $case);
    }
    $duplicate = wp_insert_post(['post_type' => 'song', 'post_title' => 'Existing recording collision test', 'post_status' => 'draft']);
    update_post_meta($duplicate, '_ag_spotify', $data['packs']['astrid-s']['songs'][0]['spotify_url']);
    $assert(is_wp_error(ag_import_reviewed(true)), 'Reject a recording collision with unrelated existing content before writing.');
    wp_delete_post($duplicate, true);
    $first = ag_import_reviewed(false, true);
    $assert(!is_wp_error($first) && count($first['created']) === 138 && !$first['failed'], 'First canonical-only import.');
    $second = ag_import_reviewed(false, true);
    $assert(!is_wp_error($second) && !$second['created'] && !$second['updated'] && !$second['conflicts'] && count($second['skipped']) === 138, 'Second import must skip all unchanged reviewed records.');
    $assert($second['ids'] === $first['ids'], 'IDs must remain stable.');
    $assert((int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type <> 'revision'") === $post_count + 138, 'Only ten artists and 128 songs added.');
    $assert($snapshot($untouched) === $before, 'All unrelated posts, metadata and terms must remain byte-for-byte unchanged.');
    foreach ($data['packs'] as $slug => $pack) {
        $artist = $first['ids'][$slug . '/profile'];
        foreach ($pack['songs'] as $song) {
            $key = $slug . '/' . $song['slug']; $id = $first['ids'][$key];
            $assert(get_post_meta($id, '_ag_artist_id', true) === (string)$artist, 'Correct parent: ' . $key);
            $assert(get_post_meta($id, '_ag_about_song', true) === $song['about_song'], 'Exact approved text: ' . $key);
            $assert(ag_about_song($id)['text'] === trim($song['about_song']), 'Approved About narrative resolves (display trims outer whitespace): ' . $key);
            $assert(get_post_meta($id, '_ag_spotify', true) === $song['spotify_url'], 'Exact reviewed recording: ' . $key);
            $stored_record = get_post_meta($id, '_ag_handoff_record', true);
            $assert(($stored_record['release_date'] ?? null) === $song['release_date'] && ($stored_record['release_date_precision'] ?? null) === $song['release_date_precision'], 'Original documented release date and precision: ' . $key);
            $assert(get_post_meta($id, '_ag_source_registry', true) === $pack['sources'], 'Evidence statuses remain exact: ' . $key);
        }
    }
    $canonical = $first['ids']['caleb-hearn/little-bit-better'];
    $assert(get_post_meta($canonical, '_ag_spotify', true) === 'https://open.spotify.com/track/0GMs3jq70wmCo12pavj4st', 'Shared duet canonical recording must be retained.');
    $media = ag_sync_canonical_photos();
    $assert(!is_wp_error($media) && count($media['imported']) === 6 && count($media['placeholders']) === 4, 'Import only six permitted portraits and retain four placeholders.');
    $media_again = ag_sync_canonical_photos();
    $assert(!is_wp_error($media_again) && count($media_again['reused']) === 6 && !$media_again['imported'], 'Portrait sync must be idempotent.');
    $after_media = ag_import_reviewed(false, true);
    $assert(!is_wp_error($after_media) && count($after_media['skipped']) === 138 && !$after_media['conflicts'], 'Media sync must preserve reviewed baselines.');
    // Failures must roll back previous writes in the same batch.
    $changed = $data;
    $changed['provenance']['review_date'] = '2026-11-15';
    $all_before = $snapshot(array_values($first['ids']));
    $fail = function ($insert, $postarr) {
        return ($postarr['post_title'] ?? '') === 'Think Before I Talk' ? true : $insert;
    };
    add_filter('wp_insert_post_empty_content', $fail, 10, 2);
    $failed = ag_import_reviewed(false, true, $changed);
    remove_filter('wp_insert_post_empty_content', $fail, 10);
    $assert(is_wp_error($failed), 'A write failure must be reported.');
    $assert($snapshot(array_values($first['ids'])) === $all_before, 'Batch failure must roll back all prior writes.');
    $assert(!get_option('ag_handoff_lock'), 'Failed import must release its lock.');
    // A changed field or source registry must preserve the entire record.
    $target = $first['ids']['astrid-s/hurts-so-good'];
    update_post_meta($target, '_ag_about_song', 'The editor has changed this text.');
    $edited = $snapshot([$target]);
    $conflicts = ag_import_reviewed(false, true);
    $assert(in_array('astrid-s/hurts-so-good', $conflicts['conflicts'], true) && $snapshot([$target]) === $edited, 'Preserve later editor changes and report conflict.');
    update_post_meta($target, '_ag_about_song', $data['packs']['astrid-s']['songs'][0]['about_song']);
    $registry = get_post_meta($target, '_ag_source_registry', true);
    $registry[0]['evidence_summary'] = 'Later evidence correction';
    update_post_meta($target, '_ag_source_registry', $registry);
    $conflicts = ag_import_reviewed(true);
    $assert(in_array('astrid-s/hurts-so-good', $conflicts['conflicts'], true), 'Protect source registry edits too.');
    update_post_meta($target, '_ag_source_registry', 'Malformed later editor value');
    $assert(ag_about_song($target)['text'] === '', 'Malformed later evidence must fail closed without crashing the page.');
    $trashed = $first['ids']['astrid-s/think-before-i-talk'];
    wp_trash_post($trashed);
    $conflicts = ag_import_reviewed(false, true);
    $assert(in_array('astrid-s/think-before-i-talk', $conflicts['conflicts'], true) && get_post_status($trashed) === 'trash', 'Never resurrect a trashed record.');
    $assert(!get_option('blog_public'), 'Staging stays noindex.');
    WP_CLI::success($checks . ' reviewed import, evidence, preservation and rollback checks passed.');
} finally {
    foreach ($wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment'") as $attachment) { wp_delete_attachment((int)$attachment, true); }
    foreach ($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like('ag_reviewed_test_') . '%')) as $table) {
        if (!preg_match('/\Aag_reviewed_test_[a-z0-9_]+\z/', $table)) { throw new RuntimeException('Unexpected test table.'); }
        $wpdb->query('DROP TABLE `' . $table . '`');
    }
}
