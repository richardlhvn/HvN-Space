<?php
/** Reversible public visibility, independent of stored records/import baselines. */
defined('ABSPATH') || exit;

function ag_public_catalog_scope() { return get_option('ag_public_catalog_scope', false); }

function ag_public_catalog_ids($refresh = false) {
    static $cache = [];
    if ($refresh) { $cache = []; }
    $scope = ag_public_catalog_scope();
    if (!$scope) { return null; } // No scope configured: preserve historic behavior.
    if (!is_array($scope['keys'] ?? null) || !$scope['keys']) { return []; }
    $cache_key = hash('sha256', serialize($scope['keys']));
    if (isset($cache[$cache_key])) { return $cache[$cache_key]; }
    global $wpdb;
    $marks = implode(',', array_fill(0, count($scope['keys']), '%s'));
    $rows = $wpdb->get_results($wpdb->prepare("SELECT p.ID, p.post_type, m.meta_value AS record_key FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON p.ID=m.post_id WHERE p.post_type IN ('artist','song') AND m.meta_key='_ag_handoff_key' AND m.meta_value IN ($marks)", $scope['keys']));
    $by_key = [];
    foreach ($rows as $row) {
        // Ambiguous identities fail closed instead of making a duplicate public.
        if (isset($by_key[$row->record_key])) { return $cache[$cache_key] = []; }
        $by_key[$row->record_key] = (int)$row->ID;
    }
    return $cache[$cache_key] = array_values($by_key);
}
foreach (['added_post_meta', 'updated_post_meta', 'deleted_post_meta'] as $hook) {
    add_action($hook, function ($meta_id, $post_id, $key) {
        if ($key === '_ag_handoff_key') { ag_public_catalog_ids(true); }
    }, 10, 3);
}

function ag_public_catalog_visible($id) {
    if (!in_array(get_post_type($id), ['artist', 'song'], true)) { return true; }
    $ids = ag_public_catalog_ids();
    return $ids === null || in_array((int)$id, $ids, true);
}

function ag_public_catalog_context() {
    $public = !is_admin() && !(defined('WP_CLI') && WP_CLI);
    // Authenticated editor APIs retain the complete working directory.
    if (defined('REST_REQUEST') && REST_REQUEST && current_user_can('edit_posts')) { $public = false; }
    if (is_preview() && current_user_can('edit_posts')) { $public = false; }
    return apply_filters('ag_public_catalog_context', $public);
}

function ag_public_catalog_hidden_ids() {
    global $wpdb;
    $allowed = ag_public_catalog_ids();
    if ($allowed === null) { return []; }
    $all = array_map('intval', $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('artist','song')"));
    return array_values(array_diff($all, $allowed));
}

// pre_get_posts also covers get_posts(..., suppress_filters=true). Intersect
// explicit inclusions: WP_Query otherwise gives post__in priority over exclusions.
add_action('pre_get_posts', function ($query) {
    if (!ag_public_catalog_scope() || !ag_public_catalog_context()) { return; }
    $hidden = ag_public_catalog_hidden_ids();
    if (!$hidden) { return; }
    $included = $query->get('post__in');
    // WP_Query also prioritizes post__not_in over post_name__in. Preserve
    // explicit slug filters (including REST ?slug=...) by resolving their IDs.
    if (!$included && $query->get('post_name__in')) {
        global $wpdb;
        $names = (array)$query->get('post_name__in');
        $marks = implode(',', array_fill(0, count($names), '%s'));
        $included = array_map('intval', $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name IN ($marks)", $names))) ?: [0];
    }
    if ($included) { $query->set('post__in', array_values(array_diff(array_map('intval', $included), $hidden)) ?: [0]); }
    $query->set('post__not_in', array_values(array_unique(array_merge((array)$query->get('post__not_in'), $hidden))));
}, 99);

// Core single-item REST controllers resolve posts by ID without WP_Query.
add_filter('rest_pre_dispatch', function ($response, $server, $request) {
    if (preg_match('~^/wp/v2/(artist|song)/(\d+)(?:/|$)~', $request->get_route(), $matches)) {
        $id = (int)$matches[2];
        if (!current_user_can('edit_post', $id) && !ag_public_catalog_visible($id)) {
            return new WP_Error('rest_post_invalid_id', 'Invalid post ID.', ['status' => 404]);
        }
    }
    return $response;
}, 10, 3);
add_filter('oembed_response_data', function ($data, $post) {
    return ag_public_catalog_visible($post->ID) ? $data : false;
}, 10, 2);

// Avoid country/mood/genre options that only describe the hidden directory.
add_filter('get_terms_args', function ($args, $taxonomies) {
    if (ag_public_catalog_scope() && ag_public_catalog_context() && array_intersect($taxonomies, ['genre', 'mood', 'country'])) {
        $allowed = ag_public_catalog_ids();
        $args['object_ids'] = isset($args['object_ids']) ? (array_values(array_intersect((array)$args['object_ids'], $allowed)) ?: [0]) : ($allowed ?: [0]);
    }
    return $args;
}, 10, 2);

function ag_preview_core_catalog() {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    try { $data = ag_reviewed_data(); ag_reviewed_validate($data); }
    catch (Throwable $e) { return new WP_Error('invalid_catalog', $e->getMessage()); }
    $keys = []; $ids = [];
    foreach ($data['packs'] as $slug => $pack) {
        $artist = ag_reviewed_find($slug);
        if (!$artist || get_post_status($artist) !== 'publish') { return new WP_Error('missing_artist', 'The reviewed artist must already be visible: ' . $slug); }
        $keys[] = $slug . '/profile'; $ids[] = $artist;
        foreach ($pack['songs'] as $song) {
            $id = ag_reviewed_find($slug, $song);
            if (!$id || get_post_status($id) !== 'publish' || (int)get_post_meta($id, '_ag_artist_id', true) !== $artist || (get_post_meta($id, '_ag_reviewed_approval', true)['decision'] ?? '') !== 'verified') { return new WP_Error('missing_song', 'Import and review this selection before enabling the core catalog: ' . $slug . '/' . $song['slug']); }
            $keys[] = $slug . '/' . $song['slug']; $ids[] = $id;
        }
    }
    return ['keys' => $keys, 'artists' => count($data['packs']), 'songs' => $data['provenance']['approval_count'], 'hidden_records' => count(ag_public_catalog_all_ids()) - count($ids), 'provenance_sha256' => $data['provenance']['sha256']];
}

function ag_public_catalog_all_ids() {
    global $wpdb;
    return $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('artist','song')");
}

function ag_enable_core_catalog() {
    $scope = ag_preview_core_catalog();
    if (is_wp_error($scope)) { return $scope; }
    update_option('ag_public_catalog_scope', $scope, false);
    return $scope;
}

function ag_restore_full_catalog_visibility() {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    delete_option('ag_public_catalog_scope');
    return true;
}

function ag_add_public_catalog_selection($artist, $songs) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    $scope = ag_public_catalog_scope();
    if (!$scope) { return new WP_Error('no_scope', 'Enable the core catalog first.'); }
    $artist = absint($artist); $artist_key = get_post_meta($artist, '_ag_handoff_key', true);
    if (get_post_type($artist) !== 'artist' || get_post_status($artist) !== 'publish' || !preg_match('~^[a-z0-9-]+/profile$~D', $artist_key)) { return new WP_Error('invalid_artist', 'Choose an existing published artist with a stable research key.'); }
    if (!is_array($songs) || !$songs) { return new WP_Error('invalid_songs', 'Choose at least one reviewed song.'); }
    $keys = array_merge($scope['keys'], [$artist_key]);
    $recommended = array_map('absint', explode(',', get_post_meta($artist, '_ag_recommended', true)));
    foreach (array_unique(array_map('absint', $songs)) as $id) {
        $key = get_post_meta($id, '_ag_handoff_key', true);
        if (get_post_type($id) !== 'song' || get_post_status($id) !== 'publish' || !preg_match('~^[a-z0-9-]+/[a-z0-9-]+$~D', $key) || ((int)get_post_meta($id, '_ag_artist_id', true) !== $artist && !in_array($id, $recommended, true))) { return new WP_Error('invalid_song', 'Every chosen song must be a published selection for this artist. No visibility changes were made.'); }
        // Shared selections keep the canonical recording key and page.
        $keys[] = $key;
        $owner = (int)get_post_meta($id, '_ag_artist_id', true);
        $owner_key = get_post_meta($owner, '_ag_handoff_key', true);
        if ($owner !== $artist && !in_array($owner_key, $keys, true)) { return new WP_Error('hidden_owner', 'Include the shared recording’s primary artist first.'); }
    }
    $scope['keys'] = array_values(array_unique($keys));
    $scope['artists'] = count(array_filter($scope['keys'], function ($key) { return str_ends_with($key, '/profile'); }));
    $scope['songs'] = count($scope['keys']) - $scope['artists'];
    $scope['hidden_records'] = count(ag_public_catalog_all_ids()) - count($scope['keys']);
    update_option('ag_public_catalog_scope', $scope, false);
    return $scope;
}

add_action('add_meta_boxes', function () {
    if (!ag_public_catalog_scope()) { return; }
    foreach (['artist', 'song'] as $type) {
        add_meta_box('ag-public-visibility', 'Public catalog visibility', function ($post) {
            echo '<p>' . esc_html(ag_public_catalog_visible($post->ID) ? 'Included in the public catalog when published.' : 'Hidden from the public core catalog. You can edit and preview this record; publishing it does not automatically add it to the public directory.') . '</p>';
        }, $type, 'side');
    }
});

add_action('admin_menu', function () {
    add_submenu_page('edit.php?post_type=artist', 'Public catalog', 'Public catalog', 'manage_options', 'ag-public-catalog', function () {
        if (!current_user_can('manage_options')) { return; }
        $scope = ag_public_catalog_scope(); $preview = ag_preview_core_catalog();
        echo '<div class="wrap"><h1>Public catalog</h1><p>Keep unfinished artists and songs in WordPress while showing visitors only the reviewed core catalog. Existing post statuses, content, URLs and import baselines are retained. New research imports do not automatically expand this public selection.</p>';
        echo '<p><strong>' . esc_html($scope ? 'Core catalog active: ' . $scope['artists'] . ' artists / ' . $scope['songs'] . ' songs.' : 'Full stored catalog visibility is active.') . '</strong></p>';
        echo '<p>You can continue editing hidden records here. Use Preview to inspect a hidden record. Normal public URLs return 404. This setting does not deploy the website or enable search-engine indexing.</p>';
        if (is_wp_error($preview)) { echo '<p>' . esc_html($preview->get_error_message()) . '</p>'; }
        else {
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ag_set_public_catalog"><input type="hidden" name="catalog" value="core">';
            wp_nonce_field('ag_set_public_catalog'); submit_button('Use ten-artist core catalog'); echo '</form>';
        }
        if ($scope) {
            echo '<h2>Grow the directory after review</h2><p>Choose an artist, review their songs, then explicitly include the selections that are ready. Unselected records stay hidden. This changes visibility only; it does not verify sources, approve photo rights or rewrite content.</p>';
            $artists = get_posts(['post_type' => 'artist', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
            $chosen = isset($_GET['ready_artist']) ? absint($_GET['ready_artist']) : 0;
            echo '<form method="get" action="' . esc_url(admin_url('edit.php')) . '"><input type="hidden" name="post_type" value="artist"><input type="hidden" name="page" value="ag-public-catalog"><label>Artist <select name="ready_artist"><option value="">Choose an artist</option>';
            foreach ($artists as $artist) { echo '<option value="' . esc_attr($artist->ID) . '" ' . selected($chosen, $artist->ID, false) . '>' . esc_html($artist->post_title) . '</option>'; }
            echo '</select></label> <button class="button">Review songs</button></form>';
            if ($chosen && in_array($chosen, array_map(function ($p) { return $p->ID; }, $artists), true)) {
                $songs = array_unique(array_merge(array_map('absint', explode(',', get_post_meta($chosen, '_ag_recommended', true))), get_posts(['post_type' => 'song', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_ag_artist_id', 'meta_value' => (string)$chosen])));
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ag_set_public_catalog"><input type="hidden" name="catalog" value="add"><input type="hidden" name="ready_artist" value="' . esc_attr($chosen) . '">';
                wp_nonce_field('ag_set_public_catalog');
                foreach ($songs as $song) {
                    if (get_post_status($song) !== 'publish') { continue; }
                    echo '<p><label><input type="checkbox" name="ready_songs[]" value="' . esc_attr($song) . '"> ' . esc_html(get_the_title($song)) . (ag_public_catalog_visible($song) ? ' (already included)' : '') . '</label> · <a href="' . esc_url(get_edit_post_link($song)) . '">Edit / preview</a></p>';
                }
                submit_button('Include artist and selected reviewed songs'); echo '</form>';
            }
            echo '<details><summary>Restore the full catalog</summary><p>This makes every otherwise published artist/song visible again. Use this only when that larger directory is ready.</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ag_set_public_catalog"><input type="hidden" name="catalog" value="full">';
            wp_nonce_field('ag_set_public_catalog'); submit_button('Restore full catalog visibility', 'secondary'); echo '</form></details>';
        }
        echo '</div>';
    });
});
add_action('admin_post_ag_set_public_catalog', function () {
    if (!current_user_can('manage_options') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Not permitted.', '', ['response' => 403]); }
    check_admin_referer('ag_set_public_catalog');
    $catalog = $_POST['catalog'] ?? '';
    if (!in_array($catalog, ['core', 'full', 'add'], true)) { wp_die('Choose a catalog.', '', ['response' => 400]); }
    $result = $catalog === 'core' ? ag_enable_core_catalog() : ($catalog === 'full' ? ag_restore_full_catalog_visibility() : ag_add_public_catalog_selection(absint($_POST['ready_artist'] ?? 0), $_POST['ready_songs'] ?? []));
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()), '', ['response' => 400]); }
    wp_safe_redirect(admin_url('edit.php?post_type=artist&page=ag-public-catalog')); exit;
});
