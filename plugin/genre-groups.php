<?php
/** Public genre navigation; original taxonomy IDs/assignments remain intact. */
defined('ABSPATH') || exit;
function ag_genre_groups() { return ag_taxonomy_groups('genre'); }
function ag_genre_default_aliases() { return ag_taxonomy_default_aliases('genre'); }
function ag_genre_aliases() { return ag_taxonomy_aliases('genre'); }
function ag_genre_group_slug($value) { return ag_taxonomy_group_slug('genre', $value); }
function ag_genre_filter_slugs($value) { return ag_taxonomy_filter_slugs('genre', $value); }
function ag_genre_family_terms($terms) { return ag_taxonomy_family_terms('genre', $terms); }
function ag_genre_family_options($type = null) { return ag_taxonomy_family_options('genre', $type); }
function ag_save_genre_group_overrides($input) { return ag_save_taxonomy_group_overrides('genre', $input); }
ag_register_taxonomy_group_navigation('genre');
