<?php
require __DIR__ . '/fingerprint.php';
global $wpdb;
$source = rtrim(get_option('home'), '/'); $target = 'https://tunetofond.com';
if ($source !== 'http://localhost:8080' || (int)get_option('blog_public') !== 0) { throw new RuntimeException('Expected noindex cloud source required.'); }
$tables = array_values(array_filter($wpdb->get_col('SHOW TABLES'), function ($name) use ($wpdb) { return str_starts_with($name, $wpdb->prefix); }));
foreach ($tables as $table) { if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $table)) { throw new RuntimeException('Unexpected database table name.'); } }
WP_CLI::line(wp_json_encode(['schema'=>'tunetofond.hosting-migration.v1','source_url'=>$source,'target_url'=>$target,'prefix'=>$wpdb->prefix,'tables'=>$tables,'wordpress'=>get_bloginfo('version'),'source_user_logins'=>array_column($wpdb->get_results("SELECT user_login FROM {$wpdb->users}",ARRAY_A),'user_login'),'expected'=>tf_hosting_fingerprint($source,$target)], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
