<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_discovery_test_') { throw new RuntimeException('Disposable namespace required.'); }
$GLOBALS['ag_discovery_checks'] = 0;
function ag_discovery_assert($ok, $message) { if (!$ok) { throw new RuntimeException($message); } $GLOBALS['ag_discovery_checks']++; }
function ag_fixture_song($artist, $name, $themes = '', $mood = '', $status = 'publish') {
    $id = wp_insert_post(['post_type' => 'song', 'post_title' => $name, 'post_status' => $status]);
    update_post_meta($id, '_ag_artist_id', (string)$artist);
    update_post_meta($id, '_ag_themes', $themes);
    if ($mood) { wp_set_object_terms($id, [$mood], 'mood'); }
    return $id;
}
try {
    wp_set_current_user(0);
    $artists = [];
    foreach (['A', 'B', 'C', 'D'] as $name) { $artists[] = wp_insert_post(['post_type' => 'artist', 'post_title' => 'Discovery fixture artist ' . $name, 'post_status' => 'publish']); }
    $current = ag_fixture_song($artists[0], 'Current fixture', 'breakup (editorial)', 'wistful');
    $same = ag_fixture_song($artists[0], 'Same artist fixture', 'breakup', 'wistful');
    $other = ag_fixture_song($artists[1], 'Other artist fixture', 'breakup', 'wistful');
    $hopeful = ag_fixture_song($artists[2], 'Hopeful fixture', 'moving on', 'hopeful');
    $third = ag_fixture_song($artists[3], 'Third artist fixture', 'breakup', 'wistful');
    $draft = ag_fixture_song($artists[1], 'Draft fixture', 'breakup', 'wistful', 'draft');
    $private = ag_fixture_song($artists[1], 'Private fixture', 'breakup', 'wistful', 'private');
    $orphan = ag_fixture_song(99999999, 'Orphan fixture', 'breakup', 'wistful');
    $hidden_artist = wp_insert_post(['post_type' => 'artist', 'post_title' => 'Private artist fixture', 'post_status' => 'private']);
    $hidden = ag_fixture_song($hidden_artist, 'Hidden artist recording', 'breakup', 'wistful');
    update_post_meta($current, '_ag_related', (string)$same);
    $before = get_post_meta($current);
    $catalog = ag_discovery_catalog();
    ag_discovery_assert(count($catalog) === 5, 'Only published, connected non-demo recordings are eligible.');
    foreach ([$draft, $private, $orphan, $hidden] as $id) { ag_discovery_assert(!isset($catalog[$id]), 'Draft, private, orphaned, and private-artist songs must not leak.'); }
    $context = ag_discovery_context($current);
    ag_discovery_assert($context['preferences']['theme'] === 'same', 'Available current themes should be the initial route.');
    ag_discovery_assert(count($context['results']) === 3, 'Three matching suggestions should be returned when available.');
    ag_discovery_assert($context['results'][0]['artist'] !== $artists[0], 'A genuine matching different artist should be the next discovery.');
    ag_discovery_assert(count(array_unique(array_column($context['results'], 'artist'))) === 3, 'Results should diversify artists when possible.');
    foreach ($context['results'] as $row) {
        ag_discovery_assert($row['id'] !== $current && isset($row['themes']['breakup']), 'Recommendations must exclude the current track and honor its theme.');
        ag_discovery_assert(str_contains($row['reason'], 'breakup'), 'Each themed result must explain its actual editorial match.');
    }
    $filtered = ag_discovery_context($current, ['ag_feeling' => 'hopeful', 'ag_theme' => 'moving-on']);
    ag_discovery_assert(array_column($filtered['results'], 'id') === [$hopeful], 'Explicit filters must intersect and must not be silently broadened.');
    ag_discovery_assert(!ag_discovery_context($current, ['ag_feeling' => 'hopeful', 'ag_theme' => 'breakup'])['results'], 'An unsupported combination must have an honest empty state.');
    $invalid = ag_discovery_context($current, ['ag_feeling' => ['bad'], 'ag_theme' => '<script>bad</script>']);
    ag_discovery_assert($invalid['invalid'] && $invalid['results'], 'Malformed inputs must safely restore a real default route.');
    ag_discovery_assert(ag_discovery_context($draft) === null && ag_discovery_context($artists[0]) === null, 'Only published song pages expose discovery contexts.');
    $trail = ag_discovery_trail($other . ',' . $draft . ',' . $current . ',bad,' . $other, $catalog, $current);
    ag_discovery_assert($trail === [$other], 'Trail must contain unique eligible IDs, not private records or the current track.');
    ag_discovery_assert(ag_discovery_trail(['bad'], $catalog, $current) === [] && ag_discovery_trail(str_repeat('1', 161), $catalog, $current) === [], 'Oversized and array trails must be rejected.');
    $visited = ag_discovery_context($current, ['ag_trail' => (string)$other]);
    ag_discovery_assert(!in_array($other, array_column($visited['results'], 'id'), true), 'Recent discoveries should not immediately repeat.');
    ag_discovery_assert(get_post_meta($current) === $before, 'Discovery must never rewrite source records, tags, or editorial metadata.');
    ag_discovery_assert(count(ag_discovery_recommend($catalog[$current], $catalog, ['feeling' => 'any', 'theme' => 'any'], [], 500)) === 3, 'Public result sets remain bounded.');
    $unknown = ag_fixture_song($artists[1], 'Untagged fixture');
    $unknown_context = ag_discovery_context($unknown);
    ag_discovery_assert($unknown_context['preferences'] === ['feeling' => 'any', 'theme' => 'any'] && $unknown_context['results'], 'Untagged songs need a useful catalog route without fabricated matching tags.');
    foreach ($unknown_context['results'] as $row) { ag_discovery_assert(!str_contains($row['reason'], 'Shares an'), 'Untagged song recommendations must not claim a theme or mood match.'); }
    $eligible = count(ag_discovery_catalog());
    for ($i = count(get_posts(['post_type' => 'artist', 'post_status' => 'publish', 'numberposts' => -1])); $i < 50; $i++) { $artists[] = wp_insert_post(['post_type' => 'artist', 'post_title' => 'Scale fixture artist ' . $i, 'post_status' => 'publish']); }
    for ($i = $eligible; $i < 500; $i++) { ag_fixture_song($artists[$i % count($artists)], 'Scale fixture song ' . $i, $i % 2 ? 'friendship' : 'moving on', $i % 2 ? 'wistful' : 'hopeful'); }
    wp_cache_flush();
    $queries = get_num_queries(); $start = microtime(true); $large = ag_discovery_catalog(); $elapsed = microtime(true) - $start; $queries = get_num_queries() - $queries;
    ag_discovery_assert(count($large) === 500, 'The index must cover five hundred eligible recordings.');
    ag_discovery_assert($queries <= 20, 'Catalog must bulk-load metadata and terms rather than query per song. Queries: ' . $queries);
    $many = ag_discovery_recommend($large[$current], $large, ['feeling' => 'any', 'theme' => 'any']);
    ag_discovery_assert(count($many) === 3 && count(array_unique(array_column($many, 'id'))) === 3, 'Larger catalogs retain bounded, distinct suggestions.');
    ag_discovery_assert(count(ag_discovery_trail(implode(',', array_slice(array_keys($large), 0, 20)), $large, $current)) === 8, 'Long discovery journeys must remain bounded to eight recent songs.');
    WP_CLI::success($GLOBALS['ag_discovery_checks'] . ' discovery checks passed; 500-song index: ' . $queries . ' queries, ' . round($elapsed * 1000) . ' ms.');
} finally {
    foreach ($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like('ag_discovery_test_') . '%')) as $table) {
        if (!preg_match('/\Aag_discovery_test_[a-z0-9_]+\z/', $table)) { throw new RuntimeException('Unexpected table name.'); }
        $wpdb->query('DROP TABLE `' . $table . '`');
    }
}
