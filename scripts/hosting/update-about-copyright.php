<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$edits = json_decode(base64_decode('W3siaWQiOjIyLCJzbHVnIjoiYWJvdXQiLCJleHBlY3RlZCI6Ijc3OGZkYjZmZmI2ZjZhYTI5NjlkMDJlMjA1ZThiYzNkNDEwYWFjYTM5NWRkMmNlZWJmYmYxNzYyNmFmOTk3MzEiLCJjb250ZW50IjoiPHA+dHVuZXRvZm9uZCBpcyBhbiBpbmRlcGVuZGVudCBkaXNjb3ZlcnkgcHJvamVjdCBmb3IgcGVvcGxlIHdobyBjb25uZWN0IHdpdGggZW1vdGlvbmFsIHBvcDogdGhlIHF1aWV0IGRldGFpbHMsIHRoZSBjb21wbGljYXRlZCBmZWVsaW5ncywgYW5kIHRoZSBtZWxvZGllcyB0aGF0IHN0YXkgYSBsaXR0bGUgbG9uZ2VyLjwvcD5cbjxoMj5Gb2xsb3cgdGhlIGZlZWxpbmcuPC9oMj5cbjxwPldlIGJ1aWxkIHNtYWxsLCBkZWxpYmVyYXRlIHJvdXRlcyBiZXR3ZWVuIGFydGlzdHMgYW5kIHNvbmdzLiBFeHBsb3JlIHNoYXJlZCBnZW5yZXMsIGZlZWxpbmdzLCBhbmQgdGhlbWVzLCBvciBzdGFydCB3aXRoIGEgc29uZyBhbmQgc2VlIHdoZXJlIGl0IGxlYWRzLiBUaGVzZSBsYWJlbHMgYXJlIGVkaXRvcmlhbCBkZXNjcmlwdGlvbnMsIG5vdCBhbiBhcnRpc3TigJlzIGVuZG9yc2VtZW50LjwvcD5cbjxoMj5GaW5kIHlvdXIgd2F5IGluLjwvaDI+XG48cD5BcnRpc3QgcHJvZmlsZXMgYnJpbmcgdG9nZXRoZXIgYmlvZ3JhcGhpZXMsIHNlbGVjdGVkIHNvbmdzLCBhbmQgbGlua3MgZm9yIGZ1cnRoZXIgbGlzdGVuaW5nIGFuZCByZWFkaW5nLiBTb25nIHBhZ2VzIG9mZmVyIGFub3RoZXIgd2F5IGludG8gdGhlIG11c2ljLCB3aXRoIGNvbnRleHQgYW5kIGNvbm5lY3Rpb25zIHRvIGV4cGxvcmUuIEZvbGxvdyB0aGUgc291cmNlIGxpbmtzIHRvIHJlYWQgZnVydGhlciwgYW5kIHNhdmUgZGlzY292ZXJpZXMgeW91IHdvdWxkIGxpa2UgdG8gcmV0dXJuIHRvIG9uIHRoaXMgYnJvd3Nlci48L3A+XG48aDI+VGhlIGltYWdlcyB5b3Ugc2VlLjwvaDI+XG48cD5Tb21lIGFydGlzdCBwcm9maWxlcyB1c2UgcGhvdG9ncmFwaHMgd2l0aCBpbmRpdmlkdWFsIGNyZWRpdHMgYW5kIGxpY2VuY2UgaW5mb3JtYXRpb24uIE90aGVycyB1c2Ugb3JpZ2luYWwgYWJzdHJhY3QgaWxsdXN0cmF0aW9ucywgd2hpY2ggYXJlIG5vdCBhcnRpc3QgcG9ydHJhaXRzIG9yIG9mZmljaWFsIGFsYnVtIGNvdmVycy4gWW91IGNhbiBmaW5kIHRoZSBwaG90b2dyYXBoeSBzb3VyY2VzIGFuZCBhdHRyaWJ1dGlvbiBkZXRhaWxzIG9uIG91ciA8YSBocmVmPVwiaHR0cHM6Ly90dW5ldG9mb25kLmNvbS9tZWRpYS1jcmVkaXRzL1wiPk1lZGlhIGNyZWRpdHMgcGFnZTwvYT4uPC9wPlxuPGgyPk91ciBlZGl0b3JpYWwgcHJvbWlzZS48L2gyPlxuPHA+T3JpZ2luYWwgd3JpdGluZy4gTm8gY29waWVkIGJpb2dyYXBoaWVzIG9yIHNvbmcgbHlyaWNzLiBObyBpbnZlbnRlZCBpbnRlcnZpZXdzLCBxdW90YXRpb25zLCBvciBhcnRpc3QgaW50ZW50LiBMaXN0ZW5pbmcgaGFwcGVucyB0aHJvdWdoIFNwb3RpZnksIGFuZCB3ZSBhcmUgbm90IGFmZmlsaWF0ZWQgd2l0aCBTcG90aWZ5IG9yIHRoZSBhcnRpc3RzIGZlYXR1cmVkLjwvcD5cbjxwPkhhdmUgYSBkaXNjb3ZlcnkgdG8gc2hhcmUgb3IgYSBkZXRhaWwgd2Ugc2hvdWxkIGdldCByaWdodD8gPGEgaHJlZj1cImh0dHBzOi8vdHVuZXRvZm9uZC5jb20vY29udGFjdC9cIj5Xcml0ZSB1cyBhIG5vdGU8L2E+LjwvcD5cbiJ9LHsiaWQiOjI1LCJzbHVnIjoiY29weXJpZ2h0IiwiZXhwZWN0ZWQiOiI3MzA0NjkwNjU2MGYxMjVhYTU0NzgwM2NhYjk0NTk5NDJiODMwZmI0ZTFhMWU5ODU4YzY5MzIyYWM5MmNjZDJkIiwiY29udGVudCI6IjxwPnR1bmV0b2ZvbmQgaXMgYW4gaW5kZXBlbmRlbnQgbXVzaWMgZGlzY292ZXJ5IHByb2plY3QuIEFydGlzdCBuYW1lcyBhbmQgc29uZyB0aXRsZXMgaWRlbnRpZnkgdGhlIHN1YmplY3RzIG9mIGVkaXRvcmlhbCBjb3ZlcmFnZTsgbm8gZW5kb3JzZW1lbnQgb3IgYWZmaWxpYXRpb24gaXMgaW1wbGllZC48L3A+XG48aDI+TXVzaWMgYW5kIGxpc3RlbmluZy48L2gyPlxuPHA+dHVuZXRvZm9uZCBkb2VzIG5vdCBob3N0IG11c2ljIHJlY29yZGluZ3MsIG9mZmVyIG11c2ljIGRvd25sb2Fkcywgb3IgcmVwcm9kdWNlIGNvbXBsZXRlIHNvbmcgbHlyaWNzLiBMaXN0ZW5pbmcgbGlua3MgbGVhZCB0byBTcG90aWZ5LCBhbmQgb3B0aW9uYWwgb2ZmaWNpYWwgU3BvdGlmeSBwbGF5ZXJzIGNvbm5lY3QgdG8gU3BvdGlmeSB3aGVuIHlvdSBjaG9vc2UgdG8gbG9hZCB0aGVtLiBNdXNpYyBhbmQgYW55IGFydHdvcmsgc2hvd24gd2l0aGluIHRob3NlIHBsYXllcnMgYXJlIHByb3ZpZGVkIHRocm91Z2ggU3BvdGlmeS48L3A+XG48aDI+QXJ0d29yayBhbmQgcGhvdG9ncmFwaHkuPC9oMj5cbjxwPk91ciBvcmlnaW5hbCBhYnN0cmFjdCBpbGx1c3RyYXRpb25zIGFyZSBub3QgcGhvdG9ncmFwaHMgb2YgYXJ0aXN0cyBvciBvZmZpY2lhbCBhbGJ1bSBjb3ZlcnMuIFNvbWUgYXJ0aXN0IHByb2ZpbGVzIGFsc28gaW5jbHVkZSBwaG90b2dyYXBocyB3aXRoIGluZGl2aWR1YWwgY3JlZGl0cywgc291cmNlIGxpbmtzLCBhbmQgbGljZW5jZSBpbmZvcm1hdGlvbi48L3A+XG48cD5TZWUgdGhlIDxhIGhyZWY9XCJodHRwczovL3R1bmV0b2ZvbmQuY29tL21lZGlhLWNyZWRpdHMvXCI+TWVkaWEgY3JlZGl0cyBwYWdlPC9hPiBhbmQgdGhlIGNyZWRpdHMgYmVzaWRlIGluZGl2aWR1YWwgcGhvdG9ncmFwaHMgZm9yIGF0dHJpYnV0aW9uIGFuZCBsaWNlbmNlIGRldGFpbHMuIFRoZSB0ZXJtcyBvZiBlYWNoIHBob3RvZ3JhcGjigJlzIGxpY2VuY2UgYXBwbHkgdG8gdGhhdCBpbWFnZTsgaXRzIGluY2x1c2lvbiBoZXJlIGRvZXMgbm90IGdyYW50IGFkZGl0aW9uYWwgcGVybWlzc2lvbiB0byByZXVzZSBpdC48L3A+XG48aDI+UmVwb3J0IGEgY29uY2Vybi48L2gyPlxuPHA+Rm9yIGEgY29weXJpZ2h0LCBwZXJtaXNzaW9uLCBvciBhdHRyaWJ1dGlvbiBjb25jZXJuLCBlbWFpbCA8YSBocmVmPVwibWFpbHRvOmNvbnRhY3RAdHVuZXRvZm9uZC5jb20/c3ViamVjdD1Db3B5cmlnaHQlMjBvciUyMGF0dHJpYnV0aW9uJTIwZW5xdWlyeVwiPmNvbnRhY3RAdHVuZXRvZm9uZC5jb208L2E+LjwvcD5cbjxwPlBsZWFzZSBpbmNsdWRlIHRoZSBwYWdlIFVSTCwgaWRlbnRpZnkgdGhlIG1hdGVyaWFsIGFuZCB0aGUgY29uY2VybiwgZXhwbGFpbiB5b3VyIHJlbGF0aW9uc2hpcCB0byB0aGUgcmlnaHRzIGhvbGRlciB3aGVyZSByZWxldmFudCwgYW5kIHByb3ZpZGUgYSB3YXkgdG8gY29udGFjdCB5b3Ugc28gd2UgY2FuIHJldmlldyB0aGUgbWF0dGVyLiBQbGVhc2UgZG8gbm90IHNlbmQgc2Vuc2l0aXZlIGlkZW50aXR5IGRvY3VtZW50cyBieSBlbWFpbC48L3A+XG4ifV0=', true), true);
if (wp_parse_url(home_url(), PHP_URL_HOST) !== 'tunetofond.com' || count($edits) !== 2) { WP_CLI::error('Unexpected site or package. Nothing changed.'); }
$before = []; $pending = [];
foreach ($edits as $edit) {
    $p = get_post($edit['id']);
    if (!$p || $p->post_type !== 'page' || $p->post_name !== $edit['slug'] || $p->post_status !== 'publish') { WP_CLI::error('Page identity or visibility differs. Nothing changed.'); }
    $before[$p->ID] = ['ID'=>$p->ID, 'post_title'=>$p->post_title, 'post_name'=>$p->post_name, 'post_status'=>$p->post_status, 'post_content'=>$p->post_content];
    if ($p->post_content === $edit['content']) { continue; }
    if (hash('sha256', apply_filters('the_content', $p->post_content)) !== $edit['expected']) { WP_CLI::error('Different edits on ' . $edit['slug'] . '. Nothing changed; review them first.'); }
    $pending[] = $edit;
}
$parent = realpath($args[0] ?? ''); $root = realpath(ABSPATH);
if (!$parent || !$root || !is_writable($parent) || $parent === $root || str_starts_with($parent . '/', $root . '/')) { WP_CLI::error('Pass a writable private backup directory outside public_html.'); }
if (in_array('check', $args ?? [], true)) { WP_CLI::success('About and Copyright checks passed. No changes made.'); return; }
if (!$pending) { WP_CLI::success('Both approved updates are already installed. Nothing changed.'); return; }
$backup = $parent . '/about-copyright-backup-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(3));
if (!mkdir($backup, 0700)) { WP_CLI::error('Could not create private backup. Nothing changed.'); }
$file = $backup . '/before.json';
if (file_put_contents($file, wp_json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false || !chmod($file, 0600)) { WP_CLI::error('Could not secure backup. Nothing changed.'); }
$attempted = [];
try {
    foreach ($pending as $edit) {
        $id = $edit['id']; $attempted[] = $id;
        $result = wp_update_post(wp_slash(['ID'=>$id, 'post_content'=>$edit['content']]), true);
        clean_post_cache($id); $after = get_post($id);
        if (is_wp_error($result) || !$after || $after->post_content !== $edit['content'] || $after->post_title !== $before[$id]['post_title'] || $after->post_name !== $before[$id]['post_name'] || $after->post_status !== $before[$id]['post_status']) { throw new RuntimeException('Verification failed for ' . $edit['slug']); }
    }
} catch (Throwable $error) {
    $restore_failed = false;
    foreach ($attempted as $id) {
        $restored = wp_update_post(wp_slash(['ID'=>$id, 'post_content'=>$before[$id]['post_content']]), true);
        clean_post_cache($id);
        if (is_wp_error($restored) || get_post($id)->post_content !== $before[$id]['post_content']) { $restore_failed = true; }
    }
    WP_CLI::error($error->getMessage() . '. Backup: ' . $backup . ($restore_failed ? ' RESTORE NEEDS REVIEW.' : ' Previous page bodies restored.'));
}
WP_CLI::success('About and Copyright text updated. Private backup: ' . $backup);
WP_CLI::line('Only page bodies 22 and 25 changed. Titles, URLs, visibility, catalog, photo credits and indexing settings retained.');
