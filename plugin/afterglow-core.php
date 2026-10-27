<?php
/**
 * Plugin Name: Afterglow Core
 * Description: Artist and song records, curated relationships, and source-aware editorial fields.
 * Version: 0.4.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 */
defined('ABSPATH') || exit;
require_once __DIR__ . '/research-collections.php';
require_once __DIR__ . '/research-handoffs.php';

function ag_register_content() {
    foreach (['artist' => ['Artists', 'Artist', 'artists', 'dashicons-groups'], 'song' => ['Songs', 'Song', 'songs', 'dashicons-format-audio']] as $type => $labels) {
        register_post_type($type, [
            'labels' => ['name' => $labels[0], 'singular_name' => $labels[1], 'add_new_item' => 'Add ' . $labels[1], 'edit_item' => 'Edit ' . $labels[1]],
            'public' => true, 'has_archive' => $labels[2], 'rewrite' => ['slug' => $labels[2]],
            'show_in_rest' => true, 'menu_icon' => $labels[3],
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
        ]);
    }
    foreach (['genre' => 'Genres', 'mood' => 'Moods', 'country' => 'Countries'] as $name => $label) {
        register_taxonomy($name, ['artist', 'song'], ['label' => $label, 'public' => true, 'show_in_rest' => true, 'hierarchical' => true, 'rewrite' => ['slug' => $name]]);
    }
    foreach (['artist', 'song', 'post', 'page'] as $type) {
        foreach (ag_field_definitions($type) as $key => $field) {
            register_post_meta($type, '_ag_' . $key, ['type' => 'string', 'single' => true, 'show_in_rest' => false, 'auth_callback' => function () { return current_user_can('edit_posts'); }]);
        }
    }
}
add_action('init', 'ag_register_content');
register_activation_hook(__FILE__, function () { ag_register_content(); flush_rewrite_rules(); });
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

function ag_field_definitions($type) {
    $common = [
        'meta_title' => ['label' => 'Search title (optional; defaults to page title)', 'type' => 'text'],
        'meta_description' => ['label' => 'Search description (original, accurate summary)', 'type' => 'textarea'],
        'demo' => ['label' => 'Demo / unfinished content — noindex and excluded from sitemap', 'type' => 'checkbox'],
    ];
    if (!in_array($type, ['artist', 'song'], true)) { return $common; }
    $fields = [
        'spotify' => ['label' => 'Verified Spotify URL (https://open.spotify.com/artist/… or /track/…; leave blank until verified)', 'type' => 'url'],
        'sources' => ['label' => 'Sources — one per line: descriptive label | https://original-source.example/interview', 'type' => 'textarea'],
        'artwork_credit' => ['label' => 'Image rights / license and attribution URL (required before uploading artist photography)', 'type' => 'textarea'],
        'palette' => ['label' => 'Abstract artwork palette', 'type' => 'palette'],
    ];
    if ($type === 'artist') {
        $fields += [
            'website' => ['label' => 'Verified official website', 'type' => 'url'],
            'social' => ['label' => 'Verified social links — one per line: label | https://…', 'type' => 'textarea'],
            'recommended' => ['label' => 'Recommended songs', 'type' => 'song_multiple'],
            'related' => ['label' => 'Similar artists', 'type' => 'artist_multiple'],
            'similarity' => ['label' => 'Why they connect — one per line: related artist ID | original editorial explanation', 'type' => 'textarea'],
        ];
    } else {
        $fields += [
            'artist_id' => ['label' => 'Artist', 'type' => 'artist_single'],
            'credits' => ['label' => 'Recording artist credits (include collaborators)', 'type' => 'text'],
            'release' => ['label' => 'Verified release date (YYYY-MM-DD; cite a source)', 'type' => 'date'],
            'themes' => ['label' => 'Lyrical themes (editorial tags, comma separated)', 'type' => 'text'],
            'meaning_type' => ['label' => 'Meaning attribution', 'type' => 'meaning'],
            'artist_comment' => ['label' => 'Sourced artist statement (short paraphrase; link the original interview in Sources)', 'type' => 'textarea'],
            'related' => ['label' => 'Similar songs', 'type' => 'song_multiple'],
        ];
    }
    return $fields + $common;
}

add_action('add_meta_boxes', function () {
    foreach (['artist', 'song', 'post', 'page'] as $type) {
        add_meta_box('afterglow-details', 'Afterglow · Editorial details', 'ag_editor_fields', $type, 'normal', 'high');
    }
});

function ag_editor_fields($post) {
    wp_nonce_field('ag_save_details', 'ag_nonce');
    echo '<p>Write the biography or song discussion in the main editor. Use the sidebar for genres, moods, country, and a licensed featured image. The built-in abstract artwork needs no photograph.</p>';
    foreach (ag_field_definitions($post->post_type) as $key => $field) {
        $value = get_post_meta($post->ID, '_ag_' . $key, true);
        $name = 'ag[' . $key . ']';
        echo '<p><label for="ag-' . esc_attr($key) . '"><strong>' . esc_html($field['label']) . '</strong></label><br>';
        if (str_contains($field['type'], '_multiple') || str_contains($field['type'], '_single')) {
            $target = explode('_', $field['type'])[0];
            $multi = str_contains($field['type'], '_multiple');
            $selected = array_filter(array_map('absint', explode(',', $value)));
            $items = get_posts(['post_type' => $target, 'post_status' => ['publish', 'draft', 'pending'], 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'exclude' => [$post->ID]]);
            echo '<select style="width:100%;max-width:700px" id="ag-' . esc_attr($key) . '" name="' . esc_attr($name) . ($multi ? '[]' : '') . '" ' . ($multi ? 'multiple size="5"' : '') . '>';
            if (!$multi) { echo '<option value="">Choose an artist</option>'; }
            foreach ($items as $item) { echo '<option value="' . esc_attr($item->ID) . '" ' . selected(in_array($item->ID, $selected, true), true, false) . '>' . esc_html($item->post_title . ' (#' . $item->ID . ')') . '</option>'; }
            echo '</select>';
            if ($multi) { echo '<small>Hold Ctrl / Cmd to select several records.</small>'; }
        } elseif ($field['type'] === 'textarea') {
            echo '<textarea rows="3" style="width:100%" id="ag-' . esc_attr($key) . '" name="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea>';
        } elseif ($field['type'] === 'checkbox') {
            echo '<input type="checkbox" id="ag-' . esc_attr($key) . '" name="' . esc_attr($name) . '" value="1" ' . checked($value, '1', false) . '> Keep checked until fact-checking and rights review are complete.';
        } elseif (in_array($field['type'], ['meaning', 'palette'], true)) {
            $choices = $field['type'] === 'meaning' ? ['editorial' => 'Editorial interpretation (not an artist statement)', 'sourced' => 'Artist explanation — original source required'] : ['lilac' => 'Lilac', 'rose' => 'Rose', 'sage' => 'Sage', 'sand' => 'Sand', 'blue' => 'Blue', 'peach' => 'Peach'];
            echo '<select id="ag-' . esc_attr($key) . '" name="' . esc_attr($name) . '">';
            foreach ($choices as $v => $label) { echo '<option value="' . esc_attr($v) . '" ' . selected($value, $v, false) . '>' . esc_html($label) . '</option>'; }
            echo '</select>';
        } else {
            echo '<input style="width:100%" type="' . esc_attr($field['type']) . '" id="ag-' . esc_attr($key) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
        }
        echo '</p>';
    }
}

function ag_clean_links($text) {
    $lines = [];
    foreach (explode("\n", $text) as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if (count($parts) === 2 && filter_var($parts[1], FILTER_VALIDATE_URL) && wp_parse_url($parts[1], PHP_URL_SCHEME) === 'https') {
            $lines[] = sanitize_text_field($parts[0]) . ' | ' . esc_url_raw($parts[1], ['https']);
        }
    }
    return implode("\n", $lines);
}

add_action('save_post', function ($id, $post) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!isset($_POST['ag_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ag_nonce'])), 'ag_save_details') || !current_user_can('edit_post', $id) || wp_is_post_revision($id)) { return; }
    $input = isset($_POST['ag']) && is_array($_POST['ag']) ? wp_unslash($_POST['ag']) : [];
    foreach (ag_field_definitions($post->post_type) as $key => $field) {
        $raw = $input[$key] ?? '';
        if (str_contains($field['type'], '_multiple') || str_contains($field['type'], '_single')) {
            $target = explode('_', $field['type'])[0];
            $ids = array_unique(array_filter(array_map('absint', (array) $raw), function ($related) use ($target, $id) { return $related !== $id && get_post_type($related) === $target; }));
            $value = implode(',', $ids);
        } else {
            $raw = is_string($raw) ? $raw : '';
            $value = $field['type'] === 'textarea' ? sanitize_textarea_field($raw) : sanitize_text_field($raw);
            if ($field['type'] === 'url') { $value = wp_parse_url($raw, PHP_URL_SCHEME) === 'https' ? esc_url_raw($raw, ['https']) : ''; }
            if ($key === 'spotify' && $value && (wp_parse_url($value, PHP_URL_HOST) !== 'open.spotify.com' || !preg_match('~^/(artist|track)/[A-Za-z0-9]+/?$~', wp_parse_url($value, PHP_URL_PATH) ?? ''))) { $value = ''; }
            if (in_array($key, ['sources', 'social'], true)) { $value = ag_clean_links($raw); }
            if ($field['type'] === 'checkbox') { $value = $raw === '1' ? '1' : ''; }
            if ($key === 'palette' && !in_array($value, ['lilac', 'rose', 'sage', 'sand', 'blue', 'peach'], true)) { $value = 'lilac'; }
            if ($key === 'meaning_type') { $value = $value === 'sourced' ? 'sourced' : 'editorial'; }
            if ($key === 'release' && $value) {
                $date = DateTime::createFromFormat('!Y-m-d', $value);
                if (!$date || $date->format('Y-m-d') !== $value) { $value = ''; }
            }
        }
        update_post_meta($id, '_ag_' . $key, $value);
    }
    // An uncited statement must never be presented as the artist's explanation.
    if ($post->post_type === 'song' && !get_post_meta($id, '_ag_sources', true)) {
        update_post_meta($id, '_ag_meaning_type', 'editorial');
        update_post_meta($id, '_ag_artist_comment', '');
    }
}, 10, 2);

add_filter('wp_robots', function ($robots) {
    $filtered = isset($_GET['q']) || isset($_GET['filter_mood']) || isset($_GET['filter_genre']) || isset($_GET['filter_country']);
    if ((is_singular() && get_post_meta(get_queried_object_id(), '_ag_demo', true)) || is_search() || is_tax(['genre', 'mood', 'country']) || $filtered) { $robots['noindex'] = true; unset($robots['index']); }
    return $robots;
});
add_filter('wp_sitemaps_taxonomies', function ($taxonomies) {
    foreach (['genre', 'mood', 'country'] as $taxonomy) { unset($taxonomies[$taxonomy]); }
    return $taxonomies;
});
add_filter('wp_sitemaps_posts_query_args', function ($args) {
    $args['meta_query'][] = ['relation' => 'OR', ['key' => '_ag_demo', 'compare' => 'NOT EXISTS'], ['key' => '_ag_demo', 'value' => '1', 'compare' => '!=']];
    return $args;
});

add_action('pre_get_posts', function ($q) {
    if (is_admin() || !$q->is_main_query()) { return; }
    if ($q->is_search()) { $q->set('post_type', ['artist', 'song', 'post']); }
    if ($q->is_post_type_archive(['artist', 'song']) || $q->is_tax(['genre', 'mood', 'country'])) {
        $q->set('posts_per_page', 12);
        $q->set('orderby', 'title'); $q->set('order', 'ASC');
        if (isset($_GET['q']) && is_string($_GET['q'])) { $q->set('s', sanitize_text_field(wp_unslash($_GET['q']))); }
        $tax = [];
        foreach (['genre', 'mood', 'country'] as $name) {
            $key = 'filter_' . $name;
            if (isset($_GET[$key]) && is_string($_GET[$key]) && $_GET[$key] !== '') { $tax[] = ['taxonomy' => $name, 'field' => 'slug', 'terms' => sanitize_title(wp_unslash($_GET[$key]))]; }
        }
        if ($tax) { $tax['relation'] = 'AND'; $q->set('tax_query', $tax); }
    }
});

add_action('admin_menu', function () {
    add_submenu_page('edit.php?post_type=artist', 'Editorial guide', 'Editorial guide', 'edit_posts', 'ag-guide', function () {
        echo '<div class="wrap"><h1>Publishing with Afterglow</h1><ol><li>Add an artist and write an original biography. Set genres, moods, and country in the sidebar only when supported.</li><li>Add songs and select the artist in Editorial details. Use verified Spotify URLs; otherwise the public page offers a labeled Spotify search.</li><li>Write your own interpretation. Select Artist explanation only with a linked original source. Never invent quotations or reproduce lyrics.</li><li>Select recommended songs and related artists, then explain each artist connection using its visible ID.</li><li>Use abstract artwork, or document photo permission and attribution before setting a featured image.</li><li>Review titles, descriptions, sources, and rights; uncheck Demo only once the page is ready. Demo records are excluded from search-engine indexing and sitemaps.</li><li>Use Posts for original journal features, and Settings → Reading for your journal page. Appearance → Customize controls branding and contact email.</li></ol><p>Save drafts and preview before publishing. Artist and song relationships are manual; no external APIs are required.</p>';
        if (current_user_can('manage_options') && !get_option('ag_demo_seed_version')) {
            echo '<h2>Optional starter collection</h2><p>Create six demo artists, six demo songs, three original journal articles, and the supporting pages. Existing records with matching slugs are retained. This also selects the homepage and journal under Settings → Reading. Use only on a new staging site.</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ag_seed_demo">';
            wp_nonce_field('ag_seed_demo'); submit_button('Create the labeled demo collection'); echo '</form>';
        } else { echo '<p>The starter collection has already been created. Editing or deleting records will not cause them to be recreated.</p>'; }
        echo '</div>';
    });
});
add_action('admin_post_ag_seed_demo', function () {
    if (!current_user_can('manage_options') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Not permitted.', '', ['response' => 403]); }
    check_admin_referer('ag_seed_demo');
    require __DIR__ . '/demo-content.php';
    flush_rewrite_rules();
    wp_safe_redirect(admin_url('edit.php?post_type=artist&page=ag-guide'));
    exit;
});
