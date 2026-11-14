<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_tag_sync_test_') { throw new RuntimeException('Disposable namespace required.'); }
$checks=0; $assert=function($ok,$message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } $checks++; };
$snapshot=function() use ($wpdb) {
    $out=[];
    foreach (['posts','postmeta','terms','term_taxonomy','term_relationships'] as $suffix) { $table=$wpdb->prefix.$suffix; $out[$suffix]=$wpdb->get_results("SELECT * FROM $table",ARRAY_A); }
    return $out;
};
try {
    wp_set_current_user(get_user_by('login','editor')->ID);
    $old=json_decode(file_get_contents(WP_PLUGIN_DIR.'/afterglow-core/data/reviewed-tunetofond-2026-11-14.json'),true);
    $current=ag_reviewed_data();
    $assert($current['packs']===ag_tag_normalize_packs($old['packs']) && $current['provenance']===$old['provenance'],'Canonical adjustment changes only classification arrays and retains original workbook provenance.');
    $result=ag_import_reviewed(false,true,$old);
    $assert(!is_wp_error($result)&&count($result['created'])===138,'Original approved ten/128 fixture.');
    ag_enable_core_catalog(); $scope=get_option('ag_public_catalog_scope');
    $edited=ag_reviewed_find('astrid-s');
    wp_update_post(['ID'=>$edited,'post_content'=>'Owner prose with a quote: "keep this".','post_date'=>'2020-01-02 03:04:05','post_date_gmt'=>'2020-01-02 03:04:05']);
    $hidden=wp_insert_post(['post_type'=>'song','post_title'=>'Retained hidden fixture','post_status'=>'trash','post_content'=>'Unrelated research remains.']);
    wp_set_object_terms($hidden,['alt-pop','confessional-pop'],'genre');
    wp_set_object_terms($hidden,['melodic','late-night','wistful','nostalgic'],'mood');
    update_post_meta($hidden,'_ag_themes','breakup, breakups, grief (editorial)');
    $post_before=get_post($hidden,ARRAY_A);$artist_before=get_post($edited,ARRAY_A);
    $before=$snapshot();wp_set_current_user(0);
    $assert(is_wp_error(ag_migrate_tags(false))&&$snapshot()===$before,'Anonymous migration is rejected without writes.');
    wp_set_current_user(get_user_by('login','editor')->ID);
    add_option('ag_handoff_lock',123,'',false);
    $assert(is_wp_error(ag_migrate_tags(false))&&get_option('ag_handoff_lock')===123,'A competing importer lock is respected and retained.');
    delete_option('ag_handoff_lock');
    update_post_meta($hidden,'_ag_themes','unreviewed custom theme');$bad=$snapshot();
    $plan=ag_migrate_tags(true);$apply=ag_migrate_tags(false);
    $assert($plan['conflicts']&&$apply['blocked']&&$snapshot()===$bad,'Unknown tags block the entire batch; no partial normalization or term creation.');
    update_post_meta($hidden,'_ag_themes','breakup, breakups, grief (editorial)');
    update_option('ag_mood_group_overrides',['wistful'=>'playful']);$bad=$snapshot();
    $assert(ag_migrate_tags(false)['blocked']&&$snapshot()===$bad,'Non-default navigation overrides require review before destructive consolidation.');
    delete_option('ag_mood_group_overrides');
    $before=$snapshot();$plan=ag_migrate_tags(true);
    $assert(!$plan['conflicts']&&$plan['updated']&&$snapshot()===$before,'Dry run reports changes without writing research or term tables.');
    $fail=function($value,$id,$key) { if ($key==='_ag_themes') { throw new RuntimeException('Injected write failure.'); } return $value; };
    add_filter('update_post_metadata',$fail,10,3);$broken=ag_migrate_tags(false);remove_filter('update_post_metadata',$fail,10);
    $assert(is_wp_error($broken)&&$snapshot()===$before&&!get_option('ag_handoff_lock'),'A mid-batch metadata failure rolls back records, term creation and assignments and releases the lock.');
    $apply=ag_migrate_tags(false);
    $assert(!is_wp_error($apply)&&!$apply['conflicts']&&$apply['updated']===$plan['updated'],'Apply matches the protected dry-run batch.');
    $assert(get_post($hidden,ARRAY_A)===$post_before&&get_post($edited,ARRAY_A)===$artist_before,'All post fields, dates, status and owner prose remain byte-identical.');
    $assert(get_option('ag_public_catalog_scope')===$scope&&!ag_public_catalog_visible($hidden),'Public selection and hidden/trash visibility remain unchanged.');
    $genres=wp_get_object_terms($hidden,'genre',['fields'=>'slugs']);sort($genres);
    $assert($genres===['alternative-pop','pop']&&wp_get_object_terms($hidden,'mood',['fields'=>'slugs'])===['nostalgia'],'Aliases merge and non-emotional descriptors retire without inferring a mood.');
    $assert(get_post_meta($hidden,'_ag_themes',true)==='Heartbreak & Breakups, Grief & Loss','Stored theme labels are deduplicated canonical topics.');
    $assert(!ag_reviewed_can_update($edited,'astrid-s/profile'),'An existing prose edit still conflicts after baseline normalization.');
    $review=ag_import_reviewed(true,false);
    $assert(!$review['updated']&&!$review['created']&&count($review['skipped'])===137&&$review['conflicts']===['astrid-s/profile'],'Updated payload hashes skip clean canonical records without blessing owner edits.');
    foreach ($current['packs'] as $slug=>$pack) {
        foreach (array_merge([$pack['artist']],$pack['songs']) as $record) {
            $song=isset($record['artist_slug']);$id=ag_reviewed_find($slug,$song?$record:null);
            $assert(get_post_meta($id,'_ag_handoff_record',true)===$record,'Stored classification arrays exactly match active canonical JSON.');
            $assert(get_post_meta($id,'_ag_source_registry',true)===$pack['sources'],'Source evidence remains exactly unchanged.');
        }
    }
    $after=$snapshot();$repeat=ag_migrate_tags(false);
    $assert(!$repeat['updated']&&!$repeat['terms']&&!$repeat['conflicts']&&$snapshot()===$after,'Repeat is idempotent across all record and term tables.');
    $assert(count(ag_taxonomy_groups('theme'))===20&&count(ag_theme_labels('breakup, breakups, grief'))===2,'Twenty deduplicated theme families.');
    $assert(ag_taxonomy_group_slug('theme','breakups')==='heartbreak-breakups','Historical theme query aliases resolve to their shared topic.');
    WP_CLI::success($checks.' tag migration, rollback, canonical sync, permissions, edit-conflict and preservation checks passed.');
} finally {
    foreach ($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($wpdb->prefix).'%')) as $table) { if (!preg_match('/\Aag_tag_sync_test_[a-z0-9_]+\z/',$table)) { throw new RuntimeException('Unexpected test table.'); } $wpdb->query('DROP TABLE `'.$table.'`'); }
}
