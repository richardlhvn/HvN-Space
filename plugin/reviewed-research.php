<?php
/** Explicit, protected enrichment from the owner's reviewed workbook export. */
defined('ABSPATH') || exit;

function ag_reviewed_data() {
    $source = json_decode(file_get_contents(__DIR__ . '/data/canonical-research-source.json'), true, 512, JSON_THROW_ON_ERROR);
    $snapshot = $source['prepared_snapshot'] ?? null;
    if (!is_string($snapshot) || !$snapshot) {
        throw new RuntimeException('The canonical workbook has changed. Import is paused until an export of the cleaned Google Sheet is available: ' . $source['url']);
    }
    if (basename($snapshot) !== $snapshot || !preg_match('/^[a-z0-9-]+\.json$/D', $snapshot)) { throw new RuntimeException('Invalid canonical snapshot path.'); }
    $path = __DIR__ . '/data/' . $snapshot;
    if (!is_readable($path)) { throw new RuntimeException('The prepared canonical snapshot is not available. Supply the cleaned workbook export before importing.'); }
    $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    ag_assert_canonical_reviewed_data($data, $source);
    return $data;
}

function ag_assert_canonical_reviewed_data($data, $source = null) {
    $source = $source ?? json_decode(file_get_contents(__DIR__ . '/data/canonical-research-source.json'), true, 512, JSON_THROW_ON_ERROR);
    if (($data['provenance']['spreadsheet_id'] ?? '') !== $source['spreadsheet_id'] || ($data['provenance']['approval_count'] ?? 0) !== $source['expected_selections'] || !is_array($data['packs'] ?? null) || count($data['packs']) !== $source['expected_artists']) {
        throw new RuntimeException('Import dataset provenance or scope does not match the cleaned canonical workbook.');
    }
}

function ag_reviewed_validate($data) {
    $scope = ['astrid-s', 'caleb-hearn', 'chelsea-cutler', 'clara-mae', 'elina', 'mokita', 'rosie-darling', 'sara-kays', 'sasha-alex-sloan', 'shy-martin'];
    if (($data['schema_version'] ?? '') !== 'afterglow.reviewed.v1' || !is_array($data['packs'] ?? null) || array_keys($data['packs']) !== $scope) {
        throw new RuntimeException('Invalid reviewed dataset or artist scope.');
    }
    $keys = []; $tracks = [];
    foreach ($data['packs'] as $slug => $pack) {
        if (($pack['artist']['slug'] ?? '') !== $slug || !is_array($pack['songs'] ?? null) || !$pack['songs']) { throw new RuntimeException('Invalid reviewed artist.'); }
        $sources = array_column($pack['sources'], null, 'id');
        if (count($sources) !== count($pack['sources'])) { throw new RuntimeException('Duplicate artist-scoped source ID.'); }
        $references = function ($value) use (&$references, $sources) {
            if (!is_array($value)) { return; }
            foreach ($value as $field => $child) {
                if (is_string($field) && str_ends_with($field, 'source_ids') && (!is_array($child) || array_diff($child, array_keys($sources)))) { throw new RuntimeException('Invalid artist-scoped reference.'); }
                $references($child);
            }
        };
        $references($pack['artist']);
        foreach ($pack['songs'] as $song) {
            $key = $slug . '/' . ($song['slug'] ?? '');
            if (($song['artist_slug'] ?? '') !== $slug || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $song['slug'] ?? '') || isset($keys[$key]) || ($song['review']['key'] ?? '') !== $key || ($song['review']['decision'] ?? '') !== 'verified') { throw new RuntimeException('Invalid, duplicate or unapproved song key.'); }
            if (!preg_match('~^https://open\.spotify\.com/track/[A-Za-z0-9]{22}$~D', $song['spotify_url'] ?? '') || isset($tracks[$song['spotify_url']])) { throw new RuntimeException('Invalid or duplicate reviewed recording.'); }
            if (!is_string($song['about_song'] ?? null) || !$song['about_song'] || !in_array($song['about_song_attribution'] ?? '', ag_about_attributions(), true) || array_diff($song['about_song_type'] ?? [], ag_about_types())) { throw new RuntimeException('Invalid reviewed About the Song.'); }
            $references($song);
            foreach ($song['about_song_source_urls'] as $url) {
                if (!ag_about_https($url) || (!in_array($url, array_column($sources, 'url'), true) && !in_array($url, $song['review']['unregistered_source_urls'] ?? [], true))) { throw new RuntimeException('Unresolved reviewed evidence URL.'); }
            }
            foreach ($song['about_song_source_ids'] as $source_id) {
                $source = $sources[$source_id];
                if (!ag_about_https($source['url']) || preg_match('/safety hold|domain unverified/i', $source['access_status'])) { throw new RuntimeException('Unsafe or unusable reviewed source.'); }
            }
            $keys[$key] = true; $tracks[$song['spotify_url']] = $key;
        }
    }
    if (count($keys) !== ($data['provenance']['approval_count'] ?? -1)) { throw new RuntimeException('Approval count mismatch.'); }
}

function ag_reviewed_state($id) {
    $state = ag_handoff_state($id);
    // Protect evidence and raw research edits as well as visible editor fields.
    foreach (['_ag_source_registry', '_ag_handoff_record', '_ag_spotify_embed', '_ag_reviewed_approval'] as $key) { $state[$key] = get_post_meta($id, $key, true); }
    return $state;
}

function ag_reviewed_can_update($id, $key) {
    if (!$id) { return true; }
    if (get_post_status($id) === 'trash' || get_post_meta($id, '_ag_handoff_key', true) !== $key) { return false; }
    $last = get_post_meta($id, '_ag_reviewed_baseline', true);
    return $last ? $last === ag_reviewed_state($id) : ag_handoff_can_update($id, $key);
}

function ag_reviewed_find($slug, $song = null) {
    if (!$song) { return ag_handoff_find($slug); }
    $key = $slug . '/' . $song['slug'];
    $items = get_posts(['post_type' => 'song', 'post_status' => ['publish', 'draft', 'private', 'pending', 'future', 'trash'], 'numberposts' => -1, 'meta_key' => '_ag_handoff_key', 'meta_value' => $key]);
    if (count($items) > 1) { throw new RuntimeException('Duplicate existing key: ' . $key); }
    if ($items) { return $items[0]->ID; }
    // Reuse the historical identity/alias resolver, but never take over another
    // managed key just because it happens to have the same title or track URL.
    $id = ag_handoff_find($slug, $song, ag_handoff_find($slug));
    if ($id && get_post_meta($id, '_ag_handoff_key', true) !== $key) { throw new RuntimeException('Recording identity requires review: ' . $key); }
    return $id;
}

function ag_reviewed_links($pack, $record) {
    $ids = ag_handoff_claim_ids($record); $lines = [];
    foreach ($pack['sources'] as $source) {
        if (in_array($source['id'], $ids, true) && ag_about_https($source['url']) && !preg_match('/safety hold|domain unverified/i', $source['access_status'])) {
            $note = $source['access_status'] === 'read' ? '' : ' (editor-reviewed evidence; independent check pending)';
            $lines[$source['url']] = $source['publisher'] . ' — ' . $source['title'] . $note . ' | ' . $source['url'];
        }
    }
    foreach ($record['review']['unregistered_source_urls'] ?? [] as $url) { $lines[$url] = wp_parse_url($url, PHP_URL_HOST) . ' — editor-supplied reference; source metadata pending | ' . $url; }
    return implode("\n", $lines);
}

function ag_reviewed_source_links($id, $ids, $urls) {
    $approval = get_post_meta($id, '_ag_reviewed_approval', true);
    $record = get_post_meta($id, '_ag_handoff_record', true);
    if (!is_array($approval) || ($approval['decision'] ?? '') !== 'verified' || !is_array($record) || ($approval['key'] ?? '') !== get_post_meta($id, '_ag_handoff_key', true)) { return null; }
    // A reviewed exemption applies only to the exact imported copy/evidence.
    // Later manual changes use the ordinary evidence resolution rules.
    if ($ids !== ($record['about_song_source_ids'] ?? []) || $urls !== ($record['about_song_source_urls'] ?? []) || get_post_meta($id, '_ag_about_song', true) !== ($record['about_song'] ?? '') || get_post_meta($id, '_ag_about_song_attribution', true) !== ($record['about_song_attribution'] ?? '')) { return null; }
    $registry = get_post_meta($id, '_ag_source_registry', true);
    if (!is_array($registry)) { return []; }
    $registry = array_column($registry, null, 'id'); $links = [];
    foreach ($ids as $source_id) {
        $source = $registry[$source_id] ?? null;
        if (!$source || !ag_about_https($source['url'])) { return []; }
        $note = $source['access_status'] === 'read' ? '' : ' · Editor-reviewed evidence; independent check pending';
        if (str_contains(strtolower($source['source_type']), 'transcript')) { $note .= ' · Transcript; primary-audio verification pending'; }
        $links[$source['url']] = ['label' => $source['publisher'] . ' — ' . $source['title'] . $note, 'url' => $source['url'], 'type' => $source['source_type'], 'editor_reviewed' => true];
    }
    foreach ($urls as $url) {
        if (isset($links[$url])) { continue; }
        if (!in_array($url, $approval['unregistered_source_urls'] ?? [], true) || !ag_about_https($url)) { return []; }
        $links[$url] = ['label' => wp_parse_url($url, PHP_URL_HOST) . ' · Editor-supplied reference; source metadata pending', 'url' => $url, 'type' => 'editor_supplied_reference'];
    }
    return array_values($links);
}

function ag_import_reviewed($dry_run = true, $show_new_pages = false, $data = null) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    try { $data = $data ?? ag_reviewed_data(); ag_assert_canonical_reviewed_data($data); ag_reviewed_validate($data); }
    catch (Throwable $e) { return new WP_Error('invalid_reviewed_data', $e->getMessage()); }
    if (!$dry_run && !add_option('ag_handoff_lock', time(), '', false)) { return new WP_Error('busy', 'Another research import is running.'); }
    $report = ['dry_run' => $dry_run, 'created' => [], 'updated' => [], 'skipped' => [], 'conflicts' => [], 'rejected' => [], 'failed' => [], 'ids' => []];
    $plans = []; $seen_ids = [];
    global $wpdb;
    try {
        foreach ($data['packs'] as $slug => $pack) {
            $artist = ag_reviewed_find($slug);
            foreach (array_merge([$pack['artist']], $pack['songs']) as $record) {
                $is_artist = !isset($record['artist_slug']);
                $key = $slug . '/' . ($is_artist ? 'profile' : $record['slug']);
                $id = $is_artist ? $artist : ag_reviewed_find($slug, $record);
                if (!$is_artist) {
                    $recordings = get_posts(['post_type' => 'song', 'post_status' => ['publish', 'draft', 'private', 'pending', 'future', 'trash'], 'numberposts' => -1, 'meta_key' => '_ag_spotify', 'meta_value' => $record['spotify_url']]);
                    foreach ($recordings as $recording) {
                        if ($recording->ID !== $id) { throw new RuntimeException('Existing recording needs explicit shared-page review: ' . $key); }
                    }
                }
                if ($id && isset($seen_ids[$id])) { throw new RuntimeException('Two reviewed selections resolve to the same page.'); }
                if ($id) { $seen_ids[$id] = $key; }
                $hash = hash('sha256', wp_json_encode([$record, $pack['sources'], $data['provenance']]));
                $action = !$id ? 'created' : (!ag_reviewed_can_update($id, $key) ? 'conflicts' : (get_post_meta($id, '_ag_reviewed_payload_hash', true) === $hash ? 'skipped' : 'updated'));
                $plans[$key] = compact('id', 'key', 'record', 'is_artist', 'slug', 'artist', 'hash', 'action');
                $report[$action][] = $key; $report['ids'][$key] = $id;
            }
        }
        if ($dry_run) { return $report; }
        // All dataset/identity checks finish before any post writes. A database
        // failure rolls the batch back, allowing a clean retry from its report.
        $wpdb->query('START TRANSACTION');
        foreach ($plans as $key => &$plan) {
            if (!in_array($plan['action'], ['created', 'updated'], true)) { continue; }
            $pack = $data['packs'][$plan['slug']]; $record = $plan['record'];
            $parent = $plan['is_artist'] ? 0 : $plans[$plan['slug'] . '/profile']['id'];
            if (!$plan['is_artist'] && !$parent) { throw new RuntimeException('The approved parent artist could not be created: ' . $plan['slug']); }
            $id = ag_handoff_save($plan['id'], $pack, $record, $plan['is_artist'], $parent, false);
            if (is_wp_error($id)) { throw new RuntimeException($key . ': ' . $id->get_error_message()); }
            if ($plan['action'] === 'created' && $show_new_pages) {
                $result = wp_update_post(['ID' => $id, 'post_status' => 'publish'], true);
                if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
            }
            $plan['id'] = $id; $report['ids'][$key] = $id;
            // Re-store nested structures with WordPress's required slashing.
            update_post_meta($id, '_ag_handoff_record', wp_slash($record));
            update_post_meta($id, '_ag_source_registry', wp_slash($pack['sources']));
            update_post_meta($id, '_ag_sources', wp_slash(ag_reviewed_links($pack, $record)));
            update_post_meta($id, '_ag_reviewed_payload_hash', $plan['hash']);
            if (!$plan['is_artist']) {
                update_post_meta($id, '_ag_reviewed_approval', wp_slash($record['review']));
                update_post_meta($id, '_ag_spotify_embed', ag_spotify_embed_url($id));
            }
            update_post_meta($id, '_ag_reviewed_provenance', wp_slash($data['provenance']));
        }
        unset($plan);
        foreach ($plans as $key => $plan) {
            if (!in_array($plan['action'], ['created', 'updated'], true)) { continue; }
            $id = $plan['id']; $pack = $data['packs'][$plan['slug']];
            if ($plan['is_artist']) {
                $approved = [];
                foreach ($pack['songs'] as $song) { $approved[] = $report['ids'][$plan['slug'] . '/' . $song['slug']]; }
                // Preserve prior selections omitted by this enrichment workbook.
                $existing = array_filter(explode(',', get_post_meta($id, '_ag_recommended', true)));
                update_post_meta($id, '_ag_recommended', implode(',', array_unique(array_merge($approved, $existing))));
                $related = []; $notes = [];
                foreach ($pack['artist']['similar_artists'] ?? [] as $similar) {
                    $matches = get_posts(['post_type' => 'artist', 'post_status' => ['publish', 'draft'], 'title' => $similar['name'], 'numberposts' => 1]);
                    if ($matches && $matches[0]->ID !== $id) { $related[] = $matches[0]->ID; $notes[] = $matches[0]->ID . ' | ' . $similar['explanation']; }
                }
                update_post_meta($id, '_ag_related', implode(',', $related));
                update_post_meta($id, '_ag_similarity', wp_slash(implode("\n", $notes)));
            } else {
                $related = array_filter(explode(',', get_post_meta($id, '_ag_related', true)));
                foreach ($plan['record']['related_songs'] as $other) {
                    $target = $report['ids'][$plan['slug'] . '/' . $other['song_slug']] ?? ag_reviewed_find($plan['slug'], ['slug' => $other['song_slug'], 'title' => '', 'spotify_url' => null]);
                    if ($target && $target !== $id) { $related[] = $target; }
                }
                update_post_meta($id, '_ag_related', implode(',', array_unique($related)));
            }
            // Intentionally do not refresh the historical import baseline:
            // the old research and media sync must not revert reviewed data.
            update_post_meta($id, '_ag_reviewed_baseline', wp_slash(ag_reviewed_state($id)));
        }
        $wpdb->query('COMMIT');
        update_option('ag_reviewed_last_report', $report, false);
        return $report;
    } catch (Throwable $e) {
        if (!$dry_run) { $wpdb->query('ROLLBACK'); wp_cache_flush(); }
        $report['failed'][] = $e->getMessage();
        $report['rolled_back'] = !$dry_run;
        return new WP_Error('reviewed_import_failed', $e->getMessage(), $report);
    } finally { if (!$dry_run) { delete_option('ag_handoff_lock'); } }
}

function ag_sync_canonical_photos() {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    try { $data = ag_reviewed_data(); ag_reviewed_validate($data); }
    catch (Throwable $e) { return new WP_Error('invalid_reviewed_data', $e->getMessage()); }
    $report = ['imported' => [], 'reused' => [], 'placeholders' => [], 'preserved' => []];
    $reviews = ag_media_reviews();
    foreach (ag_media_manifest()['artists'] as $candidate) {
        $slug = $candidate['artist_slug'];
        if (!isset($data['packs'][$slug])) { continue; }
        if (!$candidate['publish_image']) { $report['placeholders'][] = $slug; continue; }
        $rights = $data['packs'][$slug]['media_rights'];
        foreach (['media_file_page_url' => 'source_file_page_url', 'media_license_url' => 'license_url', 'media_photographer' => 'photographer'] as $field => $expected) {
            if (($rights[$field] ?? '') !== $candidate['image'][$expected]) { return new WP_Error('rights_mismatch', 'Canonical rights differ from the cleared local asset; review before importing: ' . $slug); }
        }
        $artist = ag_reviewed_find($slug);
        if (ag_media_credit($artist)) { $report['reused'][] = $slug; continue; }
        $result = ag_import_verified_photo($artist, $candidate, $reviews[$slug] ?? [], __DIR__ . '/media-assets/' . $slug . '.jpg');
        if (is_wp_error($result)) { $report['preserved'][$slug] = $result->get_error_message(); }
        else { $report['imported'][] = $slug; }
    }
    if (!get_page_by_path('media-credits', OBJECT, 'page')) {
        $content = ag_media_credits_content();
        $page = wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'media-credits', 'post_title' => 'Media credits.', 'post_content' => $content], true);
        if (is_wp_error($page)) { return $page; }
        update_post_meta($page, '_ag_demo', '1');
        update_post_meta($page, '_ag_media_page_baseline', $content);
    }
    return $report;
}

add_action('admin_menu', function () {
    add_submenu_page('edit.php?post_type=artist', 'Reviewed workbook enrichment', 'Reviewed workbook enrichment', 'manage_options', 'ag-reviewed', function () {
        if (!current_user_can('manage_options')) { return; }
        $report = ag_import_reviewed(true);
        echo '<div class="wrap"><h1>Reviewed workbook enrichment</h1><p>Enrich the ten existing artists using 128 approved selections. Retain all other catalog records, existing URLs and editor changes. New pages stay noindex. This does not upload spreadsheets or deploy the site.</p>';
        if (is_wp_error($report)) { echo '<p>' . esc_html($report->get_error_message()) . '</p></div>'; return; }
        echo '<h2>Dry run</h2>';
        foreach (['created', 'updated', 'skipped', 'conflicts', 'rejected', 'failed'] as $type) { echo '<p>' . esc_html(ucfirst($type) . ': ' . count($report[$type])) . '</p>'; }
        if ($report['conflicts']) { echo '<p>Conflicts preserve existing editor changes; review these manually:</p><pre>' . esc_html(implode("\n", $report['conflicts'])) . '</pre>'; }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ag_import_reviewed">';
        wp_nonce_field('ag_import_reviewed');
        echo '<p><label><input type="checkbox" name="show_new_pages" value="1"> Show new pages on this local/staging site for review (noindex).</label></p>';
        submit_button('Apply reviewed enrichment'); echo '</form></div>';
    });
});
add_action('admin_post_ag_import_reviewed', function () {
    if (!current_user_can('manage_options') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Not permitted.', '', ['response' => 403]); }
    check_admin_referer('ag_import_reviewed');
    $result = ag_import_reviewed(false, ($_POST['show_new_pages'] ?? '') === '1');
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()), '', ['response' => 400]); }
    wp_safe_redirect(admin_url('edit.php?post_type=artist&page=ag-reviewed')); exit;
});
