<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_scope_test_') { throw new RuntimeException('Disposable namespace required.'); }
$checks = 0;
$assert = function ($ok, $message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } $checks++; };
$public_context = function () { return true; };
try {
    wp_set_current_user(get_user_by('login', 'editor')->ID);
    $new = ag_import_reviewed(false, true);
    $assert(!is_wp_error($new), 'Initialize reviewed enrichment.');
    // Synthetic visibility fixtures; no historical artists are imported.
    $hidden_artist = wp_insert_post(['post_type' => 'artist', 'post_title' => 'Hidden scope fixture', 'post_name' => 'hidden-scope-fixture', 'post_status' => 'publish']);
    update_post_meta($hidden_artist, '_ag_handoff_key', 'hidden-scope-fixture/profile');
    $own = wp_insert_post(['post_type' => 'song', 'post_title' => 'Hidden owned fixture', 'post_status' => 'publish']);
    update_post_meta($own, '_ag_handoff_key', 'hidden-scope-fixture/owned');
    update_post_meta($own, '_ag_artist_id', (string)$hidden_artist);
    $hidden_song = wp_insert_post(['post_type' => 'song', 'post_title' => 'Hidden omitted fixture', 'post_status' => 'publish']);
    update_post_meta($hidden_song, '_ag_handoff_key', 'clara-mae/omitted-fixture');
    update_post_meta($hidden_song, '_ag_artist_id', (string)$new['ids']['clara-mae/profile']);
    $core_song = $new['ids']['caleb-hearn/little-bit-better'];
    update_post_meta($hidden_artist, '_ag_recommended', implode(',', [$own, $core_song]));
    update_post_meta($new['ids']['clara-mae/profile'], '_ag_recommended', get_post_meta($new['ids']['clara-mae/profile'], '_ag_recommended', true) . ',' . $hidden_song);
    $before = hash('sha256', wp_json_encode([$wpdb->get_results("SELECT * FROM {$wpdb->posts} ORDER BY ID", ARRAY_A), $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} ORDER BY meta_id", ARRAY_A)]));
    $scope = ag_enable_core_catalog();
    $assert(!is_wp_error($scope) && $scope['artists'] === 10 && $scope['songs'] === 128 && $scope['hidden_records'] === 3, 'Exact public scope excludes synthetic hidden fixtures.');
    $after = hash('sha256', wp_json_encode([$wpdb->get_results("SELECT * FROM {$wpdb->posts} ORDER BY ID", ARRAY_A), $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} ORDER BY meta_id", ARRAY_A)]));
    $assert($before === $after, 'Scope must not mutate any content, statuses, metadata, revisions or import baselines.');
    $assert(!ag_public_catalog_visible($hidden_artist) && !ag_public_catalog_visible($hidden_song) && ag_public_catalog_visible($core_song), 'Hidden profiles/old selections excluded; canonical duet included.');
    $assert(count(get_posts(['post_type' => 'artist', 'numberposts' => -1])) === 11, 'CLI/editor workflow keeps hidden fixtures.');
    add_filter('ag_public_catalog_context', $public_context);
    $assert(count(get_posts(['post_type' => 'artist', 'numberposts' => -1])) === 10, 'Guest artist directory.');
    $assert(count(get_posts(['post_type' => 'song', 'numberposts' => -1])) === 128, 'Guest song directory.');
    $assert(!get_posts(['post_type' => 'artist', 'post__in' => [$hidden_artist], 'numberposts' => -1]), 'Explicit hidden-ID inclusion must not bypass the scope.');
    $assert(!get_posts(['post_type' => 'song', 'post__in' => [$hidden_song], 'numberposts' => -1]), 'An empty intersection must not return the full catalog.');
    $assert(!get_posts(['post_type' => 'artist', 'name' => 'hidden-scope-fixture', 'numberposts' => -1]), 'Hidden direct artist query is empty.');
    $assert(!get_posts(['post_type' => 'artist', 'post_name__in' => ['hidden-scope-fixture'], 'numberposts' => -1]), 'Explicit hidden slug filters must remain empty.');
    $assert(count(get_posts(['post_type' => 'artist', 'post_name__in' => ['astrid-s'], 'numberposts' => -1])) === 1, 'Approved slug filters must not expand to the entire public catalog.');
    $public = new WP_Query(['post_type' => ['artist', 'song'], 'posts_per_page' => -1]);
    $assert($public->found_posts === 138, 'Combined public search/feed query has only approved records.');
    $assert(count(ag_discovery_catalog()) === 128, 'Recommendations use only the public song catalog.');
    $assert(!in_array($hidden_song, ag_related_ids(ag_handoff_find('clara-mae'), 'recommended'), true), 'Stored links to omitted songs are hidden in public collections.');
    wp_set_current_user(0);
    foreach (['/wp/v2/artist/' . $hidden_artist, '/wp/v2/song/' . $hidden_song] as $path) {
        $response = rest_do_request(new WP_REST_Request('GET', $path));
        $assert($response->get_status() === 404, 'Hidden REST detail must return 404: ' . $path);
    }
    $request = new WP_REST_Request('GET', '/wp/v2/song'); $request->set_param('include', [$hidden_song]);
    $response = rest_do_request($request);
    $assert($response->get_status() === 200 && $response->get_data() === [], 'REST include parameter cannot leak excluded songs.');
    $request = new WP_REST_Request('GET', '/afterglow/v1/saved'); $request->set_param('ids', (string)$hidden_song);
    $response = rest_do_request($request);
    $assert($response->get_status() === 200 && $response->get_data() === [], 'Existing saved IDs cannot retrieve hidden cards.');
    $assert(!get_oembed_response_data($hidden_artist, 600), 'Hidden profiles do not expose oEmbed metadata.');
    $assert(is_wp_error(ag_enable_core_catalog()) && is_wp_error(ag_restore_full_catalog_visibility()), 'Visitors cannot change visibility.');
    wp_set_current_user(get_user_by('login', 'editor')->ID);
    $request = new WP_REST_Request('GET', '/wp/v2/artist/' . $hidden_artist); $request->set_param('context', 'edit');
    $assert(rest_do_request($request)->get_status() === 200, 'Editors retain access to hidden records.');
    $future = wp_insert_post(['post_type' => 'artist', 'post_title' => 'A future artist', 'post_status' => 'publish']);
    update_post_meta($future, '_ag_handoff_key', 'future-artist/profile');
    wp_set_object_terms($future, ['Hidden-only country'], 'country');
    $assert(!ag_public_catalog_visible($future) && count(get_posts(['post_type' => 'artist', 'numberposts' => -1])) === 10, 'New imports cannot expand the public catalog automatically.');
    $terms = get_terms(['taxonomy' => 'country', 'hide_empty' => true, 'fields' => 'names']);
    $assert(!in_array('Hidden-only country', $terms, true), 'Public filter options exclude hidden-only taxonomy values.');
    wp_delete_post($future, true);
    $assert(is_wp_error(ag_add_public_catalog_selection($hidden_artist, [])), 'Adding an artist requires explicit song selections.');
    $added = ag_add_public_catalog_selection($hidden_artist, [$core_song]);
    $assert(!is_wp_error($added) && $added['artists'] === 11 && $added['songs'] === 128, 'A shared duet reuses the already public page when adding a later artist.');
    $assert(count(get_posts(['post_type' => 'artist', 'numberposts' => -1])) === 11, 'Controlled expansion reveals only the selected next artist.');
    $added = ag_add_public_catalog_selection($hidden_artist, [$own]);
    $assert(!is_wp_error($added) && $added['artists'] === 11 && $added['songs'] === 129, 'Controlled expansion includes only the selected additional song.');
    $again = ag_add_public_catalog_selection($hidden_artist, [$own]);
    $assert($again === $added, 'Adding the same artist/song again is idempotent.');
    $assert(is_wp_error(ag_add_public_catalog_selection($hidden_artist, [$hidden_song])) && ag_public_catalog_scope() === $again, 'Unrelated selections cannot be added under another artist.');
    $assert(ag_restore_full_catalog_visibility() === true && count(get_posts(['post_type' => 'artist', 'numberposts' => -1])) === 11, 'Visibility is reversible without reimporting content.');
    WP_CLI::success($checks . ' public-catalog scope, API, preservation and restoration checks passed.');
} finally {
    remove_filter('ag_public_catalog_context', $public_context);
    foreach ($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like('ag_scope_test_') . '%')) as $table) {
        if (!preg_match('/\Aag_scope_test_[a-z0-9_]+\z/', $table)) { throw new RuntimeException('Unexpected test table.'); }
        $wpdb->query('DROP TABLE `' . $table . '`');
    }
}
