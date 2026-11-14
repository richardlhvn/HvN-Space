<?php
global $wpdb;
if($wpdb->prefix!=='ag_mood_groups_test_'){throw new RuntimeException('Disposable namespace required.');}
$checks=0;$assert=function($ok,$m)use(&$checks){if(!$ok){throw new RuntimeException($m);}$checks++;};
try{
 wp_set_current_user(get_user_by('login','editor')->ID);
 $assert(count(ag_mood_groups())===20,'Twenty controlled emotion families.');
 $assert(ag_mood_group_slug('introspective')==='reflection'&&ag_mood_group_slug('regretful')==='reflection','Related reflective feelings share Reflection.');
 $assert(ag_mood_group_slug('heartbroken')==='heartbreak'&&ag_mood_group_slug('hurt')==='heartbreak','Heartbreak synonyms share one family.');
 $assert(ag_mood_group_slug('cheeky')==='playful'&&ag_mood_group_slug('wry')==='playful','Playful synonyms share one family.');
 foreach(['melodic','atmospheric','late-night'] as $slug){$assert(ag_mood_group_slug($slug)===null,'A context/sound descriptor is not guessed into an emotion: '.$slug);}
 $make=function($type,$slug,$moods,$artist=0){$id=wp_insert_post(['post_type'=>$type,'post_name'=>$slug,'post_title'=>$slug,'post_content'=>'Original research.','post_status'=>'publish']);update_post_meta($id,'_ag_handoff_key',$slug.'/profile');if($artist){update_post_meta($id,'_ag_artist_id',(string)$artist);}wp_set_object_terms($id,$moods,'mood');return $id;};
 $a=$make('artist','mood-artist-a',['reflective','introspective','regretful','hopeful']);
 $b=$make('artist','mood-artist-b',['heartbroken']);
 $s1=$make('song','mood-song-a',['heartbroken','hurt'],$a);
 $s2=$make('song','mood-song-b',['heartbreak','playful','wry'],$b);
 $s3=$make('song','mood-song-c',['introspective'],$a);
 $descriptor=$make('song','mood-descriptor',['melodic'],$a);
 $hidden=$make('artist','mood-hidden',['anxious']);
 $hidden_song=$make('song','mood-hidden-song',['anxious'],$hidden);
 update_option('ag_public_catalog_scope',['keys'=>array_map(fn($slug)=>$slug.'/profile',['mood-artist-a','mood-artist-b','mood-song-a','mood-song-b','mood-song-c','mood-descriptor']),'artists'=>2,'songs'=>4]);
 $snapshot=function()use($wpdb){return [$wpdb->get_results("SELECT * FROM {$wpdb->posts} ORDER BY ID",ARRAY_A),$wpdb->get_results("SELECT * FROM {$wpdb->postmeta} ORDER BY meta_id",ARRAY_A),$wpdb->get_results("SELECT * FROM {$wpdb->terms} ORDER BY term_id",ARRAY_A),$wpdb->get_results("SELECT * FROM {$wpdb->term_taxonomy} ORDER BY term_taxonomy_id",ARRAY_A),$wpdb->get_results("SELECT * FROM {$wpdb->term_relationships} ORDER BY object_id,term_taxonomy_id",ARRAY_A)];};
 $before=$snapshot();$options=array_column(ag_mood_family_options('song'),null,'slug');
 $assert($options['heartbreak']->count===2,'Multiple aliases on one song count only once.');
 $assert(!isset($options['anxiety'])&&!isset($options['dreamy']),'Hidden-only moods and unmapped descriptors do not populate public menus.');
 $assert(count(ag_mood_family_terms(get_the_terms($a,'mood')))===2,'Profile tags merge duplicate feelings without changing source labels.');
 $assert(get_option('ag_mood_group_overrides','absent')==='absent','Defaults require no initialization writes.');
 add_filter('ag_public_catalog_context','__return_true');
 $catalog=ag_discovery_catalog();
 $assert(!isset($catalog[$hidden_song]),'Grouped discovery still excludes hidden songs.');
 $assert(array_keys($catalog[$s1]['moods'])===['heartbreak']&&array_keys($catalog[$s2]['moods'])===['heartbreak','playful'],'Discovery uses the same deduplicated emotion families.');
 $assert(!$catalog[$descriptor]['moods'],'No emotional claim is invented for a purely musical descriptor.');
 $context=ag_discovery_context($s1,['ag_feeling'=>'heartbroken','ag_theme'=>'any']);
 $assert(!$context['invalid']&&$context['preferences']['feeling']==='heartbreak'&&array_column($context['results'],'id')===[$s2],'Legacy feeling links normalize and find the correct grouped match.');
 $reflection=ag_discovery_context($s1,['ag_feeling'=>'introspective','ag_theme'=>'any']);
 $assert(!$reflection['invalid']&&$reflection['preferences']['feeling']==='reflection'&&array_column($reflection['results'],'id')===[$s3],'A chosen emotion matches its source-tag union.');
 $same=ag_discovery_context($s1,['ag_feeling'=>'same','ag_theme'=>'any']);
 $assert(array_column($same['results'],'id')===[$s2],'Same feeling links related aliases even when the original exact tags differ.');
 $assert(ag_discovery_context($s1,['ag_feeling'=>['heartbreak']])['invalid'],'Malformed feeling inputs remain safe.');
 $assert(ag_discovery_context($s1,['ag_feeling'=>'melodic'])['invalid'],'An unmapped non-emotion cannot become a false feeling filter.');
 remove_filter('ag_public_catalog_context','__return_true');
 $assert($snapshot()===$before,'Directory and discovery grouping never changes stored research/terms/relationships.');
 wp_set_current_user(0);$assert(is_wp_error(ag_save_mood_group_overrides(['melodic'=>'dreamy'])),'Anonymous mood mapping writes are rejected.');
 wp_set_current_user(get_user_by('login','editor')->ID);
 $assert(is_wp_error(ag_save_mood_group_overrides(['melodic'=>'pop']))&&get_option('ag_mood_group_overrides','absent')==='absent','A genre cannot accidentally be saved as a mood family.');
 $assert(is_wp_error(ag_save_mood_group_overrides(['melodic'=>['dreamy']])),'Malformed mapping values are rejected.');
 update_option('ag_genre_group_overrides',['custom-genre'=>'pop']);$genre=get_option('ag_genre_group_overrides');
 $r=ag_save_mood_group_overrides(['melodic'=>'dreamy']);
 $assert(!is_wp_error($r)&&ag_mood_group_slug('melodic')==='dreamy','An administrator can explicitly map a reviewed source label.');
 $assert(get_option('ag_genre_group_overrides')===$genre,'Mood mapping changes preserve genre overrides.');
 $assert($snapshot()===$before,'Explicit mood navigation overrides also leave all source data intact.');
 ag_save_mood_group_overrides(['melodic'=>'']);$assert(ag_mood_group_slug('melodic')===null,'Clearing an override restores the non-inferred default.');
 WP_CLI::success($checks.' mood-family, emotion matching, aliases, scope, permissions and source-preservation checks passed.');
}finally{
 foreach($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($wpdb->prefix).'%')) as $table){if(!preg_match('/\Aag_mood_groups_test_[a-z0-9_]+\z/',$table)){throw new RuntimeException('Unexpected test table.');}$wpdb->query('DROP TABLE `'.$table.'`');}
}
