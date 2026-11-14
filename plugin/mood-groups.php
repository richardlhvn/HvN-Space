<?php
/** Public mood navigation; original taxonomy IDs/assignments remain intact. */
defined('ABSPATH') || exit;
function ag_mood_groups() { return ag_taxonomy_groups('mood'); }
function ag_mood_default_aliases() { return ag_taxonomy_default_aliases('mood'); }
function ag_mood_aliases() { return ag_taxonomy_aliases('mood'); }
function ag_mood_group_slug($value) { return ag_taxonomy_group_slug('mood', $value); }
function ag_mood_filter_slugs($value) { return ag_taxonomy_filter_slugs('mood', $value); }
function ag_mood_family_terms($terms) { return ag_taxonomy_family_terms('mood', $terms); }
function ag_mood_family_options($type = null) { return ag_taxonomy_family_options('mood', $type); }
function ag_save_mood_group_overrides($input) { return ag_save_taxonomy_group_overrides('mood', $input); }
ag_register_taxonomy_group_navigation('mood');
