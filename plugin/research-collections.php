<?php
defined('ABSPATH') || exit;

/** Research candidates, not verified catalogue data or a popularity ranking. */
function ag_astrid_candidates() {
    return [
        'hurts-so-good' => 'Hurts So Good',
        'its-ok-if-you-forget-me' => "It's OK If You Forget Me",
        'think-before-i-talk' => 'Think Before I Talk',
        'such-a-boy' => 'Such a Boy',
        'paper-thin' => 'Paper Thin',
        'breathe' => 'Breathe',
        'emotion' => 'Emotion',
        'someone-new' => 'Someone New',
        'the-first-one' => 'The First One',
        'dance-dance-dance' => 'Dance Dance Dance',
    ];
}

function ag_astrid_artist() {
    $id = absint(get_option('ag_astrid_research_artist'));
    if ($id && get_post_type($id) === 'artist') { return get_post($id); }
    return get_page_by_path('astrid-s', OBJECT, 'artist');
}

function ag_astrid_find_song($key, $title, $artist_id) {
    // Retain an imported record even if its title, slug, or publication status changes.
    $common = ['post_type' => 'song', 'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'], 'numberposts' => 1];
    $owned = get_posts($common + ['meta_key' => '_ag_research_key', 'meta_value' => 'astrid-s/' . $key]);
    if ($owned) { return $owned[0]->ID; }
    $existing = get_posts($common + ['title' => $title, 'meta_key' => '_ag_artist_id', 'meta_value' => (string)$artist_id]);
    return $existing ? $existing[0]->ID : 0;
}

function ag_import_astrid_research() {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    // WordPress option uniqueness prevents two requests creating duplicate collections.
    if (!add_option('ag_astrid_import_lock', time(), '', false)) {
        if ((int)get_option('ag_astrid_import_lock') < time() - 300) { delete_option('ag_astrid_import_lock'); }
        return new WP_Error('busy', 'An import is running. Try again shortly.');
    }
    $created = []; $reused = [];
    try {
        $artist = ag_astrid_artist();
        if (!$artist) {
            $artist_id = wp_insert_post(['post_type' => 'artist', 'post_status' => 'draft', 'post_name' => 'astrid-s', 'post_title' => 'Astrid S', 'post_content' => '<p>Research draft: biography, official links, country, and source citations await verification. Do not publish until researched and reviewed.</p>'], true);
            if (is_wp_error($artist_id)) { return $artist_id; }
            update_post_meta($artist_id, '_ag_demo', '1');
            update_post_meta($artist_id, '_ag_palette', 'sand');
            $created[] = $artist_id;
        } else { $artist_id = $artist->ID; $reused[] = $artist_id; }
        update_option('ag_astrid_research_artist', $artist_id, false);
        foreach (ag_astrid_candidates() as $key => $title) {
            $existing = ag_astrid_find_song($key, $title, $artist_id);
            if ($existing) { $reused[] = $existing; continue; }
            $id = wp_insert_post([
                'post_type' => 'song', 'post_status' => 'draft',
                'post_name' => 'astrid-s-' . $key, 'post_title' => $title,
                'post_content' => '<h2>Research pending</h2><p>This title is a provisional research candidate, not an entry in a verified top-ten ranking. Verify the artist credit, official track link, release information, and original sources before publishing.</p><h2>Meaning and sources</h2><p>No artist explanation or editorial song analysis has been added. Do not attribute a meaning or quotation to the artist without a credible original source.</p>',
            ], true);
            if (is_wp_error($id)) { return $id; }
            foreach (['demo' => '1', 'artist_id' => (string)$artist_id, 'palette' => 'sand', 'meaning_type' => 'editorial', 'research_key' => 'astrid-s/' . $key] as $field => $value) {
                update_post_meta($id, '_ag_' . $field, $value);
            }
            $created[] = $id;
        }
        return ['created' => $created, 'reused' => $reused, 'artist_id' => $artist_id];
    } finally { delete_option('ag_astrid_import_lock'); }
}

add_action('admin_menu', function () {
    add_submenu_page('edit.php?post_type=artist', 'Research collections', 'Research collections', 'manage_options', 'ag-research', 'ag_research_page');
});

function ag_research_page() {
    if (!current_user_can('manage_options')) { return; }
    echo '<div class="wrap"><h1>Astrid S · Research collection</h1><p><a class="button button-primary" href="' . esc_url(admin_url('edit.php?post_type=artist&page=ag-handoffs')) . '">Import all supplied artist research</a></p><p>This older screen holds the original provisional queue. Use Import supplied research for the new sourced collections.</p><p>A provisional ten-song research queue. This is <strong>not a verified Spotify top ten</strong>. The order below is alphabetical, not a popularity ranking.</p>';
    echo '<div class="notice notice-warning inline"><p><strong>Source verification pending.</strong> Research websites were blocked in the development environment. No new biography facts, dates, interviews, quotations, official Spotify IDs, or song meanings have been verified. Created records stay in draft with the Demo flag checked.</p></div>';
    if (isset($_GET['imported'])) { echo '<div class="notice notice-success inline"><p>Research drafts prepared. Existing records were retained without changes.</p></div>'; }
    echo '<p>The importer creates only missing drafts. It does not overwrite your biography, song text, sources, artwork, relationships, or publication status. It does not add unpublished songs to the public artist profile.</p><form action="' . esc_url(admin_url('admin-post.php')) . '" method="post"><input type="hidden" name="action" value="ag_astrid_research">';
    wp_nonce_field('ag_astrid_research'); submit_button('Create missing research drafts'); echo '</form>';
    $artist = ag_astrid_artist();
    if ($artist) { echo '<p><a href="' . esc_url(get_edit_post_link($artist->ID)) . '">Edit Astrid S profile</a></p>'; }
    echo '<table class="widefat striped"><thead><tr><th>Candidate title</th><th>WordPress record</th></tr></thead><tbody>';
    $titles = ag_astrid_candidates(); asort($titles, SORT_NATURAL | SORT_FLAG_CASE);
    foreach ($titles as $key => $title) {
        $id = $artist ? ag_astrid_find_song($key, $title, $artist->ID) : 0;
        echo '<tr><td>' . esc_html($title) . '</td><td>';
        if ($id) { echo '<a href="' . esc_url(get_edit_post_link($id)) . '">' . esc_html(get_post_status($id)) . ' · Edit record</a>'; } else { echo 'Not imported'; }
        echo '</td></tr>';
    }
    echo '</tbody></table><h2>Before publishing</h2><ol><li>Choose and document the ranking basis, market, and retrieval date, or label this a curated selection.</li><li>Verify track identity and artist credits through official listening/catalogue pages.</li><li>Record original interview URLs for artist statements. Leave unsourced meaning fields empty.</li><li>Write original editorial content, keep licensed or abstract artwork, and review each page before unchecking Demo.</li></ol></div>';
}

add_action('admin_post_ag_astrid_research', function () {
    if (!current_user_can('manage_options') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Not permitted.', '', ['response' => 403]); }
    check_admin_referer('ag_astrid_research');
    $result = ag_import_astrid_research();
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()), '', ['response' => 400]); }
    wp_safe_redirect(admin_url('edit.php?post_type=artist&page=ag-research&imported=1'));
    exit;
});
