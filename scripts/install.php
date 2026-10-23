<?php
// Local-only setup. Production installation uses Hostinger's WordPress installer.
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$result = activate_plugin('afterglow-core/afterglow-core.php');
if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
switch_theme('afterglow');
update_option('permalink_structure', '/%postname%/');
update_option('blogdescription', 'A little closer to the music.');
update_option('blog_public', 0); // This local demo must never be indexed.
update_option('timezone_string', 'UTC');
// Plugin activation registers the types on subsequent requests; register for this CLI run too.
ag_register_content();
wp_set_current_user(get_user_by('login', 'editor')->ID);
require WP_PLUGIN_DIR . '/afterglow-core/demo-content.php';
WP_CLI::success('Theme, editorial tools, and demo content are ready.');
