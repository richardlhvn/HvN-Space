<?php get_header(); while (have_posts()) : the_post();
    $id = get_the_ID();
    $recommended = array_values(array_unique(array_filter(ag_related_ids($id, 'recommended'), function ($song) { return get_post_type($song) === 'song'; })));
    $biography = apply_filters('the_content', get_the_content());
    $preview = wp_trim_words(wp_strip_all_tags($biography), 65);
    $photo = function_exists('ag_media_credit') && ag_media_credit($id);
?>
<main id="main" class="shell profile-main artist-profile">
    <a class="back-link" href="<?php echo esc_url(ag_url('artists')); ?>">← All artists</a>
    <div class="profile-layout artist-discovery-hero">
        <div class="profile-art-column"><?php ag_artist_listening_panel($id); ?></div>
        <div class="profile-copy">
            <div class="artist-introduction">
                <div class="artist-identity">
                    <?php if ($photo) : ?><div class="artist-portrait-wrap"><?php ag_art($id, 'artist-identity-portrait', true); ?></div><?php endif; ?>
                    <div class="artist-heading"><p class="eyebrow">ON OUR RADAR / ARTIST PROFILE</p><div class="profile-title"><h1><?php the_title(); ?></h1><?php ag_save_button($id, 'large-save'); ?></div></div>
                </div>
                <?php if ($photo) : ?><p class="art-credit artist-identity-credit"><?php echo ag_photo_credit_html($id, true); ?></p><?php endif; ?>
                <div class="tag-list"><?php foreach (['genre', 'mood', 'country'] as $tax) { $terms = get_the_terms($id, $tax); foreach ($terms && !is_wp_error($terms) ? $terms : [] as $term) { echo '<a class="tag" href="' . esc_url(ag_url('artists') . '?filter_' . $tax . '=' . $term->slug) . '">' . esc_html($term->name) . '</a>'; } } ?></div>
                <?php if (get_post_field('post_excerpt', $id) !== '') : ?><p class="profile-intro"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
            </div>
            <?php if ($preview && $preview !== wp_strip_all_tags(get_the_excerpt())) : ?><div class="artist-biography-preview"><h2>Meet <?php the_title(); ?>.</h2><p><?php echo esc_html($preview); ?></p><a class="text-link" href="#artist-biography">Read the full biography <?php echo ag_icon('arrow'); ?></a></div><?php endif; ?>
            <div class="artist-profile-actions"><?php if ($recommended) : ?><a class="button button-dark" href="#artist-start">Find your first song <?php echo ag_icon('arrow'); ?></a><?php endif; ?><?php if (ag_meta('demo')) : ?><a class="song-review-status" href="#artist-biography"><?php echo esc_html(ag_research_label($id)); ?> · Research details</a><?php endif; ?></div>
        </div>
    </div>
    <?php ag_artist_starter_songs($recommended); ?>
    <div class="artist-details">
        <section class="artist-full-biography" id="artist-biography" aria-labelledby="artist-biography-heading"><p class="eyebrow">GET TO KNOW THE ARTIST</p><h2 id="artist-biography-heading">About <?php the_title(); ?>.</h2><?php ag_demo_notice($id); ?><div class="prose"><?php echo $biography; ?></div></section>
        <?php if ($recommended) : ?><section class="profile-section curated-song-section" id="artist-songs" aria-labelledby="artist-selection-heading"><p class="eyebrow">KEEP EXPLORING</p><h2 id="artist-selection-heading">Explore the full selection.</h2><div class="song-list"><?php foreach ($recommended as $i => $song) { ag_song_row($song, sprintf('%02d', $i + 1)); } ?></div></section><?php endif; ?>
        <?php $collaborations = ag_collaboration_ids($id); if ($collaborations) : ?><section class="profile-section collaboration-section"><p class="eyebrow">RECORDED TOGETHER</p><h2>Also appears on.</h2><p class="fine-print">Collaborations from other artists’ curated selections.</p><div class="song-list"><?php foreach ($collaborations as $song) { ag_song_row($song); echo '<p class="fine-print recording-credits">Recording artists: ' . ag_recording_credits_html($song) . '</p>'; } ?></div></section><?php endif; ?>
        <?php $links = ag_source_links($id); $social = ag_source_links($id, 'social'); if ($links || $social || ag_meta('website')) : ?><section class="profile-section"><h2>Further listening &amp; reading.</h2><ul class="source-list"><?php if (ag_meta('website')) { echo '<li><a href="' . esc_url(ag_meta('website')) . '" target="_blank" rel="noopener noreferrer">Official website ↗</a></li>'; } foreach (array_merge($links, $social) as $link) { echo '<li><a href="' . esc_url($link[1]) . '" target="_blank" rel="noopener noreferrer">' . esc_html($link[0]) . ' ↗</a></li>'; } ?></ul></section><?php endif; ?>
    </div>
    <?php $related = ag_related_ids($id); if ($related) : ?><section class="section related-section"><?php ag_section_heading('FOLLOW THE FEELING', 'If this connects, try these.'); ?><div class="related-grid"><?php $notes = []; foreach (explode("\n", ag_meta('similarity')) as $line) { $p = explode('|', $line, 2); if (count($p) === 2) { $notes[absint($p[0])] = trim($p[1]); } } foreach ($related as $other) : ?><div><?php ag_card($other); ?><p class="similarity-note"><?php echo esc_html($notes[$other] ?? 'A hand-picked editorial connection. Explore and see how it feels to you.'); ?></p></div><?php endforeach; ?></div></section><?php endif; ?>
</main>
<?php endwhile; get_footer(); ?>
