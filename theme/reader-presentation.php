<?php
/** Reader-only removal of known site/editor workflow notes. Stored research stays intact. */
defined('ABSPATH') || exit;

function ag_reader_content($content) {
    // Remove this heading only beside the exact generated selection boilerplate.
    $content = preg_replace('~<h2\b[^>]*>\s*A curated way into the music\s*</h2>\s*(?=<p\b[^>]*>\s*An editorial selection from the owner-approved launch catalog\.)~iu', '', $content);
    $notes = [
        'For tunetofond, the attraction is the contrast between accessible pop songwriting and emotionally complicated stories; that final characterization is editorial, not an official genre designation.',
        'For tunetofond listeners, Hearn fits the intersection of confessional pop, folk-pop, and intimate singer-songwriter storytelling. That genre description is editorial, informed by published descriptions of his music, not a rigid official classification.',
        'For tunetofond, she offers a bridge from softer emotional pop toward understated electronic and singer-songwriter production. Mood and genre labels here are editorial navigation aids, not universally defined categories.',
        'For tunetofond listeners, her catalogue offers a bridge between polished Scandinavian pop, reflective breakup songs, and country-tinged singer-songwriter music.',
        'tunetofond’s emotional-pop categorization reflects these lyrical connections; it is an editorial recommendation framework, not a rigid genre designation.',
        'On tunetofond, Mokita offers a route from confessional songwriting into understated electronic pop. Those cross-genre recommendations are editorial choices rather than claims made by Carter himself.',
        'For tunetofond, Darling provides an intimate link between soft heartbreak pop and reflective coming-of-age songwriting. That last categorization is editorial, rather than a formal description supplied by the artist.',
        'For tunetofond, her catalogue offers several entry points into intimate, narrative-led emotional pop.',
        'tunetofond groups her with emotional and confessional pop as an editorial classification rather than a claim about a single official genre.',
        'An editorial selection from the owner-approved launch catalog.',
        'This selection is editorial, not a Spotify popularity ranking. Genre and mood tags reflect editorial judgments.',
        'Genre and mood tags reflect editorial judgments.',
    ];
    // Keep the historical identity variants readable without changing source files.
    foreach ($notes as $note) { $notes[] = str_replace('tunetofond', 'Afterglow', $note); }
    $parts = wp_html_split($content); $protected = [];
    foreach ($parts as &$part) {
        if (preg_match('~^<(a|blockquote|q|code|pre|script|style)\b~i', $part, $match)) {
            $tag = strtolower($match[1]); $protected[$tag] = ($protected[$tag] ?? 0) + 1;
        } elseif (preg_match('~^</(a|blockquote|q|code|pre|script|style)\b~i', $part, $match)) {
            $tag = strtolower($match[1]); $protected[$tag] = max(0, ($protected[$tag] ?? 0) - 1);
        } elseif ($part !== '' && $part[0] !== '<' && !array_sum($protected)) {
            $text = html_entity_decode($part, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $clean = str_replace($notes, '', $text);
            if ($clean !== $text) { $part = esc_html(rtrim($clean)); }
        }
    }
    unset($part);
    return preg_replace('~<p\b[^>]*>\s*</p>~iu', '', implode('', $parts));
}

function ag_discovery_reason_html($reason) {
    $brand = preg_quote(ag_site_name(), '~');
    $label = '';
    if (preg_match('~^(?:Shares a |Shares an )?' . $brand . ' (theme|feeling): (.+)$~u', $reason, $match)) {
        $label = ucfirst($match[1]) . ': ';
        $reason = $match[2];
    } else {
        $reason = str_replace([
            'Linked by ' . ag_site_name() . '’s song recommendations.',
            'From an artist linked in our editorial recommendations.',
            'Another artist in ' . ag_site_name() . '’s curated collection.',
            'Another song in ' . ag_site_name() . '’s curated collection.',
        ], [
            'From this song’s recommendations.',
            'From a related artist.',
            'Another artist to explore.',
            'Another song to explore.',
        ], $reason);
    }
    return ag_icon('spark') . ($label ? '<span class="sr-only">' . esc_html($label) . '</span>' : '') . esc_html($reason);
}
