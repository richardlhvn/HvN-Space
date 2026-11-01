<?php
get_header();
while (have_posts()) : the_post();
    $id = get_the_ID(); $artist = absint(ag_meta('artist_id'));
    $sources = ag_source_links($id);
    $sourced = ag_meta('meaning_type') === 'sourced' && $sources && ag_meta('artist_comment');
    $story = ag_song_story_sections(apply_filters('the_content', get_the_content()), (bool) ag_meta('handoff_key'));
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
            <p class="profile-intro"><?php echo esc_html(get_post_field('post_excerpt', $id) !== '' || !ag_meta('handoff_key') ? get_the_excerpt() : 'A recording from ' . (ag_artist_name($id) ?: 'this artist') . '’s curated selection. Explore the sources and continue discovering.'); ?></p>
            </div>
            <div class="song-insights">
                <section class="song-listening-notes" aria-labelledby="listening-notes-heading">
                    <div class="meaning-label"><?php echo ag_icon('spark'); ?> Afterglow interpretation</div>
                    <h2 id="listening-notes-heading">Listening notes</h2>
                    <p class="fine-print">An independent interpretation, not a statement of the artist’s intent.</p>
                    <div class="prose"><?php echo $story['listening']; ?></div>
                </section>
                <section class="profile-section song-artist-statement" aria-labelledby="artist-words-heading">
                    <h2 id="artist-words-heading">In the artist’s own words</h2>
                    <?php if ($sourced) : ?><p class="attribution-label">Artist statement · editorial paraphrase, not a direct quotation</p><p><?php echo esc_html(ag_meta('artist_comment')); ?></p><a class="song-citation-link" href="#song-sources">Read the supporting sources ↓</a><?php else : ?><p>No verified artist explanation has been added for this song. The listening notes are Afterglow’s editorial interpretation.</p><?php endif; ?>
                </section>
            </div>
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
        <?php if ($story['research']) : ?><div class="prose"><?php echo $story['research']; ?></div><?php endif; ?>
        <?php if (ag_meta('credits')) : ?><p class="fine-print recording-credits"><strong>Recording artists:</strong> <?php echo ag_recording_credits_html($id); ?></p><?php endif; ?>
        <?php if (ag_meta('themes')) : ?><div class="theme-note"><strong>Themes we’re exploring</strong><p><?php echo esc_html(ag_meta('themes')); ?></p></div><?php endif; ?>
        <section class="profile-section" id="song-sources" aria-label="Sources and further reading">
            <?php if ($sources) : ?><h3>Sources &amp; further reading</h3><ul class="source-list"><?php foreach ($sources as $link) { echo '<li><a href="' . esc_url($link[1]) . '" target="_blank" rel="noopener noreferrer">' . esc_html($link[0]) . ' ↗</a></li>'; } ?></ul><?php endif; ?>
            <?php if (ag_meta('release') && $sources) : ?><p class="fine-print">Release date: <?php echo esc_html(ag_meta('release')); ?> · See sources above.</p><?php endif; ?>
        </section>
    </section>
</main>
<?php endwhile; get_footer(); ?>
