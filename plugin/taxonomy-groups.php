<?php
/** Shared read-only navigation over source genre/mood taxonomies. */
defined('ABSPATH') || exit;

function ag_taxonomy_groups($taxonomy) {
    static $cache = [];
    if (!in_array($taxonomy, ['genre','mood'], true)) { throw new InvalidArgumentException('Unsupported grouping taxonomy.'); }
    if (!isset($cache[$taxonomy])) {
        $data = json_decode(file_get_contents(__DIR__ . '/data/' . $taxonomy . '-groups.json'), true, 512, JSON_THROW_ON_ERROR);
        if (($data['schema_version'] ?? '') !== 'afterglow.' . $taxonomy . '-groups.v1' || count($data['groups'] ?? []) < 15 || count($data['groups']) > 20) { throw new RuntimeException('Invalid grouping catalog.'); }
        $cache[$taxonomy] = $data['groups'];
    }
    return $cache[$taxonomy];
}

function ag_taxonomy_default_aliases($taxonomy) {
    $map = [];
    foreach (ag_taxonomy_groups($taxonomy) as $slug => $group) {
        foreach (array_unique(array_merge([$slug], $group['aliases'])) as $alias) { $map[$alias] = $slug; }
    }
    return $map;
}

function ag_taxonomy_aliases($taxonomy) {
    $map = ag_taxonomy_default_aliases($taxonomy);
    $overrides = get_option('ag_' . $taxonomy . '_group_overrides', []);
    foreach (is_array($overrides) ? $overrides : [] as $alias => $group) {
        if (is_string($alias) && $alias === sanitize_title($alias) && is_string($group) && isset(ag_taxonomy_groups($taxonomy)[$group])) { $map[$alias] = $group; }
    }
    return $map;
}

function ag_taxonomy_group_slug($taxonomy, $value) {
    if (!is_string($value)) { return null; }
    $slug = sanitize_title($value);
    return isset(ag_taxonomy_groups($taxonomy)[$slug]) ? $slug : (ag_taxonomy_aliases($taxonomy)[$slug] ?? null);
}

function ag_taxonomy_filter_slugs($taxonomy, $value) {
    $slug = sanitize_title($value);
    $group = ag_taxonomy_group_slug($taxonomy, $slug);
    // Unknown legacy filters keep their original exact-match behavior.
    return $group ? array_keys(ag_taxonomy_aliases($taxonomy), $group, true) : [$slug];
}

function ag_taxonomy_family_terms($taxonomy, $terms) {
    $groups = ag_taxonomy_groups($taxonomy); $map = ag_taxonomy_aliases($taxonomy); $out = [];
    foreach ($terms as $term) {
        $slug = $map[$term->slug] ?? null;
        if ($slug && isset($groups[$slug])) { $out[$slug] = (object)['slug'=>$slug, 'name'=>$groups[$slug]['name']]; }
    }
    uasort($out, function ($a, $b) { return strnatcasecmp($a->name, $b->name); });
    return array_values($out);
}

function ag_taxonomy_family_options($taxonomy, $type = null) {
    $types = in_array($type, ['artist','song'], true) ? [$type] : ['artist','song'];
    $ids = get_posts(['post_type'=>$types, 'post_status'=>'publish', 'numberposts'=>-1, 'fields'=>'ids']);
    $allowed = ag_public_catalog_ids();
    if ($allowed !== null) { $ids = array_values(array_intersect($ids, $allowed)); }
    if (!$ids) { return []; }
    $terms = wp_get_object_terms($ids, $taxonomy, ['fields'=>'all_with_object_id']);
    if (is_wp_error($terms)) { return []; }
    $map = ag_taxonomy_aliases($taxonomy); $members = [];
    foreach ($terms as $term) {
        $group = $map[$term->slug] ?? null;
        if ($group) { $members[$group][(int)$term->object_id] = true; }
    }
    $out = [];
    foreach (ag_taxonomy_groups($taxonomy) as $slug => $group) {
        if (isset($members[$slug])) { $out[] = (object)['slug'=>$slug, 'name'=>$group['name'], 'count'=>count($members[$slug])]; }
    }
    usort($out, function ($a, $b) { return strnatcasecmp($a->name, $b->name); });
    return $out;
}

function ag_save_taxonomy_group_overrides($taxonomy, $input) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    if (!is_array($input)) { return new WP_Error('invalid_group_mapping', 'Choose an existing family.'); }
    $groups = ag_taxonomy_groups($taxonomy); $defaults = ag_taxonomy_default_aliases($taxonomy);
    $existing = get_option('ag_' . $taxonomy . '_group_overrides', []);
    $existing = is_array($existing) ? $existing : []; $next = [];
    $terms = get_terms(['taxonomy'=>$taxonomy,'hide_empty'=>false]);
    if (is_wp_error($terms)) { return $terms; }
    foreach ($terms as $term) {
        $choice = array_key_exists($term->slug, $input) ? $input[$term->slug] : ($existing[$term->slug] ?? '');
        if (!is_string($choice) || ($choice !== '' && !isset($groups[$choice]))) { return new WP_Error('invalid_group_mapping', 'Choose an existing family for ' . $term->name . '.'); }
        if ($choice !== '' && $choice !== ($defaults[$term->slug] ?? null)) { $next[$term->slug] = $choice; }
    }
    update_option('ag_' . $taxonomy . '_group_overrides', $next, false);
    return $next;
}

function ag_register_taxonomy_group_navigation($taxonomy) {
    if (!in_array($taxonomy, ['genre','mood'], true)) { throw new InvalidArgumentException('Unsupported grouping taxonomy.'); }
    add_action('template_redirect', function () use ($taxonomy) {
        if (!is_tax($taxonomy) || is_404() || !ag_public_catalog_context()) { return; }
        $term = get_queried_object();
        $group = $term instanceof WP_Term ? (ag_taxonomy_aliases($taxonomy)[$term->slug] ?? null) : null;
        if (!$group) { return; }
        $type = null;
        foreach (['artist','song'] as $candidate) {
            foreach (ag_taxonomy_family_options($taxonomy, $candidate) as $option) {
                if ($option->slug === $group) { $type = $candidate; break 2; }
            }
        }
        if (!$type) { return; }
        wp_safe_redirect(add_query_arg('filter_' . $taxonomy, $group, home_url('/' . $type . 's/')), 302); exit;
    });
    add_action('admin_menu', function () use ($taxonomy) {
        $title = ucfirst($taxonomy) . ' groups';
        add_submenu_page('edit.php?post_type=artist', $title, $title, 'manage_options', 'ag-' . $taxonomy . '-groups', function () use ($taxonomy, $title) {
            $groups = ag_taxonomy_groups($taxonomy); $map = ag_taxonomy_aliases($taxonomy);
            $overrides = get_option('ag_' . $taxonomy . '_group_overrides', []);
            $overrides = is_array($overrides) ? $overrides : [];
            $terms = get_terms(['taxonomy'=>$taxonomy,'hide_empty'=>false]);
            echo '<div class="wrap"><h1>' . esc_html($title) . '</h1><p>Public browsing uses ' . count($groups) . ' shared ' . esc_html($taxonomy) . ' families. Original labels, term IDs, research and editor assignments stay intact. Only families with published records in the public selection appear in each directory filter.</p><p>Adjust how a source label is grouped below. This changes navigation only. Unmapped labels stay available in the editor and need a family before appearing in grouped browsing.</p>';
            if ($taxonomy === 'mood') { echo '<p>Musical/context labels such as melodic, atmospheric and late-night do not identify an emotion. They remain stored without an automatic emotional classification.</p>'; }
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ag_save_' . esc_attr($taxonomy) . '_groups">';
            wp_nonce_field('ag_save_' . $taxonomy . '_groups');
            echo '<table class="widefat striped"><thead><tr><th>Original ' . esc_html($taxonomy) . ' label</th><th>Public family</th><th>Grouping override</th></tr></thead><tbody>';
            foreach (is_wp_error($terms) ? [] : $terms as $term) {
                $name = html_entity_decode($term->name, ENT_QUOTES|ENT_HTML5, 'UTF-8'); $family = $map[$term->slug] ?? null;
                echo '<tr><td>' . esc_html($name) . '</td><td>' . esc_html($family ? $groups[$family]['name'] : 'Unmapped — review needed') . '</td><td><label class="screen-reader-text" for="' . esc_attr($taxonomy) . '-group-' . (int)$term->term_id . '">Group for ' . esc_html($name) . '</label><select id="' . esc_attr($taxonomy) . '-group-' . (int)$term->term_id . '" name="' . esc_attr($taxonomy) . '_groups[' . esc_attr($term->slug) . ']"><option value="">Use default grouping</option>';
                foreach ($groups as $slug => $group) { echo '<option value="' . esc_attr($slug) . '" ' . selected($overrides[$term->slug] ?? '', $slug, false) . '>' . esc_html($group['name']) . '</option>'; }
                echo '</select></td></tr>';
            }
            echo '</tbody></table>'; submit_button('Save ' . $taxonomy . ' grouping'); echo '</form></div>';
        });
    });
    add_action('admin_post_ag_save_' . $taxonomy . '_groups', function () use ($taxonomy) {
        if (!current_user_can('manage_options') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Not permitted.', '', ['response'=>403]); }
        check_admin_referer('ag_save_' . $taxonomy . '_groups');
        $key = $taxonomy . '_groups';
        $result = ag_save_taxonomy_group_overrides($taxonomy, isset($_POST[$key]) ? wp_unslash($_POST[$key]) : []);
        if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()), '', ['response'=>400]); }
        wp_safe_redirect(admin_url('edit.php?post_type=artist&page=ag-' . $taxonomy . '-groups')); exit;
    });
}
