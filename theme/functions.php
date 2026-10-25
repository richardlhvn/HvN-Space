<?php
defined('ABSPATH') || exit;
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_editor_style('assets/editor.css');
    register_nav_menus(['primary' => 'Primary navigation']);
    add_image_size('ag-card', 800, 880, true);
    add_image_size('ag-hero', 1100, 1100, true);
});
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('ag-fonts', get_theme_file_uri('assets/fonts.css'), [], '1');
    wp_enqueue_style('ag-design', get_theme_file_uri('assets/app.css'), [], filemtime(get_theme_file_path('assets/app.css')));
    wp_enqueue_script('ag-app', get_theme_file_uri('assets/app.js'), [], filemtime(get_theme_file_path('assets/app.js')), ['in_footer' => true, 'strategy' => 'defer']);
    wp_localize_script('ag-app', 'afterglow', ['savedEndpoint' => rest_url('afterglow/v1/saved')]);
});
add_action('customize_register', function ($c) {
    $c->add_section('ag_identity', ['title' => 'Afterglow identity']);
    foreach (['eyebrow' => ['Independent music discovery', 'Homepage eyebrow'], 'tagline' => ['A little closer to the music.', 'Footer tagline'], 'contact_email' => ['', 'Public contact / copyright email (leave blank until ready)']] as $key => $info) {
        $c->add_setting('ag_' . $key, ['default' => $info[0], 'sanitize_callback' => $key === 'contact_email' ? 'sanitize_email' : 'sanitize_text_field']);
        $c->add_control('ag_' . $key, ['section' => 'ag_identity', 'label' => $info[1], 'type' => $key === 'contact_email' ? 'email' : 'text']);
    }
});

function ag_icon($name, $class = '') {
    $paths = [
        'arrow' => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'diagonal' => '<path d="M6 18 18 6M6 6h12v12"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/>',
        'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>',
        'moon' => '<path d="M20 15.5A9 9 0 0 1 8.5 4 9 9 0 1 0 20 15.5Z"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>',
        'flower' => '<path d="M12 3v18M4.2 7.5l15.6 9M4.2 16.5l15.6-9" stroke-width="3.5"/>',
        'spark' => '<path d="m12 2 2.5 7.5L22 12l-7.5 2.5L12 22l-2.5-7.5L2 12l7.5-2.5Z"/>',
        'play' => '<path d="m9 5 11 7-11 7Z"/>',
        'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'spotify' => '<circle cx="12" cy="12" r="9"/><path d="M6.5 9c4-1.5 7-1 11 1M7 12.5c3-1 6-1 9.5.7M8 16c2-.7 4.5-.5 7 .5"/>',
    ];
    return '<svg class="icon ' . esc_attr($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['arrow']) . '</svg>';
}
function ag_meta($key, $id = 0) { return get_post_meta($id ?: get_the_ID(), '_ag_' . $key, true); }
function ag_url($path = '') { return home_url('/' . trim($path, '/') . '/'); }
function ag_terms_text($id, $taxonomy) {
    $terms = get_the_terms($id, $taxonomy);
    return $terms && !is_wp_error($terms) ? implode(' · ', wp_list_pluck($terms, 'name')) : '';
}
function ag_palette($id) { $p = ag_meta('palette', $id); return in_array($p, ['lilac', 'rose', 'sage', 'sand', 'blue', 'peach'], true) ? $p : 'lilac'; }
function ag_art($id, $class = '', $eager = false) {
    echo '<div class="artwork palette-' . esc_attr(ag_palette($id)) . ' ' . esc_attr($class) . '">';
    if (has_post_thumbnail($id) && ag_meta('artwork_credit', $id)) {
        echo get_the_post_thumbnail($id, 'ag-card', ['loading' => $eager ? 'eager' : 'lazy', 'alt' => get_the_title($id)]);
    } else {
        echo '<span class="art-sun" aria-hidden="true"></span><span class="art-hill hill-one" aria-hidden="true"></span><span class="art-hill hill-two" aria-hidden="true"></span><span class="art-grain" aria-hidden="true"></span><span class="art-label" aria-hidden="true">AFTERGLOW / DISCOVERY SERIES</span>';
    }
    echo '</div>';
}
function ag_save_button($id, $class = '') {
    echo '<button class="save-button ' . esc_attr($class) . '" type="button" data-save="' . esc_attr($id) . '" aria-pressed="false" aria-label="Save ' . esc_attr(get_the_title($id)) . '">' . ag_icon('heart') . '</button>';
}
function ag_artist_name($id) { $artist = absint(ag_meta('artist_id', $id)); return $artist && get_post_status($artist) === 'publish' ? get_the_title($artist) : ''; }
function ag_card($id, $kind = '') {
    $kind = $kind ?: get_post_type($id);
    $label = $kind === 'song' ? ag_artist_name($id) : ag_terms_text($id, 'genre');
    echo '<article class="music-card"><div class="card-art"><a href="' . esc_url(get_permalink($id)) . '" aria-label="Explore ' . esc_attr(get_the_title($id)) . '">';
    ag_art($id);
    echo '</a><span class="card-type">' . esc_html($kind === 'song' ? 'SONG NOTES' : 'ON OUR RADAR') . '</span>';
    ag_save_button($id);
    echo '</div><div class="card-caption"><div><h3><a href="' . esc_url(get_permalink($id)) . '">' . esc_html(get_the_title($id)) . '</a></h3><p>' . esc_html($label ?: 'An invitation to listen') . '</p></div><a class="circle-link" href="' . esc_url(get_permalink($id)) . '" aria-label="Explore ' . esc_attr(get_the_title($id)) . '">' . ag_icon('diagonal') . '</a></div></article>';
}
function ag_song_row($id, $n = '') {
    echo '<article class="song-row"><span class="song-number">' . esc_html($n) . '</span><a class="song-thumb" href="' . esc_url(get_permalink($id)) . '" aria-label="Read ' . esc_attr(get_the_title($id)) . '">'; ag_art($id);
    echo '</a><div class="song-name"><h3><a href="' . esc_url(get_permalink($id)) . '">' . esc_html(get_the_title($id)) . '</a></h3><p>' . esc_html(ag_artist_name($id)) . '</p></div><span class="song-mood">' . esc_html(ag_terms_text($id, 'mood')) . '</span>';
    ag_save_button($id);
    echo '<a class="circle-link" href="' . esc_url(get_permalink($id)) . '" aria-label="Read song notes">' . ag_icon('arrow') . '</a></article>';
}
function ag_items($type, $n = 4) { return get_posts(['post_type' => $type, 'numberposts' => $n, 'post_status' => 'publish', 'orderby' => $type === 'post' ? 'date ID' : 'menu_order ID', 'order' => $type === 'post' ? 'DESC' : 'ASC']); }
function ag_section_heading($eyebrow, $title, $link = '', $label = '') {
    echo '<div class="section-heading"><div><p class="eyebrow">' . esc_html($eyebrow) . '</p><h2>' . esc_html($title) . '</h2></div>';
    if ($link) { echo '<a class="text-link" href="' . esc_url($link) . '">' . esc_html($label) . ag_icon('arrow') . '</a>'; }
    echo '</div>';
}
function ag_demo_notice($id) {
    if (ag_meta('demo', $id) && ag_meta('handoff_key', $id)) {
        echo '<aside class="editorial-notice"><strong>Research review pending.</strong> This page uses supplied source-linked research. Open questions are listed below; some source pages have not been independently rechecked. This page stays out of search-engine indexes until editorial review.</aside>';
        return;
    }
    if (ag_meta('demo', $id)) { echo '<aside class="editorial-notice"><strong>From the demo collection.</strong> This is a starter editorial page, not a fact-checked artist biography or an artist-endorsed explanation. Abstract artwork is a placeholder. This page is excluded from search-engine indexing.</aside>'; }
}
function ag_source_links($id, $key = 'sources') {
    $rows = explode("\n", ag_meta($key, $id));
    $links = [];
    foreach ($rows as $row) {
        $p = array_map('trim', explode('|', $row, 2));
        if (count($p) === 2 && filter_var($p[1], FILTER_VALIDATE_URL) && wp_parse_url($p[1], PHP_URL_SCHEME) === 'https') { $links[] = $p; }
    }
    return $links;
}
function ag_listen($id) {
    $url = ag_meta('spotify', $id);
    $valid = $url && wp_parse_url($url, PHP_URL_SCHEME) === 'https' && wp_parse_url($url, PHP_URL_HOST) === 'open.spotify.com';
    $search = get_the_title($id) . (get_post_type($id) === 'song' ? ' ' . ag_artist_name($id) : '');
    if (!$valid) { $url = 'https://open.spotify.com/search/' . rawurlencode($search); }
    echo '<a class="button spotify-button" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . ag_icon('spotify') . esc_html($valid ? 'Open in Spotify' : 'Find on Spotify') . ag_icon('diagonal') . '</a>';
    echo '<p class="fine-print">' . esc_html($valid ? 'Listen on Spotify. No music is hosted here.' : 'Opens Spotify search. An official artist or track link has not been verified yet.') . '</p>';
}
function ag_related_ids($id, $key = 'related') {
    return array_values(array_filter(array_map('absint', explode(',', ag_meta($key, $id))), function ($v) { return get_post_status($v) === 'publish'; }));
}
function ag_filters() {
    $action = is_post_type_archive('song') ? ag_url('songs') : ag_url('artists');
    echo '<form class="filter-bar" action="' . esc_url($action) . '" method="get"><div class="filter-search">' . ag_icon('search') . '<label class="sr-only" for="directory-search">Search this directory</label><input id="directory-search" type="search" name="q" placeholder="Find your next favorite…" value="' . esc_attr(isset($_GET['q']) && is_string($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '') . '"></div>';
    foreach (['genre' => 'All genres', 'mood' => 'All moods', 'country' => 'All countries'] as $tax => $label) {
        echo '<label class="sr-only" for="filter-' . esc_attr($tax) . '">' . esc_html($label) . '</label><select id="filter-' . esc_attr($tax) . '" name="filter_' . esc_attr($tax) . '"><option value="">' . esc_html($label) . '</option>';
        $terms = get_terms(['taxonomy' => $tax, 'hide_empty' => true]);
        $current = isset($_GET['filter_' . $tax]) && is_string($_GET['filter_' . $tax]) ? sanitize_title(wp_unslash($_GET['filter_' . $tax])) : '';
        foreach (is_wp_error($terms) ? [] : $terms as $term) { echo '<option value="' . esc_attr($term->slug) . '" ' . selected($current, $term->slug, false) . '>' . esc_html($term->name) . '</option>'; }
        echo '</select>';
    }
    echo '<button class="button button-dark" type="submit">Explore ' . ag_icon('arrow') . '</button><a class="filter-reset" href="' . esc_url($action) . '">Reset</a></form>';
}

add_filter('pre_get_document_title', function ($title) { return is_singular() && ag_meta('meta_title', get_queried_object_id()) ? ag_meta('meta_title', get_queried_object_id()) : $title; });
add_action('wp_head', function () {
    $id = get_queried_object_id();
    $description = is_singular() ? (ag_meta('meta_description', $id) ?: wp_strip_all_tags(get_the_excerpt($id))) : 'Find your next favorite artist. Thoughtful discoveries, song stories, and music for the way you feel.';
    if ($description) { echo '<meta name="description" content="' . esc_attr(wp_trim_words($description, 35, '…')) . '">' . "\n"; }
    if (is_singular()) {
        echo '<meta property="og:title" content="' . esc_attr(get_the_title($id)) . '"><meta property="og:description" content="' . esc_attr($description) . '"><meta property="og:url" content="' . esc_url(get_permalink($id)) . '"><meta property="og:type" content="article">' . "\n";
        // Demo or unsourced pages make no machine-readable claims about real musicians.
        if (!ag_meta('demo', $id) && in_array(get_post_type($id), ['artist', 'song'], true)) {
            $schema = ['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => get_the_title($id), 'url' => get_permalink($id), 'description' => $description];
            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . '</script>';
        }
    }
});
add_action('rest_api_init', function () {
    register_rest_route('afterglow/v1', '/saved', ['methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => function ($r) {
        $raw = $r->get_param('ids');
        if (!is_string($raw) || strlen($raw) > 1000) { return new WP_Error('invalid_ids', 'Provide a comma-separated list of IDs.', ['status' => 400]); }
        $ids = array_slice(array_unique(array_filter(array_map('absint', explode(',', $raw)))), 0, 100);
        if (!$ids) { return []; }
        $posts = get_posts(['post_type' => ['artist', 'song'], 'post_status' => 'publish', 'post__in' => $ids, 'orderby' => 'post__in', 'numberposts' => 100]);
        return array_map(function ($p) { return ['id' => $p->ID, 'title' => get_the_title($p), 'url' => get_permalink($p), 'type' => $p->post_type, 'palette' => ag_palette($p->ID), 'subtitle' => $p->post_type === 'song' ? ag_artist_name($p->ID) : ag_terms_text($p->ID, 'genre')]; }, $posts);
    }]);
});

// Keep all display assets local; emoji conversion would fetch third-party artwork.
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');
