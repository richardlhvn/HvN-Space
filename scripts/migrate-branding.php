<?php
// Dry run by default. Back up first; apply only the protected first-party rename.
// wp --user=editor eval-file /scripts/migrate-branding.php [apply]
$result = ag_migrate_branding(($args[0] ?? '') !== 'apply');
if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
WP_CLI::line(wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
