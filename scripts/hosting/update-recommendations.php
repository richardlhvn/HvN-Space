<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$release=json_decode(base64_decode('W3sicGF0aCI6IndwLWNvbnRlbnQvcGx1Z2lucy9hZnRlcmdsb3ctY29yZS9kaXNjb3ZlcnkucGhwIiwiYmVmb3JlIjpbIjllYTEyMGJkZDFmZDU4YTQyZjNjODY3MWQ4MzY1Y2FjYzZjZGQwOWZjNjlmODk5M2U1Mjc5YTIzNDBhZTM0ZGEiLCI3YWI1MTVmMDQxZDA1NWM5MWM1NGU2ODRlYmMxYWJkZGJmNjI4N2UzNGIxYWZmMGNjYWJhMDAwN2RmMmFmNjcyIl0sImFmdGVyIjpbIjFiMjQyY2Y5YWQxMjkxN2I1ODk2NzYxOTUzMGM1NzRlNzcxOWY5MGMwMjRmOTllMmE1N2MwMTlhMGE5MWQ0YWEiLCIyNzJiYzhlNGFjYjZhZTM4M2ZlYjAxODdiNGZiZWY4N2FlZDk1MzA0NGE4ZGFmNzQ5NGU2NDU5YmZhZGQ0OTA4Il0sInJlcGxhY2VtZW50cyI6W1siZnVuY3Rpb24gYWdfZGlzY292ZXJ5X3JlY29tbWVuZCgkY3VycmVudCwgJGNhdGFsb2csICRwcmVmZXJlbmNlcywgJHRyYWlsID0gW10sICRsaW1pdCA9IDMpIiwiZnVuY3Rpb24gYWdfZGlzY292ZXJ5X3JlY29tbWVuZCgkY3VycmVudCwgJGNhdGFsb2csICRwcmVmZXJlbmNlcywgJHRyYWlsID0gW10sICRsaW1pdCA9IDMsICRyZXNlcnZlX2N1cnJlbnRfYXJ0aXN0ID0gZmFsc2UpIl0sWyIkc2NvcmUgPSAoJHRoZW1lcyA/IDQwIDogMCkgKyAoJG1vb2RzID8gMjUgOiAwKSArICgkaXNfY3VyYXRlZCA/IDM1IDogMCkgKyAoJHJlbGF0ZWRfYXJ0aXN0ID8gMzAgOiAwKSArICgkZGlmZmVyZW50ID8gMTIgOiAwKTsiLCIvLyBOZXZlciBmaWxsIGEgZ2FwIHdpdGggYW4gdW5yZWxhdGVkIHNvbmcuIFJlbGV2YW5jZSBjb21lcyBiZWZvcmUgdmFyaWV0eS5cbiAgICAgICAgaWYgKCEkdGhlbWVzICYmICEkbW9vZHMgJiYgISRpc19jdXJhdGVkICYmICEkcmVsYXRlZF9hcnRpc3QpIHsgY29udGludWU7IH1cbiAgICAgICAgJHRpZXIgPSAkdGhlbWVzID8gNDAwMCA6ICgkbW9vZHMgPyAzMDAwIDogKCRpc19jdXJhdGVkID8gMjAwMCA6IDEwMDApKTtcbiAgICAgICAgJHNjb3JlID0gJHRpZXIgKyBjb3VudCgkdGhlbWVzKSAqIDEwMCArIGNvdW50KCRtb29kcykgKiAyMCArICgkaXNfY3VyYXRlZCA/IDM1IDogMCkgKyAoJHJlbGF0ZWRfYXJ0aXN0ID8gMzAgOiAwKSArICgkZGlmZmVyZW50ID8gMTIgOiAwKTsiXSxbIiAgICAvLyBQcmVmZXIgYSBkaWZmZXJlbnQgYXJ0aXN0IHdoZW4gdGhlcmUgaXMgYSBtYXRjaGluZyBvcHRpb247IGRpdmVyc2lmeSB0aGUgcmVtYWluaW5nIGNhcmRzIHRvby5cbiAgICAkZGlmZmVyZW50ID0gYXJyYXlfdmFsdWVzKGFycmF5X2ZpbHRlcigkcm93cywgZnVuY3Rpb24gKCRyKSB1c2UgKCRjdXJyZW50KSB7IHJldHVybiAkclsnYXJ0aXN0J10gIT09ICRjdXJyZW50WydhcnRpc3QnXTsgfSkpO1xuICAgIGlmICgkZGlmZmVyZW50ICYmICRkaWZmZXJlbnRbMF1bJ3Njb3JlJ10gPj0gMjUpIHsgJGZpcnN0ID0gJGRpZmZlcmVudFswXTsgJHJvd3MgPSBhcnJheV9tZXJnZShbJGZpcnN0XSwgYXJyYXlfZmlsdGVyKCRyb3dzLCBmdW5jdGlvbiAoJHIpIHVzZSAoJGZpcnN0KSB7IHJldHVybiAkclsnaWQnXSAhPT0gJGZpcnN0WydpZCddOyB9KSk7IH1cbiAgICAkc2VsZWN0ZWQgPSBbXTsgJHVzZWRfYXJ0aXN0cyA9IFtdO1xuICAgIGZvcmVhY2ggKCRyb3dzIGFzICRyb3cpIHtcbiAgICAgICAgaWYgKGlzc2V0KCR1c2VkX2FydGlzdHNbJHJvd1snYXJ0aXN0J11dKSkgeyBjb250aW51ZTsgfVxuICAgICAgICAkc2VsZWN0ZWRbJHJvd1snaWQnXV0gPSAkcm93OyAkdXNlZF9hcnRpc3RzWyRyb3dbJ2FydGlzdCddXSA9IHRydWU7XG4gICAgICAgIGlmIChjb3VudCgkc2VsZWN0ZWQpID49ICRsaW1pdCkgeyByZXR1cm4gYXJyYXlfdmFsdWVzKCRzZWxlY3RlZCk7IH1cbiAgICB9XG4gICAgZm9yZWFjaCAoJHJvd3MgYXMgJHJvdykgeyAkc2VsZWN0ZWRbJHJvd1snaWQnXV0gPSAkcm93OyBpZiAoY291bnQoJHNlbGVjdGVkKSA+PSAkbGltaXQpIHsgYnJlYWs7IH0gfVxuICAgIHJldHVybiBhcnJheV92YWx1ZXMoJHNlbGVjdGVkKTsiLCIgICAgJHNlbGVjdGVkID0gW107ICR1c2VkX2FydGlzdHMgPSBbXTtcbiAgICAvLyBSZXNlcnZlIG9uZSByZWxldmFudCBhbHRlcm5hdGl2ZSBmcm9tIHRoZSBjdXJyZW50IGFydGlzdCB3aGVuIGF2YWlsYWJsZS5cbiAgICBpZiAoJHJlc2VydmVfY3VycmVudF9hcnRpc3QpIHtcbiAgICAgICAgZm9yZWFjaCAoJHJvd3MgYXMgJHJvdykge1xuICAgICAgICAgICAgaWYgKCRyb3dbJ2FydGlzdCddICE9PSAkY3VycmVudFsnYXJ0aXN0J10pIHsgY29udGludWU7IH1cbiAgICAgICAgICAgICRzZWxlY3RlZFskcm93WydpZCddXSA9ICRyb3c7ICR1c2VkX2FydGlzdHNbJHJvd1snYXJ0aXN0J11dID0gdHJ1ZTsgYnJlYWs7XG4gICAgICAgIH1cbiAgICB9XG4gICAgZm9yZWFjaCAoJHJvd3MgYXMgJHJvdykge1xuICAgICAgICBpZiAoY291bnQoJHNlbGVjdGVkKSA+PSAkbGltaXQpIHsgYnJlYWs7IH1cbiAgICAgICAgaWYgKGlzc2V0KCR1c2VkX2FydGlzdHNbJHJvd1snYXJ0aXN0J11dKSkgeyBjb250aW51ZTsgfVxuICAgICAgICAkc2VsZWN0ZWRbJHJvd1snaWQnXV0gPSAkcm93OyAkdXNlZF9hcnRpc3RzWyRyb3dbJ2FydGlzdCddXSA9IHRydWU7XG4gICAgfVxuICAgICRzZWxlY3RlZCA9IGFycmF5X3ZhbHVlcygkc2VsZWN0ZWQpO1xuICAgIGlmICgkcmVzZXJ2ZV9jdXJyZW50X2FydGlzdCkge1xuICAgICAgICB1c29ydCgkc2VsZWN0ZWQsIGZ1bmN0aW9uICgkYSwgJGIpIHVzZSAoJGN1cnJlbnQpIHsgcmV0dXJuICgoJGFbJ2FydGlzdCddID09PSAkY3VycmVudFsnYXJ0aXN0J10pIDw9PiAoJGJbJ2FydGlzdCddID09PSAkY3VycmVudFsnYXJ0aXN0J10pKSA/OiAoJGJbJ3Njb3JlJ10gPD0+ICRhWydzY29yZSddKSA/OiAoJGFbJ2lkJ10gPD0+ICRiWydpZCddKTsgfSk7XG4gICAgfVxuICAgIHJldHVybiAkc2VsZWN0ZWQ7Il0sWyJyZXR1cm4gWydjdXJyZW50JyA9PiAkY3VycmVudCwgJ3ByZWZlcmVuY2VzJyA9PiAkcHJlZmVyZW5jZXMsICdjaG9pY2VzJyA9PiAkY2hvaWNlcywgJ3RyYWlsJyA9PiAkdHJhaWwsICdpbnZhbGlkJyA9PiAkaW52YWxpZCwgJ3Jlc3VsdHMnID0+IGFnX2Rpc2NvdmVyeV9yZWNvbW1lbmQoJGN1cnJlbnQsICRjYXRhbG9nLCAkcHJlZmVyZW5jZXMsICR0cmFpbCldOyIsIi8vIFRoZSBmZWF0dXJlZCByb3V0ZSBhbHdheXMgc2hhcmVzIGEgdGhlbWUgd2l0aCB0aGlzIHNvbmcsIGluZGVwZW5kZW50bHkgb2YgYWx0ZXJuYXRpdmUgZmlsdGVycy5cbiAgICAkZmVhdHVyZWQgPSBhZ19kaXNjb3ZlcnlfcmVjb21tZW5kKCRjdXJyZW50LCAkY2F0YWxvZywgWydmZWVsaW5nJyA9PiAnYW55JywgJ3RoZW1lJyA9PiAnc2FtZSddLCAkdHJhaWwsIDEpWzBdID8/IG51bGw7XG4gICAgJGV4Y2x1ZGUgPSAkZmVhdHVyZWQgPyBhcnJheV9tZXJnZSgkdHJhaWwsIFskZmVhdHVyZWRbJ2lkJ11dKSA6ICR0cmFpbDtcbiAgICAkcmVzdWx0cyA9IGFnX2Rpc2NvdmVyeV9yZWNvbW1lbmQoJGN1cnJlbnQsICRjYXRhbG9nLCAkcHJlZmVyZW5jZXMsICRleGNsdWRlLCAzLCB0cnVlKTtcbiAgICByZXR1cm4gWydjdXJyZW50JyA9PiAkY3VycmVudCwgJ3ByZWZlcmVuY2VzJyA9PiAkcHJlZmVyZW5jZXMsICdjaG9pY2VzJyA9PiAkY2hvaWNlcywgJ3RyYWlsJyA9PiAkdHJhaWwsICdpbnZhbGlkJyA9PiAkaW52YWxpZCwgJ2ZlYXR1cmVkJyA9PiAkZmVhdHVyZWQsICdyZXN1bHRzJyA9PiAkcmVzdWx0c107Il1dfSx7InBhdGgiOiJ3cC1jb250ZW50L3RoZW1lcy9hZnRlcmdsb3cvZGlzY292ZXJ5LXVpLnBocCIsImJlZm9yZSI6WyJiMGM2MGEyZDMxZDNkZThiZWRjZmVmYzNmYzk3YTUwZTBmNDAyZjEwM2VmMGEzY2FhODBiZTk1YmViYzAzNzdhIiwiZDA0Njk3NTkyYTQxMzc2YTJmYTdjNDQzMzU0ZDIxNGUzMDY5NjdiOWI4MTI5YzA2OTQxNzdjMzFiNzU2YjNiMiJdLCJhZnRlciI6WyI4Mzk4Mzc3ZjRiZDA3YjkzZTM5ODBhYmZkMjFmZTdkOGY3MzA0ZWIxOTcyNTdjZTk1NTkxYjA2YjgwYzU5YTFhIiwiYzE2ODk5ODM5OTNhNjVkOTE3Yzg5MmJjMGI1MWNmY2U5MjU3ZGE4NTE1YTM4NzI5MDUyMDJlMGExNzMzOWY2ZCJdLCJyZXBsYWNlbWVudHMiOltbIiRzb25nID0gJGNvbnRleHRbJ3Jlc3VsdHMnXVswXSA/PyBudWxsOyIsIiRzb25nID0gJGNvbnRleHRbJ2ZlYXR1cmVkJ10gPz8gbnVsbDsiXSxbIk5vIG5leHQgc29uZyBmb3IgdGhpcyBjb21iaW5hdGlvbiB5ZXQuIiwiTm8gbmV4dCBzb25nIHdpdGggYSBzaGFyZWQgdGhlbWUgaXMgYXZhaWxhYmxlIHlldC4iXSxbIlN0YXkgd2l0aCBhIGZlZWxpbmcsIGZvbGxvdyBhIHRoZW1lLCBvciBtZWV0IGFub3RoZXIgYXJ0aXN0LiIsIkZvbGxvdyBhIHNoYXJlZCB0aGVtZSwgdGhlbiBleHBsb3JlIGEgZmV3IG90aGVyIGNvbm5lY3Rpb25zLiJdLFsiJGNvdW50IC4gJyBzdWdnZXN0aW9uJyIsIiRjb3VudCAuICcgYWx0ZXJuYXRpdmUnIl1dfV0=',true),true);
if(wp_parse_url(home_url(),PHP_URL_HOST)!=='tunetofond.com'||get_stylesheet()!=='afterglow'||count($release)!==2){WP_CLI::error('Unexpected site/theme/package. Nothing changed.');}
$root=realpath(ABSPATH);$parent=realpath($args[0]??'');
if(!$root||!$parent||!is_writable($parent)||$parent===$root||str_starts_with($parent.'/',$root.'/')){WP_CLI::error('Pass a private writable backup directory outside public_html.');}
$files=[];$pending=[];$engine=null;
foreach($release as $edit){
 if(!in_array($edit['path'],['wp-content/plugins/afterglow-core/discovery.php','wp-content/themes/afterglow/discovery-ui.php'],true)){WP_CLI::error('Unexpected path.');}
 $path=$root.'/'.$edit['path'];
 if(is_link($path)||!is_file($path)||!is_readable($path)||!is_writable(dirname($path))){WP_CLI::error('Cannot safely update '.$edit['path']);}
 $source=file_get_contents($path);$hash=hash('sha256',$source);
 if(!in_array($hash,array_merge($edit['before'],$edit['after']),true)){WP_CLI::error('Different edits in '.$edit['path'].'. Nothing changed; preserve those edits.');}
 $files[$edit['path']]=['content'=>$source,'mode'=>fileperms($path)&0777];$next=$source;
 if(!in_array($hash,$edit['after'],true)){
  foreach($edit['replacements'] as [$old,$new]){if(substr_count($next,$old)!==1){WP_CLI::error('Ambiguous replacement. Nothing changed.');}$next=str_replace($old,$new,$next);}
  if(!in_array(hash('sha256',$next),$edit['after'],true)){WP_CLI::error('Package integrity mismatch. Nothing changed.');}
  $pending[$edit['path']]=$next;
 }
 if(str_ends_with($edit['path'],'/discovery.php')){$engine=$next;}
}
// Run the candidate engine under private function names without changing the live plugin.
preg_match_all('/function (ag_discovery_[a-z_]+)\(/',$engine,$names);
foreach(array_unique($names[1]) as $name){$engine=preg_replace('/\b'.preg_quote($name,'/').'\b/','tf_preview_'.$name,$engine);}
eval(preg_replace('/^<\?php\s*/','',$engine));
add_filter('ag_public_catalog_context','__return_true',PHP_INT_MAX);
try{
 $catalog=tf_preview_ag_discovery_catalog();
 if(count($catalog)!==128||count(array_unique(array_column($catalog,'artist')))!==10){throw new RuntimeException('Expected the existing ten artists / 128 public songs.');}
 $featured_count=0;
 foreach($catalog as $current){
  $feature=tf_preview_ag_discovery_recommend($current,$catalog,['feeling'=>'any','theme'=>'same'],[],1)[0]??null;
  if($feature){$featured_count++;if(!array_intersect_key($feature['themes'],$current['themes'])){throw new RuntimeException('Featured song lacks shared theme.');}}
  foreach([tf_preview_ag_discovery_defaults($current,$catalog),['feeling'=>'any','theme'=>'any']] as $prefs){
   $exclude=$feature?[$feature['id']]:[];
   $rows=tf_preview_ag_discovery_recommend($current,$catalog,$prefs,$exclude,3,true);
   $ids=array_column($rows,'id');$artists=array_column($rows,'artist');
   if(count($rows)>3||count(array_unique($ids))!==count($ids)||count(array_unique($artists))!==count($artists)||in_array($current['id'],$ids,true)||array_intersect($ids,$exclude)||count(array_filter($artists,fn($a)=>$a===$current['artist']))>1){throw new RuntimeException('Duplicate or identity selection rule failed.');}
   $curated=array_map('absint',explode(',',(string)get_post_meta($current['id'],'_ag_related',true)));
   $related=array_map('absint',explode(',',(string)get_post_meta($current['artist'],'_ag_related',true)));
   foreach($rows as $row){if(!array_intersect_key($row['themes'],$current['themes'])&&!array_intersect_key($row['moods'],$current['moods'])&&!in_array($row['id'],$curated,true)&&!in_array($row['artist'],$related,true)){throw new RuntimeException('Unrelated fallback detected.');}}
  }
 }
 WP_CLI::line('Candidate preflight passed for 128 public songs; '.$featured_count.' have a shared-theme next discovery.');
}catch(Throwable $error){WP_CLI::error($error->getMessage().' Nothing changed.');}
finally{remove_filter('ag_public_catalog_context','__return_true',PHP_INT_MAX);}
function tf_rec_preservation() {
    global $wpdb;
    $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('artist','song') ORDER BY ID");
    $posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type IN ('artist','song') ORDER BY ID", ARRAY_A);
    $meta = $ids ? $wpdb->get_results('SELECT * FROM ' . $wpdb->postmeta . ' WHERE post_id IN (' . implode(',', array_map('intval',$ids)) . ') ORDER BY meta_id', ARRAY_A) : [];
    $terms = $ids ? $wpdb->get_results('SELECT * FROM ' . $wpdb->term_relationships . ' WHERE object_id IN (' . implode(',',array_map('intval',$ids)) . ') ORDER BY object_id,term_taxonomy_id', ARRAY_A) : [];
    return hash('sha256', serialize([$posts,$meta,$terms,get_option('ag_public_catalog_scope'),get_option('blog_public')]));
}
if(in_array('check',$args??[],true)){WP_CLI::success('Recommendation checks passed. No live changes made.');return;}
if(!$pending){WP_CLI::success('Recommendation update is already installed. Nothing changed.');return;}
$preserved=tf_rec_preservation();$backup=$parent.'/recommendations-backup-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(3));
if(!mkdir($backup,0700)){WP_CLI::error('Could not create private backup. Nothing changed.');}
$before=['files'=>$files,'catalog_fingerprint'=>$preserved];$file=$backup.'/before.json';
if(file_put_contents($file,wp_json_encode($before,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))===false||!chmod($file,0600)){WP_CLI::error('Could not secure backup. Nothing changed.');}
function tf_rec_write($path,$contents,$mode) {
    $tmp = tempnam(dirname($path),'.tf-fix-');
    if (!$tmp) { throw new RuntimeException('Could not create temporary file.'); }
    if (file_put_contents($tmp,$contents) !== strlen($contents) || !chmod($tmp,$mode) || !rename($tmp,$path)) { @unlink($tmp); throw new RuntimeException('Could not install file.'); }
    clearstatcache(true,$path);
    if (function_exists('opcache_invalidate')) { opcache_invalidate($path,true); }
    if (file_get_contents($path) !== $contents) { throw new RuntimeException('File verification failed.'); }
}
$attempted=[];
try{
 foreach($pending as $relative=>$contents){$attempted[]=$relative;tf_rec_write($root.'/'.$relative,$contents,$files[$relative]['mode']);}
 if(tf_rec_preservation()!==$preserved){throw new RuntimeException('Catalog preservation verification failed.');}
}catch(Throwable $error){
 $failed=false;foreach($attempted as $relative){try{tf_rec_write($root.'/'.$relative,$files[$relative]['content'],$files[$relative]['mode']);}catch(Throwable $e){$failed=true;}}
 WP_CLI::error($error->getMessage().'. Backup: '.$backup.($failed?' RESTORE NEEDS REVIEW.':' Previous files restored.'));
}
WP_CLI::success('Recommendation logic and duplicate layout updated. Private backup: '.$backup);
WP_CLI::line('Catalog posts, metadata, taxonomy, visibility and indexing fingerprint unchanged.');
