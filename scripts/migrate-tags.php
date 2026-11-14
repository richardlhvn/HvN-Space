<?php
// wp --user=editor eval-file /scripts/migrate-tags.php [apply]
$result = ag_migrate_tags(($args[0] ?? '') !== 'apply');
if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
WP_CLI::line(wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
if ($result['conflicts']) { WP_CLI::error('Tag sync stopped without changes. Resolve the reported mappings before applying.'); }
