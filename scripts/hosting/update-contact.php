<?php
// WP-CLI only. The downloadable release embeds these two public theme files.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if (!isset($tf_contact_release)) {
    $tf_contact_release = [];
    foreach (['page-contact.php', 'assets/contact.css'] as $relative) {
        $tf_contact_release[$relative] = base64_encode(file_get_contents(get_stylesheet_directory() . '/' . $relative));
    }
}
$original = '<p>Have a listening recommendation, a correction, or a thoughtful idea for the site? We would like to hear it.</p><h2>Corrections & sources.</h2><p>For a correction, include the page URL, the specific detail that needs attention, and an original source where possible. An editor should review submissions before making changes.</p><h2>Before launch.</h2><p>The owner must configure a public contact email. This MVP does not collect submissions through a form.</p>';
$updated = '<p>A song you can’t stop playing. A detail we should get right. Or just something you’d like to share. We’d love to hear from you.</p>';
$email = 'contact@tunetofond.com';
$host = wp_parse_url(home_url(), PHP_URL_HOST);
if (!in_array($host, ['tunetofond.com', 'www.tunetofond.com', 'localhost'], true) || get_stylesheet() !== 'afterglow' || get_template() !== 'afterglow') {
    WP_CLI::error('Expected the tunetofond site with its existing afterglow theme. Nothing changed.');
}
$page = get_page_by_path('contact', OBJECT, 'page');
if (!$page || !in_array($page->post_content, [$original, $updated], true)) {
    WP_CLI::error('The Contact page has edits outside this update. Nothing changed; preserve and review those edits first.');
}
$previous_email = get_theme_mod('ag_contact_email', '');
if ($previous_email !== '' && $previous_email !== $email) {
    WP_CLI::error('A different public contact email is configured. Nothing changed.');
}
$files = [];
$theme = get_stylesheet_directory();
foreach ($tf_contact_release as $relative => $encoded) {
    if (!in_array($relative, ['page-contact.php', 'assets/contact.css'], true)) { WP_CLI::error('Unexpected release file.'); }
    $contents = base64_decode($encoded, true);
    $path = $theme . '/' . $relative;
    if ($contents === false || (file_exists($path) && file_get_contents($path) !== $contents)) {
        WP_CLI::error('A Contact template or stylesheet already has different edits. Nothing changed.');
    }
    $files[$relative] = $contents;
}
if (count($files) !== 2) { WP_CLI::error('Incomplete Contact release.'); }
if (in_array('check', $args ?? [], true)) {
    WP_CLI::success('Contact update checks passed. No changes made.');
    return;
}
$backup_parent = realpath($args[0] ?? '');
$web_root = realpath(ABSPATH);
if (!$backup_parent || !is_dir($backup_parent) || !is_writable($backup_parent) || $backup_parent === $web_root || str_starts_with($backup_parent . '/', $web_root . '/')) {
    WP_CLI::error('Pass an existing writable backup directory outside public_html. Nothing changed.');
}
$already_installed = $page->post_content === $updated && $previous_email === $email;
foreach ($files as $relative => $contents) { $already_installed = $already_installed && is_file($theme . '/' . $relative); }
if ($already_installed) { WP_CLI::success('This Contact update is already installed. Nothing changed.'); return; }
$backup = $backup_parent . '/contact-page-backup-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(3));
if (!mkdir($backup, 0700)) { WP_CLI::error('Could not create private backup directory. Nothing changed.'); }
$had_email = array_key_exists('ag_contact_email', get_theme_mods() ?: []);
$before = ['page_id' => $page->ID, 'post_title' => $page->post_title, 'post_content' => $page->post_content, 'had_contact_email' => $had_email, 'contact_email' => $previous_email, 'previous_files' => []];
foreach ($files as $relative => $contents) {
    $path = $theme . '/' . $relative;
    $before['previous_files'][$relative] = is_file($path) ? base64_encode(file_get_contents($path)) : null;
}
$backup_file = $backup . '/before.json';
if (file_put_contents($backup_file, wp_json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false || !chmod($backup_file, 0600)) {
    WP_CLI::error('Could not write private backup. Nothing changed.');
}
$created = [];
try {
    foreach ($files as $relative => $contents) {
        $path = $theme . '/' . $relative;
        if (is_file($path)) { continue; }
        $temporary = tempnam(dirname($path), '.tf-contact-');
        if (!$temporary) { throw new RuntimeException('Could not create a theme update file.'); }
        if (file_put_contents($temporary, $contents) !== strlen($contents) || !chmod($temporary, 0644) || !rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('Could not install a Contact file.');
        }
        $created[] = $path;
    }
    if ($page->post_content !== $updated) {
        $result = wp_update_post(wp_slash(['ID' => $page->ID, 'post_content' => $updated]), true);
        if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
    }
    set_theme_mod('ag_contact_email', $email);
    if (get_post($page->ID)->post_content !== $updated || get_theme_mod('ag_contact_email') !== $email) {
        throw new RuntimeException('Contact content verification failed.');
    }
} catch (Throwable $error) {
    foreach ($created as $path) { @unlink($path); }
    $restored = wp_update_post(wp_slash(['ID' => $page->ID, 'post_content' => $before['post_content']]), true);
    if ($had_email) { set_theme_mod('ag_contact_email', $previous_email); } else { remove_theme_mod('ag_contact_email'); }
    WP_CLI::error($error->getMessage() . ' Update rolled back. Backup: ' . $backup . (is_wp_error($restored) ? ' CONTENT RESTORE NEEDS REVIEW.' : ''));
}
clean_post_cache($page->ID);
WP_CLI::success('Contact page updated. Private backup: ' . $backup);
WP_CLI::line('Clear the hosting/CDN cache, then open /contact/. No catalog records, accounts, or indexing settings were changed.');
