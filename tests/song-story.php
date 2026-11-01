<?php
// Pure rendering checks: no content, metadata, or options are written.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$checks = 0;
$assert = function ($condition, $message) use (&$checks) {
    if (!$condition) { throw new RuntimeException($message); }
    $checks++;
};
$content = '<h2>Listening notes</h2><p>Élan, regret &amp; hope — an editor’s updated reading.</p><p><strong>Basis:</strong> Original interview.</p><h2>Release information</h2><p>2024 (year precision)</p><h2>Research notes</h2><ul><li>Edition unconfirmed.</li></ul><p><small>Based on the supplied research dated 2026-10-31. Keep uncertain facts open.</small></p>';
$story = ag_song_story_sections($content, true);
$assert(str_contains($story['listening'], 'Élan, regret &amp; hope — an editor’s updated reading.'), 'Current editor text and UTF-8 punctuation must survive.');
$assert(!str_contains($story['listening'], 'Listening notes'), 'The template supplies the listening-notes heading once.');
foreach (['Original interview.', '2024 (year precision)', 'Edition unconfirmed.', 'Based on the supplied research dated'] as $text) {
    $assert(str_contains($story['research'], $text) && !str_contains($story['listening'], $text), 'Research details must be preserved only below: ' . $text);
}
$nested = '<!-- wp:group --><div class="wp-block-group"><h2>Personal listening notes</h2><p>Keep the <em>editor’s emphasis</em> and <a href="https://example.com/review">source link</a>.</p></div><!-- /wp:group -->';
$story = ag_song_story_sections($nested, true);
$assert(str_contains($story['listening'], '<em>editor’s emphasis</em>') && str_contains($story['listening'], 'href="https://example.com/review"'), 'Custom block markup must remain intact.');
$assert(str_contains($story['listening'], 'Personal listening notes') && $story['research'] === '', 'Unknown/custom headings remain in the listening section.');
$plain = '<p>Owner-written notes without importer headings.</p>';
$assert(ag_song_story_sections($plain, false) === ['listening' => $plain, 'research' => ''], 'Legacy/manual content stays intact.');
$assert(ag_song_story_sections('', true) === ['listening' => '', 'research' => ''], 'Empty content must render without a fabricated explanation.');
WP_CLI::success($checks . ' song-story rendering checks passed; no records changed.');
