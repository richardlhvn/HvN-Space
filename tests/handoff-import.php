<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_handoff_test_') { throw new RuntimeException('A disposable namespace is required.'); }
$GLOBALS['ag_handoff_checks'] = 0;
function ag_handoff_assert($value, $message) { if (!$value) { throw new RuntimeException($message); } $GLOBALS['ag_handoff_checks']++; }
try {
    wp_set_current_user(0); ag_handoff_assert(is_wp_error(ag_apply_handoffs()), 'Anonymous imports must be rejected.');
    wp_set_current_user(get_user_by('login', 'editor')->ID);
    $packs = ag_handoff_packs(); $expected = array_sum(array_map(function ($p) { return 1 + count($p['songs']); }, $packs));
    ag_handoff_assert(count($packs) === 20 && $expected === 220, 'All twenty registered artist packages must be available.');
    ag_handoff_assert(count(ag_handoff_collection_packs('2026-10-29')) === 10, 'Latest-date collection must contain both new batches only.');
    ag_handoff_assert(is_wp_error(ag_apply_handoffs(true, ['not-a-package'])), 'Invalid scope must be rejected before importing.');
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
    ag_handoff_assert(count($result['created']) + count($result['updated']) === $expected && !$result['preserved'], 'All untouched catalog targets must import.');
    ag_handoff_assert(ag_handoff_find('sara-kays') === $old_sara, 'Existing artist URL/ID must remain stable.');
    foreach (ag_handoff_packs() as $slug => $pack) {
        $artist_id = ag_handoff_find($slug);
        ag_handoff_assert(get_post_status($artist_id) === 'publish', 'Artist must appear on preview site.');
        ag_handoff_assert(count(explode(',', ag_meta('recommended', $artist_id))) === 10, 'Artist must recommend ten tracks.');
        ag_handoff_assert(str_contains(preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags(get_post($artist_id)->post_content), ENT_QUOTES, 'UTF-8')), preg_replace('/\s+/u', ' ', $pack['artist']['biography'])), 'Biography must be populated.');
        foreach ($pack['songs'] as $song) {
            $id = ag_handoff_find($slug, $song, $artist_id);
            ag_handoff_assert($id && ag_meta('artist_id', $id) === (string)$artist_id, 'Every track must link to its artist.');
            ag_handoff_assert(ag_meta('spotify', $id) === (string)($song['spotify_url'] ?? ''), 'Official recording URL or deliberate null must be mapped.');
            ag_handoff_assert(ag_meta('demo', $id) === '1' && get_post_status($id) === 'publish', 'Preview pages must keep noindex review status.');
            ag_handoff_assert(ag_meta('credits', $id) === implode(', ', $song['artist_credits']), 'Collaborator credits must be retained.');
            if ($pack['research_date'] === '2026-10-29') {
                ag_handoff_assert(get_post_meta($id, '_ag_handoff_record', true) === $song, 'Raw research, nulls, date context, and source references must survive.');
                ag_handoff_assert(get_post_meta($id, '_ag_source_scope', true) === $slug && get_post_meta($id, '_ag_source_registry', true) === $pack['sources'], 'Source registries must be artist-scoped, never merged by bare source ID.');
                if ($song['spotify_url'] === null) {
                    ag_handoff_assert(ag_spotify_embed_url($id) === '', 'Missing track URLs must not acquire an embed.');
                    ob_start(); ag_listen($id); $listen = ob_get_clean();
                    ag_handoff_assert(str_contains($listen, $pack['artist']['spotify_url']) && str_contains($listen, 'Explore artist on Spotify'), 'Missing track must link to its verified artist profile.');
                }
            }
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
    ag_handoff_assert(!$again['created'] && !$again['preserved'] && count($again['updated']) === $expected, 'Repeat imports must be safe and duplicate-free.');
    $old_states = [];
    foreach ($packs as $slug => $p) { if ($p['research_date'] === '2026-10-29') { continue; } $a = ag_handoff_find($slug); $old_states[$a] = ag_handoff_state($a); foreach ($p['songs'] as $s) { $sid = ag_handoff_find($slug, $s, $a); $old_states[$sid] = ag_handoff_state($sid); } }
    $scoped = ag_apply_handoffs(true, array_keys(ag_handoff_collection_packs('2026-10-29')));
    ag_handoff_assert(count($scoped['updated']) === 110 && !$scoped['created'] && !$scoped['preserved'], 'Scoped reimport must touch only the 110 new records.');
    foreach ($old_states as $sid => $state) { ag_handoff_assert(ag_handoff_state($sid) === $state, 'Scoped import must preserve each previous record.'); }
    $loote = ag_handoff_find('loote'); $loote_songs = array_column($packs['loote']['songs'], null, 'slug');
    $review = ag_handoff_find('loote', $loote_songs['85'], $loote);
    ag_handoff_assert(ag_meta('artist_comment', $review) === '' && ag_meta('meaning_type', $review) === 'editorial', 'A reviewer interpretation without artist-statement sources must remain withheld.');
    $duet = ag_handoff_find('loote', $loote_songs['this-is-the-last-song-about-you'], $loote);
    ag_handoff_assert(in_array($duet, ag_collaboration_ids(ag_handoff_find('anson-seabra')), true), 'Anson must link to the existing Loote collaboration rather than duplicate it.');
    ag_handoff_assert(str_contains(ag_recording_credits_html($duet), get_permalink(ag_handoff_find('anson-seabra'))), 'Recording credits must link known collaborators.');
    $fly = ag_handoff_find('fly-by-midnight'); $fly_songs = array_column($packs['fly-by-midnight']['songs'], null, 'slug');
    $different = array_values(array_filter($fly_songs, function ($s) { return in_array('Rachel Grae', $s['artist_credits'], true); }))[0];
    $different_id = ag_handoff_find('fly-by-midnight', $different, $fly);
    ag_handoff_assert(in_array($different_id, ag_collaboration_ids(ag_handoff_find('rachel-grae')), true), 'Rachel must link to Fly By Midnight’s single collaboration record.');
    $rachel_colors = array_values(array_filter($packs['rachel-grae']['songs'], function ($s) { return $s['slug'] === 'colorblind'; }))[0];
    $mokita_colors = array_values(array_filter($packs['mokita']['songs'], function ($s) { return $s['slug'] === 'colorblind'; }))[0];
    ag_handoff_assert(ag_handoff_find('rachel-grae', $rachel_colors, ag_handoff_find('rachel-grae')) !== ag_handoff_find('mokita', $mokita_colors, $mokita), 'Unrelated recordings with the same title must stay separate.');
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
