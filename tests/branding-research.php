<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_branding_research_test_') { throw new RuntimeException('Disposable namespace required.'); }
$checks=0;$assert=function($ok,$message) use (&$checks){if(!$ok){throw new RuntimeException($message);}$checks++;};
try {
    wp_set_current_user(get_user_by('login','editor')->ID);
    $original=json_decode(file_get_contents(WP_PLUGIN_DIR.'/afterglow-core/data/reviewed-2026-11-14.json'),true);
    $data=ag_reviewed_data();
    $assert($data['packs']===ag_tag_normalize_packs(ag_branding_normalize($original['packs'])) && $data['provenance']===$original['provenance'],'Active canonical data contains only owner-authorized branding and classification adjustments.');
    $partial=wp_insert_post(['post_type'=>'artist','post_title'=>'Partial installation','post_name'=>'astrid-s','post_status'=>'draft']);
    update_post_meta($partial,'_ag_handoff_key','astrid-s/profile');
    $assert(is_wp_error(ag_migrate_research_branding(false)),'An incomplete catalog cannot be created by the branding migration.');
    wp_delete_post($partial,true);
    $import=ag_import_reviewed(false,true,$original);
    $assert(!is_wp_error($import) && count($import['created'])===138,'Build only the approved original ten/128 fixture.');
    $sync=ag_migrate_tags(false);
    $assert(!is_wp_error($sync) && !$sync['conflicts'],'Normalize tags before the separate brand-only migration.');
    wp_update_post(['ID'=>ag_reviewed_find('astrid-s'),'post_date'=>'2020-01-02 03:04:05','post_date_gmt'=>'2020-01-02 03:04:05']);
    $artist=ag_reviewed_find('sara-kays');
    wp_update_post(['ID'=>$artist,'post_content'=>get_post_field('post_content',$artist).'<p>Later owner wording stays.</p>']);
    $unrelated=wp_insert_post(['post_type'=>'song','post_title'=>'Unrelated hidden fixture','post_status'=>'draft','post_content'=>'For Afterglow, historical material.']);
    $snapshot=function($id)use($wpdb){return [get_post($id,ARRAY_A),get_post_meta($id),$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->term_relationships} WHERE object_id=%d ORDER BY term_taxonomy_id",$id),ARRAY_A)];};
    $before=[];foreach($import['ids'] as $key=>$id){$before[$key]=$snapshot($id);}
    $unrelated_before=$snapshot($unrelated);
    $plan=ag_migrate_research_branding(true);
    $assert(!is_wp_error($plan) && !$plan['created'] && $plan['updated'] && $plan['conflicts']===['sara-kays/profile'],'Dry run reports existing brand-only changes and preserves the edited artist as a conflict.');
    foreach($import['ids'] as $key=>$id){$assert($before[$key]===$snapshot($id),'Dry run must not alter research: '.$key);}
    $apply=ag_migrate_research_branding(false);
    $assert(!is_wp_error($apply) && $apply['updated']===$plan['updated'],'Apply only the reviewed existing-record plan.');
    foreach($import['ids'] as $key=>$id){
        $after=$snapshot($id);$old=$before[$key];
        if($id===$artist){$assert($after===$old,'Later editor change remains completely untouched.');continue;}
        foreach(['ID','post_name','post_status','post_title','post_author','post_date','post_date_gmt'] as $field){$assert($after[0][$field]===$old[0][$field],'Stable post identity: '.$key.' '.$field);}
        foreach(['post_content','post_excerpt'] as $field){$assert($after[0][$field]===ag_branding_normalize($old[0][$field]),'Only known brand phrases change in stored prose: '.$key.' '.$field);}
        $assert($after[2]===$old[2],'Taxonomy relationships are unchanged: '.$key);
        foreach(['_ag_spotify','_ag_related','_ag_recommended','_thumbnail_id','_ag_artwork_credit','_ag_reviewed_approval'] as $meta){$assert(($after[1][$meta]??null)===($old[1][$meta]??null),'Recording URLs, relationships, images and approvals remain: '.$key.' '.$meta);}
        foreach(['_ag_handoff_record','_ag_source_registry'] as $meta){$assert(get_post_meta($id,$meta,true)===ag_branding_normalize(maybe_unserialize($old[1][$meta][0])),'Stored research/evidence change only brand references: '.$key.' '.$meta);}
    }
    $assert($snapshot($unrelated)===$unrelated_before,'Unrelated hidden records are byte-identical.');
    $repeat=ag_migrate_research_branding(false);
    $assert(!is_wp_error($repeat) && !$repeat['updated'] && !$repeat['created'] && count($repeat['skipped'])===137 && count($repeat['conflicts'])===1,'Repeat is idempotent; the owner conflict remains protected.');
    WP_CLI::success($checks.' stored-research branding, conflict, identity, evidence and repeatability checks passed.');
}finally{
    foreach($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($wpdb->prefix).'%')) as $table){if(!preg_match('/\Aag_branding_research_test_[a-z0-9_]+\z/',$table)){throw new RuntimeException('Unexpected test table.');}$wpdb->query('DROP TABLE `'.$table.'`');}
}
