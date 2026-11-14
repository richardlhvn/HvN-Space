<?php
/** Configured display identity; compatibility keys and research stay untouched. */
defined('ABSPATH') || exit;

function ag_site_name() {
    return trim(wp_strip_all_tags(get_bloginfo('name'))) ?: 'tunetofond';
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
    $plan = ag_branding_plan();
    if (!$dry_run) {
        foreach ($plan['changes'] as $change) {
            if ($change['type'] === 'option') {
                update_option($change['field'], $change['after']);
            } else {
                $result = wp_update_post(wp_slash(['ID' => $change['id'], $change['field'] => $change['after']]), true);
                if (is_wp_error($result)) { return $result; }
            }
        }
    }
    return ['dry_run' => $dry_run, 'changed' => count($plan['changes'])] + $plan;
}

add_action('admin_menu', function () {
    add_management_page('Site branding', 'Site branding', 'manage_options', 'ag-branding', function () {
        $plan = ag_branding_plan();
        echo '<div class="wrap"><h1>' . esc_html(ag_site_name()) . ' · Site branding</h1><p>Preview the protected rename to tunetofond. Only the old default site title and the listed first-party phrases change. Artist/song research, custom wording, URLs, license credits and compatibility identifiers are retained. Back up your database before applying.</p>';
        echo '<p>' . count($plan['changes']) . ' pending changes.</p>';
        foreach ($plan['manual_review'] as $message) { echo '<p><strong>' . esc_html($message) . '</strong></p>'; }
        foreach ($plan['changes'] as $change) {
            echo '<h2>' . esc_html(($change['slug'] ?? 'Settings') . ' · ' . $change['field']) . '</h2><p>Before</p><pre style="white-space:pre-wrap">' . esc_html($change['before']) . '</pre><p>After</p><pre style="white-space:pre-wrap">' . esc_html($change['after']) . '</pre>';
        }
        if ($plan['changes']) {
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
