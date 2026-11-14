<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_branding_test_') { throw new RuntimeException('Disposable branding namespace required.'); }
$checks = 0;
$assert = function ($ok, $message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } $checks++; };
try {
    wp_set_current_user(get_user_by('login', 'editor')->ID);
    update_option('blogname', 'Afterglow');
    $body = '<p>Afterglow is an independent discovery project for music.</p><p>Owner’s later wording stays. Historical source: Afterglow_Master_Worksheet.xlsx.</p>';
    $id = wp_insert_post(['post_type'=>'page','post_name'=>'about','post_title'=>'My original heading','post_content'=>$body,'post_status'=>'publish']);
    $song = wp_insert_post(['post_type'=>'song','post_title'=>'Research fixture','post_content'=>'Afterglow interpretation — owner-approved narrative','post_status'=>'draft']);
    update_post_meta($song, '_ag_about_song', 'Exact approved Afterglow wording');
    update_post_meta($song, '_ag_spotify', 'https://open.spotify.com/track/0GMs3jq70wmCo12pavj4st');
    $research = serialize([get_post($song),get_post_meta($song)]);
    $options = [get_option('home'),get_option('siteurl'),get_option('blog_public'),get_option('permalink_structure'),ag_public_catalog_scope()];
    $plan = ag_migrate_branding(true);
    $assert($plan['changed'] === 2 && get_option('blogname') === 'Afterglow' && get_post_field('post_content',$id) === $body, 'Dry run reports both changes without writing.');
    $subscriber = wp_insert_user(['user_login'=>'branding-subscriber','user_pass'=>wp_generate_password(),'role'=>'subscriber']);
    wp_set_current_user($subscriber);
    $assert(is_wp_error(ag_migrate_branding(false)), 'A subscriber cannot migrate branding.');
    $assert(get_option('blogname') === 'Afterglow', 'Unauthorized request leaves identity unchanged.');
    wp_set_current_user(get_user_by('login','editor')->ID);
    $apply = ag_migrate_branding(false);
    $assert($apply['changed'] === 2 && get_option('blogname') === 'tunetofond', 'Explicit apply renames the configured identity.');
    $assert(get_post_field('post_content',$id) === str_replace('Afterglow is an independent discovery project','tunetofond is an independent discovery project',$body), 'Only the known first-party phrase changes; all owner wording and original workbook identity stay.');
    $assert(get_post_field('post_title',$id) === 'My original heading' && get_post_status($id) === 'publish', 'Page identity and publication status remain.');
    $assert(serialize([get_post($song),get_post_meta($song)]) === $research, 'Research content, stored attribution and Spotify mapping stay byte-identical.');
    $assert([get_option('home'),get_option('siteurl'),get_option('blog_public'),get_option('permalink_structure'),ag_public_catalog_scope()] === $options, 'URLs, indexing, permalinks and catalog selection stay unchanged.');
    $assert(ag_migrate_branding(false)['changed'] === 0, 'Repeat apply makes zero changes.');
    $assert(count(ag_branding_plan()['manual_review']) === 1, 'Unknown historical wording is reported for manual review without rewriting.');
    $assert(ag_public_brand_text('For Afterglow, this is an Afterglow theme.') === 'For tunetofond, this is a tunetofond theme.', 'Only first-party public brand references are adapted.');
    $historical = 'Afterglow_Master_Worksheet.xlsx; Afterglow — a song title; https://example.test/Afterglow';
    $assert(ag_public_brand_text($historical) === $historical, 'Original filenames, titles and URLs are not blindly replaced.');
    $html = '<p>For Afterglow, <em>formatting stays</em>.</p><a href="https://example.test/Afterglow">For Afterglow, source title</a><blockquote>For Afterglow, quoted words</blockquote>';
    $expected = str_replace('<p>For Afterglow,', '<p>For tunetofond,', $html);
    $assert(ag_public_brand_html($html) === $expected, 'Public HTML preserves source links, quotations and inline formatting.');
    $assert(strpos(get_post_field('post_content',$id), 'Afterglow_Master_Worksheet.xlsx') !== false && serialize([get_post($song),get_post_meta($song)]) === $research, 'Public substitutions never write back to protected research.');
    update_option('blogname','Owner custom identity');
    $assert(ag_migrate_branding(false)['changed'] === 0 && get_option('blogname') === 'Owner custom identity', 'Unexpected customized identity is preserved for explicit review.');
    $assert(ag_site_name() === 'Owner custom identity', 'Rendered labels follow the configured identity.');
    WP_CLI::success($checks . ' branding migration, permissions, idempotence and preservation checks passed.');
} finally {
    foreach ($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($wpdb->prefix) . '%')) as $table) {
        if (!preg_match('/\Aag_branding_test_[a-z0-9_]+\z/', $table)) { throw new RuntimeException('Unexpected test table.'); }
        $wpdb->query('DROP TABLE `' . $table . '`');
    }
}
