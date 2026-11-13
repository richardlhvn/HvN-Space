<?php
// Read-only checks: no post, option, source, approval or indexing writes.
$checks = 0;
$assert = function ($ok, $message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } $checks++; };
foreach ([['2019-11-08', 'day', '8 November 2019'], ['2019-11', 'month', 'November 2019'], ['2019', 'year', '2019'], ['2024-02-29', 'day', '29 February 2024'], ['2025-02-29', 'day', ''], ['2019-11-08', 'year', ''], ['', 'day', ''], [[], 'day', '']] as [$date, $precision, $expected]) {
    $assert(ag_release_date_label($date, $precision) === $expected, 'Respect stored date precision and reject invalid dates.');
}
$input = '<p>A biography with <em>editor formatting</em>.</p><h2>A public heading</h2><p>Keep this paragraph.</p><p><strong>Basis:</strong> Internal source audit. No independent audio listening is claimed.</p><h2>Research notes</h2><ul><li>A retained open question.</li></ul><p><small>Based on the supplied research dated 2026-11-13.</small></p>';
$output = ag_public_research_content($input);
$assert(str_contains($output, '<em>editor formatting</em>') && str_contains($output, '<h2>A public heading</h2>') && str_contains($output, 'Keep this paragraph.'), 'Preserve public editor prose and formatting.');
$assert(!str_contains($output, 'Basis:') && !str_contains($output, 'Research notes') && !str_contains($output, 'Based on the supplied'), 'Omit generated QA sections from public prose.');
$label = 'PAPER — An article';
$assert(ag_public_source_label($label . ' · Editor-reviewed evidence; independent check pending') === $label, 'Keep article identity while omitting internal workflow suffix.');
$assert(ag_public_source_label($label) === $label, 'Existing source titles must remain unchanged.');
$id = ag_reviewed_find('shy-martin', ['slug' => 'make-us-never-happen', 'title' => 'Make us never happen', 'spotify_url' => null]);
wp_set_current_user(0);
$request = new WP_REST_Request('GET', '/wp/v2/song/' . $id);
$public = rest_do_request($request)->get_data();
$assert(!str_contains($public['content']['rendered'], 'Basis:') && !str_contains($public['content']['rendered'], 'Research notes') && str_contains($public['content']['rendered'], 'About the Song'), 'Public REST content must not expose retained QA fields.');
wp_set_current_user(get_user_by('login', 'editor')->ID);
$request->set_param('context', 'edit');
$editor = rest_do_request($request)->get_data();
$assert($editor['content']['raw'] === get_post_field('post_content', $id), 'Editor REST content retains the exact stored working research, including resolved-note omissions.');
global $post;
$previous_post = $post;
$post = get_post($id);
$feed = apply_filters('the_content_feed', $post->post_content);
$assert(!str_contains($feed, 'Basis:') && !str_contains($feed, 'Research notes') && !str_contains($feed, 'day precision') && str_contains($feed, '8 November 2019'), 'RSS must use public facts rather than expose working QA.');
$assert(!str_contains(apply_filters('the_excerpt_rss', 'Old working excerpt'), 'Old working excerpt'), 'RSS summary uses About rather than the older working excerpt.');
$post = $previous_post;
WP_CLI::success($checks . ' read-only public-details, date-precision and QA-label checks passed.');
