<?php
/** Owner-authorized tag-only migration. No post/content/source/visibility writes. */
defined('ABSPATH') || exit;

function ag_tag_slug($value) {
    return sanitize_title(trim(preg_replace('/\s*\([^)]*\)/u', '', html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
}

function ag_normalized_tags($taxonomy, $values, $slugs = false) {
    if (!is_array($values)) { throw new RuntimeException('Invalid ' . $taxonomy . ' tag list.'); }
    $groups = ag_taxonomy_groups($taxonomy); $aliases = ag_taxonomy_default_aliases($taxonomy); $out = [];
    foreach ($values as $value) {
        if (!is_string($value)) { throw new RuntimeException('Invalid ' . $taxonomy . ' tag value.'); }
        $key = ag_tag_slug($value);
        if ($key === '' || ($taxonomy === 'mood' && in_array($key, ['atmospheric','melodic','late-night'], true))) { continue; }
        $group = $aliases[$key] ?? null;
        if (!$group) { throw new RuntimeException('Unmapped ' . $taxonomy . ' tag: ' . $value); }
        $out[$group] = $slugs ? $group : $groups[$group]['name'];
    }
    return array_values($out);
}

function ag_tag_normalize_record($record) {
    if (!is_array($record)) { return $record; }
    foreach (['genres'=>'genre','moods'=>'mood','themes'=>'theme'] as $field=>$taxonomy) {
        if (array_key_exists($field, $record)) { $record[$field] = ag_normalized_tags($taxonomy, $record[$field]); }
    }
    return $record;
}

function ag_tag_normalize_packs($packs) {
    foreach ($packs as &$pack) {
        $pack['artist'] = ag_tag_normalize_record($pack['artist']);
        $pack['songs'] = array_map('ag_tag_normalize_record', $pack['songs']);
    }
    unset($pack); return $packs;
}

function ag_tag_normalize_state($state) {
    if (!is_array($state)) { return $state; }
    foreach (['genre','mood'] as $tax) {
        if (isset($state['terms'][$tax])) { $state['terms'][$tax] = ag_normalized_tags($tax, $state['terms'][$tax], true); sort($state['terms'][$tax]); }
    }
    if (isset($state['meta']['themes'])) { $state['meta']['themes'] = implode(', ', ag_normalized_tags('theme', explode(',', $state['meta']['themes']))); }
    if (isset($state['_ag_handoff_record'])) { $state['_ag_handoff_record'] = ag_tag_normalize_record($state['_ag_handoff_record']); }
    return $state;
}

function ag_theme_labels($value) {
    $groups = ag_taxonomy_groups('theme'); $aliases = ag_taxonomy_default_aliases('theme'); $out = [];
    foreach (is_array($value) ? $value : explode(',', $value) as $label) {
        $key = $aliases[ag_tag_slug($label)] ?? null;
        if ($key) { $out[$key] = $groups[$key]['name']; }
    }
    return $out;
}

function ag_tag_sync_plan() {
    $report = ['updated'=>[], 'skipped'=>[], 'conflicts'=>[], 'terms'=>[], 'retired_moods'=>['atmospheric','melodic','late-night']]; $plans = [];
    foreach (['genre','mood'] as $tax) {
        $defaults = ag_taxonomy_default_aliases($tax);
        foreach ((array)get_option('ag_' . $tax . '_group_overrides', []) as $alias=>$group) {
            if (($defaults[$alias] ?? null) !== $group) { $report['conflicts'][] = 'Navigation override needs review: ' . $tax . '/' . $alias; }
        }
        foreach (ag_taxonomy_groups($tax) as $slug=>$group) {
            $term = get_term_by('slug', $slug, $tax);
            if (!$term || html_entity_decode($term->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') !== $group['name']) { $report['terms'][] = $tax . '/' . $slug; }
        }
    }
    // Explicit SQL includes drafts, trash and retained records outside public scope.
    global $wpdb;
    $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('artist','song') ORDER BY ID");
    foreach ($ids as $id) {
        $id = (int)$id; $key = get_post_meta($id, '_ag_handoff_key', true) ?: get_post_type($id) . ':' . $id;
        try {
            $plan = ['id'=>$id, 'key'=>$key, 'terms'=>[], 'meta'=>[]];
            foreach (['_ag_themes','_ag_handoff_record','_ag_handoff_baseline','_ag_reviewed_baseline','_ag_reviewed_payload_hash'] as $field) {
                if (count(get_post_meta($id, $field, false)) > 1) { throw new RuntimeException('Duplicate metadata needs review: ' . $field); }
            }
            foreach (['genre','mood'] as $tax) {
                $old = wp_get_object_terms($id, $tax, ['fields'=>'slugs']);
                if (is_wp_error($old)) { throw new RuntimeException($old->get_error_message()); }
                $new = ag_normalized_tags($tax, $old, true); sort($old); sort($new);
                if ($old !== $new) { $plan['terms'][$tax] = $new; }
            }
            if (metadata_exists('post', $id, '_ag_themes')) {
                $old = get_post_meta($id, '_ag_themes', true);
                $new = implode(', ', ag_normalized_tags('theme', explode(',', $old)));
                if ($old !== $new) { $plan['meta']['_ag_themes'] = $new; }
            }
            foreach (['_ag_handoff_record','_ag_handoff_baseline','_ag_reviewed_baseline'] as $field) {
                if (!metadata_exists('post', $id, $field)) { continue; }
                $old = get_post_meta($id, $field, true);
                $new = $field === '_ag_handoff_record' ? ag_tag_normalize_record($old) : ag_tag_normalize_state($old);
                if ($old !== $new) { $plan['meta'][$field] = $new; }
            }
            $old = get_post_meta($id, '_ag_handoff_record', true);
            $hash = get_post_meta($id, '_ag_reviewed_payload_hash', true);
            if ($hash && is_array($old)) {
                $inputs = [$old, get_post_meta($id, '_ag_source_registry', true), get_post_meta($id, '_ag_reviewed_provenance', true)];
                // Never bless an edited payload as a new approved baseline.
                if (hash_equals($hash, hash('sha256', wp_json_encode($inputs)))) {
                    $inputs[0] = ag_tag_normalize_record($old); $new = hash('sha256', wp_json_encode($inputs));
                    if ($new !== $hash) { $plan['meta']['_ag_reviewed_payload_hash'] = $new; }
                }
            }
            if ($plan['terms'] || $plan['meta']) { $plans[] = $plan; $report['updated'][] = $key; }
            else { $report['skipped'][] = $key; }
        } catch (Throwable $e) { $report['conflicts'][] = $key . ': ' . $e->getMessage(); }
    }
    return [$report, $plans];
}

function ag_migrate_tags($dry_run = true) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    if (!$dry_run && !add_option('ag_handoff_lock', time(), '', false)) { return new WP_Error('busy', 'Another research migration is running.'); }
    global $wpdb; $in_transaction = false;
    try {
        if (!$dry_run) {
            $wpdb->query('START TRANSACTION'); $in_transaction = true;
            $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('artist','song') ORDER BY ID FOR UPDATE");
            $wpdb->get_col("SELECT m.meta_id FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID=m.post_id WHERE p.post_type IN ('artist','song') FOR UPDATE");
            $wpdb->get_col("SELECT r.object_id FROM {$wpdb->term_relationships} r INNER JOIN {$wpdb->posts} p ON p.ID=r.object_id WHERE p.post_type IN ('artist','song') FOR UPDATE");
            wp_cache_flush();
        }
        [$report, $plans] = ag_tag_sync_plan(); $report['dry_run'] = $dry_run;
        if ($dry_run || $report['conflicts']) {
            if ($in_transaction) { $wpdb->query('ROLLBACK'); $report['blocked'] = true; }
            return $report;
        }
        $term_ids = [];
        foreach (['genre','mood'] as $tax) {
            foreach (ag_taxonomy_groups($tax) as $slug=>$group) {
                $term = get_term_by('slug', $slug, $tax);
                if (!$term) { $result = wp_insert_term($group['name'], $tax, ['slug'=>$slug]); }
                elseif (html_entity_decode($term->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') !== $group['name']) { $result = wp_update_term($term->term_id, $tax, ['name'=>$group['name']]); }
                else { $result = ['term_id'=>$term->term_id]; }
                if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
                $term_ids[$tax][$slug] = (int)$result['term_id'];
            }
        }
        foreach ($plans as $plan) {
            foreach ($plan['terms'] as $tax=>$slugs) {
                $result = wp_set_object_terms($plan['id'], array_map(function ($slug) use ($term_ids, $tax) { return $term_ids[$tax][$slug]; }, $slugs), $tax);
                if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
                $actual = wp_get_object_terms($plan['id'], $tax, ['fields'=>'slugs']);
                if (is_wp_error($actual)) { throw new RuntimeException($actual->get_error_message()); }
                sort($actual);
                if ($actual !== $slugs) { throw new RuntimeException('Tag assignment write failed: ' . $plan['key']); }
            }
            foreach ($plan['meta'] as $field=>$value) {
                update_post_meta($plan['id'], $field, wp_slash($value));
                if (get_post_meta($plan['id'], $field, true) !== $value) { throw new RuntimeException('Tag metadata write failed: ' . $plan['key'] . ' ' . $field); }
            }
        }
        if ($wpdb->last_error) { throw new RuntimeException('Database write failed.'); }
        $wpdb->query('COMMIT'); $in_transaction = false;
        update_option('ag_tag_sync_last_report', $report, false);
        return $report;
    } catch (Throwable $e) {
        if ($in_transaction) { $wpdb->query('ROLLBACK'); wp_cache_flush(); }
        return new WP_Error('tag_sync_failed', $e->getMessage());
    } finally { if (!$dry_run) { delete_option('ag_handoff_lock'); } }
}
