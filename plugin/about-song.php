<?php
defined('ABSPATH') || exit;

function ag_about_types() {
    return ['song-meaning', 'creative-process', 'artist-feelings', 'collaboration', 'inspiration', 'song-background', 'interesting-facts'];
}
function ag_about_fields() {
    return ['about_song', 'about_song_type', 'about_song_source_urls', 'about_song_source_ids', 'about_song_attribution'];
}
function ag_about_attributions() {
    return ['artist_statement', 'third_party_reporting', 'afterglow_editorial', 'mixed'];
}
function ag_about_values($value) {
    if (is_array($value)) { return array_values(array_unique(array_filter($value, 'is_string'))); }
    $json = json_decode((string) $value, true);
    if (is_array($json)) { return ag_about_values($json); }
    return array_values(array_unique(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $value)))));
}
function ag_about_https($url) {
    return is_string($url) && filter_var($url, FILTER_VALIDATE_URL) && wp_parse_url($url, PHP_URL_SCHEME) === 'https';
}
function ag_about_validate($record, $pack) {
    $present = array_intersect(ag_about_fields(), array_keys($record));
    if (!$present) { return; }
    if (!array_key_exists('about_song', $record) || (!is_string($record['about_song']) && $record['about_song'] !== null)) { throw new RuntimeException('About the Song must be text or null.'); }
    foreach (['about_song_type', 'about_song_source_urls', 'about_song_source_ids'] as $key) {
        if (!is_array($record[$key] ?? [])) { throw new RuntimeException('About the Song lists must be JSON arrays.'); }
        foreach ($record[$key] ?? [] as $value) { if (!is_string($value)) { throw new RuntimeException('About the Song list entries must be strings.'); } }
        if (count($record[$key] ?? []) !== count(array_unique($record[$key] ?? []))) { throw new RuntimeException('About the Song lists must not contain duplicates.'); }
    }
    if (array_diff($record['about_song_type'] ?? [], ag_about_types())) { throw new RuntimeException('Unknown About the Song category.'); }
    $attribution = $record['about_song_attribution'] ?? '';
    if ($attribution && !in_array($attribution, ag_about_attributions(), true)) { throw new RuntimeException('Unknown About the Song attribution.'); }
    $registry = array_column($pack['sources'], null, 'id');
    foreach ($record['about_song_source_ids'] ?? [] as $source_id) {
        if (!isset($registry[$source_id])) { throw new RuntimeException('Unknown artist-scoped About the Song source.'); }
    }
    foreach ($record['about_song_source_urls'] ?? [] as $url) {
        if (!ag_about_https($url)) { throw new RuntimeException('About the Song sources must use HTTPS.'); }
    }
    if (!empty($record['about_song'])) {
        if (!$attribution) { throw new RuntimeException('About the Song requires explicit attribution.'); }
        $sources = ag_handoff_sources($pack, $record['about_song_source_ids'] ?? []);
        if (count($sources) !== count($record['about_song_source_ids'] ?? [])) { throw new RuntimeException('Every About the Song source must be usable; retain unread evidence in the research queue.'); }
        if ($attribution !== 'afterglow_editorial' && !$sources) { throw new RuntimeException('Factual About the Song imports require accessible sources in the artist registry.'); }
        foreach ($sources as $source) {
            if ($attribution === 'artist_statement' && str_contains(strtolower($source['source_type']), 'transcript')) { throw new RuntimeException('Unconfirmed transcript cannot establish an artist statement.'); }
        }
        $source_urls = array_column($sources, 'url');
        if (array_diff($record['about_song_source_urls'] ?? [], $source_urls)) { throw new RuntimeException('About the Song URLs must match their scoped source IDs.'); }
    }
}
function ag_about_import_meta($id, $record) {
    foreach (ag_about_fields() as $key) {
        if (array_key_exists($key, $record)) {
            $value = $record[$key];
            update_post_meta($id, '_ag_' . $key, wp_slash(is_array($value) ? implode("\n", $value) : (string) $value));
        }
    }
}
function ag_about_source_links($id, $ids, $urls) {
    $links = []; $resolved = []; $registry = get_post_meta($id, '_ag_source_registry', true);
    // Early catalog imports kept the full registry on the artist. Resolve only
    // that matching scope; src-001 on a different artist is never equivalent.
    if (!is_array($registry)) {
        $artist = (int) get_post_meta($id, '_ag_artist_id', true);
        $scope = explode('/', (string) get_post_meta($id, '_ag_handoff_key', true))[0];
        if ($scope && get_post_meta($artist, '_ag_handoff_key', true) === $scope . '/profile') {
            $registry = get_post_meta($artist, '_ag_source_registry', true);
        }
    }
    foreach (is_array($registry) ? $registry : [] as $source) {
        if (in_array($source['id'] ?? '', $ids, true) && ($source['access_status'] ?? '') === 'read' && ($source['source_type'] ?? '') !== 'website_current_status' && ag_about_https($source['url'] ?? '')) {
            $resolved[$source['id']] = true;
            $links[$source['url']] = ['label' => $source['publisher'] . ' — ' . $source['title'], 'url' => $source['url'], 'type' => $source['source_type']];
        }
    }
    // Explicit URLs entered by an editor are review decisions, not automatic
    // proof. Imports must resolve every URL through their scoped source IDs.
    foreach ($urls as $url) {
        if (ag_about_https($url) && !isset($links[$url]) && !$ids) {
            $links[$url] = ['label' => wp_parse_url($url, PHP_URL_HOST), 'url' => $url, 'type' => 'editor_supplied'];
        }
    }
    if ($ids && (count($resolved) !== count($ids) || array_diff($urls, array_keys($links)))) { return []; }
    return array_values($links);
}
function ag_about_text($text) {
    return trim(html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}
function ag_about_song($id, $editorial_html = '') {
    $empty = ['text' => '', 'types' => [], 'attribution' => '', 'sources' => [], 'origin' => 'empty'];
    if (metadata_exists('post', $id, '_ag_about_song')) {
        $text = trim((string) get_post_meta($id, '_ag_about_song', true));
        $attribution = get_post_meta($id, '_ag_about_song_attribution', true);
        $sources = ag_about_source_links($id, ag_about_values(get_post_meta($id, '_ag_about_song_source_ids', true)), ag_about_values(get_post_meta($id, '_ag_about_song_source_urls', true)));
        if (!$text || !in_array($attribution, ag_about_attributions(), true) || ($attribution !== 'afterglow_editorial' && !$sources)) { return $empty; }
        if ($attribution === 'artist_statement' && array_filter($sources, function ($s) { return str_contains(strtolower($s['type']), 'transcript'); })) { return $empty; }
        return ['text' => $text, 'types' => array_values(array_intersect(ag_about_values(get_post_meta($id, '_ag_about_song_type', true)), ag_about_types())), 'attribution' => $attribution, 'sources' => $sources, 'origin' => 'explicit'];
    }
    $sources = [];
    foreach (explode("\n", get_post_meta($id, '_ag_sources', true)) as $line) {
        $pair = array_map('trim', explode('|', $line, 2));
        if (count($pair) === 2 && ag_about_https($pair[1])) { $sources[] = ['label' => $pair[0], 'url' => $pair[1], 'type' => 'legacy_editor_review']; }
    }
    $comment = trim((string) get_post_meta($id, '_ag_artist_comment', true));
    if ($comment && get_post_meta($id, '_ag_meaning_type', true) === 'sourced' && $sources) {
        $record = get_post_meta($id, '_ag_handoff_record', true);
        $scoped = ag_about_source_links($id, $record['artist_statement_source_ids'] ?? [], []);
        if (!empty($record['artist_statement_source_ids']) && !$scoped) { return $empty; }
        if (array_filter($scoped, function ($s) { return str_contains(strtolower($s['type']), 'transcript'); })) { return $empty; }
        if ($scoped) { $sources = $scoped; }
        return ['text' => $comment, 'types' => [], 'attribution' => 'artist_statement', 'sources' => $sources, 'origin' => 'legacy_statement'];
    }
    $text = ag_about_text($editorial_html);
    // Sparse catalog/import guidance is retained in research details, not used
    // to manufacture an interesting reader-facing story.
    if (!$text || !$sources || $text === ag_about_text(get_post_field('post_excerpt', $id)) || preg_match('/not yet been added|await[s]? (editorial|research)|catalogue candidate|catalogue-backed|curated (catalogue|starter)|no (verified|song-specific|lyric-level)|entry point|insufficient .*evidence|has not been established/i', $text)) { return $empty; }
    return ['text' => $text, 'types' => [], 'attribution' => 'afterglow_editorial', 'sources' => $sources, 'origin' => 'legacy_editorial'];
}

// An explicit, additive migration only for newly researched optional fields.
// Legacy records map at read time; no mass text rewrite or automatic upgrade.
function ag_sync_about_songs($dry_run = true) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    $report = ['dry_run' => $dry_run, 'added' => [], 'preserved' => [], 'missing' => []];
    if (!$dry_run && !add_option('ag_handoff_lock', time(), '', false)) { return new WP_Error('busy', 'A research update is already running.'); }
    try {
        foreach (ag_handoff_packs() as $slug => $pack) {
            foreach ($pack['songs'] as $song) {
                if (!array_key_exists('about_song', $song) || isset(ag_handoff_aliases()[ag_handoff_key($slug, $song)])) { continue; }
                $key = ag_handoff_key($slug, $song); $id = ag_handoff_find($slug, $song, ag_handoff_find($slug));
                if (!$id) { $report['missing'][] = $key; continue; }
                $has_about = false;
                foreach (ag_about_fields() as $field) { $has_about = $has_about || metadata_exists('post', $id, '_ag_' . $field); }
                if ($has_about || !ag_handoff_can_update($id, $key)) { $report['preserved'][] = $key; continue; }
                $report['added'][] = $key;
                if (!$dry_run) {
                    ag_about_import_meta($id, $song);
                    update_post_meta($id, '_ag_handoff_baseline', ag_handoff_state($id));
                }
            }
        }
    } finally { if (!$dry_run) { delete_option('ag_handoff_lock'); } }
    return $report;
}
add_action('admin_menu', function () {
    add_submenu_page('edit.php?post_type=song', 'About the Song', 'About the Song', 'manage_options', 'ag-about-song', function () {
        if (!current_user_can('manage_options')) { return; }
        $report = ag_sync_about_songs();
        echo '<div class="wrap"><h1>About the Song</h1><p>Existing supported artist statements map automatically. Meaning, creative process, feelings, collaboration, inspiration and documented background are all valid; none is mandatory. Empty stories remain hidden.</p><p>Back up the database before applying the additive update. Existing editor changes and every existing About the Song field are preserved. This does not reimport the catalog.</p><h2>New sourced background available</h2><ul>';
        foreach ($report['added'] as $key) { echo '<li>' . esc_html($key) . '</li>'; }
        echo '</ul><p>' . count($report['added']) . ' additions, ' . count($report['preserved']) . ' preserved, ' . count($report['missing']) . ' missing records.</p><form action="' . esc_url(admin_url('admin-post.php')) . '" method="post"><input type="hidden" name="action" value="ag_sync_about_songs">';
        wp_nonce_field('ag_sync_about_songs'); submit_button('Add sourced song background'); echo '</form></div>';
    });
});
add_action('admin_post_ag_sync_about_songs', function () {
    if (!current_user_can('manage_options') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Not permitted.', '', ['response' => 403]); }
    check_admin_referer('ag_sync_about_songs');
    $report = ag_sync_about_songs(false);
    if (is_wp_error($report)) { wp_die(esc_html($report->get_error_message()), '', ['response' => 400]); }
    wp_safe_redirect(admin_url('edit.php?post_type=song&page=ag-about-song')); exit;
});
