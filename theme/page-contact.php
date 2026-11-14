<?php
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts', static function () {
    wp_enqueue_style('ag-contact', get_theme_file_uri('assets/contact.css'), ['ag-design'], filemtime(get_theme_file_path('assets/contact.css')));
});
get_header();
while (have_posts()) : the_post();
    $email = sanitize_email(get_theme_mod('ag_contact_email', ''));
    $mail_link = static function ($subject) use ($email) {
        return 'mailto:' . $email . '?subject=' . rawurlencode($subject);
    };
?>
<main id="main" class="shell contact-main">
    <section class="contact-hero" aria-labelledby="contact-title">
        <div class="contact-intro">
            <p class="eyebrow contact-eyebrow"><?php echo ag_icon('spark'); ?> A little closer to the music</p>
            <h1 id="contact-title"><?php the_title(); ?></h1>
            <div class="contact-deck"><?php the_content(); ?></div>
            <p class="contact-signoff">Good music brings people together.<br>So does a good conversation.</p>
        </div>
        <div class="contact-inbox">
            <div class="contact-inbox-top"><span class="eyebrow">A note, a thought, a new favorite</span><?php echo ag_icon('diagonal'); ?></div>
            <h2>We’re all ears.</h2>
            <p>However you found us, we’re glad you’re here.</p>
            <?php if ($email) : ?>
                <a class="contact-email" href="<?php echo esc_url($mail_link('Hello, ' . ag_site_name())); ?>"><?php echo esc_html($email); ?></a>
                <a class="button button-dark contact-write" href="<?php echo esc_url($mail_link('Hello, ' . ag_site_name())); ?>">Write us a note <?php echo ag_icon('arrow'); ?></a>
                <span class="contact-email-note">Opens your email app</span>
            <?php endif; ?>
            <span class="contact-inbox-spark" aria-hidden="true"><?php echo ag_icon('spark'); ?></span>
        </div>
    </section>

    <section class="contact-topics" aria-labelledby="contact-topics-title">
        <div class="contact-section-heading"><p class="eyebrow">A few good reasons to write</p><h2 id="contact-topics-title">What’s on your mind?</h2></div>
        <div class="contact-topic-grid">
            <article class="contact-topic">
                <span class="contact-number" aria-hidden="true">01 /</span>
                <h3>Share a discovery.</h3>
                <p>Found a song that stays with you? Send us the artist, a listening link, and what made you connect with it.</p>
                <?php if ($email) : ?><a class="text-link" href="<?php echo esc_url($mail_link('A music discovery for ' . ag_site_name())); ?>">Recommend a song <?php echo ag_icon('arrow'); ?></a><?php endif; ?>
            </article>
            <article class="contact-topic">
                <span class="contact-number" aria-hidden="true">02 /</span>
                <h3>Help us get it right.</h3>
                <p>Spotted a detail that needs a second look? Include the page link, what needs changing, and an original source if you have one.</p>
                <?php if ($email) : ?><a class="text-link" href="<?php echo esc_url($mail_link('A correction for ' . ag_site_name())); ?>">Send a correction <?php echo ag_icon('arrow'); ?></a><?php endif; ?>
            </article>
            <article class="contact-topic">
                <span class="contact-number" aria-hidden="true">03 /</span>
                <h3>Talk about your work.</h3>
                <p>If you’re an artist, photographer, or rights holder with a question about something featured here, send the page link and a little context.</p>
                <?php if ($email) : ?><a class="text-link" href="<?php echo esc_url($mail_link('An artist or rights enquiry for ' . ag_site_name())); ?>">Get in touch <?php echo ag_icon('arrow'); ?></a><?php endif; ?>
            </article>
        </div>
    </section>
    <div class="contact-return"><span>While you’re here, find something that feels like you.</span><a class="text-link" href="<?php echo esc_url(ag_url('artists')); ?>">Explore the artists <?php echo ag_icon('arrow'); ?></a></div>
</main>
<?php endwhile; get_footer(); ?>
