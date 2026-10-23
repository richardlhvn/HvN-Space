<!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#f8f7f3"><?php if (!has_site_icon()) : ?><link rel="icon" type="image/svg+xml" href="<?php echo esc_url(get_theme_file_uri('assets/favicon.svg')); ?>"><?php endif; ?><?php wp_head(); ?></head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header"><div class="header-inner shell">
    <a class="wordmark" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> home"><?php echo ag_icon('spark', 'brand-mark'); ?><span><?php echo esc_html(strtolower(get_bloginfo('name'))); ?><span class="brand-dot">.</span></span></a>
    <button class="mobile-menu icon-button" aria-label="Open navigation" aria-expanded="false" aria-controls="primary-nav"><?php echo ag_icon('menu'); ?></button>
    <nav id="primary-nav" class="primary-nav" aria-label="Primary">
        <a href="<?php echo esc_url(home_url('/')); ?>" <?php if (is_front_page()) echo 'aria-current="page"'; ?>>Discover</a>
        <a href="<?php echo esc_url(ag_url('artists')); ?>" <?php if (is_post_type_archive('artist') || is_singular('artist')) echo 'aria-current="page"'; ?>>Artists</a>
        <a href="<?php echo esc_url(ag_url('songs')); ?>" <?php if (is_post_type_archive('song') || is_singular('song')) echo 'aria-current="page"'; ?>>Songs</a>
        <a href="<?php echo esc_url(ag_url('journal')); ?>" <?php if (is_home() || is_singular('post')) echo 'aria-current="page"'; ?>>The journal <span class="nav-dot"></span></a>
    </nav>
    <div class="header-actions"><button class="icon-button search-trigger" aria-label="Search artists, songs, and stories"><?php echo ag_icon('search'); ?></button><span class="header-divider"></span><a class="saved-link" aria-label="Your saved discoveries" href="<?php echo esc_url(ag_url('saved')); ?>"><?php echo ag_icon('heart'); ?><span>Your saved</span><span class="saved-count" aria-label="Saved items">0</span></a></div>
</div></header>
<dialog id="search-dialog" class="search-dialog" aria-labelledby="search-title"><div class="search-dialog-heading"><h2 id="search-title">What’s on your mind?</h2><button class="icon-button search-close" aria-label="Close search"><?php echo ag_icon('close'); ?></button></div><p>Find an artist, a song, or a little inspiration.</p><form action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search"><label class="sr-only" for="global-search">Search</label><input type="search" id="global-search" name="s" placeholder="Try Sara Kays or heartbreak…" required><button class="button button-dark" type="submit">Search <?php echo ag_icon('arrow'); ?></button></form></dialog>
<div id="save-status" class="toast" role="status" aria-live="polite"></div>
<?php do_action('afterglow_after_header'); ?>
