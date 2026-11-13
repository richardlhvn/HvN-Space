<?php
// Local/staging only. Existing editor conflicts remain protected.
require '/scripts/activate-staging.php';
$result = ag_import_reviewed(false, true);
if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
foreach (['created', 'updated', 'skipped', 'conflicts', 'rejected', 'failed'] as $field) { WP_CLI::log($field . ': ' . count($result[$field])); }
if ($result['conflicts']) { WP_CLI::warning('Editor conflicts were preserved; review them in Artists → Reviewed workbook enrichment.'); }
define('AG_SITE_PAGES_ONLY', true);
require WP_PLUGIN_DIR . '/afterglow-core/demo-content.php';
$media = ag_sync_canonical_photos();
if (is_wp_error($media)) { WP_CLI::error($media->get_error_message()); }
if ($media['preserved']) { WP_CLI::warning('Existing images or edited artist records were preserved; review media before launch.'); }
$scope = ag_enable_core_catalog();
if (is_wp_error($scope)) { WP_CLI::error($scope->get_error_message()); }
WP_CLI::success('Canonical ten-artist / 128-song staging catalog initialized; existing pages and hidden records retained.');
