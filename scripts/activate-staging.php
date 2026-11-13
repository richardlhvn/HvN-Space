<?php
// Activate the existing site architecture without importing any demo artists.
$loopback_hosts = ['localhost', '127.0.0.1', '[::1]'];
$local_urls = in_array(wp_parse_url(home_url(), PHP_URL_HOST), $loopback_hosts, true)
    && in_array(wp_parse_url(getenv('AFTERGLOW_SITE_URL') ?: '', PHP_URL_HOST), $loopback_hosts, true);
if (!$local_urls && !in_array(wp_get_environment_type(), ['local', 'development', 'staging'], true)) { throw new RuntimeException('This initializer is for local/staging environments only.'); }
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$result = activate_plugin('afterglow-core/afterglow-core.php');
if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
if (get_stylesheet() !== 'afterglow') { switch_theme('afterglow'); }
global $wpdb;
if (!(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ('artist','song')")) {
    update_option('permalink_structure', '/%postname%/');
    update_option('blogdescription', 'A little closer to the music.');
    update_option('timezone_string', 'UTC');
}
update_option('blog_public', 0);
ag_register_content();
wp_set_current_user(get_user_by('login', 'editor')->ID);
WP_CLI::success('Staging architecture activated; no artist/song data seeded.');
