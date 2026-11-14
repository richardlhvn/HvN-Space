<?php
/** Configured identity and explicit, protected first-party branding migrations. */
defined('ABSPATH') || exit;

function ag_site_name() {
    return trim(wp_strip_all_tags(get_bloginfo('name'))) ?: 'tunetofond';
}

function ag_public_brand_text($text, $html = false) {
    return ag_replace_brand_text($text, $html ? esc_html(ag_site_name()) : ag_site_name());
}

function ag_replace_brand_text($text, $name = 'tunetofond') {
    return strtr($text, ag_brand_reference_map($name));
}

function ag_brand_reference_map($name = 'tunetofond') {
    // Only known first-party self-references, never a song title, workbook
    // filename, URL, source identity or generic occurrence of the old name.
    return [
        'For Afterglow,' => 'For ' . $name . ',',
        'For Afterglow listeners,' => 'For ' . $name . ' listeners,',
        'On Afterglow,' => 'On ' . $name . ',',
        'Afterglow’s emotional-pop categorization' => $name . '’s emotional-pop categorization',
        'Afterglow groups her' => $name . ' groups her',
        "Afterglow editor's pick" => $name . " editor's pick",
        'an Afterglow editorial pairing' => 'a ' . $name . ' editorial pairing',
        'Afterglow editorial' => $name . ' editorial',
        'Afterglow reads it' => $name . ' reads it',
        'an Afterglow theme' => 'a ' . $name . ' theme',
        'A useful Afterglow entry' => 'A useful ' . $name . ' entry',
        'give Afterglow an example' => 'give ' . $name . ' an example',
        'for Afterglow’s song-meaning pages' => 'for ' . $name . '’s song-meaning pages',
        'the Afterglow editor;' => 'the ' . $name . ' editor;',
        'Afterglow’s emotional-pop classification' => $name . '’s emotional-pop classification',
        'Afterglow’s abstract image placeholder' => $name . '’s abstract image placeholder',
        'not republished in Afterglow.' => 'not republished in ' . $name . '.',
        'editorial Afterglow taxonomy' => 'editorial ' . $name . ' taxonomy',
        'the editorial taxonomy of Afterglow.' => 'the editorial taxonomy of ' . $name . '.',
        'licensed for Afterglow;' => 'licensed for ' . $name . ';',
    ];
}

function ag_branding_normalize($value) {
    if (is_string($value)) { return ag_replace_brand_text($value); }
    if (is_array($value)) { foreach ($value as &$child) { $child = ag_branding_normalize($child); } unset($child); }
    return $value;
}

function ag_migrate_research_branding($dry_run = true) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    $original = json_decode(file_get_contents(__DIR__ . '/data/reviewed-2026-11-14.json'), true, 512, JSON_THROW_ON_ERROR);
    $current = ag_reviewed_data();
    $expected = ag_branding_normalize($original['packs']);
    if (isset($current['tag_adjustment'])) { $expected = ag_tag_normalize_packs($expected); }
    if ($current['packs'] !== $expected || $current['provenance'] !== $original['provenance']) {
        return new WP_Error('branding_source_mismatch', 'The dataset must match the cleaned source with only the owner-authorized branding and tag adjustments.');
    }
    $has_catalog = false;
    foreach ($current['packs'] as $slug => $pack) { if (ag_reviewed_find($slug)) { $has_catalog = true; break; } }
    if (!$has_catalog) { return ['updated'=>[], 'skipped'=>[], 'conflicts'=>[], 'not_installed'=>true]; }
    $plan = ag_import_reviewed(true, false, $current);
    if (is_wp_error($plan)) { return $plan; }
    if ($plan['created']) { return new WP_Error('canonical_required', 'The cleaned ten/128 catalog is incomplete. Apply its separate protected canonical update before the branding migration. No research records were created.'); }
    foreach ($plan['updated'] as $key) {
        [$slug, $record_key] = explode('/', $key, 2);
        $old = $record_key === 'profile' ? $original['packs'][$slug]['artist'] : array_column($original['packs'][$slug]['songs'], null, 'slug')[$record_key];
        if (isset($current['tag_adjustment'])) { $old = ag_tag_normalize_record($old); }
        $id = $plan['ids'][$key];
        if (get_post_meta($id, '_ag_handoff_record', true) !== $old || get_post_meta($id, '_ag_source_registry', true) !== $original['packs'][$slug]['sources']) {
            return new WP_Error('canonical_required', 'Existing research is not the current canonical baseline: ' . $key . '. Run the protected tag-sync migration first if tags have not been updated; otherwise review the separate canonical update. Branding never imports unrelated factual or tag changes.');
        }
    }
    // Reuse the transactional importer and its edit-conflict/baseline protection.
    // Only existing, baseline-matching records can receive this brand-only data.
    return $dry_run ? $plan : ag_import_reviewed(false, false, $current);
}

function ag_public_brand_html($html) {
    $parts = wp_html_split($html); $protected = [];
    foreach ($parts as &$part) {
        if (preg_match('~^<(a|blockquote|q|code|pre|script|style)\b~i', $part, $match)) {
            $tag = strtolower($match[1]); $protected[$tag] = ($protected[$tag] ?? 0) + 1;
        } elseif (preg_match('~^</(a|blockquote|q|code|pre|script|style)\b~i', $part, $match)) {
            $tag = strtolower($match[1]); $protected[$tag] = max(0, ($protected[$tag] ?? 0) - 1);
        } elseif ($part !== '' && $part[0] !== '<' && !array_sum($protected)) {
            $part = ag_public_brand_text($part, true);
        }
    }
    unset($part);
    return implode('', $parts);
}

function ag_branding_plan() {
    $changes = []; $manual = [];
    $name = get_option('blogname');
    if ($name === 'Afterglow') {
        $changes[] = ['type' => 'option', 'field' => 'blogname', 'before' => $name, 'after' => 'tunetofond'];
    } elseif ($name !== 'tunetofond') {
        $manual[] = 'The site title has been customized. Set it to tunetofond in Settings → General if that is intended.';
    }
    // Only known first-party phrases on these exact site pages. Never visit
    // artist/song posts, quotations, source registries, URLs or license records.
    $phrases = [
        'about' => ['Afterglow is an independent discovery project' => 'tunetofond is an independent discovery project'],
        'copyright' => ['Afterglow does not host music' => 'tunetofond does not host music'],
        'media-credits' => [
            'Afterglow is an independent music discovery publication' => 'tunetofond is an independent music discovery publication',
            'original Afterglow designs' => 'original tunetofond designs',
            'Afterglow does not download or self-host' => 'tunetofond does not download or self-host',
        ],
    ];
    foreach ($phrases as $slug => $map) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if (!$page || $page->post_status === 'trash') { continue; }
        $after = strtr($page->post_content, $map);
        if ($after !== $page->post_content) {
            $changes[] = ['type' => 'page', 'id' => $page->ID, 'slug' => $slug, 'field' => 'post_content', 'before' => $page->post_content, 'after' => $after];
        }
    }
    // Unrecognized prose is reported, not rewritten. No global replacement.
    foreach (['home', 'about', 'contact', 'privacy', 'copyright', 'media-credits', 'journal', 'saved'] as $slug) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if (!$page || $page->post_status === 'trash') { continue; }
        $content = $page->post_content;
        foreach ($changes as $change) { if (($change['id'] ?? 0) === $page->ID) { $content = $change['after']; } }
        foreach ([$content, $page->post_title, $page->post_excerpt, get_post_meta($page->ID, '_ag_meta_title', true), get_post_meta($page->ID, '_ag_meta_description', true)] as $value) {
            // The retained localStorage key is a compatibility identifier.
            if (preg_match('/afterglow/i', str_replace('afterglow:saved:v1', '', $value))) {
                $manual[] = 'Review customized branding on page: ' . $slug;
                break;
            }
        }
    }
    if (stripos(get_option('blogdescription'), 'afterglow') !== false) { $manual[] = 'Review the customized site tagline in Settings → General.'; }
    foreach (['ag_tagline', 'ag_eyebrow'] as $mod) {
        if (stripos((string)get_theme_mod($mod, ''), 'afterglow') !== false) { $manual[] = 'Review the customized identity setting: ' . $mod; }
    }
    return ['changes' => $changes, 'manual_review' => $manual];
}

function ag_migrate_branding($dry_run = true) {
    if (!current_user_can('manage_options')) { return new WP_Error('forbidden', 'Administrator access is required.'); }
    $research = ag_migrate_research_branding(true);
    if (is_wp_error($research)) { return $research; }
    $plan = ag_branding_plan();
    if (!$dry_run) {
        $research = ag_migrate_research_branding(false);
        if (is_wp_error($research)) { return $research; }
        foreach ($plan['changes'] as $change) {
            if ($change['type'] === 'option') {
                update_option($change['field'], $change['after']);
            } else {
                $result = wp_update_post(wp_slash(['ID' => $change['id'], $change['field'] => $change['after']]), true);
                if (is_wp_error($result)) { return $result; }
            }
        }
    }
    return ['dry_run' => $dry_run, 'changed' => count($plan['changes']) + count($research['updated']), 'research' => $research, 'research_substitutions' => $research['updated'] ? ag_brand_reference_map() : []] + $plan;
}

add_action('admin_menu', function () {
    add_management_page('Site branding', 'Site branding', 'manage_options', 'ag-branding', function () {
        $plan = ag_migrate_branding(true);
        echo '<div class="wrap"><h1>' . esc_html(ag_site_name()) . ' · Site branding</h1><p>Preview the protected rename to tunetofond. Only identified brand references change in first-party pages and the approved ten/128 research. Other wording, URLs, license credits and compatibility identifiers are retained. Later editor changes are preserved as conflicts. Back up your database before applying.</p>';
        if (is_wp_error($plan)) { echo '<p>' . esc_html($plan->get_error_message()) . '</p></div>'; return; }
        echo '<p>' . $plan['changed'] . ' pending changes.</p><p>Approved research records to update: ' . count($plan['research']['updated']) . '.</p>';
        foreach ($plan['research_substitutions'] as $before => $after) { echo '<p><code>' . esc_html($before) . '</code> → <code>' . esc_html($after) . '</code></p>'; }
        foreach ($plan['research']['updated'] as $key) { echo '<p>' . esc_html($key) . '</p>'; }
        foreach ($plan['research']['conflicts'] as $key) { echo '<p>Preserved editor conflict: ' . esc_html($key) . '</p>'; }
        foreach ($plan['manual_review'] as $message) { echo '<p><strong>' . esc_html($message) . '</strong></p>'; }
        foreach ($plan['changes'] as $change) {
            echo '<h2>' . esc_html(($change['slug'] ?? 'Settings') . ' · ' . $change['field']) . '</h2><p>Before</p><pre style="white-space:pre-wrap">' . esc_html($change['before']) . '</pre><p>After</p><pre style="white-space:pre-wrap">' . esc_html($change['after']) . '</pre>';
        }
        if ($plan['changed']) {
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ag_migrate_branding">';
            wp_nonce_field('ag_migrate_branding'); submit_button('Apply protected tunetofond branding'); echo '</form>';
        }
        echo '</div>';
    });
});
add_action('admin_post_ag_migrate_branding', function () {
    if (!current_user_can('manage_options') || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Not permitted.', '', ['response' => 403]); }
    check_admin_referer('ag_migrate_branding');
    $result = ag_migrate_branding(false);
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message())); }
    wp_safe_redirect(admin_url('tools.php?page=ag-branding')); exit;
});
