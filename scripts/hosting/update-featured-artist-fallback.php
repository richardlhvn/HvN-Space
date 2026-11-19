<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$release=json_decode(base64_decode('W3sicGF0aCI6IndwLWNvbnRlbnQvcGx1Z2lucy9hZnRlcmdsb3ctY29yZS9kaXNjb3ZlcnkucGhwIiwiYmVmb3JlIjpbIjFiMjQyY2Y5YWQxMjkxN2I1ODk2NzYxOTUzMGM1NzRlNzcxOWY5MGMwMjRmOTllMmE1N2MwMTlhMGE5MWQ0YWEiLCIyNzJiYzhlNGFjYjZhZTM4M2ZlYjAxODdiNGZiZWY4N2FlZDk1MzA0NGE4ZGFmNzQ5NGU2NDU5YmZhZGQ0OTA4Il0sImFmdGVyIjpbIjk4NGJmNTlhODRkYWZmMzc1NjMxZDBhMTYzYzEzNGZjZjg4M2NhN2Q4MTc0NjQ4Njc5YTlmODI0NDBiMzQ2YTEiLCI5NzQ5Nzc4YzQxOWM5YjFjNDBhNmRmNzMyZWZiMzY0NDE0YmYzYmRhNDZjYWVkNjViNDcyMzNjZjc1Y2VkY2ZjIl0sInJlcGxhY2VtZW50cyI6W1siZnVuY3Rpb24gYWdfZGlzY292ZXJ5X2NvbnRleHQoJGlkLCAkaW5wdXQgPSBbXSkgeyIsImZ1bmN0aW9uIGFnX2Rpc2NvdmVyeV9mZWF0dXJlZCgkY3VycmVudCwgJGNhdGFsb2csICR0cmFpbCA9IFtdKSB7XG4gICAgJHNoYXJlZCA9IGFnX2Rpc2NvdmVyeV9yZWNvbW1lbmQoJGN1cnJlbnQsICRjYXRhbG9nLCBbJ2ZlZWxpbmcnID0+ICdhbnknLCAndGhlbWUnID0+ICdzYW1lJ10sICR0cmFpbCwgMSlbMF0gPz8gbnVsbDtcbiAgICBpZiAoJHNoYXJlZCkgeyByZXR1cm4gJHNoYXJlZCArIFsnbWF0Y2hfdHlwZScgPT4gJ3NoYXJlZF90aGVtZSddOyB9XG4gICAgLy8gQW4gZXhwbGljaXQgYXJ0aXN0IGNvbm5lY3Rpb24gaXMgdGhlIGZhbGxiYWNrLCBuZXZlciBhIGNsYWltZWQgdGhlbWUgbWF0Y2guXG4gICAgJGN1cmF0ZWQgPSBhcnJheV9maWx0ZXIoYXJyYXlfbWFwKCdhYnNpbnQnLCBleHBsb2RlKCcsJywgKHN0cmluZylnZXRfcG9zdF9tZXRhKCRjdXJyZW50WydpZCddLCAnX2FnX3JlbGF0ZWQnLCB0cnVlKSkpKTtcbiAgICAkcm93cyA9IFtdO1xuICAgIGZvcmVhY2ggKCRjYXRhbG9nIGFzICRzb25nKSB7XG4gICAgICAgIGlmICgkc29uZ1snYXJ0aXN0J10gIT09ICRjdXJyZW50WydhcnRpc3QnXSB8fCAkc29uZ1snaWQnXSA9PT0gJGN1cnJlbnRbJ2lkJ10gfHwgaW5fYXJyYXkoJHNvbmdbJ2lkJ10sICR0cmFpbCwgdHJ1ZSkpIHsgY29udGludWU7IH1cbiAgICAgICAgJG1vb2RzID0gYXJyYXlfaW50ZXJzZWN0X2tleSgkc29uZ1snbW9vZHMnXSwgJGN1cnJlbnRbJ21vb2RzJ10pO1xuICAgICAgICAkcm93c1tdID0gJHNvbmcgKyBbJ21hdGNoX3R5cGUnID0+ICdzYW1lX2FydGlzdCcsICdkaWZmZXJlbnRfYXJ0aXN0JyA9PiBmYWxzZSwgJ3JlYXNvbicgPT4gJ0Fub3RoZXIgc29uZyBmcm9tIHRoaXMgYXJ0aXN04oCZcyBzZWxlY3Rpb24uJywgJ3Njb3JlJyA9PiBjb3VudCgkbW9vZHMpICogMTAwICsgKGluX2FycmF5KCRzb25nWydpZCddLCAkY3VyYXRlZCwgdHJ1ZSkgPyAzNSA6IDApLCAndGllJyA9PiBzcHJpbnRmKCcldScsIGNyYzMyKCRjdXJyZW50WydpZCddIC4gJzonIC4gJHNvbmdbJ2lkJ10pKV07XG4gICAgfVxuICAgIHVzb3J0KCRyb3dzLCBmdW5jdGlvbiAoJGEsICRiKSB7IHJldHVybiAoJGJbJ3Njb3JlJ10gPD0+ICRhWydzY29yZSddKSA/OiAoJGJbJ3RpZSddIDw9PiAkYVsndGllJ10pID86ICgkYVsnaWQnXSA8PT4gJGJbJ2lkJ10pOyB9KTtcbiAgICByZXR1cm4gJHJvd3NbMF0gPz8gbnVsbDtcbn1cblxuZnVuY3Rpb24gYWdfZGlzY292ZXJ5X2NvbnRleHQoJGlkLCAkaW5wdXQgPSBbXSkgeyJdLFsiLy8gVGhlIGZlYXR1cmVkIHJvdXRlIGFsd2F5cyBzaGFyZXMgYSB0aGVtZSB3aXRoIHRoaXMgc29uZywgaW5kZXBlbmRlbnRseSBvZiBhbHRlcm5hdGl2ZSBmaWx0ZXJzLlxuICAgICRmZWF0dXJlZCA9IGFnX2Rpc2NvdmVyeV9yZWNvbW1lbmQoJGN1cnJlbnQsICRjYXRhbG9nLCBbJ2ZlZWxpbmcnID0+ICdhbnknLCAndGhlbWUnID0+ICdzYW1lJ10sICR0cmFpbCwgMSlbMF0gPz8gbnVsbDsiLCIvLyBQcmVmZXIgYSBzaGFyZWQgdGhlbWU7IG90aGVyd2lzZSBjb250aW51ZSB3aXRoIHRoZSBjdXJyZW50IGFydGlzdC5cbiAgICAkZmVhdHVyZWQgPSBhZ19kaXNjb3ZlcnlfZmVhdHVyZWQoJGN1cnJlbnQsICRjYXRhbG9nLCAkdHJhaWwpOyJdXX0seyJwYXRoIjoid3AtY29udGVudC90aGVtZXMvYWZ0ZXJnbG93L2Rpc2NvdmVyeS11aS5waHAiLCJiZWZvcmUiOlsiODM5ODM3N2Y0YmQwN2I5M2UzOTgwYWJmZDIxZmU3ZDhmNzMwNGViMTk3MjU3Y2U5NTU5MWIwNmI4MGM1OWExYSIsImMxNjg5OTgzOTkzYTY1ZDkxN2M4OTJiYzBiNTFjZmNlOTI1N2RhODUxNWEzODcyOTA1MjAyZTBhMTczMzlmNmQiXSwiYWZ0ZXIiOlsiMTAxNDA1MjA2ODM2OGEwMzQwZDAxZmEwNjU5ZThlZWY4NjdmMmVmZjRlYmNhM2RjYzgyNWU1ODJiNDQwNWZkZCIsIjVkMTFjNTY1NTRhMDhkZWZhMGZkNDBiZTY5NWExZDEyYzI5ZGYyMjhjNDVkODg0MmQyMzhkNDhmMWQ3YTQ2YzciXSwicmVwbGFjZW1lbnRzIjpbWyJlY2hvICc8c2VjdGlvbiBjbGFzcz1cIm5leHQtZGlzY292ZXJ5XCIgZGF0YS1kaXNjb3ZlcnktcHJpbWFyeSBhcmlhLWxhYmVsPVwiWW91ciBuZXh0IGRpc2NvdmVyeVwiPjxwIGNsYXNzPVwiZXllYnJvd1wiPllPVVIgTkVYVCBESVNDT1ZFUlk8L3A+JzsiLCIkZmFsbGJhY2sgPSAoJGNvbnRleHRbJ2ZlYXR1cmVkJ11bJ21hdGNoX3R5cGUnXSA/PyAnJykgPT09ICdzYW1lX2FydGlzdCc7XG4gICAgJGxhYmVsID0gJGZhbGxiYWNrID8gJ01PUkUgRlJPTSBUSElTIEFSVElTVCcgOiAnWU9VUiBORVhUIERJU0NPVkVSWSc7XG4gICAgZWNobyAnPHNlY3Rpb24gY2xhc3M9XCJuZXh0LWRpc2NvdmVyeVwiIGRhdGEtZGlzY292ZXJ5LXByaW1hcnkgYXJpYS1sYWJlbD1cIicgLiBlc2NfYXR0cigkZmFsbGJhY2sgPyAnTW9yZSBmcm9tIHRoaXMgYXJ0aXN0JyA6ICdZb3VyIG5leHQgZGlzY292ZXJ5JykgLiAnXCI+PHAgY2xhc3M9XCJleWVicm93XCI+JyAuIGVzY19odG1sKCRsYWJlbCkgLiAnPC9wPic7Il0sWyJObyBuZXh0IHNvbmcgd2l0aCBhIHNoYXJlZCB0aGVtZSBpcyBhdmFpbGFibGUgeWV0LiIsIk5vIG5leHQgc29uZyBpcyBhdmFpbGFibGUgZm9yIHRoaXMgcm91dGUgeWV0LiJdLFsiRm9sbG93IGEgc2hhcmVkIHRoZW1lLCB0aGVuIGV4cGxvcmUgYSBmZXcgb3RoZXIgY29ubmVjdGlvbnMuIiwiRm9sbG93IGEgc2hhcmVkIHRoZW1lLCBoZWFyIG1vcmUgZnJvbSBhbiBhcnRpc3QsIG9yIGV4cGxvcmUgYW5vdGhlciBjb25uZWN0aW9uLiJdXX1d',true),true);
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
 $featured_count=0;$fallback_count=0;
 foreach($catalog as $current){
  $feature=tf_preview_ag_discovery_featured($current,$catalog,[]);
  if($feature){
   if($feature['id']===$current['id']||!isset($catalog[$feature['id']])){throw new RuntimeException('Invalid featured identity.');}
   if(($feature['match_type']??'')==='same_artist'){
    $fallback_count++;
    if($feature['artist']!==$current['artist']||tf_preview_ag_discovery_recommend($current,$catalog,['feeling'=>'any','theme'=>'same'],[],1)){throw new RuntimeException('Invalid same-artist fallback or shared-theme priority.');}
   }else{$featured_count++;if(!array_intersect_key($feature['themes'],$current['themes'])){throw new RuntimeException('Featured song lacks shared theme.');}}
  }
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
 WP_CLI::line('Candidate preflight passed for 128 public songs; '.$featured_count.' have a shared-theme next discovery; '.$fallback_count.' use the same-artist fallback.');
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
$preserved=tf_rec_preservation();$backup=$parent.'/artist-fallback-backup-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(3));
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
WP_CLI::success('Featured same-artist fallback updated. Private backup: '.$backup);
WP_CLI::line('Catalog posts, metadata, taxonomy, visibility and indexing fingerprint unchanged.');
