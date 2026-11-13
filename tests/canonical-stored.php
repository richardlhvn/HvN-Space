<?php
// Read-only reconciliation of the actual staging records against the canonical export.
$checks = 0;
$assert = function ($ok, $message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } $checks++; };
$data = ag_reviewed_data();
ag_reviewed_validate($data);
$keys = [];
foreach ($data['packs'] as $slug => $pack) {
    foreach (array_merge([$pack['artist']], $pack['songs']) as $record) {
        $song = isset($record['artist_slug']);
        $key = $slug . '/' . ($song ? $record['slug'] : 'profile');
        $id = ag_reviewed_find($slug, $song ? $record : null);
        $keys[] = $key;
        $assert($id && get_post_status($id) === 'publish', 'Approved record exists: ' . $key);
        $assert(get_post_meta($id, '_ag_handoff_record', true) === $record, 'Exact canonical record, including date and precision: ' . $key);
        $assert(get_post_meta($id, '_ag_source_registry', true) === $pack['sources'], 'Exact artist-scoped source registry: ' . $key);
        $assert(get_post_meta($id, '_ag_reviewed_provenance', true) === $data['provenance'], 'Exact canonical workbook provenance: ' . $key);
        if ($song) {
            $assert(get_post_meta($id, '_ag_about_song', true) === $record['about_song'], 'Exact approved About wording: ' . $key);
            $assert(get_post_meta($id, '_ag_spotify', true) === $record['spotify_url'], 'Exact owner-approved Spotify URL: ' . $key);
        }
    }
}
$scope = ag_public_catalog_scope();
$actual_keys = $scope['keys']; sort($keys); sort($actual_keys);
$assert($keys === $actual_keys && $scope['artists'] === 10 && $scope['songs'] === 128, 'Public scope matches all canonical composite keys exactly.');
$assert($scope['provenance_sha256'] === $data['provenance']['sha256'], 'Scope identifies the current workbook snapshot.');
$assert(!get_option('blog_public'), 'Staging remains noindex.');
WP_CLI::success($checks . ' read-only canonical staging reconciliation checks passed.');
