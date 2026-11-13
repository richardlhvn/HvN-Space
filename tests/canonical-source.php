<?php
// Read-only source-contract checks. Never substitute an older fixture as new data.
global $wpdb;
$checks = 0;
$assert = function ($ok, $message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } $checks++; };
$source = json_decode(file_get_contents(WP_PLUGIN_DIR . '/afterglow-core/data/canonical-research-source.json'), true, 512, JSON_THROW_ON_ERROR);
$assert($source['spreadsheet_id'] === '1CBJWklaLjymaRVw_wiPcmRMsumhfr_VUQheJeI7PvcI', 'Current owner-designated canonical Sheet ID.');
$assert($source['expected_artists'] === 10 && $source['expected_selections'] === 128, 'Exact approved scope.');
$assert($source['spotify_links']['approval'] === 'owner_checked_and_approved' && $source['spotify_links']['count'] === 128, 'Record owner approval without claiming fresh runtime playback.');
$assert(count($source['placeholder_artists']) === 4 && count($source['internal_qa_tabs']) === 4, 'Intentional placeholders and internal-only tabs.');
$count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}");
$lock = get_option('ag_handoff_lock');
wp_set_current_user(get_user_by('login', 'editor')->ID);
if ($source['prepared_snapshot'] === null) {
    foreach ([true, false] as $dry) {
        $r = ag_import_reviewed($dry, false);
        $assert(is_wp_error($r) && str_contains($r->get_error_message(), 'canonical workbook has changed'), 'Missing new data must block both preview and apply rather than reimport older rows.');
    }
} else {
    $data = ag_reviewed_data();
    ag_reviewed_validate($data);
    $assert($data['provenance']['spreadsheet_id'] === $source['spreadsheet_id'], 'Prepared data must identify the new source.');
}
$legacy = ag_apply_handoffs(true);
$assert(is_wp_error($legacy) && $legacy->get_error_code() === 'superseded_research', 'Historical all-catalog imports are superseded.');
$old_data = json_decode(file_get_contents(WP_PLUGIN_DIR . '/afterglow-core/data/' . $source['superseded_snapshot']), true, 512, JSON_THROW_ON_ERROR);
$explicit_old = ag_import_reviewed(true, false, $old_data);
$assert(is_wp_error($explicit_old) && str_contains($explicit_old->get_error_message(), 'provenance or scope'), 'Passing the old dataset explicitly must not bypass the canonical source guard.');
$assert((int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}") === $count && get_option('ag_handoff_lock') === $lock, 'Guard checks do not write records or leave import locks.');
WP_CLI::success($checks . ' canonical-source contract checks passed.');
