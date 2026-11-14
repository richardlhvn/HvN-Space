<?php
// Read-only fingerprint: normalize only the authorized tag delta before hashing.
global $wpdb; $records = [];
foreach ($wpdb->get_results("SELECT * FROM {$wpdb->posts} ORDER BY ID", ARRAY_A) as $post) {
    $id = (int)$post['ID']; $meta = get_post_meta($id); $terms = [];
    if (in_array($post['post_type'], ['artist','song'], true)) {
        foreach ($meta as $field=>&$values) {
            foreach ($values as &$value) {
                $decoded = maybe_unserialize($value);
                if ($field === '_ag_themes') { $value = implode(', ', ag_normalized_tags('theme', explode(',', $decoded))); }
                if ($field === '_ag_handoff_record') { $value = maybe_serialize(ag_tag_normalize_record($decoded)); }
                if (in_array($field, ['_ag_handoff_baseline','_ag_reviewed_baseline'], true)) { $value = maybe_serialize(ag_tag_normalize_state($decoded)); }
                if ($field === '_ag_reviewed_payload_hash') {
                    $inputs = [get_post_meta($id, '_ag_handoff_record', true), get_post_meta($id, '_ag_source_registry', true), get_post_meta($id, '_ag_reviewed_provenance', true)];
                    if (hash_equals($value, hash('sha256', wp_json_encode($inputs)))) { $inputs[0] = ag_tag_normalize_record($inputs[0]); $value = hash('sha256', wp_json_encode($inputs)); }
                }
            } unset($value);
        } unset($values);
        foreach (['genre','mood'] as $tax) { $terms[$tax] = ag_normalized_tags($tax, wp_get_object_terms($id, $tax, ['fields'=>'slugs']), true); sort($terms[$tax]); }
    }
    $other = $wpdb->get_results($wpdb->prepare("SELECT r.* FROM {$wpdb->term_relationships} r INNER JOIN {$wpdb->term_taxonomy} t ON t.term_taxonomy_id=r.term_taxonomy_id WHERE r.object_id=%d AND t.taxonomy NOT IN ('genre','mood') ORDER BY r.term_taxonomy_id", $id), ARRAY_A);
    ksort($meta);
    $records[$id] = hash('sha256', serialize([$post, $meta, $terms, $other]));
}
$options=[];
foreach (['home','siteurl','blog_public','permalink_structure','ag_public_catalog_scope','stylesheet','template','theme_mods_afterglow','ag_genre_group_overrides','ag_mood_group_overrides'] as $key) { $options[$key]=get_option($key); }
WP_CLI::line(wp_json_encode(['records'=>$records,'options'=>$options], JSON_PRETTY_PRINT));
