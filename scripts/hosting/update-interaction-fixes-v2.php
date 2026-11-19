<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$release = json_decode(base64_decode('eyJmaWxlcyI6W3sicGF0aCI6IndwLWNvbnRlbnQvcGx1Z2lucy9hZnRlcmdsb3ctY29yZS9hZnRlcmdsb3ctY29yZS5waHAiLCJiZWZvcmUiOiIwZWI3M2QwYWYyMTdlZDQxZTY1YjYxODBmOTAxNmE5YzM0Nzk0Y2E1ZGZlMjU1YmU4ZWE1NjI5MTM2NTFhMzUzIiwiYWZ0ZXIiOiI1MDRiNWIxODMxY2YxNTk0MmM5NTZjZjhlZjU5MDNhNDQ0ZmVlMzdmMmVlN2M3ZWZhODQ3ODEzZjI1NjUzYThhIiwib2xkIjoiaWYgKGlzc2V0KCRfR0VUWyRrZXldKSAmJiBpc19zdHJpbmcoJF9HRVRbJGtleV0pICYmICRfR0VUWyRrZXldICE9PSAnJykgeyAkdGF4W10gPSBbJ3RheG9ub215JyA9PiAkbmFtZSwgJ2ZpZWxkJyA9PiAnc2x1ZycsICd0ZXJtcycgPT4gaW5fYXJyYXkoJG5hbWUsIFsnZ2VucmUnLCdtb29kJ10sIHRydWUpID8gYWdfdGF4b25vbXlfZmlsdGVyX3NsdWdzKCRuYW1lLCB3cF91bnNsYXNoKCRfR0VUWyRrZXldKSkgOiBzYW5pdGl6ZV90aXRsZSh3cF91bnNsYXNoKCRfR0VUWyRrZXldKSldOyB9IiwibmV3IjoiaWYgKGlzc2V0KCRfR0VUWyRrZXldKSAmJiBpc19zdHJpbmcoJF9HRVRbJGtleV0pICYmICRfR0VUWyRrZXldICE9PSAnJykge1xuICAgICAgICAgICAgICAgIGlmICgkbmFtZSA9PT0gJ2NvdW50cnknICYmICRxLT5pc19wb3N0X3R5cGVfYXJjaGl2ZSgnc29uZycpKSB7XG4gICAgICAgICAgICAgICAgICAgIC8vIEEgc29uZydzIGNvdW50cnkgZm9sbG93cyBpdHMgcHJpbWFyeSBhcnRpc3Q7IGRvIG5vdCByZXdyaXRlIHN0b3JlZCB0ZXJtcy5cbiAgICAgICAgICAgICAgICAgICAgJGFydGlzdHMgPSBnZXRfcG9zdHMoWydwb3N0X3R5cGUnID0+ICdhcnRpc3QnLCAncG9zdF9zdGF0dXMnID0+ICdwdWJsaXNoJywgJ251bWJlcnBvc3RzJyA9PiAtMSwgJ2ZpZWxkcycgPT4gJ2lkcycsICd0YXhfcXVlcnknID0+IFtbJ3RheG9ub215JyA9PiAnY291bnRyeScsICdmaWVsZCcgPT4gJ3NsdWcnLCAndGVybXMnID0+IHNhbml0aXplX3RpdGxlKHdwX3Vuc2xhc2goJF9HRVRbJGtleV0pKV1dXSk7XG4gICAgICAgICAgICAgICAgICAgICRhcnRpc3RzID0gYXJyYXlfbWFwKCdpbnR2YWwnLCAkYXJ0aXN0cyk7XG4gICAgICAgICAgICAgICAgICAgIGlmICghJGFydGlzdHMpIHsgJHEtPnNldCgncG9zdF9faW4nLCBbMF0pOyB9XG4gICAgICAgICAgICAgICAgICAgICRjb3VudHJ5ID0gWydrZXknID0+ICdfYWdfYXJ0aXN0X2lkJywgJ3ZhbHVlJyA9PiAkYXJ0aXN0cyA/OiBbMF0sICdjb21wYXJlJyA9PiAnSU4nXTtcbiAgICAgICAgICAgICAgICAgICAgJGV4aXN0aW5nID0gJHEtPmdldCgnbWV0YV9xdWVyeScpO1xuICAgICAgICAgICAgICAgICAgICAkcS0+c2V0KCdtZXRhX3F1ZXJ5JywgJGV4aXN0aW5nID8gWydyZWxhdGlvbicgPT4gJ0FORCcsICRleGlzdGluZywgJGNvdW50cnldIDogWyRjb3VudHJ5XSk7XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgJHRheFtdID0gWyd0YXhvbm9teScgPT4gJG5hbWUsICdmaWVsZCcgPT4gJ3NsdWcnLCAndGVybXMnID0+IGluX2FycmF5KCRuYW1lLCBbJ2dlbnJlJywnbW9vZCddLCB0cnVlKSA/IGFnX3RheG9ub215X2ZpbHRlcl9zbHVncygkbmFtZSwgd3BfdW5zbGFzaCgkX0dFVFska2V5XSkpIDogc2FuaXRpemVfdGl0bGUod3BfdW5zbGFzaCgkX0dFVFska2V5XSkpXTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9IiwiYmVmb3JlX3dpdGhfbmV3bGluZSI6ImJkYWU5YmJkZDU3MTFmNTJhMzNjNmFmNTc3MmNlMzk1ZjEwYzAwNWQ3YjE0Nzg2MzNmOWU2YjI1ZmE5Y2JkOTYiLCJhZnRlcl93aXRoX25ld2xpbmUiOiJlZDdiMjEyODg1MjRhYWJkZmY2NzlhYTc3NjBlZjM5ODIyOGI4NjY5Y2JmMWQxYmI2NzdhN2RlYmU5YmI4Nzk5In0seyJwYXRoIjoid3AtY29udGVudC90aGVtZXMvYWZ0ZXJnbG93L2FyY2hpdmUucGhwIiwiYmVmb3JlIjoiN2EwYTE1MjdiMDQ3MDNiZDdkMDY0YmQ1YzFkMDkxMzg3YjY2Yjg3ZmY3NTk5NTdmMGVjZDI5YWUxYjc0MzI2ZCIsImFmdGVyIjoiMmQyOGMxZTY4YWU5Y2FiM2E5MTE3YWFmODQwODQ5MDlhOThhOTJjNTY5MWY4OTU0MTI1NDg2NDQxMDgwZWEwNCIsIm9sZCI6ImVjaG8gZXNjX2h0bWwoJHdwX3F1ZXJ5LT5mb3VuZF9wb3N0cyk7ID8+IGRpc2NvdmVyaWVzIiwibmV3IjoiZWNobyBlc2NfaHRtbChzcHJpbnRmKF9uKCclcyBkaXNjb3ZlcnknLCAnJXMgZGlzY292ZXJpZXMnLCAoaW50KSR3cF9xdWVyeS0+Zm91bmRfcG9zdHMsICdhZnRlcmdsb3cnKSwgbnVtYmVyX2Zvcm1hdF9pMThuKCR3cF9xdWVyeS0+Zm91bmRfcG9zdHMpKSk7ID8+IiwiYmVmb3JlX3dpdGhfbmV3bGluZSI6IjdlMTQ4YjEzZjllM2U0Y2U3OWJiODk4M2YwZjYzOWFlMjU3YmNjNzlkMzVhYzkzNzQ3NzM1Y2MyZTJhZmFmYjAiLCJhZnRlcl93aXRoX25ld2xpbmUiOiIwNjAzMGY0YzVlZWZjYWNlODExYzFiNzUxODdlOWQyNmIyZTFkZTg3YzdkYzY5NGMyOTEyZmZhNGJjYmVmMjNiIn1dLCJwcml2YWN5Ijp7ImlkIjoyMywic2x1ZyI6InByaXZhY3kiLCJiZWZvcmUiOiJmODI3MmY5MDFiYTI1MjhkYWZkOTg5ZGUzNDM4OWU5YTJlYTZiZDQ1YWM1Y2UxMWFkNWVmMzllMGYyYjg0ZmJmIiwiYWZ0ZXIiOiJiNzI1YTEzNjA3NGIxMjBmZTI3YmRhNmM0ZDk5YjFhYTU4NmJlOTY1OTVjNTg3ZTBjMWNjYzU4NzRiZDI2MWQ0Iiwib2xkIjoiQSBwbGF5ZXIgaXMgbG9hZGVkIHdoZW4geW91IGNob29zZSDigJxMb2FkIFNwb3RpZnkgcGxheWVyLOKAnSB3aGljaCBjb25uZWN0cyB5b3VyIGJyb3dzZXIgdG8gU3BvdGlmeS4iLCJuZXciOiJDaG9vc2luZyB0byBsb2FkIGEgcGxheWVyIGNvbm5lY3RzIHlvdXIgYnJvd3NlciB0byBTcG90aWZ5LiJ9fQ==', true), true);
if (wp_parse_url(home_url(), PHP_URL_HOST) !== 'tunetofond.com' || get_stylesheet() !== 'afterglow' || count($release['files'] ?? []) !== 2) { WP_CLI::error('Unexpected site/theme/package. Nothing changed.'); }
$root = realpath(ABSPATH); $parent = realpath($args[0] ?? '');
if (!$parent || !$root || !is_writable($parent) || $parent === $root || str_starts_with($parent . '/', $root . '/')) { WP_CLI::error('Pass a writable private backup directory outside public_html.'); }
$files = []; $pending = [];
foreach ($release['files'] as $edit) {
    if (!in_array($edit['path'], ['wp-content/plugins/afterglow-core/afterglow-core.php','wp-content/themes/afterglow/archive.php'], true)) { WP_CLI::error('Unexpected file path.'); }
    $path = $root . '/' . $edit['path'];
    if (is_link($path) || !is_file($path) || !is_readable($path) || !is_writable(dirname($path))) { WP_CLI::error('File cannot be safely updated: ' . $edit['path']); }
    $source = file_get_contents($path); $hash = hash('sha256', $source);
    if (!in_array($hash, [$edit['before'],$edit['before_with_newline'],$edit['after'],$edit['after_with_newline']], true)) { WP_CLI::error('Different edits in ' . $edit['path'] . '. Nothing changed; preserve those edits.'); }
    $files[$edit['path']] = ['content'=>$source, 'mode'=>fileperms($path) & 0777];
    if (in_array($hash, [$edit['after'],$edit['after_with_newline']], true)) { continue; }
    if (substr_count($source, $edit['old']) !== 1) { WP_CLI::error('Replacement is ambiguous. Nothing changed.'); }
    $next = str_replace($edit['old'], $edit['new'], $source);
    if (!in_array(hash('sha256', $next), [$edit['after'],$edit['after_with_newline']], true)) { WP_CLI::error('Release integrity mismatch. Nothing changed.'); }
    $pending[$edit['path']] = $next;
}
$p = get_post(23);
if (!$p || $p->post_type !== 'page' || $p->post_name !== 'privacy' || $p->post_status !== 'publish') { WP_CLI::error('Privacy page identity differs. Nothing changed.'); }
$rendered = hash('sha256', apply_filters('the_content', $p->post_content));
$privacy = $release['privacy'];
if (!in_array($rendered, [$privacy['before'],$privacy['after']], true)) { WP_CLI::error('Privacy has different edits. Nothing changed.'); }
$body = $p->post_content;
if ($rendered === $privacy['before']) {
    if (substr_count($body, $privacy['old']) !== 1) { WP_CLI::error('Unexpected Privacy source. Nothing changed.'); }
    $body = str_replace($privacy['old'], $privacy['new'], $body);
    if (hash('sha256', apply_filters('the_content', $body)) !== $privacy['after']) { WP_CLI::error('Privacy replacement integrity mismatch.'); }
}
// Fingerprint visibility/indexing and retained catalog data for post-update verification.
function tf_fixes_preservation() {
    global $wpdb;
    $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('artist','song') ORDER BY ID");
    $posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type IN ('artist','song') ORDER BY ID", ARRAY_A);
    $meta = $ids ? $wpdb->get_results('SELECT * FROM ' . $wpdb->postmeta . ' WHERE post_id IN (' . implode(',', array_map('intval',$ids)) . ') ORDER BY meta_id', ARRAY_A) : [];
    $terms = $ids ? $wpdb->get_results('SELECT * FROM ' . $wpdb->term_relationships . ' WHERE object_id IN (' . implode(',',array_map('intval',$ids)) . ') ORDER BY object_id,term_taxonomy_id', ARRAY_A) : [];
    return hash('sha256', serialize([$posts,$meta,$terms,get_option('ag_public_catalog_scope'),get_option('blog_public')]));
}
if (in_array('check', $args ?? [], true)) { WP_CLI::success('All three fix guards passed. No live changes made.'); return; }
if (!$pending && $body === $p->post_content) { WP_CLI::success('All three fixes are already installed. Nothing changed.'); return; }
$preserved = tf_fixes_preservation();
$backup = $parent . '/interaction-fixes-backup-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(3));
if (!mkdir($backup,0700)) { WP_CLI::error('Could not create private backup. Nothing changed.'); }
$before = ['files'=>$files,'privacy'=>['ID'=>23,'post_title'=>$p->post_title,'post_name'=>$p->post_name,'post_status'=>$p->post_status,'post_content'=>$p->post_content],'catalog_fingerprint'=>$preserved];
$backup_file = $backup . '/before.json';
if (file_put_contents($backup_file,wp_json_encode($before,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false || !chmod($backup_file,0600)) { WP_CLI::error('Could not secure backup. Nothing changed.'); }
function tf_fixes_write($path,$contents,$mode) {
    $tmp = tempnam(dirname($path),'.tf-fix-');
    if (!$tmp) { throw new RuntimeException('Could not create temporary file.'); }
    if (file_put_contents($tmp,$contents) !== strlen($contents) || !chmod($tmp,$mode) || !rename($tmp,$path)) { @unlink($tmp); throw new RuntimeException('Could not install file.'); }
    clearstatcache(true,$path);
    if (function_exists('opcache_invalidate')) { opcache_invalidate($path,true); }
    if (file_get_contents($path) !== $contents) { throw new RuntimeException('File verification failed.'); }
}
$attempted = []; $page_attempted = false;
try {
    foreach ($pending as $relative=>$contents) { $attempted[]=$relative; tf_fixes_write($root . '/' . $relative,$contents,$files[$relative]['mode']); }
    if ($body !== $p->post_content) {
        $page_attempted=true;
        $result=wp_update_post(wp_slash(['ID'=>23,'post_content'=>$body]),true);
        clean_post_cache(23);
        if (is_wp_error($result) || get_post(23)->post_content !== $body) { throw new RuntimeException('Privacy update failed.'); }
    }
    $after = get_post(23);
    if ($after->post_title !== $before['privacy']['post_title'] || $after->post_name !== 'privacy' || $after->post_status !== 'publish' || tf_fixes_preservation() !== $preserved) { throw new RuntimeException('Preservation verification failed.'); }
} catch (Throwable $error) {
    $failed=false;
    foreach ($attempted as $relative) { try { tf_fixes_write($root . '/' . $relative,$files[$relative]['content'],$files[$relative]['mode']); } catch (Throwable $restore_error) { $failed=true; } }
    if ($page_attempted) { $restored=wp_update_post(wp_slash(['ID'=>23,'post_content'=>$before['privacy']['post_content']]),true); clean_post_cache(23); if (is_wp_error($restored) || get_post(23)->post_content !== $before['privacy']['post_content']) { $failed=true; } }
    WP_CLI::error($error->getMessage() . '. Backup: ' . $backup . ($failed ? ' RESTORE NEEDS REVIEW.' : ' Previous files and Privacy body restored.'));
}
WP_CLI::success('Country filter, result count and Privacy wording updated. Private backup: ' . $backup);
WP_CLI::line('Catalog posts, metadata, taxonomy relationships, public scope and indexing fingerprint unchanged.');
