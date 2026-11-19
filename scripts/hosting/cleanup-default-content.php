<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$targets=json_decode(base64_decode('W3siaWQiOjIsInR5cGUiOiJwYWdlIiwic2x1ZyI6InNhbXBsZS1wYWdlIiwidGl0bGUiOiJTYW1wbGUgUGFnZSIsImhhc2giOiJiYzdkMzhiYzlhNGY2MTI4Y2Q0ZTgwYzA5NzZiMjhmNDg4ZWY4MTc3NGY0OGUxM2EyMWYyMmI0OGQ2NTNiOWUxIn0seyJpZCI6MSwidHlwZSI6InBvc3QiLCJzbHVnIjoiaGVsbG8td29ybGQiLCJ0aXRsZSI6IkhlbGxvIHdvcmxkISIsImhhc2giOiJjMzA4NzVjNjJiN2QzZDU4MDdkMTExOTczODZmODIxODA4NGUzNjk5OTk4NzA3MjEwNmQwOGEyM2Y4Y2Q1ZmNjIn1d',true),true);
if(wp_parse_url(home_url(),PHP_URL_HOST)!=='tunetofond.com'||count($targets)!==2){WP_CLI::error('Unexpected site or package. Nothing changed.');}
$parent=realpath($args[0]??'');$root=realpath(ABSPATH);
if(!$parent||!$root||!is_writable($parent)||$parent===$root||str_starts_with($parent.'/',$root.'/')){WP_CLI::error('A writable private backup directory outside public_html is required.');}
$before=[];$pending=[];
foreach($targets as $target){
 $p=get_post($target['id']);
 if(!$p||$p->post_type!==$target['type']||$p->post_name!==$target['slug']||$p->post_title!==$target['title']||!in_array($p->post_status,['publish','draft'],true)||hash('sha256',apply_filters('the_content',$p->post_content))!==$target['hash']){WP_CLI::error('Different content or identity on '.$target['slug'].'. Nothing changed; preserve those edits.');}
 foreach(['page_on_front','page_for_posts','wp_page_for_privacy_policy'] as $setting){if((int)get_option($setting)===$p->ID){WP_CLI::error('Target is assigned to '.$setting.'. Nothing changed.');}}
 $before[$p->ID]=['ID'=>$p->ID,'post_type'=>$p->post_type,'post_title'=>$p->post_title,'post_name'=>$p->post_name,'post_content'=>$p->post_content,'post_excerpt'=>$p->post_excerpt,'post_status'=>$p->post_status];
 if($p->post_status==='publish'){$pending[]=$p->ID;}
}
function tf_defaults_preservation(){
 global $wpdb;
 $ids=$wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('artist','song') ORDER BY ID");
 $posts=$wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type IN ('artist','song') ORDER BY ID",ARRAY_A);
 $meta=$ids?$wpdb->get_results('SELECT * FROM '.$wpdb->postmeta.' WHERE post_id IN ('.implode(',',array_map('intval',$ids)).') ORDER BY meta_id',ARRAY_A):[];
 $terms=$ids?$wpdb->get_results('SELECT * FROM '.$wpdb->term_relationships.' WHERE object_id IN ('.implode(',',array_map('intval',$ids)).') ORDER BY object_id,term_taxonomy_id',ARRAY_A):[];
 $settings=[];foreach(['ag_public_catalog_scope','blog_public','page_on_front','page_for_posts','wp_page_for_privacy_policy','permalink_structure'] as $key){$settings[$key]=get_option($key);}
 return hash('sha256',serialize([$posts,$meta,$terms,$settings]));
}
if(in_array('check',$args??[],true)){WP_CLI::success('Both unchanged default-content items passed checks. No changes made.');return;}
if(!$pending){WP_CLI::success('Both items are already drafts. Nothing changed.');return;}
$fingerprint=tf_defaults_preservation();$backup=$parent.'/default-content-backup-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(3));
if(!mkdir($backup,0700)){WP_CLI::error('Could not create private backup. Nothing changed.');}
$file=$backup.'/before.json';
if(file_put_contents($file,wp_json_encode(['posts'=>$before,'catalog_fingerprint'=>$fingerprint],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))===false||!chmod($file,0600)){WP_CLI::error('Could not secure backup. Nothing changed.');}
$attempted=[];
try{
 foreach($pending as $id){
  $attempted[]=$id;$result=wp_update_post(['ID'=>$id,'post_status'=>'draft'],true);clean_post_cache($id);$p=get_post($id);
  if(is_wp_error($result)||!$p||$p->post_status!=='draft'||$p->post_title!==$before[$id]['post_title']||$p->post_name!==$before[$id]['post_name']||$p->post_content!==$before[$id]['post_content']||$p->post_excerpt!==$before[$id]['post_excerpt']){throw new RuntimeException('Draft verification failed for ID '.$id);}
 }
 if(tf_defaults_preservation()!==$fingerprint){throw new RuntimeException('Library/settings preservation check failed.');}
}catch(Throwable $error){
 $failed=false;foreach($attempted as $id){$restored=wp_update_post(['ID'=>$id,'post_status'=>$before[$id]['post_status']],true);clean_post_cache($id);if(is_wp_error($restored)||get_post($id)->post_status!==$before[$id]['post_status']){$failed=true;}}
 WP_CLI::error($error->getMessage().'. Backup: '.$backup.($failed?' RESTORE NEEDS REVIEW.':' Previous publication statuses restored.'));
}
WP_CLI::success('Sample Page and Hello world! moved to drafts. Private backup: '.$backup);
WP_CLI::line('Both items retained. Catalog posts, metadata, taxonomy, public scope and indexing/settings fingerprint unchanged.');
