<?php
defined('ABSPATH') || exit;

function ag_handoff_packs() {
    $packs = [];
    foreach (['astrid-s', 'sara-kays', 'caleb-hearn'] as $slug) {
        $data = json_decode(file_get_contents(__DIR__ . '/data/' . $slug . '.json'), true);
        if (!is_array($data) || ($data['schema_version'] ?? '') !== 'afterglow.research.v1' || ($data['artist']['slug'] ?? '') !== $slug || count($data['songs'] ?? []) !== 10) {
            throw new RuntimeException('Invalid bundled research package: ' . $slug);
        }
        $ids = array_column($data['sources'], 'id');
        if (count(array_unique($ids)) !== count($ids)) { throw new RuntimeException('Duplicate source IDs.'); }
        $validate = function ($value) use (&$validate, $ids) {
            if (!is_array($value)) { return; }
            foreach ($value as $key => $child) {
                if (is_string($key) && str_ends_with($key, 'source_ids')) {
                    foreach ($child ?? [] as $id) { if (!in_array($id, $ids, true)) { throw new RuntimeException('Unknown source reference.'); } }
                }
                $validate($child);
            }
        };
        $validate($data); $packs[$slug] = $data;
    }
    return $packs;
}

function ag_handoff_state($id) {
    $post = get_post($id);
    $state = ['title' => $post->post_title, 'content' => $post->post_content, 'excerpt' => $post->post_excerpt, 'status' => $post->post_status, 'meta' => [], 'terms' => []];
    foreach (ag_field_definitions($post->post_type) as $key => $field) {
        $value = (string)get_post_meta($id, '_ag_' . $key, true);
        if (in_array($key, ['artist_id', 'related', 'recommended'], true)) {
            $value = array_map(function ($target) { $p = get_post(absint($target)); return $p ? $p->post_type . ':' . $p->post_name : 'missing'; }, array_filter(explode(',', $value)));
        }
        if ($key === 'similarity') {
            $value = preg_replace_callback('/^(\d+)\s*\|/m', function ($m) { $p = get_post((int)$m[1]); return ($p ? $p->post_type . ':' . $p->post_name : 'missing') . '|'; }, $value);
        }
        $state['meta'][$key] = $value;
    }
    $state['meta']['thumbnail'] = (string)get_post_thumbnail_id($id);
    foreach (['genre', 'mood', 'country'] as $tax) {
        $terms = wp_get_object_terms($id, $tax, ['fields' => 'slugs']);
        if (is_wp_error($terms)) { $terms = []; } sort($terms); $state['terms'][$tax] = $terms;
    }
    return $state;
}

function ag_handoff_key($slug, $song = null) { return $slug . '/' . ($song ? $song['slug'] : 'profile'); }
function ag_handoff_find($slug, $song = null, $artist_id = 0) {
    $type = $song ? 'song' : 'artist';
    $args = ['post_type' => $type, 'post_status' => ['publish', 'draft', 'private', 'pending', 'future', 'trash'], 'numberposts' => -1];
    $owned = get_posts($args + ['meta_key' => '_ag_handoff_key', 'meta_value' => ag_handoff_key($slug, $song)]);
    if ($owned) { return $owned[0]->ID; }
    if (!$song) {
        if ($slug === 'astrid-s' && ($p = ag_astrid_artist())) { return $p->ID; }
        $p = get_page_by_path($slug, OBJECT, 'artist'); return $p ? $p->ID : 0;
    }
    if ($slug === 'astrid-s' && ($id = ag_astrid_find_song($song['slug'], $song['title'], $artist_id))) { return $id; }
    $items = get_posts($args + ['meta_key' => '_ag_artist_id', 'meta_value' => (string)$artist_id]);
    $normalize = function ($t) { return strtolower(str_replace(['’', '‘'], "'", $t)); };
    foreach ($items as $p) {
        if (($song['spotify_url'] && get_post_meta($p->ID, '_ag_spotify', true) === $song['spotify_url']) || $normalize($p->post_title) === $normalize($song['title'])) { return $p->ID; }
    }
    return 0;
}

function ag_handoff_can_update($id, $key) {
    if (!$id) { return true; }
    if (get_post_status($id) === 'trash') { return false; }
    $state = ag_handoff_state($id);
    $last = get_post_meta($id, '_ag_handoff_baseline', true);
    if ($last) { return $last === $state; }
    $seeds = json_decode(file_get_contents(__DIR__ . '/data/seed-baselines.json'), true);
    return isset($seeds[$key]) && $seeds[$key] === $state;
}

function ag_handoff_sources($pack, $ids) {
    $out = [];
    foreach ($pack['sources'] as $source) {
        if (in_array($source['id'], $ids, true) && ($source['access_status'] ?? '') === 'read' && filter_var($source['url'], FILTER_VALIDATE_URL) && wp_parse_url($source['url'], PHP_URL_SCHEME) === 'https') { $out[$source['id']] = $source; }
    }
    return $out;
}
function ag_handoff_claim_ids($record) {
    $ids = [];
    foreach ($record['supported_claims'] ?? [] as $claim) { $ids = array_merge($ids, $claim['source_ids'] ?? []); }
    return array_unique(array_merge($ids, $record['editorial_notes_source_ids'] ?? [], $record['artist_statement_source_ids'] ?? []));
}
function ag_handoff_links($pack, $record) {
    $lines = [];
    foreach (ag_handoff_sources($pack, ag_handoff_claim_ids($record)) as $s) { $lines[] = sanitize_text_field($s['publisher'] . ' — ' . $s['title']) . ' | ' . esc_url_raw($s['url']); }
    return implode("\n", $lines);
}
function ag_handoff_tags($values) {
    $out = [];
    foreach ($values ?? [] as $v) {
        $v = sanitize_text_field(trim(preg_replace('/\s*\([^)]*\)/', '', $v)));
        if ($v !== '') { $out[] = $v; }
    }
    return array_unique($out);
}
function ag_handoff_paragraph($text) { return $text ? wpautop(esc_html($text)) : ''; }
function ag_handoff_content($pack, $record, $artist) {
    $html = '';
    if ($artist) {
        $html .= ag_handoff_paragraph($record['biography']);
        $html .= '<h2>A curated way into the music</h2>' . ag_handoff_paragraph($pack['selection']['description']);
        $html .= '<p>This selection is editorial, not a Spotify popularity ranking. Genre and mood tags reflect editorial judgments.</p>';
    } else {
        $html .= '<h2>Listening notes</h2>' . ag_handoff_paragraph($record['editorial_notes'] ?: 'A source-backed interpretation has not yet been added for this recording.');
        if ($record['editorial_notes_basis']) { $html .= '<p><strong>Basis:</strong> ' . esc_html($record['editorial_notes_basis']) . '. No independent audio listening is claimed.</p>'; }
        if ($record['release_date']) {
            $html .= '<h2>Release information</h2><p>' . esc_html($record['release_date']) . ' (' . esc_html($record['release_date_precision']) . ' precision)';
            if ($record['release_title']) { $html .= ' · ' . esc_html($record['release_title']); }
            if ($record['release_type']) { $html .= ' · ' . esc_html($record['release_type']); }
            $html .= '</p>' . ag_handoff_paragraph($record['release_date_context']);
        }
    }
    if (!empty($record['uncertainties'])) {
        $html .= '<h2>Research notes</h2><ul>';
        foreach ($record['uncertainties'] as $note) { $html .= '<li>' . esc_html($note) . '</li>'; } $html .= '</ul>';
    }
    $html .= '<p><small>Based on the supplied research dated ' . esc_html($pack['research_date']) . '. Source links accompany this page; details marked uncertain remain open for review.</small></p>';
    return $html;
}

function ag_handoff_save($id, $pack, $record, $artist, $artist_id, $publish) {
    $key = ag_handoff_key($pack['artist']['slug'], $artist ? null : $record);
    $args = ['post_type' => $artist ? 'artist' : 'song', 'post_title' => $artist ? $record['name'] : $record['title'], 'post_content' => ag_handoff_content($pack, $record, $artist), 'post_excerpt' => $record['short_description'] ?: '', 'post_status' => $publish ? 'publish' : ($id ? get_post_status($id) : 'draft')];
    if ($id) { $args['ID'] = $id; } else { $args['post_name'] = $artist ? $record['slug'] : $pack['artist']['slug'] . '-' . $record['slug']; }
    $id = wp_insert_post($args, true);
    if (is_wp_error($id)) { return $id; }
    $meta = ['demo' => '1', 'spotify' => $record['spotify_url'] ?: '', 'sources' => ag_handoff_links($pack, $record)];
    if ($artist) {
        $meta['website'] = $record['official_website'] ?: '';
        $social = [];
        foreach ($record['social_profiles'] as $s) { if (ag_handoff_sources($pack, $s['source_ids'])) { $social[] = sanitize_text_field($s['platform']) . ' | ' . esc_url_raw($s['url'], ['https']); } }
        $meta['social'] = implode("\n", $social);
        wp_set_object_terms($id, $record['country'] ? [$record['country']] : [], 'country');
    } else {
        $comment_sources = ag_handoff_sources($pack, $record['artist_statement_source_ids']);
        $transcript = array_filter($comment_sources, function ($s) { return str_contains(strtolower($s['source_type']), 'transcript'); });
        $comment = $record['artist_statement_paraphrase'] && $comment_sources && !$transcript ? $record['artist_statement_paraphrase'] : '';
        $meta += ['artist_id' => (string)$artist_id, 'credits' => implode(', ', $record['artist_credits']), 'themes' => implode(', ', $record['themes']), 'artist_comment' => $comment, 'meaning_type' => $comment ? 'sourced' : 'editorial', 'release' => $record['release_date_precision'] === 'day' ? $record['release_date'] : ''];
    }
    foreach ($meta as $field => $value) { update_post_meta($id, '_ag_' . $field, $value); }
    if (!get_post_meta($id, '_ag_palette', true)) { update_post_meta($id, '_ag_palette', $artist ? 'sand' : 'blue'); }
    foreach (['genre' => 'genres', 'mood' => 'moods'] as $tax => $field) { wp_set_object_terms($id, ag_handoff_tags($record[$field]), $tax); }
    update_post_meta($id, '_ag_handoff_key', $key);
    update_post_meta($id, '_ag_handoff_record', $record);
    update_post_meta($id, '_ag_handoff_date', $pack['research_date']);
    return $id;
}

function ag_apply_handoffs($publish = true) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    if (!add_option('ag_handoff_lock', time(), '', false)) { return new WP_Error('busy', 'An import is running. If it was interrupted, an administrator can clear its import lock after inspection.'); }
    $report = ['updated' => [], 'created' => [], 'preserved' => []]; $editable = []; $maps = [];
    try {
        foreach (ag_handoff_packs() as $slug => $pack) {
            $artist_id = ag_handoff_find($slug); $key = ag_handoff_key($slug);
            if (ag_handoff_can_update($artist_id, $key)) {
                $old = $artist_id; $artist_id = ag_handoff_save($artist_id, $pack, $pack['artist'], true, 0, $publish);
                if (is_wp_error($artist_id)) { return $artist_id; }
                $report[$old ? 'updated' : 'created'][] = $key; $editable[$key] = $artist_id;
            } else { $report['preserved'][] = $key; }
            $maps[$slug] = ['artist' => $artist_id, 'songs' => []];
            foreach ($pack['songs'] as $song) {
                $key = ag_handoff_key($slug, $song); $id = ag_handoff_find($slug, $song, $artist_id);
                if (ag_handoff_can_update($id, $key)) {
                    $old = $id; $id = ag_handoff_save($id, $pack, $song, false, $artist_id, $publish);
                    if (is_wp_error($id)) { return $id; }
                    $report[$old ? 'updated' : 'created'][] = $key; $editable[$key] = $id;
                } else { $report['preserved'][] = $key; }
                $maps[$slug]['songs'][$song['slug']] = $id;
            }
        }
        foreach (ag_handoff_packs() as $slug => $pack) {
            $key = ag_handoff_key($slug); $map = $maps[$slug];
            if (isset($editable[$key])) {
                update_post_meta($map['artist'], '_ag_recommended', implode(',', $map['songs']));
                $related = []; $notes = [];
                foreach ($pack['artist']['similar_artists'] as $similar) {
                    $posts = get_posts(['post_type' => 'artist', 'post_status' => ['publish', 'draft'], 'title' => $similar['name'], 'numberposts' => 1]);
                    if ($posts && $posts[0]->ID !== $map['artist']) { $related[] = $posts[0]->ID; $notes[] = $posts[0]->ID . ' | ' . $similar['explanation']; }
                }
                update_post_meta($map['artist'], '_ag_related', implode(',', $related)); update_post_meta($map['artist'], '_ag_similarity', implode("\n", $notes));
            }
            foreach ($pack['songs'] as $song) {
                $key = ag_handoff_key($slug, $song);
                if (!isset($editable[$key])) { continue; }
                $related = [];
                foreach ($song['related_songs'] as $other) { if (isset($map['songs'][$other['song_slug']])) { $related[] = $map['songs'][$other['song_slug']]; } }
                update_post_meta($map['songs'][$song['slug']], '_ag_related', implode(',', $related));
            }
        }
        foreach ($editable as $id) { update_post_meta($id, '_ag_handoff_baseline', ag_handoff_state($id)); }
        update_option('ag_handoff_last_report', $report, false);
        return $report;
    } finally { delete_option('ag_handoff_lock'); }
}

add_action('admin_menu', function () {
    add_submenu_page('edit.php?post_type=artist', 'Import supplied research', 'Import supplied research', 'manage_options', 'ag-handoffs', 'ag_handoff_page');
});
function ag_handoff_page() {
    echo '<div class="wrap"><h1>Import supplied research</h1><p>Three supplied research packages dated 25 October 2026: Astrid S, Sara Kays, and Caleb Hearn. Each contains ten curated songs, not a ranked top ten.</p><p>Unchanged starter records are updated; edited records and records in Trash are preserved. New pages remain noindex with the review flag. Artist photographs are not imported. The automatic-transcript statement for It’s Ok If You Forget Me is withheld pending audio verification.</p>';
    $report = get_option('ag_handoff_last_report');
    if ($report) { echo '<div class="notice notice-info inline"><p>Last import: ' . count($report['created']) . ' created, ' . count($report['updated']) . ' updated, ' . count($report['preserved']) . ' preserved.</p></div>'; }
    echo '<table class="widefat striped"><thead><tr><th>Record</th><th>Action</th></tr></thead><tbody>';
    foreach (ag_handoff_packs() as $slug => $pack) {
        $artist_id = ag_handoff_find($slug);
        $records = [[$pack['artist']['name'], ag_handoff_key($slug), $artist_id]];
        foreach ($pack['songs'] as $song) { $records[] = [$song['title'], ag_handoff_key($slug, $song), ag_handoff_find($slug, $song, $artist_id)]; }
        foreach ($records as [$title, $key, $id]) {
            echo '<tr><td>' . esc_html($title) . '</td><td>' . esc_html(!$id ? 'Create' : (ag_handoff_can_update($id, $key) ? 'Update unchanged record' : 'Preserve your edits / trash'));
            if ($id) { echo ' · <a href="' . esc_url(get_edit_post_link($id)) . '">Open record</a>'; } echo '</td></tr>';
        }
    }
    echo '</tbody></table><form action="' . esc_url(admin_url('admin-post.php')) . '" method="post"><input type="hidden" name="action" value="ag_apply_handoffs">'; wp_nonce_field('ag_apply_handoffs');
    echo '<p><label><input type="checkbox" name="show_on_site" value="1" checked> Show imported pages on the site for review (keep noindex). Uncheck to keep new pages as drafts and preserve existing statuses.</label></p>';
    submit_button('Apply supplied research'); echo '</form><h2>Preserved records</h2><p>If a row says Preserve, its content differs from the original starter or its last import. Review it manually using the bundled package; this importer will not overwrite your work.</p></div>';
}
add_action('admin_post_ag_apply_handoffs', function () {
    if (!current_user_can('manage_options') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Not permitted.', '', ['response' => 403]); }
    check_admin_referer('ag_apply_handoffs');
    $result = ag_apply_handoffs(($_POST['show_on_site'] ?? '') === '1');
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()), '', ['response' => 400]); }
    wp_safe_redirect(admin_url('edit.php?post_type=artist&page=ag-handoffs')); exit;
});
