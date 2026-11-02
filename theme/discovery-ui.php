<?php
defined('ABSPATH') || exit;

function ag_artist_listening_panel($id) {
    $url = function_exists('ag_spotify_embed_url') ? ag_spotify_embed_url($id) : '';
    $name = get_the_title($id);
    echo '<section class="artist-listening-panel' . ($url ? ' spotify-player' : '') . '" aria-label="' . esc_attr('Listen to ' . $name) . '"' . ($url ? ' data-spotify-embed="' . esc_url($url) . '" data-player-title="' . esc_attr('Spotify artist player: ' . $name) . '"' : '') . '><p class="eyebrow">A LITTLE CLOSER TO THE MUSIC</p><div class="artist-spotify-stage"><div class="spotify-preview artist-listening-preview">';
    ag_art($id, 'artist-listening-art', true, true);
    echo '<div class="artist-listening-caption"><p class="eyebrow">MEET THE ARTIST</p><p>' . esc_html($name) . '</p><span>Find your first favourite.</span></div></div>';
    if ($url) { echo '<div class="spotify-frame-slot" aria-live="polite"></div>'; }
    echo '</div>';
    if ($url) {
        echo '<div class="listening-actions"><button type="button" class="button button-dark spotify-load">' . ag_icon('play') . 'Listen to ' . esc_html($name) . '</button><details class="volume-help"><summary>' . ag_icon('volume') . '<span>Volume help</span></summary><p>Use your phone’s volume buttons or your computer’s volume controls. On Windows, Volume Mixer lets you adjust your browser separately.</p></details></div><p class="player-disclosure">Loads Spotify’s official artist player here; press play inside to listen. <a href="https://www.spotify.com/legal/privacy-policy/" target="_blank" rel="noopener noreferrer">Spotify’s privacy policy</a> applies.</p><p class="fine-print"><a href="' . esc_url(ag_meta('spotify', $id)) . '" target="_blank" rel="noopener noreferrer">Open directly in Spotify ↗</a> · This link stays available if the player cannot load.</p><noscript><p>Use the Spotify link above to listen.</p></noscript>';
    } else {
        echo '<div class="artist-listening-fallback">'; ag_listen($id); echo '</div>';
    }
    echo '<p class="artist-art-disclosure">Original Afterglow artwork · Not an artist photograph</p></section>';
}

function ag_artist_starter_note($id) {
    $description = trim((string) get_post_field('post_excerpt', $id));
    if ($description && !preg_match('/not yet|not established|not been established|no verified|curated (selection|candidate)|awaiting|specific autobiographical meaning/i', $description)) { return wp_trim_words($description, 28); }
    $themes = array_slice(array_filter(array_map('trim', explode(',', ag_meta('themes', $id)))), 0, 2);
    if ($themes) { return 'Afterglow themes: ' . implode(' · ', $themes) . '.'; }
    $moods = ag_terms_text($id, 'mood');
    return $moods ? 'Afterglow feelings: ' . $moods . '.' : 'A starting point from this artist’s Afterglow selection.';
}

function ag_artist_starter_songs($songs) {
    if (!$songs) { return; }
    echo '<section class="artist-starters section" id="artist-start" aria-labelledby="artist-starters-heading"><div class="section-heading"><div><p class="eyebrow">YOUR WAY IN</p><h2 id="artist-starters-heading">Start with these songs.</h2></div><a class="text-link" href="#artist-songs">Explore the full selection ' . ag_icon('arrow') . '</a></div><p class="section-intro">A few introductions from our curated selection.</p><div class="artist-starter-grid">';
    foreach (array_slice($songs, 0, 3) as $index => $song) {
        echo '<article class="artist-starter-card"><span class="artist-starter-number">' . esc_html(sprintf('%02d', $index + 1)) . '</span><h3><a href="' . esc_url(get_permalink($song)) . '">' . esc_html(get_the_title($song)) . '</a></h3><p class="artist-starter-note">' . esc_html(ag_artist_starter_note($song)) . '</p><div class="discovery-card-actions"><a class="text-link" href="' . esc_url(get_permalink($song)) . '">Explore this song ' . ag_icon('arrow') . '</a>'; ag_save_button($song); echo '</div></article>';
    }
    echo '</div></section>';
}

function ag_song_story_sections($content, $is_research = false) {
    // Separate presentation only: render the current editor content, never the
    // imported JSON, so later editorial changes remain visible without a reimport.
    $sections = ['listening' => $content, 'research' => ''];
    if (!$is_research || !class_exists('DOMDocument')) { return $sections; }
    $document = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML('<?xml encoding="UTF-8"><div id="ag-song-story">' . $content . '</div>', LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $root = $loaded ? $document->getElementById('ag-song-story') : null;
    if (!$root) { return $sections; }
    $sections = ['listening' => '', 'research' => ''];
    $section = 'listening';
    foreach ($root->childNodes as $node) {
        $text = trim(preg_replace('/\s+/u', ' ', $node->textContent));
        if ($node->nodeName === 'h2') {
            if ($text === 'Listening notes') { $section = 'listening'; continue; }
            if (in_array($text, ['Release information', 'Research notes'], true)) { $section = 'research'; }
        }
        $metadata = $node->nodeName === 'p' && (
            preg_match('/^Basis:\s/u', $text) ||
            str_starts_with($text, 'Based on the supplied research dated ')
        );
        $sections[$metadata ? 'research' : $section] .= $document->saveHTML($node);
    }
    return $sections;
}

function ag_song_listening_panel($id) {
    $artist = absint(ag_meta('artist_id', $id));
    $photo = $artist && get_post_status($artist) === 'publish' && function_exists('ag_media_credit') && ag_media_credit($artist);
    $url = function_exists('ag_spotify_embed_url') ? ag_spotify_embed_url($id) : '';
    $title = 'Spotify player: ' . get_the_title($id);
    echo '<section class="song-listening-panel' . ($url ? ' spotify-player' : '') . '" aria-label="' . esc_attr('Listen to ' . get_the_title($id)) . '"' . ($url ? ' data-spotify-embed="' . esc_url($url) . '" data-player-title="' . esc_attr($title) . '"' : '') . '><p class="eyebrow">BEFORE THE FIRST NOTE</p><div class="spotify-stage"><div class="spotify-preview' . ($photo ? ' has-artist-photo' : ' has-original-art') . '">';
    if ($photo) {
        ag_art($artist, 'listening-portrait', true);
        echo '<p class="preview-artist-name">' . esc_html(get_the_title($artist)) . '<span>Artist photograph</span></p><p class="listening-photo-credit">' . ag_photo_credit_html($artist, true) . '</p>';
    } else {
        ag_art($id, 'listening-art', true, true);
        echo '<div class="listening-art-caption"><p class="eyebrow">ORIGINAL AFTERGLOW ARTWORK</p><p>' . esc_html(get_the_title($id)) . '</p><span>' . esc_html(ag_artist_name($id)) . ' · Not official cover art</span></div>';
    }
    echo '</div>';
    if ($url) { echo '<div class="spotify-frame-slot" aria-live="polite"></div>'; }
    echo '</div>';
    if ($url) {
        echo '<div class="listening-actions"><button type="button" class="button button-dark spotify-load">' . ag_icon('play') . 'Listen to this song</button><details class="volume-help"><summary>' . ag_icon('volume') . '<span>Volume help</span></summary><p>Use your phone’s volume buttons or your computer’s volume controls. On Windows, Volume Mixer lets you adjust your browser separately.</p></details></div><p class="player-disclosure">Loads the Spotify player here; press play inside to listen. <a href="https://www.spotify.com/legal/privacy-policy/" target="_blank" rel="noopener noreferrer">Spotify’s privacy policy</a> applies.</p><p class="fine-print"><a href="' . esc_url(ag_meta('spotify', $id)) . '" target="_blank" rel="noopener noreferrer">Open directly in Spotify ↗</a> · This link stays available if the player cannot load.</p><noscript><p>Use the Spotify link above to listen.</p></noscript>';
    } else {
        echo '<div class="listen-panel song-listening-fallback">'; ag_listen($id); echo '</div>';
    }
    echo '</section>';
}

function ag_discovery_song_url($song, $context) {
    $trail = array_slice(array_values(array_unique(array_merge($context['trail'], [$context['current']['id']]))), -8);
    return add_query_arg('ag_trail', implode(',', $trail), get_permalink($song['id']));
}

function ag_next_discovery($context) {
    if (!$context) { return; }
    echo '<section class="next-discovery" data-discovery-primary aria-label="Your next discovery"><p class="eyebrow">YOUR NEXT DISCOVERY</p>';
    $song = $context['results'][0] ?? null;
    if ($song) {
        $url = ag_discovery_song_url($song, $context);
        echo '<h2><a href="' . esc_url($url) . '">' . esc_html(get_the_title($song['id'])) . '</a></h2><p class="next-artist">by <a href="' . esc_url(get_permalink($song['artist'])) . '">' . esc_html(get_the_title($song['artist'])) . '</a>' . ($song['different_artist'] ? '<span>A new artist to explore</span>' : '') . '</p><p class="discovery-reason">' . esc_html($song['reason']) . '</p><a class="text-link next-discovery-link" href="' . esc_url($url) . '" aria-label="' . esc_attr('Next discovery: ' . get_the_title($song['id']) . ' by ' . get_the_title($song['artist'])) . '">Next discovery ' . ag_icon('arrow') . '</a>';
    } else { echo '<p>No next song for this combination yet. <a href="#discover-more" data-discovery-open>Change the feeling or theme.</a></p>'; }
    echo '</section>';
}

function ag_discovery_panel($context) {
    if (!$context) { return; }
    $count = count($context['results']); $filtered = isset($_GET['ag_feeling']) || isset($_GET['ag_theme']);
    echo '<section class="discovery-explore section" id="discover-more" aria-labelledby="discovery-heading"><div class="discovery-heading"><div><p class="eyebrow">FOLLOW YOUR CURIOSITY</p><h2 id="discovery-heading">Where do you want to go next?</h2><p>Stay with a feeling, follow a theme, or meet another artist.</p></div>';
    ag_next_discovery($context);
    echo '</div><details class="discovery-controls"' . ($filtered ? ' open' : '') . '><summary>Change the feeling or theme ' . ag_icon('arrow') . '</summary><form class="discovery-form" action="' . esc_url(get_permalink($context['current']['id'])) . '#discover-more" method="get">';
    if ($context['trail']) { echo '<input type="hidden" name="ag_trail" value="' . esc_attr(implode(',', $context['trail'])) . '">'; }
    foreach (['feeling' => 'moods', 'theme' => 'themes'] as $filter => $field) {
        echo '<div class="discovery-field"><label for="discovery-' . $filter . '">' . ucfirst($filter) . '</label><select id="discovery-' . $filter . '" name="ag_' . $filter . '">';
        $options = ['any' => 'Any ' . $filter];
        if ($context['current'][$field]) { $options = ['same' => 'Same ' . $filter . ' as this song'] + $options; }
        foreach ($options + $context['choices'][$filter] as $value => $label) { echo '<option value="' . esc_attr($value) . '" ' . selected($context['preferences'][$filter], $value, false) . '>' . esc_html($label) . '</option>'; }
        echo '</select></div>';
    }
    echo '<button class="button button-dark" type="submit">Find songs ' . ag_icon('arrow') . '</button></form><p class="fine-print">Feelings and themes are Afterglow editorial tags. Suggestions also use our curated song and artist connections.</p></details><p class="discovery-status" role="status">' . esc_html($context['invalid'] ? 'That filter is unavailable; showing this song’s suggestions. ' : '') . $count . ' suggestion' . ($count === 1 ? '' : 's') . ' to explore.</p><div class="discovery-results" data-discovery-results data-result-count="' . $count . '">';
    foreach ($context['results'] as $song) {
        $url = ag_discovery_song_url($song, $context);
        echo '<article class="discovery-card"><p class="eyebrow">' . ($song['different_artist'] ? 'MEET ANOTHER ARTIST' : 'MORE FROM THIS ARTIST') . '</p><h3><a href="' . esc_url($url) . '">' . esc_html(get_the_title($song['id'])) . '</a></h3><a class="discovery-artist" href="' . esc_url(get_permalink($song['artist'])) . '">' . esc_html(get_the_title($song['artist'])) . ' ' . ag_icon('diagonal') . '</a><p class="discovery-reason">' . esc_html($song['reason']) . '</p><div class="discovery-card-actions"><a class="text-link" href="' . esc_url($url) . '">Explore this song ' . ag_icon('arrow') . '</a>'; ag_save_button($song['id']); echo '</div></article>';
    }
    if (!$count) { echo '<div class="discovery-empty"><h3>No songs for that combination yet.</h3><p>Try one filter at a time, or start again with this song’s suggestions.</p><a class="text-link" href="' . esc_url(get_permalink($context['current']['id'])) . '#discover-more">Reset suggestions ' . ag_icon('arrow') . '</a></div>'; }
    echo '</div></section>';
}
