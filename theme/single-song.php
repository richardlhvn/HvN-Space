<?php
get_header();
while (have_posts()) : the_post();
    $id = get_the_ID(); $artist = absint(ag_meta('artist_id'));
    $sources = ag_source_links($id);
    $story = ag_song_story_sections(apply_filters('the_content', get_the_content()), (bool) ag_meta('handoff_key'));
    $about = function_exists('ag_about_song') ? ag_about_song($id, $story['listening']) : ['text' => '', 'sources' => [], 'attribution' => ''];
    $labels = ['artist_statement' => 'Artist statement · paraphrased', 'third_party_reporting' => 'Reported song background', 'afterglow_editorial' => 'Afterglow interpretation · not an artist statement', 'mixed' => 'Multiple perspectives · attributed in the text'];
    $record = get_post_meta($id, '_ag_handoff_record', true);
    $custom_notes = !is_array($record) || !isset($record['editorial_notes_source_ids']) || get_post_field('post_content', $id) !== ag_handoff_content(['research_date' => ag_meta('handoff_date')], $record, false);
    $discovery = function_exists('ag_discovery_context') ? ag_discovery_context($id, wp_unslash($_GET)) : null;
?>
<main id="main" class="shell profile-main song-profile">
    <a class="back-link" href="<?php echo esc_url(ag_url('songs')); ?>">← All songs</a>
    <div class="profile-layout song-discovery-hero">
        <div class="profile-art-column"><?php ag_song_listening_panel($id); ?></div>
        <div class="profile-copy">
            <div class="song-introduction">
            <p class="eyebrow">BETWEEN THE NOTES / SONG EXPLORER</p>
            <div class="profile-title"><h1><?php the_title(); ?></h1><?php ag_save_button($id, 'large-save'); ?></div>
            <?php if ($artist && get_post_status($artist) === 'publish') : ?><a class="artist-byline" href="<?php echo esc_url(get_permalink($artist)); ?>">by <?php echo esc_html(get_the_title($artist)); ?> <?php echo ag_icon('diagonal'); ?></a><?php endif; ?>
            <div class="tag-list"><?php foreach (['genre', 'mood'] as $tax) { $terms = get_the_terms($id, $tax); foreach ($terms && !is_wp_error($terms) ? $terms : [] as $term) { echo '<a class="tag" href="' . esc_url(ag_url('songs') . '?filter_' . $tax . '=' . $term->slug) . '">' . esc_html($term->name) . '</a>'; } } ?></div>
            <?php if (!$about['text'] || ag_about_text(get_the_excerpt()) !== ag_about_text($about['text'])) : ?><p class="profile-intro"><?php echo esc_html(get_post_field('post_excerpt', $id) !== '' || !ag_meta('handoff_key') ? get_the_excerpt() : 'A recording from ' . (ag_artist_name($id) ?: 'this artist') . '’s curated selection. Explore the sources and continue discovering.'); ?></p><?php endif; ?>
            </div>
            <?php if ($about['text']) : ?>
            <section class="song-insights song-about" aria-labelledby="about-song-heading">
                <h2 id="about-song-heading">About the Song</h2>
                <p class="about-attribution<?php echo $about['attribution'] === 'artist_statement' ? ' attribution-label' : ''; ?>"><?php echo esc_html($labels[$about['attribution']]); ?></p>
                <div class="prose"><?php
                    if (($about['origin'] ?? '') === 'legacy_editorial') {
                        // Preserve existing WordPress formatting and inline
                        // citations instead of flattening the editor's work.
                        echo $story['listening'];
                    } else {
                        $paragraphs = array_unique(array_filter(array_map('trim', preg_split('/\n\s*\n/', $about['text']))));
                        foreach ($paragraphs as $paragraph) { echo '<p>' . esc_html($paragraph) . '</p>'; }
                    }
                ?></div>
                <?php if ($about['sources']) : ?><ul class="about-source-links" aria-label="Sources for About the Song"><?php foreach ($about['sources'] as $source) : ?><li><a href="<?php echo esc_url($source['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($source['label']); ?> ↗</a></li><?php endforeach; ?></ul><?php endif; ?>
            </section>
            <?php endif; ?>
            <div class="song-discovery-actions">
                <?php if ($discovery) : ?><a class="button button-dark" href="#discover-more" data-discovery-open>Find another song like this <?php echo ag_icon('arrow'); ?></a><?php endif; ?>
                <?php if ($artist && get_post_status($artist) === 'publish') : ?><a class="text-link" href="<?php echo esc_url(get_permalink($artist) . '#artist-songs'); ?>">More from <?php echo esc_html(get_the_title($artist)); ?> <?php echo ag_icon('diagonal'); ?></a><?php endif; ?>
            </div>
            <?php if (ag_meta('demo')) : ?><a class="song-review-status" href="#song-notes"><?php echo esc_html(ag_research_label($id)); ?> · Research details</a><?php endif; ?>
        </div>
    </div>
    <?php ag_discovery_panel($discovery); ?>
    <section class="song-story" id="song-notes" aria-labelledby="research-heading">
        <p class="eyebrow">BEHIND THE NOTES</p>
        <h2 id="research-heading">Research &amp; sources</h2>
        <?php ag_demo_notice($id); ?>
        <?php if ($custom_notes && $story['listening'] && ($about['origin'] ?? '') !== 'legacy_editorial' && ag_about_text($story['listening']) !== ag_about_text($about['text'])) : ?><div class="prose"><h3>Editorial notes</h3><?php echo $story['listening']; ?></div><?php endif; ?>
        <?php if ($story['research']) : ?><div class="prose"><?php echo $story['research']; ?></div><?php endif; ?>
        <?php if (ag_meta('credits')) : ?><p class="fine-print recording-credits"><strong>Recording artists:</strong> <?php echo ag_recording_credits_html($id); ?></p><?php endif; ?>
        <?php if (ag_meta('themes')) : ?><div class="theme-note"><strong>Themes we’re exploring</strong><p><?php echo esc_html(ag_meta('themes')); ?></p></div><?php endif; ?>
        <section class="profile-section" id="song-sources" aria-label="Sources and further reading">
            <?php foreach ($about['sources'] as $source) { if (!in_array($source['url'], array_column($sources, 1), true)) { $sources[] = [$source['label'], $source['url']]; } } ?>
            <?php if ($sources) : ?><h3>Sources &amp; further reading</h3><ul class="source-list"><?php foreach ($sources as $link) { echo '<li><a href="' . esc_url($link[1]) . '" target="_blank" rel="noopener noreferrer">' . esc_html($link[0]) . ' ↗</a></li>'; } ?></ul><?php endif; ?>
            <?php if (ag_meta('release') && $sources) : ?><p class="fine-print">Release date: <?php echo esc_html(ag_meta('release')); ?> · See sources above.</p><?php endif; ?>
        </section>
    </section>
</main>
<?php endwhile; get_footer(); ?>
