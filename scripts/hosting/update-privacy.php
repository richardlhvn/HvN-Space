<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$new = base64_decode('PHA+VGhpcyBub3RpY2UgZXhwbGFpbnMgaG93IGluZm9ybWF0aW9uIGlzIGhhbmRsZWQgYnkgdHVuZXRvZm9uZOKAmXMgY3VycmVudCB3ZWJzaXRlIGZlYXR1cmVzLjwvcD4KPGgyPllvdXIgc2F2ZWQgZGlzY292ZXJpZXMuPC9oMj4KPHA+V2hlbiB5b3Ugc2F2ZSBhbiBhcnRpc3Qgb3Igc29uZywgaXRzIHJlZmVyZW5jZSBudW1iZXIgaXMgc3RvcmVkIGluIHlvdXIgYnJvd3Nlci4gWW91IGRvIG5vdCBuZWVkIGFuIGFjY291bnQuIFlvdXIgY29sbGVjdGlvbiBpcyBzcGVjaWZpYyB0byB0aGF0IGJyb3dzZXIgYW5kIGRvZXMgbm90IGF1dG9tYXRpY2FsbHkgc3luYyBhY3Jvc3MgZGV2aWNlcy4gV2hlbiB5b3Ugb3BlbiBZb3VyIHNhdmVkLCB0aG9zZSByZWZlcmVuY2UgbnVtYmVycyBhcmUgc2VudCB0byB0aGlzIHdlYnNpdGUgdG8gcmV0cmlldmUgdGhlIHB1YmxpYyBhcnRpc3QgYW5kIHNvbmcgZGV0YWlscy48L3A+CjxwPllvdSBjYW4gcmVtb3ZlIHNhdmVkIGl0ZW1zIHVzaW5nIHRoZWlyIGhlYXJ0IGJ1dHRvbnMsIG9yIGNsZWFyIHRoaXMgd2Vic2l0ZeKAmXMgc3RvcmVkIGRhdGEgdGhyb3VnaCB5b3VyIGJyb3dzZXIgc2V0dGluZ3MuPC9wPgo8aDI+SG9zdGluZyBhbmQgc2l0ZSBvcGVyYXRpb24uPC9oMj4KPHA+VGhpcyB3ZWJzaXRlIHVzZXMgSG9zdGluZ2VyIGhvc3RpbmcgYW5kIGl0cyBjb250ZW50IGRlbGl2ZXJ5IG5ldHdvcmsuIFJlcXVlc3RzIHRvIHRoZSBzaXRlIGludm9sdmUgdGVjaG5pY2FsIGluZm9ybWF0aW9uIHN1Y2ggYXMgYW4gSVAgYWRkcmVzcywgdGhlIHJlcXVlc3RlZCBwYWdlLCBhbmQgYnJvd3NlciByZXF1ZXN0IGluZm9ybWF0aW9uLiBIb3N0aW5nIGluZnJhc3RydWN0dXJlIHByb2Nlc3NlcyB0aGlzIGluZm9ybWF0aW9uIHRvIGRlbGl2ZXIgYW5kIHByb3RlY3QgdGhlIHNpdGUgYW5kIHByb3ZpZGVzIHJlcXVlc3QgbG9ncyBhbmQgdHJhZmZpYyBzdGF0aXN0aWNzLjwvcD4KPHA+V29yZFByZXNzIGFsc28gdXNlcyBjb29raWVzIGZvciBzaWduZWQtaW4gc2l0ZSBhZG1pbmlzdHJhdG9ycy48L3A+CjxoMj5MaXN0ZW5pbmcgd2l0aCBTcG90aWZ5LjwvaDI+CjxwPlNwb3RpZnkgcGxheWVycyBhcmUgb3B0aW9uYWwuIEEgcGxheWVyIGlzIGxvYWRlZCB3aGVuIHlvdSBjaG9vc2Ug4oCcTG9hZCBTcG90aWZ5IHBsYXllcizigJ0gd2hpY2ggY29ubmVjdHMgeW91ciBicm93c2VyIHRvIFNwb3RpZnkuIFNwb3RpZnkgdGhlbiBoYW5kbGVzIGluZm9ybWF0aW9uIGFuZCBzdG9yYWdlIGFjY29yZGluZyB0byBpdHMgb3duIDxhIGhyZWY9Imh0dHBzOi8vd3d3LnNwb3RpZnkuY29tL2xlZ2FsL3ByaXZhY3ktcG9saWN5LyI+cHJpdmFjeSBwb2xpY3k8L2E+LiBGb2xsb3dpbmcgYSBTcG90aWZ5IGxpbmsgYWxzbyB0YWtlcyB5b3UgdG8gU3BvdGlmeS48L3A+CjxoMj5Xcml0aW5nIHRvIHVzLjwvaDI+CjxwPlRoZSBDb250YWN0IHBhZ2Ugb3BlbnMgeW91ciBvd24gZW1haWwgYXBwOyBpdCBkb2VzIG5vdCBzdWJtaXQgYSB3ZWJzaXRlIGZvcm0uIElmIHlvdSBlbWFpbCB1cywgeW91ciBhZGRyZXNzLCBtZXNzYWdlLCBhbmQgYW55IGluZm9ybWF0aW9uIHlvdSBpbmNsdWRlIGFyZSBwcm9jZXNzZWQgdGhyb3VnaCBlbWFpbCBzZXJ2aWNlcywgaW5jbHVkaW5nIG91ciBIb3N0aW5nZXIgbWFpbGJveCwgc28gd2UgY2FuIHJlYWQgYW5kIHJlc3BvbmQgdG8geW91ciBtZXNzYWdlLiBQbGVhc2UgaW5jbHVkZSBvbmx5IHRoZSBpbmZvcm1hdGlvbiBuZWVkZWQgZm9yIHlvdXIgZW5xdWlyeS48L3A+CjxoMj5Qcml2YWN5IHF1ZXN0aW9ucy48L2gyPgo8cD5Gb3IgcXVlc3Rpb25zIGFib3V0IHRoaXMgbm90aWNlIG9yIHRoZSBpbmZvcm1hdGlvbiB5b3UgaGF2ZSBzaGFyZWQgd2l0aCB1cywgZW1haWwgPGEgaHJlZj0ibWFpbHRvOmNvbnRhY3RAdHVuZXRvZm9uZC5jb20/c3ViamVjdD1Qcml2YWN5JTIwZW5xdWlyeSI+Y29udGFjdEB0dW5ldG9mb25kLmNvbTwvYT4uPC9wPgo=', true);
$p = get_post(23);
if (wp_parse_url(home_url(), PHP_URL_HOST) !== 'tunetofond.com' || !$p || $p->post_type !== 'page' || $p->post_name !== 'privacy' || $p->post_status !== 'publish' || $p->post_title !== 'Privacy, in plain language.') {
    WP_CLI::error('Site or Privacy page differs. Nothing changed.');
}
if ($p->post_content === $new) { WP_CLI::success('Approved Privacy text is already installed. Nothing changed.'); return; }
if (hash('sha256', apply_filters('the_content', $p->post_content)) !== 'cd8aa2e36db9e319bf963ae2afa402f7d70ee8a72909918605c16f4ed74d80bf') {
    WP_CLI::error('Privacy has different edits. Nothing changed; review them first.');
}
$parent = realpath($args[0] ?? '');
$root = realpath(ABSPATH);
if (!$parent || !$root || !is_writable($parent) || $parent === $root || str_starts_with($parent . '/', $root . '/')) {
    WP_CLI::error('A writable private backup directory outside public_html is required.');
}
if (in_array('check', $args ?? [], true)) { WP_CLI::success('Privacy checks passed. No changes made.'); return; }
$backup = $parent . '/privacy-backup-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(3));
if (!mkdir($backup, 0700)) { WP_CLI::error('Could not create private backup. Nothing changed.'); }
$before = ['page_id'=>23, 'post_title'=>$p->post_title, 'post_name'=>$p->post_name, 'post_status'=>$p->post_status, 'post_content'=>$p->post_content];
$backup_file = $backup . '/before.json';
if (file_put_contents($backup_file, wp_json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false || !chmod($backup_file, 0600)) {
    WP_CLI::error('Could not secure backup. Nothing changed.');
}
$result = wp_update_post(wp_slash(['ID'=>23, 'post_content'=>$new]), true);
clean_post_cache(23);
$after = get_post(23);
if (is_wp_error($result) || !$after || $after->post_content !== $new) {
    $restored = wp_update_post(wp_slash(['ID'=>23, 'post_content'=>$before['post_content']]), true);
    WP_CLI::error('Update failed. Backup: ' . $backup . (is_wp_error($restored) ? ' RESTORE NEEDS REVIEW.' : ' Original content restored.'));
}
WP_CLI::success('Privacy text updated. Private backup: ' . $backup);
WP_CLI::line('Only page 23 body changed. Title, slug, visibility and indexing settings retained.');
