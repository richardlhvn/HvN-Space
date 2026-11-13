<?php
global $wpdb;
if ($wpdb->prefix !== 'ag_canonical_setup_test_') { throw new RuntimeException('Disposable namespace required.'); }
$checks = 0;
$assert = function ($ok, $message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } $checks++; };
try {
    wp_set_current_user(get_user_by('login', 'editor')->ID);
    $assert((int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='artist'") === 10, 'Only the ten approved artists initialized.');
    $assert((int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='song'") === 128, 'Only the 128 approved songs initialized.');
    $scope = ag_public_catalog_scope();
    $assert($scope['artists'] === 10 && $scope['songs'] === 128 && $scope['hidden_records'] === 0, 'Fresh scope contains no historical imports.');
    $again = ag_import_reviewed(true);
    $assert(!is_wp_error($again) && count($again['skipped']) === 138 && !$again['conflicts'] && !$again['updated'] && !$again['created'], 'Repeated setup leaves the catalog unchanged.');
    foreach (ag_reviewed_data()['packs'] as $slug => $pack) {
        $artist = ag_reviewed_find($slug);
        $placeholder = in_array($slug, ['caleb-hearn','chelsea-cutler','elina','mokita'], true);
        $assert($placeholder ? !get_post_thumbnail_id($artist) : (bool)ag_media_credit($artist), 'Expected permitted image or intentional placeholder: ' . $slug);
    }
    foreach (['home','journal','about','privacy','contact','copyright','media-credits'] as $slug) {
        $assert((bool)get_page_by_path($slug, OBJECT, 'page'), 'Site page initialized: ' . $slug);
    }
    $assert((int)get_option('page_on_front') === get_page_by_path('home')->ID && (int)get_option('page_for_posts') === get_page_by_path('journal')->ID, 'Homepage and journal use the existing architecture.');
    $assert(!get_option('blog_public'), 'Fresh staging is noindex.');
    WP_CLI::success($checks . ' canonical setup and repeatability checks passed.');
} finally {
    foreach ($wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment'") as $id) { wp_delete_attachment((int)$id, true); }
    foreach ($wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($wpdb->prefix) . '%')) as $table) {
        if (!preg_match('/\Aag_canonical_setup_test_[a-z0-9_]+\z/', $table)) { throw new RuntimeException('Unexpected test table.'); }
        $wpdb->query('DROP TABLE `' . $table . '`');
    }
}
