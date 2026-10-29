<?php
defined('ABSPATH') || exit;

// One local catalog build per page, with bulk meta/term caches. No external service or listener profile.
function ag_discovery_catalog() {
    $artists = get_posts(['post_type' => 'artist', 'post_status' => 'publish', 'numberposts' => -1]);
    $artist_ids = array_fill_keys(array_map(function ($p) { return $p->ID; }, $artists), true);
    $posts = get_posts(['post_type' => 'song', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC', 'update_post_meta_cache' => true, 'update_post_term_cache' => true]);
    $catalog = [];
    foreach ($posts as $post) {
        $artist = absint(get_post_meta($post->ID, '_ag_artist_id', true));
        if (!isset($artist_ids[$artist]) || (get_post_meta($post->ID, '_ag_demo', true) && !get_post_meta($post->ID, '_ag_handoff_key', true))) { continue; }
        $themes = [];
        foreach (explode(',', (string)get_post_meta($post->ID, '_ag_themes', true)) as $label) {
            $label = trim(preg_replace('/\s*\(editorial\)/iu', '', $label));
            if ($label !== '' && ($key = sanitize_title($label))) { $themes[$key] = $label; }
        }
        $moods = [];
        $terms = get_the_terms($post->ID, 'mood');
        foreach ($terms && !is_wp_error($terms) ? $terms : [] as $term) { $moods[$term->slug] = $term->name; }
        $catalog[$post->ID] = ['id' => $post->ID, 'artist' => $artist, 'themes' => $themes, 'moods' => $moods];
    }
    return $catalog;
}

function ag_discovery_choices($catalog) {
    $choices = ['feeling' => [], 'theme' => []];
    foreach ($catalog as $song) {
        foreach ($song['moods'] as $key => $label) { $choices['feeling'][$key] = $label; }
        foreach ($song['themes'] as $key => $label) { $choices['theme'][$key] = $label; }
    }
    foreach ($choices as &$values) { natcasesort($values); }
    return $choices;
}

function ag_discovery_trail($raw, $catalog, $current) {
    if (!is_string($raw) || strlen($raw) > 160) { return []; }
    $ids = [];
    foreach (explode(',', $raw) as $id) {
        if (preg_match('/^[1-9][0-9]{0,9}$/D', $id) && isset($catalog[(int)$id]) && (int)$id !== $current) { $ids[] = (int)$id; }
    }
    return array_slice(array_values(array_unique($ids)), -8);
}

function ag_discovery_defaults($current, $catalog, $trail = []) {
    foreach (['themes' => 'theme', 'moods' => 'feeling'] as $field => $filter) {
        foreach ($catalog as $song) {
            if ($song['id'] !== $current['id'] && !in_array($song['id'], $trail, true) && array_intersect_key($song[$field], $current[$field])) { return ['feeling' => $filter === 'feeling' ? 'same' : 'any', 'theme' => $filter === 'theme' ? 'same' : 'any']; }
        }
    }
    return ['feeling' => 'any', 'theme' => 'any'];
}

function ag_discovery_recommend($current, $catalog, $preferences, $trail = [], $limit = 3) {
    $limit = max(1, min(3, absint($limit)));
    $curated = array_filter(array_map('absint', explode(',', (string)get_post_meta($current['id'], '_ag_related', true))));
    $related_artists = array_filter(array_map('absint', explode(',', (string)get_post_meta($current['artist'], '_ag_related', true))));
    $rows = [];
    foreach ($catalog as $song) {
        if ($song['id'] === $current['id'] || in_array($song['id'], $trail, true)) { continue; }
        $matches = true;
        foreach (['feeling' => 'moods', 'theme' => 'themes'] as $filter => $field) {
            $choice = $preferences[$filter];
            if ($choice === 'same' && !array_intersect_key($song[$field], $current[$field])) { $matches = false; }
            elseif ($choice !== 'same' && $choice !== 'any' && !isset($song[$field][$choice])) { $matches = false; }
        }
        if (!$matches) { continue; }
        $themes = array_intersect_key($song['themes'], $current['themes']);
        $moods = array_intersect_key($song['moods'], $current['moods']);
        $different = $song['artist'] !== $current['artist'];
        $is_curated = in_array($song['id'], $curated, true);
        $related_artist = in_array($song['artist'], $related_artists, true);
        $score = ($themes ? 40 : 0) + ($moods ? 25 : 0) + ($is_curated ? 35 : 0) + ($related_artist ? 30 : 0) + ($different ? 12 : 0);
        if ($preferences['theme'] !== 'any') { $labels = $preferences['theme'] === 'same' ? $themes : [$song['themes'][$preferences['theme']]]; $reason = 'Afterglow theme: ' . implode(', ', array_slice(array_values($labels), 0, 2)) . '.'; }
        elseif ($preferences['feeling'] !== 'any') { $labels = $preferences['feeling'] === 'same' ? $moods : [$song['moods'][$preferences['feeling']]]; $reason = 'Afterglow feeling: ' . implode(', ', array_slice(array_values($labels), 0, 2)) . '.'; }
        elseif ($themes) { $reason = 'Shares an Afterglow theme: ' . reset($themes) . '.'; }
        elseif ($moods) { $reason = 'Shares an Afterglow feeling: ' . reset($moods) . '.'; }
        elseif ($is_curated) { $reason = 'Linked by Afterglow’s song recommendations.'; }
        elseif ($related_artist) { $reason = 'From an artist linked in our editorial recommendations.'; }
        else { $reason = 'Another ' . ($different ? 'artist' : 'song') . ' in Afterglow’s curated collection.'; }
        $rows[] = $song + ['reason' => $reason, 'different_artist' => $different, 'score' => $score, 'tie' => sprintf('%u', crc32($current['id'] . ':' . $song['id']))];
    }
    usort($rows, function ($a, $b) { return ($b['score'] <=> $a['score']) ?: ($b['tie'] <=> $a['tie']) ?: ($a['id'] <=> $b['id']); });
    // Prefer a different artist when there is a matching option; diversify the remaining cards too.
    $different = array_values(array_filter($rows, function ($r) use ($current) { return $r['artist'] !== $current['artist']; }));
    if ($different && $different[0]['score'] >= 25) { $first = $different[0]; $rows = array_merge([$first], array_filter($rows, function ($r) use ($first) { return $r['id'] !== $first['id']; })); }
    $selected = []; $used_artists = [];
    foreach ($rows as $row) {
        if (isset($used_artists[$row['artist']])) { continue; }
        $selected[$row['id']] = $row; $used_artists[$row['artist']] = true;
        if (count($selected) >= $limit) { return array_values($selected); }
    }
    foreach ($rows as $row) { $selected[$row['id']] = $row; if (count($selected) >= $limit) { break; } }
    return array_values($selected);
}

function ag_discovery_context($id, $input = []) {
    if (get_post_type($id) !== 'song' || get_post_status($id) !== 'publish') { return null; }
    $catalog = ag_discovery_catalog();
    $current = $catalog[$id] ?? ['id' => $id, 'artist' => absint(get_post_meta($id, '_ag_artist_id', true)), 'themes' => [], 'moods' => []];
    $trail = ag_discovery_trail($input['ag_trail'] ?? '', $catalog, $id);
    $choices = ag_discovery_choices($catalog); $preferences = ag_discovery_defaults($current, $catalog, $trail); $invalid = false;
    foreach (['feeling' => 'moods', 'theme' => 'themes'] as $filter => $field) {
        if (!isset($input['ag_' . $filter])) { continue; }
        $raw = $input['ag_' . $filter];
        if (!is_string($raw) || strlen($raw) > 160 || ($raw !== 'any' && !($raw === 'same' && $current[$field]) && !isset($choices[$filter][$raw]))) { $invalid = true; continue; }
        $preferences[$filter] = $raw;
    }
    return ['current' => $current, 'preferences' => $preferences, 'choices' => $choices, 'trail' => $trail, 'invalid' => $invalid, 'results' => ag_discovery_recommend($current, $catalog, $preferences, $trail)];
}
