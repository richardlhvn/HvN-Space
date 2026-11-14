<?php
/** Public genre families over the retained source taxonomy. No term/post migration. */
defined('ABSPATH') || exit;

function ag_genre_groups() {
    static $groups;
    if ($groups === null) {
        $data = json_decode(file_get_contents(__DIR__ . '/data/genre-groups.json'), true, 512, JSON_THROW_ON_ERROR);
        if (($data['schema_version'] ?? '') !== 'afterglow.genre-groups.v1' || count($data['groups'] ?? []) < 15 || count($data['groups']) > 20) { throw new RuntimeException('Invalid genre-family catalog.'); }
        $groups = $data['groups'];
    }
    return $groups;
}

function ag_genre_default_aliases() {
    $map = [];
    foreach (ag_genre_groups() as $slug => $group) {
        foreach (array_unique(array_merge([$slug], $group['aliases'])) as $alias) { $map[$alias] = $slug; }
    }
    return $map;
}

function ag_genre_aliases() {
    $map = ag_genre_default_aliases();
    $overrides = get_option('ag_genre_group_overrides', []);
    foreach (is_array($overrides) ? $overrides : [] as $alias => $group) {
        if (is_string($alias) && $alias === sanitize_title($alias) && is_string($group) && isset(ag_genre_groups()[$group])) { $map[$alias] = $group; }
    }
    return $map;
}

function ag_genre_group_slug($value) {
    if (!is_string($value)) { return null; }
    $slug = sanitize_title($value);
    return isset(ag_genre_groups()[$slug]) ? $slug : (ag_genre_aliases()[$slug] ?? null);
}

function ag_genre_filter_slugs($value) {
    $slug = sanitize_title($value);
    $group = ag_genre_group_slug($slug);
    // Unknown legacy filters keep their original exact-match behavior.
    return $group ? array_keys(ag_genre_aliases(), $group, true) : [$slug];
}

function ag_genre_family_terms($terms) {
    $groups = ag_genre_groups(); $map = ag_genre_aliases(); $out = [];
    foreach ($terms as $term) {
        $slug = $map[$term->slug] ?? null;
        if ($slug && isset($groups[$slug])) { $out[$slug] = (object)['slug'=>$slug, 'name'=>$groups[$slug]['name']]; }
    }
    uasort($out, function ($a, $b) { return strnatcasecmp($a->name, $b->name); });
    return array_values($out);
}

function ag_genre_family_options($type = null) {
    $types = in_array($type, ['artist','song'], true) ? [$type] : ['artist','song'];
    $ids = get_posts(['post_type'=>$types, 'post_status'=>'publish', 'numberposts'=>-1, 'fields'=>'ids']);
    $allowed = ag_public_catalog_ids();
    if ($allowed !== null) { $ids = array_values(array_intersect($ids, $allowed)); }
    if (!$ids) { return []; }
    $terms = wp_get_object_terms($ids, 'genre', ['fields'=>'all_with_object_id']);
    if (is_wp_error($terms)) { return []; }
    $map = ag_genre_aliases(); $members = [];
    foreach ($terms as $term) {
        $group = $map[$term->slug] ?? null;
        if ($group) { $members[$group][(int)$term->object_id] = true; }
    }
    $out = [];
    foreach (ag_genre_groups() as $slug => $group) {
        if (isset($members[$slug])) { $out[] = (object)['slug'=>$slug, 'name'=>$group['name'], 'count'=>count($members[$slug])]; }
    }
    usort($out, function ($a, $b) { return strnatcasecmp($a->name, $b->name); });
    return $out;
}

function ag_save_genre_group_overrides($input) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    if (!is_array($input)) { return new WP_Error('invalid_genre_mapping', 'Choose an existing genre family.'); }
    $groups = ag_genre_groups(); $defaults = ag_genre_default_aliases();
    $existing = get_option('ag_genre_group_overrides', []);
    $existing = is_array($existing) ? $existing : []; $next = [];
    $terms = get_terms(['taxonomy'=>'genre','hide_empty'=>false]);
    if (is_wp_error($terms)) { return $terms; }
    foreach ($terms as $term) {
        $choice = array_key_exists($term->slug, $input) ? $input[$term->slug] : ($existing[$term->slug] ?? '');
        if (!is_string($choice) || ($choice !== '' && !isset($groups[$choice]))) { return new WP_Error('invalid_genre_mapping', 'Choose an existing genre family for ' . $term->name . '.'); }
        if ($choice !== '' && $choice !== ($defaults[$term->slug] ?? null)) { $next[$term->slug] = $choice; }
    }
    update_option('ag_genre_group_overrides', $next, false);
    return $next;
}

// Keep public legacy taxonomy bookmarks on the same grouped navigation.
add_action('template_redirect', function () {
    if (!is_tax('genre') || is_404() || !ag_public_catalog_context()) { return; }
    $term = get_queried_object();
    $group = $term instanceof WP_Term ? (ag_genre_aliases()[$term->slug] ?? null) : null;
    if (!$group) { return; }
    $type = null;
    foreach (['artist','song'] as $candidate) {
        foreach (ag_genre_family_options($candidate) as $option) {
            if ($option->slug === $group) { $type = $candidate; break 2; }
        }
    }
    if (!$type) { return; }
    wp_safe_redirect(add_query_arg('filter_genre', $group, home_url('/' . $type . 's/')), 302);
    exit;
});

add_action('admin_menu', function () {
    add_submenu_page('edit.php?post_type=artist', 'Genre groups', 'Genre groups', 'manage_options', 'ag-genre-groups', function () {
        $groups = ag_genre_groups(); $map = ag_genre_aliases();
        $overrides = get_option('ag_genre_group_overrides', []);
        $overrides = is_array($overrides) ? $overrides : [];
        $terms = get_terms(['taxonomy'=>'genre','hide_empty'=>false]);
        echo '<div class="wrap"><h1>Genre groups</h1><p>Public browsing uses ' . count($groups) . ' shared genre families. Original labels, term IDs, research and editor assignments stay intact. Only families with published records in the public selection appear in each directory filter.</p><p>Adjust how a source label is grouped below. This changes navigation only. Unmapped labels stay available in the editor and need a family before appearing in grouped browsing.</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ag_save_genre_groups">';
        wp_nonce_field('ag_save_genre_groups');
        echo '<table class="widefat striped"><thead><tr><th>Original genre label</th><th>Public family</th><th>Grouping override</th></tr></thead><tbody>';
        foreach (is_wp_error($terms) ? [] : $terms as $term) {
            $name = html_entity_decode($term->name, ENT_QUOTES|ENT_HTML5, 'UTF-8');
            $family = $map[$term->slug] ?? null;
            echo '<tr><td>' . esc_html($name) . '</td><td>' . esc_html($family ? $groups[$family]['name'] : 'Unmapped — review needed') . '</td><td><label class="screen-reader-text" for="genre-group-' . (int)$term->term_id . '">Group for ' . esc_html($name) . '</label><select id="genre-group-' . (int)$term->term_id . '" name="genre_groups[' . esc_attr($term->slug) . ']"><option value="">Use default grouping</option>';
            foreach ($groups as $slug => $group) { echo '<option value="' . esc_attr($slug) . '" ' . selected($overrides[$term->slug] ?? '', $slug, false) . '>' . esc_html($group['name']) . '</option>'; }
            echo '</select></td></tr>';
        }
        echo '</tbody></table>'; submit_button('Save genre grouping'); echo '</form></div>';
    });
});
add_action('admin_post_ag_save_genre_groups', function () {
    if (!current_user_can('manage_options') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Not permitted.', '', ['response'=>403]); }
    check_admin_referer('ag_save_genre_groups');
    $result = ag_save_genre_group_overrides(isset($_POST['genre_groups']) ? wp_unslash($_POST['genre_groups']) : []);
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()), '', ['response'=>400]); }
    wp_safe_redirect(admin_url('edit.php?post_type=artist&page=ag-genre-groups')); exit;
});
