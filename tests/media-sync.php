<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_media_test_') { throw new RuntimeException('Disposable namespace required.'); }
$GLOBALS['ag_media_checks'] = 0; $fixture_attachment = 0;
function ag_media_assert($ok, $message) { if (!$ok) { throw new RuntimeException($message); } $GLOBALS['ag_media_checks']++; }
try {
    wp_set_current_user(0); ag_media_assert(is_wp_error(ag_sync_media()), 'Anonymous sync must be forbidden.');
    wp_set_current_user(get_user_by('login', 'editor')->ID);
    $packs = ag_handoff_packs(); $expected = array_sum(array_map(function ($p) { return 1 + count($p['songs']); }, $packs)) - count(ag_handoff_aliases());
    $expected_embeds = 0;
    foreach ($packs as $slug => $pack) { foreach ($pack['songs'] as $song) { if ($song['spotify_url'] && !isset(ag_handoff_aliases()[ag_handoff_key($slug, $song)])) { $expected_embeds++; } } }
    // Exercise the blocked/unverified state independently of the live review register.
    add_filter('ag_media_reviews', function ($reviews) { foreach ($reviews as &$r) { $r['status'] = 'pending_live_verification'; } return $reviews; });
    ag_media_assert(is_wp_error(ag_sync_media(false)), 'Missing catalog must prevent writes.');
    ag_apply_handoffs(true);
    $before = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}");
    $dry = ag_sync_media(true);
    ag_media_assert($dry['matched'] === $expected && !$dry['missing'], 'Dry run must match all registered records.');
    ag_media_assert($before === $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}"), 'Dry run must not create records.');
    ag_media_assert(count($dry['photos_pending']) === 6 && count($dry['placeholders']) === 47, 'Original photo gates and all forty-seven placeholders must remain protected.');
    ag_media_assert($dry['embed_updates'] === $expected_embeds && $expected_embeds === 174 && count($dry['shared_selections']) === 1, 'Exact canonical players must be mapped once, keeping alternate selection references separate.');
    $apply = ag_sync_media(false);
    $shared_id = ag_handoff_find('rosie', array_column($packs['rosie']['songs'], null, 'slug')['little-bit-better']);
    ag_media_assert(get_post_meta($shared_id, '_ag_handoff_record', true) === array_column($packs['caleb-hearn']['songs'], null, 'slug')['little-bit-better'], 'Media sync must not overwrite the shared canonical record with a thinner alias.');
    ag_media_assert(!is_wp_error($apply) && !$apply['photos_imported'], 'Unverified photos must never be imported.');
    ag_media_assert((int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='attachment'") === 0, 'No artist binaries may be uploaded without verification.');
    foreach (ag_handoff_packs() as $slug => $pack) {
        $artist = ag_handoff_find($slug);
        ag_media_assert(get_post_meta($artist, '_ag_source_registry', true) === $pack['sources'], 'Complete source registry must be retained.');
        foreach ($pack['songs'] as $song) {
            $id = ag_handoff_find($slug, $song, $artist);
            ag_media_assert(get_post_meta($id, '_ag_spotify_embed', true) === ag_spotify_embed_url($id), 'Exact official embed mapping must be stored.');
        }
    }
    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}");
    $again = ag_sync_media(false);
    ag_media_assert(!$again['embed_updates'] && !$again['record_metadata_updates'] && !$again['source_registry_updates'] && $again['credits_page'] === 'unchanged', 'Second sync must have no effective writes.');
    ag_media_assert($count === $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}"), 'Repeat sync must not duplicate records.');
    $credits = get_page_by_path('media-credits');
    ag_media_assert($credits && str_contains($credits->post_content, 'No artist photographs'), 'Credits must not claim pending photos are used.');
    wp_update_post(['ID' => $credits->ID, 'post_content' => 'Owner-edited credits.']);
    ag_media_assert(ag_sync_media(false)['credits_page'] === 'preserved_editor_content', 'Edited credits must be preserved.');
    $privacy = get_page_by_path('privacy'); wp_update_post(['ID' => $privacy->ID, 'post_content' => 'Owner privacy policy.']);
    ag_media_assert(ag_sync_media(false)['privacy_page'] === 'owner_review_required' && get_post($privacy->ID)->post_content === 'Owner privacy policy.', 'Edited privacy policy must be preserved.');
    $song_id = ag_handoff_find('sara-kays', ag_handoff_packs()['sara-kays']['songs'][0], ag_handoff_find('sara-kays'));
    update_post_meta($song_id, '_ag_spotify', 'https://evil.example/track/123');
    ag_media_assert(ag_spotify_embed_url($song_id) === '', 'Foreign iframe origins must be rejected.');
    ag_media_assert(in_array(get_post_meta($song_id, '_ag_handoff_key', true), ag_sync_media(false)['preserved'], true), 'Manually changed records must be preserved.');
    // A tiny synthetic PNG exercises attachment handling; it is not an artist photograph.
    $fixture = WP_PLUGIN_DIR . '/afterglow-core/media-assets/.test-fixture.png';
    $image = ['source_file_page_url' => 'https://example.org/synthetic-fixture', 'source_original_page_url' => null, 'photographer' => 'Synthetic test fixture', 'rights_holder' => 'Afterglow test suite', 'license' => 'CC BY-SA 4.0', 'license_url' => 'https://creativecommons.org/licenses/by-sa/4.0/', 'attribution_text' => 'Synthetic fixture — not an artist photo'];
    $candidate = ['artist_slug' => 'synthetic-fixture', 'artist_name' => 'Synthetic fixture', 'publish_image' => true, 'image' => $image];
    $review = ['status' => 'live_verified', 'live_source_checked' => true, 'identity_caption_checked' => true, 'license_checked' => true, 'commercial_editorial_use_confirmed' => true, 'source_url' => $image['source_file_page_url'], 'license_url' => $image['license_url'], 'license' => $image['license'], 'photographer' => $image['photographer'], 'checked_at' => gmdate('Y-m-d'), 'evidence_summary' => 'Synthetic test-only evidence; no real artist license claim.', 'changes' => 'Uncropped scaling; synthetic test fixture.', 'sha256' => hash_file('sha256', $fixture), 'derivative_license' => 'CC BY-SA 4.0'];
    ag_media_assert(ag_photo_review_valid($candidate, $review), 'Complete synthetic review should pass structural validation.');
    $bad = $review; unset($bad['derivative_license']);
    ag_media_assert(!ag_photo_review_valid($candidate, $bad), 'ShareAlike conditions are mandatory.');
    $blocked = $candidate; $blocked['publish_image'] = false;
    ag_media_assert(!ag_photo_review_valid($blocked, $review), 'Four placeholder statuses must not be overridden.');
    $artist = wp_insert_post(['post_type' => 'artist', 'post_title' => 'Synthetic fixture', 'post_status' => 'draft']);
    update_post_meta($artist, '_ag_handoff_baseline', ag_handoff_state($artist));
    $fixture_attachment = ag_import_verified_photo($artist, $candidate, $review, $fixture);
    ag_media_assert(!is_wp_error($fixture_attachment), 'Verified synthetic file must import.');
    $repeated = ag_import_verified_photo($artist, $candidate, $review, $fixture);
    ag_media_assert($fixture_attachment === $repeated, 'Attachment import must deduplicate source and hash.');
    wp_delete_file(get_attached_file($fixture_attachment));
    $restored = ag_import_verified_photo($artist, $candidate, $review, $fixture);
    ag_media_assert($restored === $fixture_attachment && is_readable(get_attached_file($fixture_attachment)), 'A missing upload must be repaired without creating a duplicate attachment.');
    $credit = ag_media_credit($artist);
    ag_media_assert($credit['derivative_license'] === 'CC BY-SA 4.0' && $credit['photographer'] === 'Synthetic test fixture', 'Credits, source, and derivative license must persist.');
    ag_media_assert(str_contains(ag_photo_credit_html($artist), 'Full media credits'), 'On-page attribution must link to full credits.');
    WP_CLI::success($GLOBALS['ag_media_checks'] . ' media sync and permission-gate checks passed.');
} finally {
    if ($fixture_attachment && !is_wp_error($fixture_attachment)) { wp_delete_attachment($fixture_attachment, true); }
    foreach ($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like('ag_media_test_') . '%')) as $table) {
        if (!preg_match('/\Aag_media_test_[a-z0-9_]+\z/', $table)) { throw new RuntimeException('Unexpected table name.'); }
        $wpdb->query('DROP TABLE `' . $table . '`');
    }
}
