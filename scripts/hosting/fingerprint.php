<?php
/** Read-only preservation proof for a complete WordPress database transfer. */
function tf_hosting_normalize($value, $from = '', $to = '') {
    if (is_string($value) && $from !== '') { return str_replace($from, $to, $value); }
    if (is_array($value)) { foreach ($value as &$child) { $child = tf_hosting_normalize($child, $from, $to); } unset($child); }
    return $value;
}
function tf_hosting_fingerprint($from = '', $to = '') {
    global $wpdb; $records = []; $counts = [];
    foreach ($wpdb->get_results("SELECT * FROM {$wpdb->posts} ORDER BY ID", ARRAY_A) as $post) {
        $id = (int)$post['ID']; $counts[$post['post_type']] = ($counts[$post['post_type']] ?? 0) + 1;
        // WordPress GUIDs are identities, deliberately excluded from URL replacement.
        $guid = $post['guid']; $post = tf_hosting_normalize($post, $from, $to); $post['guid'] = $guid;
        $meta = get_post_meta($id); ksort($meta);
        foreach ($meta as &$values) { $values = array_map(function ($v) use ($from, $to) { return tf_hosting_normalize(maybe_unserialize($v), $from, $to); }, $values); } unset($values);
        $relations = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->term_relationships} WHERE object_id=%d ORDER BY term_taxonomy_id", $id), ARRAY_A);
        $records[$id] = hash('sha256', serialize([$post, $meta, $relations]));
    }
    $keys = ['home','siteurl','blog_public','blogname','blogdescription','stylesheet','template','theme_mods_afterglow','permalink_structure','show_on_front','page_on_front','page_for_posts','wp_page_for_privacy_policy','sidebars_widgets','active_plugins'];
    $keys = array_merge($keys, $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'ag\\_%' ORDER BY option_name"));
    $options = []; foreach (array_unique($keys) as $key) { $options[$key] = tf_hosting_normalize(get_option($key), $from, $to); } ksort($options);
    $terms = [];
    foreach (['terms'=>'term_id','term_taxonomy'=>'term_taxonomy_id','termmeta'=>'meta_id'] as $suffix=>$order) { $table = $wpdb->prefix . $suffix; $terms[$suffix] = $wpdb->get_results("SELECT * FROM $table ORDER BY $order", ARRAY_A); }
    return ['records'=>$records, 'post_counts'=>$counts, 'options'=>$options, 'taxonomy_sha256'=>hash('sha256',serialize($terms))];
}
