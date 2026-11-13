<?php
// Run with: docker compose run --rm cli wp --user=editor eval-file /scripts/import-reviewed.php [apply] [show-new]
// No arguments means dry run. Back up before applying to an existing database.
$dry = ($args[0] ?? '') !== 'apply';
$result = ag_import_reviewed($dry, ($args[1] ?? '') === 'show-new');
if (is_wp_error($result)) {
    WP_CLI::error($result->get_error_code() . ': ' . $result->get_error_message());
}
WP_CLI::line(wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
