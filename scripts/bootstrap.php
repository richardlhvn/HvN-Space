<?php
// Bootstrap directly so WP-CLI never echoes an interactive password prompt.
$site_url = getenv('AFTERGLOW_SITE_URL');
if (!$site_url || !filter_var($site_url, FILTER_VALIDATE_URL)) { throw new RuntimeException('A valid local site URL is required.'); }
$_SERVER['HTTP_HOST'] = parse_url($site_url, PHP_URL_HOST);
define('WP_INSTALLING', true);
require '/var/www/html/wp-load.php';
if (!is_blog_installed()) {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    wp_install('Afterglow', 'editor', 'editor@example.test', false, '', getenv('AFTERGLOW_ADMIN_PASSWORD'));
    update_option('siteurl', $site_url);
    update_option('home', $site_url);
    WP_CLI::success('Local WordPress installed. Credentials remain in .env.');
} else {
    WP_CLI::success('Existing WordPress installation retained.');
}
