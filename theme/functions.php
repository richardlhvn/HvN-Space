<?php
defined('ABSPATH') || exit;
require_once __DIR__ . '/reader-presentation.php';
require_once __DIR__ . '/discovery-ui.php';
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
    $c->add_section('ag_identity', ['title' => ag_site_name() . ' identity']);
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
        'volume' => '<path d="M11 4 6 8H3v8h3l5 4Z"/><path d="M15 8a6 6 0 0 1 0 8m3-11a10 10 0 0 1 0 14"/>',
    ];
    return '<svg class="icon ' . esc_attr($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['arrow']) . '</svg>';
}
function ag_meta($key, $id = 0) { return get_post_meta($id ?: get_the_ID(), '_ag_' . $key, true); }
function ag_url($path = '') { return home_url('/' . trim($path, '/') . '/'); }
function ag_display_terms($id, $taxonomy) {
    $terms = get_the_terms($id, $taxonomy);
    if (!$terms || is_wp_error($terms)) { return []; }
    return $taxonomy === 'genre' && function_exists('ag_genre_family_terms') ? ag_genre_family_terms($terms) : $terms;
}
function ag_terms_text($id, $taxonomy) {
    return implode(' · ', wp_list_pluck(ag_display_terms($id, $taxonomy), 'name'));
}
function ag_palette($id) { $p = ag_meta('palette', $id); return in_array($p, ['lilac', 'rose', 'sage', 'sand', 'blue', 'peach'], true) ? $p : 'lilac'; }
function ag_art($id, $class = '', $eager = false, $abstract_only = false) {
    $verified_photo = !$abstract_only && function_exists('ag_media_credit') && ag_media_credit($id);
    echo '<div class="artwork palette-' . esc_attr(ag_palette($id)) . ' ' . esc_attr($class) . ($verified_photo ? ' licensed-photo' : '') . '">';
    if (!$abstract_only && has_post_thumbnail($id) && ag_meta('artwork_credit', $id)) {
        echo get_the_post_thumbnail($id, 'ag-card', ['loading' => $eager ? 'eager' : 'lazy', 'alt' => get_the_title($id)]);
    } else {
        echo '<span class="art-sun" aria-hidden="true"></span><span class="art-hill hill-one" aria-hidden="true"></span><span class="art-hill hill-two" aria-hidden="true"></span><span class="art-grain" aria-hidden="true"></span><span class="art-label" aria-hidden="true">' . esc_html(ag_site_name()) . ' / DISCOVERY SERIES</span>';
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
    echo '</div><div class="card-caption"><div><h3><a href="' . esc_url(get_permalink($id)) . '">' . esc_html(get_the_title($id)) . '</a></h3><p>' . esc_html($label ?: 'An invitation to listen') . '</p></div><a class="circle-link" href="' . esc_url(get_permalink($id)) . '" aria-label="Explore ' . esc_attr(get_the_title($id)) . '">' . ag_icon('diagonal') . '</a></div>';
    if ($kind === 'artist' && function_exists('ag_media_credit') && ag_media_credit($id)) { echo '<p class="media-credit">' . ag_photo_credit_html($id, true) . '</p>'; }
    echo '</article>';
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
function ag_research_label($id) {
    if (ag_meta('research_status', $id) === 'research_starter_not_publication_ready') { return 'Research starter — not publication ready'; }
    return ag_meta('handoff_key', $id) ? 'Research review pending' : 'Demo listening notes';
}
function ag_demo_notice($id) {
    if (ag_meta('demo', $id) && ag_meta('research_status', $id) === 'research_starter_not_publication_ready') {
        echo '<aside class="editorial-notice"><strong>Research starter — not publication ready.</strong> This biography and song selection are provisional. Track links, original release dates, and song-specific explanations remain incomplete. Review the scoped sources and open questions before publication. This page stays out of search-engine indexes.</aside>';
        return;
    }
    if (ag_meta('demo', $id) && ag_meta('handoff_key', $id)) {
        echo '<aside class="editorial-notice"><strong>Research review pending.</strong> This page uses supplied source-linked research. Open questions are listed below; some source pages have not been independently rechecked. This page stays out of search-engine indexes until editorial review.</aside>';
        return;
    }
    if (ag_meta('demo', $id)) { echo '<aside class="editorial-notice"><strong>From the demo collection.</strong> This is a starter editorial page, not a fact-checked artist biography or an artist-endorsed explanation. Abstract artwork is a placeholder. This page is excluded from search-engine indexing.</aside>'; }
}
add_filter('get_the_excerpt', function ($excerpt, $post) {
    return !is_admin() && in_array($post->post_type, ['artist', 'song'], true) ? ag_public_brand_text($excerpt) : $excerpt;
}, 10, 2);

// Internal evidence status stays in the worksheet/admin, while visitors retain
// article titles, attribution and links. This changes labels, never source data.
function ag_public_source_label($label) {
    $label = str_replace([
        ' (editor-reviewed evidence; independent check pending)',
        ' · Editor-reviewed evidence; independent check pending',
        ' · Transcript; primary-audio verification pending',
        ' · Editor-supplied reference; source metadata pending',
        ' — editor-supplied reference; source metadata pending',
    ], '', $label);
    if (preg_match('/Reviewer-supplied research link for/i', $label)) {
        $label = preg_replace('/^.*?Reviewer-supplied research link for\s*/i', 'Source reference: ', $label);
    }
    return $label;
}

function ag_public_research_content($content) {
    // Only known importer-generated QA sections/paragraphs are removed. Editor
    // biographies and other headings remain stored and displayed as written.
    $content = preg_replace('~<h2[^>]*>\s*Research notes\s*</h2>.*?(?=<h2\b|$)~si', '', $content);
    $content = preg_replace('~<p\b[^>]*>\s*(?:<small\b[^>]*>)?\s*(?:<strong\b[^>]*>)?\s*(?:Basis:|Based on the supplied research dated\s).*?</p>~si', '', $content);
    return ag_reader_content(ag_public_brand_html($content));
}

function ag_release_date_label($date, $precision) {
    $patterns = ['day' => ['Y-m-d', 'j F Y'], 'month' => ['Y-m', 'F Y'], 'year' => ['Y', 'Y']];
    if (is_string($date) && $date !== '' && is_string($precision) && isset($patterns[$precision])) {
        [$input, $output] = $patterns[$precision];
        $parsed = DateTimeImmutable::createFromFormat('!' . $input, $date, new DateTimeZone('UTC'));
        if ($parsed && $parsed->format($input) === $date) { return wp_date($output, $parsed->getTimestamp(), new DateTimeZone('UTC')); }
    }
    return '';
}

function ag_public_release_details($id) {
    $record = get_post_meta($id, '_ag_handoff_record', true);
    $record = is_array($record) ? $record : [];
    $date = ag_release_date_label($record['release_date'] ?? ag_meta('release', $id), $record['release_date_precision'] ?? 'day');
    $out = $date !== '' ? ['Release date' => $date] : [];
    if (!empty($record['release_title'])) { $out['Release'] = $record['release_title']; }
    if (!empty($record['release_type'])) { $out['Format'] = strtoupper($record['release_type']) === 'EP' ? 'EP' : ucfirst($record['release_type']); }
    return $out;
}

function ag_public_song_document($id, $fallback) {
    $about = function_exists('ag_about_song') ? ag_about_song($id) : [];
    $html = ag_public_research_content($fallback);
    if (!empty($about['text'])) {
        $html = '<h2>About the Song</h2>' . wpautop(esc_html(ag_public_brand_text($about['text'])));
    }
    $details = ag_public_release_details($id);
    if ($details) {
        $html .= '<h2>Release information</h2><dl>';
        foreach ($details as $label => $value) { $html .= '<dt>' . esc_html($label) . '</dt><dd>' . esc_html($value) . '</dd>'; }
        $html .= '</dl>';
    }
    $sources = ag_source_links($id);
    foreach ($about['sources'] ?? [] as $source) { if (!in_array($source['url'], array_column($sources, 1), true)) { $sources[] = [ag_public_source_label($source['label']), $source['url']]; } }
    if ($sources) {
        $html .= '<h2>Sources &amp; further reading</h2><ul>';
        foreach ($sources as $source) { $html .= '<li><a href="' . esc_url($source[1]) . '">' . esc_html($source[0]) . '</a></li>'; }
        $html .= '</ul>';
    }
    return $html;
}

// The public REST representation must not expose the working research body
// that the reader template deliberately omits. Editors keep content.raw in
// authenticated context=edit responses through WordPress's normal permissions.
foreach (['artist', 'song'] as $type) {
    add_filter('rest_prepare_' . $type, function ($response, $post, $request) {
        if ($request->get_param('context') === 'edit' && current_user_can('edit_post', $post->ID)) { return $response; }
        $data = $response->get_data();
        if (isset($data['content']['rendered'])) {
            if ($post->post_type === 'artist') {
                $data['content']['rendered'] = ag_public_research_content($data['content']['rendered']);
            } else {
                $about = function_exists('ag_about_song') ? ag_about_song($post->ID) : [];
                if (!empty($about['text']) && isset($data['excerpt']['rendered'])) { $data['excerpt']['rendered'] = wpautop(esc_html(wp_trim_words(ag_public_brand_text($about['text']), 35, '…'))); }
                $data['content']['rendered'] = ag_public_song_document($post->ID, $data['content']['rendered']);
            }
        }
        if (isset($data['excerpt']['rendered'])) { $data['excerpt']['rendered'] = ag_public_brand_html($data['excerpt']['rendered']); }
        $response->set_data($data);
        return $response;
    }, 10, 3);
}
add_filter('the_content_feed', function ($content) {
    return get_post_type() === 'song' ? ag_public_song_document(get_the_ID(), $content) : (get_post_type() === 'artist' ? ag_public_research_content($content) : $content);
});
add_filter('the_excerpt_rss', function ($excerpt) {
    if (get_post_type() !== 'song' || !function_exists('ag_about_song')) { return $excerpt; }
    $about = ag_about_song(get_the_ID());
    return !empty($about['text']) ? esc_html(wp_trim_words(ag_public_brand_text($about['text']), 35, '…')) : $excerpt;
});

// A read-only editor view preserves QA evidence without putting it in public
// page HTML. No save handler, flags, source status or indexing setting changes.
add_action('add_meta_boxes', function () {
    foreach (['artist', 'song'] as $type) {
        add_meta_box('ag-internal-research', 'Internal research and QA — not shown to visitors', function ($post) {
            if (!current_user_can('edit_post', $post->ID)) { return; }
            $record = get_post_meta($post->ID, '_ag_handoff_record', true);
            $record = is_array($record) ? $record : [];
            $approval = get_post_meta($post->ID, '_ag_reviewed_approval', true);
            $provenance = get_post_meta($post->ID, '_ag_reviewed_provenance', true);
            echo '<p>Keep these fields for editorial verification. Their absence from the public page does not resolve open questions or change search-engine indexing.</p><dl>';
            $items = ['Research status' => ag_meta('research_status', $post->ID), 'Review/indexing flag' => ag_meta('demo', $post->ID), 'Basis' => $record['editorial_notes_basis'] ?? '', 'Release context' => $record['release_date_context'] ?? '', 'Open questions / uncertainties' => $record['uncertainties'] ?? [], 'Supported claims' => $record['supported_claims'] ?? [], 'Workbook approval' => $approval, 'Workbook provenance' => $provenance, 'Source registry and access status' => get_post_meta($post->ID, '_ag_source_registry', true)];
            foreach ($items as $label => $value) {
                if ($value === '' || $value === [] || $value === null) { continue; }
                echo '<dt><strong>' . esc_html($label) . '</strong></dt><dd><pre style="white-space:pre-wrap;overflow-wrap:anywhere;max-height:320px;overflow:auto">' . esc_html(is_array($value) ? wp_json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string)$value) . '</pre></dd>';
            }
            echo '</dl><details><summary>Original editor content (including retained research notes)</summary><pre style="white-space:pre-wrap;overflow-wrap:anywhere">' . esc_html($post->post_content) . '</pre></details>';
        }, $type, 'normal', 'low');
    }
});

function ag_source_links($id, $key = 'sources') {
    $rows = explode("\n", ag_meta($key, $id));
    $links = [];
    foreach ($rows as $row) {
        $p = array_map('trim', explode('|', $row, 2));
        if (count($p) === 2 && filter_var($p[1], FILTER_VALIDATE_URL) && wp_parse_url($p[1], PHP_URL_SCHEME) === 'https') { $p[0] = ag_public_source_label($p[0]); $links[] = $p; }
    }
    return $links;
}
function ag_listen($id) {
    $url = ag_meta('spotify', $id);
    $song = get_post_type($id) === 'song'; $type = $song ? 'track' : 'artist';
    $valid = (bool)preg_match('~^https://open\.spotify\.com/' . $type . '/[A-Za-z0-9]{22}/?$~D', $url);
    $artist_fallback = false;
    if (!$valid && $song) {
        $artist = absint(ag_meta('artist_id', $id)); $artist_url = ag_meta('spotify', $artist);
        if ($artist && get_post_status($artist) === 'publish' && preg_match('~^https://open\.spotify\.com/artist/[A-Za-z0-9]{22}/?$~D', $artist_url)) { $url = $artist_url; $artist_fallback = true; }
    }
    $search = get_the_title($id) . (get_post_type($id) === 'song' ? ' ' . ag_artist_name($id) : '');
    if (!$valid && !$artist_fallback) { $url = 'https://open.spotify.com/search/' . rawurlencode($search); }
    $label = $valid ? 'Open in Spotify' : ($artist_fallback ? 'Explore artist on Spotify' : 'Find on Spotify');
    $note = $valid ? 'Listen on Spotify. No music is hosted here.' : ($artist_fallback ? 'The exact track link is not verified yet. This opens the artist’s verified Spotify profile.' : 'Opens Spotify search. An official artist or track link has not been verified yet.');
    echo '<a class="button spotify-button" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . ag_icon('spotify') . esc_html($label) . ag_icon('diagonal') . '</a>';
    echo '<p class="fine-print">' . esc_html($note) . '</p>';
}
function ag_recording_credits_html($id) {
    $out = [];
    foreach (array_filter(array_map('trim', explode(',', ag_meta('credits', $id)))) as $name) {
        $artists = get_posts(['post_type' => 'artist', 'post_status' => 'publish', 'title' => $name, 'numberposts' => 1]);
        $out[] = $artists ? '<a href="' . esc_url(get_permalink($artists[0])) . '">' . esc_html($name) . '</a>' : esc_html($name);
    }
    return implode(', ', $out);
}
function ag_collaboration_ids($artist) {
    $name = get_the_title($artist); $selected = ag_related_ids($artist, 'recommended'); $ids = [];
    $songs = get_posts(['post_type' => 'song', 'post_status' => 'publish', 'numberposts' => -1, 'meta_query' => [['key' => '_ag_credits', 'value' => $name, 'compare' => 'LIKE'], ['key' => '_ag_artist_id', 'value' => (string)$artist, 'compare' => '!=']]]);
    foreach ($songs as $song) {
        $credits = array_map('trim', explode(',', ag_meta('credits', $song->ID)));
        if (in_array($name, $credits, true) && !in_array($song->ID, $selected, true)) { $ids[] = $song->ID; }
    }
    return $ids;
}
function ag_related_ids($id, $key = 'related') {
    return array_values(array_filter(array_map('absint', explode(',', ag_meta($key, $id))), function ($v) { return get_post_status($v) === 'publish' && (!function_exists('ag_public_catalog_visible') || !ag_public_catalog_context() || ag_public_catalog_visible($v)); }));
}
function ag_photo_credit_html($id, $compact = false) {
    $credit = function_exists('ag_media_credit') ? ag_media_credit($id) : [];
    if (!$credit) { return esc_html(ag_meta('artwork_credit', $id) ?: 'Original abstract artwork · Not a photograph of the artist'); }
    if ($compact) { return 'Photo: ' . esc_html($credit['photographer']) . ' · <a href="' . esc_url($credit['license_url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($credit['license']) . '</a> · Scaled, no crop. <a href="' . esc_url(home_url('/media-credits/#' . get_post_field('post_name', $id))) . '">Credits</a>'; }
    return 'Photo: ' . esc_html($credit['photographer']) . ' · <a href="' . esc_url($credit['source_url']) . '" target="_blank" rel="noopener noreferrer">Source</a> · <a href="' . esc_url($credit['license_url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($credit['license']) . '</a> · ' . esc_html($credit['changes']) . ' <a href="' . esc_url(home_url('/media-credits/')) . '">Full media credits</a>';
}
function ag_spotify_player($id) {
    $url = function_exists('ag_spotify_embed_url') ? ag_spotify_embed_url($id) : '';
    if (!$url) { return; }
    $title = 'Spotify player: ' . get_the_title($id);
    echo '<section class="spotify-player" data-spotify-embed="' . esc_url($url) . '" data-player-title="' . esc_attr($title) . '" aria-label="' . esc_attr($title) . '"><p class="eyebrow">LISTEN WITH SPOTIFY</p><p>Load the official player to listen here. This connects to Spotify; <a href="https://www.spotify.com/legal/privacy-policy/" target="_blank" rel="noopener noreferrer">Spotify’s privacy policy</a> applies.</p><button type="button" class="button button-dark spotify-load">' . ag_icon('spotify') . 'Load Spotify player</button><div class="spotify-frame-slot" aria-live="polite"></div><p class="fine-print"><a href="' . esc_url(ag_meta('spotify', $id)) . '" target="_blank" rel="noopener noreferrer">Open directly in Spotify ↗</a> · If the player is blocked, this link remains available.</p><noscript><p>Use the Spotify link above to listen.</p></noscript></section>';
}
function ag_filters() {
    $action = is_post_type_archive('song') ? ag_url('songs') : ag_url('artists');
    echo '<form class="filter-bar" action="' . esc_url($action) . '" method="get"><div class="filter-search">' . ag_icon('search') . '<label class="sr-only" for="directory-search">Search this directory</label><input id="directory-search" type="search" name="q" placeholder="Find your next favorite…" value="' . esc_attr(isset($_GET['q']) && is_string($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '') . '"></div>';
    foreach (['genre' => 'All genres', 'mood' => 'All moods', 'country' => 'All countries'] as $tax => $label) {
        echo '<label class="sr-only" for="filter-' . esc_attr($tax) . '">' . esc_html($label) . '</label><select id="filter-' . esc_attr($tax) . '" name="filter_' . esc_attr($tax) . '"><option value="">' . esc_html($label) . '</option>';
        $terms = $tax === 'genre' && function_exists('ag_genre_family_options') ? ag_genre_family_options(is_post_type_archive('song') ? 'song' : 'artist') : get_terms(['taxonomy' => $tax, 'hide_empty' => true]);
        $current = isset($_GET['filter_' . $tax]) && is_string($_GET['filter_' . $tax]) ? sanitize_title(wp_unslash($_GET['filter_' . $tax])) : '';
        if ($tax === 'genre' && $current !== '' && function_exists('ag_genre_group_slug')) { $current = ag_genre_group_slug($current) ?: $current; }
        if ($tax === 'genre' && function_exists('ag_genre_groups') && isset(ag_genre_groups()[$current]) && !in_array($current, wp_list_pluck($terms, 'slug'), true)) { $terms[] = (object)['slug'=>$current, 'name'=>ag_genre_groups()[$current]['name']]; }
        foreach (is_wp_error($terms) ? [] : $terms as $term) { echo '<option value="' . esc_attr($term->slug) . '" ' . selected($current, $term->slug, false) . '>' . esc_html($term->name) . '</option>'; }
        echo '</select>';
    }
    echo '<button class="button button-dark" type="submit">Explore ' . ag_icon('arrow') . '</button><a class="filter-reset" href="' . esc_url($action) . '">Reset</a></form>';
}

add_filter('pre_get_document_title', function ($title) { return is_singular() && ag_meta('meta_title', get_queried_object_id()) ? ag_meta('meta_title', get_queried_object_id()) : $title; });
add_action('wp_head', function () {
    $id = get_queried_object_id();
    $song_about = is_singular('song') && function_exists('ag_about_song') ? ag_about_song($id) : [];
    $description = is_singular() ? (ag_meta('meta_description', $id) ?: wp_strip_all_tags(ag_public_brand_text($song_about['text'] ?? '') ?: get_the_excerpt($id))) : 'Find your next favorite artist. Thoughtful discoveries, song stories, and music for the way you feel.';
    if ($description) { echo '<meta name="description" content="' . esc_attr(wp_trim_words($description, 35, '…')) . '">' . "\n"; }
    echo '<meta property="og:site_name" content="' . esc_attr(ag_site_name()) . '"><meta name="twitter:card" content="summary">' . "\n";
    $social_title = wp_get_document_title();
    echo '<meta name="twitter:title" content="' . esc_attr($social_title) . '"><meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    if (!is_singular()) {
        echo '<meta property="og:title" content="' . esc_attr($social_title) . '"><meta property="og:description" content="' . esc_attr($description) . '"><meta property="og:type" content="website">' . "\n";
    }
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
