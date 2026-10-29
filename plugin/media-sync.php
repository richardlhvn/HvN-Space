<?php
defined('ABSPATH') || exit;

function ag_media_manifest() {
    $data = json_decode(file_get_contents(__DIR__ . '/data/media-manifest.json'), true);
    if (($data['schema_version'] ?? '') !== 'afterglow.media.v1' || count($data['artists'] ?? []) !== 10 || count($data['song_artworks'] ?? []) !== 100) { throw new RuntimeException('Invalid media manifest.'); }
    $seen = [];
    foreach ($data['song_artworks'] as $row) {
        $key = $row['artist_slug'] . '/' . $row['song_slug'];
        if (isset($seen[$key]) || !empty($row['download_cover']) || !preg_match('~^https://open\.spotify\.com/track/([A-Za-z0-9]{22})$~D', $row['spotify_track_url'], $m) || $row['spotify_embed_url'] !== 'https://open.spotify.com/embed/track/' . $m[1]) { throw new RuntimeException('Invalid or duplicate Spotify mapping.'); }
        $seen[$key] = true;
    }
    // The original photo-rights manifest covers its own artist subset, not later research batches.
    $packs = ag_handoff_packs(); $manifest_slugs = array_column($data['artists'], 'artist_slug');
    if (count(array_unique($manifest_slugs)) !== count($manifest_slugs) || array_diff($manifest_slugs, array_keys($packs))) { throw new RuntimeException('Unknown or duplicate photo-manifest artist.'); }
    foreach (array_intersect_key($packs, array_fill_keys($manifest_slugs, true)) as $slug => $pack) {
        foreach ($pack['songs'] as $song) {
            $key = $slug . '/' . $song['slug'];
            $rows = array_values(array_filter($data['song_artworks'], function ($r) use ($key) { return $r['artist_slug'] . '/' . $r['song_slug'] === $key; }));
            if (count($rows) !== 1 || $rows[0]['spotify_track_url'] !== $song['spotify_url']) { throw new RuntimeException('Manifest/catalogue mismatch.'); }
        }
    }
    return $data;
}

function ag_media_reviews() { return apply_filters('ag_media_reviews', json_decode(file_get_contents(__DIR__ . '/data/media-reviews.json'), true)['candidates'] ?? []); }
function ag_spotify_embed_url($id) {
    $url = get_post_meta($id, '_ag_spotify', true);
    if (!preg_match('~^https://open\.spotify\.com/(track|artist)/([A-Za-z0-9]{22})/?$~D', $url, $m)) { return ''; }
    if (($m[1] === 'track' && get_post_type($id) !== 'song') || ($m[1] === 'artist' && get_post_type($id) !== 'artist')) { return ''; }
    return 'https://open.spotify.com/embed/' . $m[1] . '/' . $m[2];
}

function ag_photo_review_valid($candidate, $review) {
    $image = $candidate['image'];
    if (empty($candidate['publish_image']) || ($review['status'] ?? '') !== 'live_verified') { return false; }
    foreach (['live_source_checked', 'identity_caption_checked', 'license_checked', 'commercial_editorial_use_confirmed'] as $field) { if (($review[$field] ?? false) !== true) { return false; } }
    if (($review['source_url'] ?? '') !== $image['source_file_page_url'] || ($review['license_url'] ?? '') !== $image['license_url'] || ($review['license'] ?? '') !== $image['license'] || ($review['photographer'] ?? '') !== $image['photographer']) { return false; }
    foreach (['source_url', 'license_url'] as $field) { if (!filter_var($review[$field], FILTER_VALIDATE_URL) || wp_parse_url($review[$field], PHP_URL_SCHEME) !== 'https') { return false; } }
    if (empty($review['checked_at']) || empty($review['evidence_summary']) || empty($review['changes']) || !preg_match('/^[a-f0-9]{64}$/D', $review['sha256'] ?? '')) { return false; }
    if ($image['license'] === 'CC BY-SA 4.0' && ($review['derivative_license'] ?? '') !== 'CC BY-SA 4.0') { return false; }
    return true;
}

function ag_import_verified_photo($artist_id, $candidate, $review, $path) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    if (!$artist_id || get_post_type($artist_id) !== 'artist') { return new WP_Error('missing_artist', 'An existing artist record is required.'); }
    if (!ag_photo_review_valid($candidate, $review)) { return new WP_Error('rights_pending', 'Current rights review is incomplete; placeholder retained.'); }
    $root = realpath(__DIR__ . '/media-assets'); $file = realpath($path);
    if (!$root || !$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file) || filesize($file) > 25 * 1024 * 1024 || hash_file('sha256', $file) !== $review['sha256']) { return new WP_Error('invalid_asset', 'Verified local asset is missing or has changed.'); }
    $info = wp_getimagesize($file);
    if (!$info || !in_array($info['mime'] ?? '', ['image/jpeg', 'image/png'], true)) { return new WP_Error('invalid_asset', 'The asset is not a supported image.'); }
    $source = $candidate['image']; $identity = hash('sha256', $source['source_file_page_url'] . '|' . $review['sha256']);
    $existing = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_ag_media_identity', 'meta_value' => $identity, 'numberposts' => 1]);
    $thumb = get_post_thumbnail_id($artist_id);
    if ($thumb && (!$existing || $thumb !== $existing[0]->ID)) { return new WP_Error('preserved_photo', 'An existing photograph was preserved.'); }
    if (!ag_handoff_can_update($artist_id, $candidate['artist_slug'] . '/profile')) { return new WP_Error('preserved_record', 'The edited artist record was preserved.'); }
    $attachment = $existing ? $existing[0]->ID : 0;
    if (!$attachment || !is_readable(get_attached_file($attachment))) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $extension = $info['mime'] === 'image/jpeg' ? '.jpg' : '.png';
        $upload = wp_upload_bits($candidate['artist_slug'] . '-' . substr($review['sha256'], 0, 12) . $extension, null, file_get_contents($file));
        if ($upload['error']) { return new WP_Error('upload_failed', $upload['error']); }
        if ($attachment) { update_attached_file($attachment, $upload['file']); }
        else { $attachment = wp_insert_attachment(['post_title' => $candidate['artist_name'] . ' — ' . $source['photographer'], 'post_mime_type' => $info['mime'], 'post_status' => 'inherit', 'post_parent' => $artist_id], $upload['file'], $artist_id, true); }
        if (is_wp_error($attachment)) { wp_delete_file($upload['file']); return $attachment; }
        // Preserve full frames and signatures; all generated sizes use uncropped scaling.
        $uncropped = function ($sizes) { foreach ($sizes as &$size) { $size['crop'] = false; } return $sizes; };
        add_filter('intermediate_image_sizes_advanced', $uncropped);
        try { wp_update_attachment_metadata($attachment, wp_generate_attachment_metadata($attachment, $upload['file'])); }
        finally { remove_filter('intermediate_image_sizes_advanced', $uncropped); }
        update_post_meta($attachment, '_ag_media_identity', $identity);
        update_post_meta($attachment, '_wp_attachment_image_alt', 'Editorial photograph of ' . $candidate['artist_name']);
    }
    $credit = ['photographer' => $source['photographer'], 'rights_holder' => $source['rights_holder'], 'source_url' => $source['source_file_page_url'], 'original_source_url' => $source['source_original_page_url'], 'license' => $source['license'], 'license_url' => $source['license_url'], 'credit' => $source['attribution_text'], 'changes' => $review['changes'], 'checked_at' => $review['checked_at'], 'sha256' => $review['sha256'], 'derivative_license' => $review['derivative_license'] ?? $source['license'], 'evidence_summary' => $review['evidence_summary']];
    update_post_meta($attachment, '_ag_media_credit', $credit); update_post_meta($attachment, '_ag_media_verified', '1');
    set_post_thumbnail($artist_id, $attachment);
    update_post_meta($artist_id, '_ag_artwork_credit', $credit['credit'] . ' · ' . $credit['changes']);
    update_post_meta($artist_id, '_ag_handoff_baseline', ag_handoff_state($artist_id));
    return $attachment;
}

function ag_media_credit($artist_id) {
    $id = get_post_thumbnail_id($artist_id);
    if (!$id || get_post_meta($id, '_ag_media_verified', true) !== '1') { return []; }
    return get_post_meta($id, '_ag_media_credit', true) ?: [];
}

function ag_media_credits_content() {
    $html = '<p>Afterglow is an independent music discovery publication. Artist names and photographs identify editorial subjects and do not imply endorsement, sponsorship, or affiliation.</p><h2>Original abstract artwork</h2><p>Our abstract landscapes and discovery illustrations are original Afterglow designs. They are not official covers or portraits.</p>';
    $photos = 0;
    foreach (ag_handoff_packs() as $slug => $pack) {
        $id = ag_handoff_find($slug); $credit = $id ? ag_media_credit($id) : [];
        if (!$credit) { continue; } $photos++;
        $html .= '<h2 id="' . esc_attr(get_post_field('post_name', $id)) . '">' . esc_html($pack['artist']['name']) . '</h2><p>Photo: ' . esc_html($credit['photographer']) . ' · <a href="' . esc_url($credit['source_url']) . '">Source and permission</a> · <a href="' . esc_url($credit['license_url']) . '">' . esc_html($credit['license']) . '</a>.';
        if ($credit['original_source_url']) { $html .= ' <a href="' . esc_url($credit['original_source_url']) . '">Original photographer page</a>.'; }
        $html .= '</p><p>' . esc_html($credit['changes']) . ' Adapted image files retain ' . esc_html($credit['derivative_license']) . '.</p>';
    }
    if (!$photos) { $html .= '<h2>Artist photographs</h2><p>No artist photographs from the candidate register are currently used. Abstract placeholders remain while current permissions are reviewed. A candidate license is not a credit for a photo used here.</p>'; }
    $html .= '<h2>Spotify players</h2><p>Optional official Spotify players may show artwork within Spotify’s widget. Afterglow does not download or self-host that artwork or music. Players contact Spotify only when you choose to load them.</p><h2>Corrections and copyright inquiries</h2><p>For attribution corrections or rights questions, use our <a href="' . esc_url(home_url('/copyright/')) . '">copyright inquiry page</a> or <a href="' . esc_url(home_url('/contact/')) . '">contact page</a>.</p>';
    return $html;
}

function ag_sync_media($dry_run = true) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    if ($dry_run) { return ag_sync_media_run(true); }
    if (!add_option('ag_media_sync_lock', time(), '', false)) { return new WP_Error('busy', 'A media sync is running; inspect an interrupted sync before clearing its lock.'); }
    try {
        $preview = ag_sync_media_run(true);
        if (is_wp_error($preview)) { return $preview; }
        if ($preview['missing']) { return new WP_Error('missing_records', 'Import the supplied artist/song research before syncing media. No media changes were made.'); }
        return ag_sync_media_run(false);
    } finally { delete_option('ag_media_sync_lock'); }
}

function ag_sync_media_run($dry_run = true) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    $manifest = ag_media_manifest(); $packs = ag_handoff_packs(); $reviews = ag_media_reviews();
    $report = ['dry_run' => $dry_run, 'matched' => 0, 'missing' => [], 'preserved' => [], 'record_metadata_updates' => 0, 'source_registry_updates' => 0, 'embed_updates' => 0, 'photos_imported' => [], 'photos_reused' => [], 'photos_ready' => [], 'photos_pending' => [], 'placeholders' => [], 'credits_page' => 'unchanged'];
    foreach ($packs as $slug => $pack) {
        $artist = ag_handoff_find($slug);
        $records = [['record' => $pack['artist'], 'id' => $artist, 'key' => $slug . '/profile']];
        foreach ($pack['songs'] as $song) { $records[] = ['record' => $song, 'id' => ag_handoff_find($slug, $song, $artist), 'key' => $slug . '/' . $song['slug']]; }
        foreach ($records as $row) {
            $id = $row['id']; if (!$id) { $report['missing'][] = $row['key']; continue; } $report['matched']++;
            if (!ag_handoff_can_update($id, $row['key'])) { $report['preserved'][] = $row['key']; continue; }
            if (get_post_meta($id, '_ag_handoff_record', true) !== $row['record']) {
                $report['record_metadata_updates']++; if (!$dry_run) { update_post_meta($id, '_ag_handoff_record', $row['record']); }
            }
            if ($id === $artist && get_post_meta($id, '_ag_source_registry', true) !== $pack['sources']) {
                $report['source_registry_updates']++; if (!$dry_run) { update_post_meta($id, '_ag_source_registry', $pack['sources']); }
            }
            if ($id !== $artist) {
                $url = ag_spotify_embed_url($id);
                if ($url && get_post_meta($id, '_ag_spotify_embed', true) !== $url) { $report['embed_updates']++; if (!$dry_run) { update_post_meta($id, '_ag_spotify_embed', $url); } }
            }
        }
    }
    foreach ($manifest['artists'] as $candidate) {
        $slug = $candidate['artist_slug']; $review = $reviews[$slug] ?? [];
        if (!$candidate['publish_image']) { $report['placeholders'][] = $slug; continue; }
        if (!ag_photo_review_valid($candidate, $review)) { $report['photos_pending'][] = $slug; continue; }
        $report['photos_ready'][] = $slug;
        if (!$dry_run) {
            $path = __DIR__ . '/media-assets/' . $slug . '.jpg';
            $artist_id = ag_handoff_find($slug); $previous = get_post_thumbnail_id($artist_id);
            $result = ag_import_verified_photo($artist_id, $candidate, $review, $path);
            if (is_wp_error($result)) { $report['photos_pending'][] = $slug; } elseif ($previous === $result) { $report['photos_reused'][$slug] = $result; } else { $report['photos_imported'][$slug] = $result; }
        }
    }
    foreach (array_diff(array_keys($packs), array_column($manifest['artists'], 'artist_slug')) as $slug) { $report['placeholders'][] = $slug; }
    $page = get_page_by_path('media-credits', OBJECT, 'page'); $content = ag_media_credits_content();
    if (!$page) {
        $report['credits_page'] = 'create';
        if (!$dry_run) { $id = wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'media-credits', 'post_title' => 'Media credits.', 'post_content' => $content], true); if (is_wp_error($id)) { return $id; } update_post_meta($id, '_ag_demo', '1'); update_post_meta($id, '_ag_media_page_baseline', $content); }
    } elseif ($page->post_content !== $content) {
        if (get_post_meta($page->ID, '_ag_media_page_baseline', true) !== $page->post_content) { $report['credits_page'] = 'preserved_editor_content'; }
        else { $report['credits_page'] = 'update'; if (!$dry_run) { wp_update_post(['ID' => $page->ID, 'post_content' => $content]); update_post_meta($page->ID, '_ag_media_page_baseline', $content); } }
    }
    $privacy = get_page_by_path('privacy', OBJECT, 'page');
    $migration = json_decode(file_get_contents(__DIR__ . '/data/privacy-media-migration.json'), true);
    $report['privacy_page'] = 'unchanged';
    if ($privacy && hash('sha256', $privacy->post_content) === $migration['old_sha256']) {
        $report['privacy_page'] = 'update_starter';
        if (!$dry_run) { wp_update_post(['ID' => $privacy->ID, 'post_content' => str_replace($migration['old_paragraph'], $migration['new_paragraph'], $privacy->post_content)]); }
    } elseif (!$privacy || !str_contains($privacy->post_content, $migration['new_paragraph'])) { $report['privacy_page'] = 'owner_review_required'; }
    if (!$dry_run) { update_option('ag_media_last_report', $report, false); }
    return $report;
}

add_action('admin_menu', function () { add_submenu_page('edit.php?post_type=artist', 'Media and embeds', 'Media and embeds', 'manage_options', 'ag-media', 'ag_media_page'); });
function ag_media_page() {
    if (!current_user_can('manage_options')) { return; }
    $report = ag_sync_media(true); if (is_wp_error($report)) { return; }
    echo '<div class="wrap"><h1>Media and embeds</h1><p>Dry run: ' . esc_html($report['matched']) . ' existing records matched; ' . count($report['missing']) . ' missing; ' . count($report['preserved']) . ' edited records preserved. ' . esc_html($report['embed_updates']) . ' embed mappings and ' . esc_html($report['record_metadata_updates']) . ' supplied-record metadata updates planned.</p><p>Sync does not replace biographies, publish research drafts, download covers, or approve candidate photographs. ' . count($report['placeholders']) . ' artists retain placeholders pending separately verified photo permissions. Missing track links do not produce embeds.</p><table class="widefat striped"><thead><tr><th>Artist</th><th>Permission status</th><th>Candidate license</th></tr></thead><tbody>';
    $reviews = ag_media_reviews();
    $status_labels = ['live_verified' => 'Live copyright license verified', 'pending_live_verification' => 'Live source review pending', 'placeholder_permission_unresolved' => 'Permission unresolved — keep placeholder'];
    $candidates = array_column(ag_media_manifest()['artists'], null, 'artist_slug');
    foreach (ag_handoff_packs() as $slug => $pack) {
        $a = $candidates[$slug] ?? null;
        $status = $reviews[$slug]['status'] ?? ($a ? 'pending_live_verification' : 'placeholder_permission_unresolved');
        echo '<tr data-review-status="' . esc_attr($status) . '"><td>' . esc_html($pack['artist']['name']) . '</td><td>' . esc_html($status_labels[$status] ?? 'Review pending') . '</td><td>' . esc_html($a['image']['license'] ?? 'No established permission') . '</td></tr>';
    }
    echo '</tbody></table><form action="' . esc_url(admin_url('admin-post.php')) . '" method="post"><input type="hidden" name="action" value="ag_sync_media">'; wp_nonce_field('ag_sync_media'); submit_button('Sync checked mappings and credits'); echo '</form><p>Live source review is required before a candidate photo becomes eligible. See docs/MEDIA_SYNC_PLAN.md and the media review register for current blockers.</p></div>';
}
add_action('admin_post_ag_sync_media', function () {
    if (!current_user_can('manage_options') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Not permitted.', '', ['response' => 403]); }
    check_admin_referer('ag_sync_media'); $result = ag_sync_media(false);
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()), '', ['response' => 400]); }
    wp_safe_redirect(admin_url('edit.php?post_type=artist&page=ag-media')); exit;
});
