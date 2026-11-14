<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_genre_groups_test_') { throw new RuntimeException('Disposable namespace required.'); }
$checks=0;$assert=function($ok,$message)use(&$checks){if(!$ok){throw new RuntimeException($message);}$checks++;};
try{
 wp_set_current_user(get_user_by('login','editor')->ID);
 $groups=ag_genre_groups();$assert(count($groups)>=15&&count($groups)<=20,'Controlled 15–20 family vocabulary.');
 $assert(ag_genre_group_slug('country-influenced-pop')===ag_genre_group_slug('country-tinged-pop'),'Country influence variants share a family.');
 $assert(ag_genre_group_slug('electronic-pop')===ag_genre_group_slug('electropop')&&ag_genre_group_slug('synth-pop')===ag_genre_group_slug('electropop'),'Electronic-pop variants share a family.');
 $assert(in_array('emotional-pop',ag_genre_filter_slugs('pop'),true)&&in_array('pop-duet',ag_genre_filter_slugs('pop'),true),'Descriptors/recording-format labels generalize to Pop.');
 $assert(ag_genre_filter_slugs('unmapped-future-style')===['unmapped-future-style'],'Unknown legacy filters keep exact-match behavior.');
 $make=function($type,$slug,$genres){$id=wp_insert_post(['post_type'=>$type,'post_name'=>$slug,'post_title'=>$slug,'post_status'=>'publish','post_content'=>'Original research.']);update_post_meta($id,'_ag_handoff_key',$slug.'/profile');wp_set_object_terms($id,$genres,'genre');return $id;};
 $artist=$make('artist','group-fixture-artist',['pop','emotional pop','Norwegian pop','country-influenced pop','country-tinged pop']);
 $song=$make('song','group-fixture-song',['singer-songwriter pop']);
 $unknown=$make('song','group-fixture-unknown',['unmapped-future-style']);
 $hidden=$make('artist','group-fixture-hidden',['piano pop']);
 update_option('ag_public_catalog_scope',['keys'=>['group-fixture-artist/profile','group-fixture-song/profile','group-fixture-unknown/profile'],'artists'=>1,'songs'=>2]);
 $snapshot=function()use($wpdb){return [$wpdb->get_results("SELECT * FROM {$wpdb->posts} ORDER BY ID",ARRAY_A),$wpdb->get_results("SELECT * FROM {$wpdb->postmeta} ORDER BY meta_id",ARRAY_A),$wpdb->get_results("SELECT * FROM {$wpdb->terms} ORDER BY term_id",ARRAY_A),$wpdb->get_results("SELECT * FROM {$wpdb->term_taxonomy} ORDER BY term_taxonomy_id",ARRAY_A),$wpdb->get_results("SELECT * FROM {$wpdb->term_relationships} ORDER BY object_id,term_taxonomy_id",ARRAY_A)];};
 $before=$snapshot();
 $options=ag_genre_family_options('artist');$by_slug=array_column($options,null,'slug');
 $assert(count($options)===2&&$by_slug['pop']->count===1&&$by_slug['country-pop']->count===1,'Options count unique records, not duplicate aliases.');
 $assert(!isset($by_slug['piano-pop']),'Hidden-only genres cannot appear in public options, including CLI/editor inspection.');
 $song_options=ag_genre_family_options('song');
 $assert(count($song_options)===1&&$song_options[0]->slug==='singer-songwriter','Each directory shows only its populated families; unknown genres are not guessed.');
 $source_terms=get_the_terms($artist,'genre');$display=ag_genre_family_terms($source_terms);
 $assert(count($source_terms)===5&&count($display)===2,'Display merges aliases without removing original terms.');
 $assert($snapshot()===$before,'Read-only grouping leaves all research, source terms and assignments untouched.');
 $assert(get_option('ag_genre_group_overrides','absent')==='absent','Defaults require no setup or database migration.');
 wp_set_current_user(0);
 $assert(is_wp_error(ag_save_genre_group_overrides(['pop'=>'electronic'])),'Anonymous mapping writes are rejected.');
 wp_set_current_user(get_user_by('login','editor')->ID);
 $assert(is_wp_error(ag_save_genre_group_overrides(['pop'=>'nonexistent-group']))&&get_option('ag_genre_group_overrides','absent')==='absent','Invalid family is rejected before any option write.');
 $assert(is_wp_error(ag_save_genre_group_overrides(['pop'=>['electronic']])),'Malformed values are rejected.');
 $r=ag_save_genre_group_overrides(['unmapped-future-style'=>'pop']);
 $assert(!is_wp_error($r)&&ag_genre_group_slug('unmapped-future-style')==='pop','An editor can explicitly classify a new source label.');
 $song_options=array_column(ag_genre_family_options('song'),null,'slug');
 $assert(isset($song_options['pop'])&&$song_options['pop']->count===1,'New explicit mapping updates real public filter counts.');
 $assert($snapshot()===$before,'Changing navigation mappings never changes posts, terms, taxonomies or relationships.');
 ag_save_genre_group_overrides(['country-influenced-pop'=>'pop']);
 $assert(ag_genre_group_slug('unmapped-future-style')==='pop','Partial mapping updates retain previous overrides.');
 ag_save_genre_group_overrides(['unmapped-future-style'=>'']);
 $assert(ag_genre_group_slug('unmapped-future-style')===null,'Clearing an override restores the default/unmapped state.');
 $override=get_option('ag_genre_group_overrides');
 $assert(is_wp_error(ag_save_genre_group_overrides(['pop'=>'bad']))&&get_option('ag_genre_group_overrides')===$override,'A rejected update preserves the existing mapping configuration.');
 WP_CLI::success($checks.' genre-group mapping, query inputs, counts, permissions and preservation checks passed.');
}finally{
 foreach($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($wpdb->prefix).'%')) as $table){if(!preg_match('/\Aag_genre_groups_test_[a-z0-9_]+\z/',$table)){throw new RuntimeException('Unexpected test table.');}$wpdb->query('DROP TABLE `'.$table.'`');}
}
