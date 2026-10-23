<?php
// Run via scripts/test-editorial.sh. Only temporary draft records are mutated.
$GLOBALS['ag_test_checks'] = 0;
function ag_assert($test, $message) {  if (!$test) { throw new RuntimeException($message); } $GLOBALS['ag_test_checks']++; }
$admin = get_user_by('login', 'editor');
wp_set_current_user($admin->ID);
$artist = wp_insert_post(['post_type' => 'artist', 'post_title' => 'Editorial Test Artist', 'post_status' => 'draft']);
$song = wp_insert_post(['post_type' => 'song', 'post_title' => 'Editorial Test Song', 'post_status' => 'draft']);
$subscriber = wp_insert_user(['user_login' => 'ag_test_subscriber_' . time(), 'user_pass' => wp_generate_password(), 'role' => 'subscriber']);
try {
    $_POST = ['ag' => ['spotify' => 'https://open.spotify.com/track/test123']];
    do_action('save_post', $song, get_post($song), true);
    ag_assert(get_post_meta($song, '_ag_spotify', true) === '', 'Saving without a nonce must be rejected.');
    $nonce = wp_create_nonce('ag_save_details');
    $_POST = ['ag_nonce' => $nonce, 'ag' => ['spotify' => 'https://evil.example/track/a', 'artist_id' => $artist, 'related' => [$artist, $song], 'meaning_type' => 'sourced', 'artist_comment' => 'An uncited claim', 'release' => '2025-02-31', 'sources' => "Bad | javascript:alert(1)\nUnsafe | http://example.org/interview", 'meta_description' => '<script>alert(1)</script>Original summary', 'palette' => '../../x']];
    do_action('save_post', $song, get_post($song), true);
    ag_assert(get_post_meta($song, '_ag_spotify', true) === '', 'Non-Spotify URLs must be rejected.');
    ag_assert(get_post_meta($song, '_ag_artist_id', true) === (string)$artist, 'Artist relationship must save.');
    ag_assert(get_post_meta($song, '_ag_related', true) === '', 'Self and wrong-type relations must be rejected.');
    ag_assert(get_post_meta($song, '_ag_release', true) === '', 'Impossible calendar dates must be rejected.');
    ag_assert(get_post_meta($song, '_ag_meaning_type', true) === 'editorial', 'Uncited intent must become editorial.');
    ag_assert(get_post_meta($song, '_ag_artist_comment', true) === '', 'Uncited artist comments must be removed.');
    ag_assert(get_post_meta($song, '_ag_sources', true) === '', 'Unsafe source URLs must be removed.');
    ag_assert(!str_contains(get_post_meta($song, '_ag_meta_description', true), '<'), 'Descriptions must be plain text.');
    ag_assert(get_post_meta($song, '_ag_palette', true) === 'lilac', 'Palette must use an allowed value.');
    $_POST = ['ag_nonce' => $nonce, 'ag' => ['spotify' => 'https://open.spotify.com/track/test123', 'sources' => 'Original interview | https://example.org/interview', 'meaning_type' => 'sourced', 'artist_comment' => 'A short sourced paraphrase', 'release' => '2024-02-29']];
    do_action('save_post', $song, get_post($song), true);
    ag_assert(get_post_meta($song, '_ag_spotify', true) === 'https://open.spotify.com/track/test123', 'Valid Spotify URL must save.');
    ag_assert(get_post_meta($song, '_ag_meaning_type', true) === 'sourced', 'Sourced attribution must save.');
    ag_assert(get_post_meta($song, '_ag_release', true) === '2024-02-29', 'Valid leap-year date must save.');
    wp_set_current_user($subscriber);
    $_POST = ['ag_nonce' => wp_create_nonce('ag_save_details'), 'ag' => ['spotify' => 'https://open.spotify.com/track/changed']];
    do_action('save_post', $song, get_post($song), true);
    ag_assert(get_post_meta($song, '_ag_spotify', true) === 'https://open.spotify.com/track/test123', 'Subscribers must not edit metadata.');
    wp_set_current_user($admin->ID);
    $args = apply_filters('wp_sitemaps_posts_query_args', ['post_type' => 'artist'], 'artist');
    $public_artists = new WP_Query($args);
    ag_assert($public_artists->post_count === 0, 'Demo artists must not appear in sitemap queries.');
    $sara = get_page_by_path('sara-kays', OBJECT, 'artist');
    ag_assert($sara && get_post_meta($sara->ID, '_ag_demo', true) === '1', 'Seed artists must be explicitly marked as demo.');
    WP_CLI::success($GLOBALS['ag_test_checks'] . ' editorial integrity and permission checks passed.');
} finally {
    $_POST = []; wp_set_current_user($admin->ID);
    wp_delete_post($song, true); wp_delete_post($artist, true);
    require_once ABSPATH . 'wp-admin/includes/user.php'; wp_delete_user($subscriber);
}
