<?php
// wp --user=editor eval-file /scripts/set-public-catalog.php [core|full]
// No argument previews the ten-artist scope without changing any setting.
$mode = $args[0] ?? 'preview';
if (!in_array($mode, ['preview', 'core', 'full'], true)) { WP_CLI::error('Use preview, core or full.'); }
$result = $mode === 'core' ? ag_enable_core_catalog() : ($mode === 'full' ? ag_restore_full_catalog_visibility() : ag_preview_core_catalog());
if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
WP_CLI::line(wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
