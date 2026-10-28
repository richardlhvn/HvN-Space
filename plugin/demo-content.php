<?php
defined('ABSPATH') || exit;
// Explicit demo seeding; admin action or local installer only, never activation.
// Stable slugs and an option guard make repeat setup safe for editorial changes.
if (get_option('ag_demo_seed_version')) { return; }
$GLOBALS['ag_seed_created'] = [];
function ag_seed_record($type, $slug, $title, $excerpt, $content, $meta = [], $terms = []) {
    $existing = get_page_by_path($slug, OBJECT, $type);
    if ($existing) { return $existing->ID; } // Never overwrite existing content.
    $id = wp_insert_post(['post_type' => $type, 'post_name' => $slug, 'post_title' => $title, 'post_excerpt' => $excerpt, 'post_content' => $content, 'post_status' => 'publish'], true);
    if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    $GLOBALS['ag_seed_created'][] = $id;
    foreach ($meta as $key => $value) { update_post_meta($id, '_ag_' . $key, $value); }
    foreach ($terms as $tax => $names) { wp_set_object_terms($id, $names, $tax); }
    return $id;
}
$artists = [
    ['sara-kays', 'Sara Kays', 'For the moments you wish you could put into words.', 'lilac', ['Reflective', 'Heartbreak'], 'Try this listening route when you want an intimate, reflective corner of pop. Start with a single song, notice the details that catch your attention, and follow the recommendations at your own pace.'],
    ['rosie-darling', 'Rosie Darling', 'A softer place to land when everything feels a little loud.', 'rose', ['Heartbreak', 'Late night'], 'Our proposed route into this profile starts with the feeling of needing a little space. Pair it with a quiet evening and listen for the moments that make you want to pause and return.'],
    ['shy-martin', 'SHY Martin', 'A little light in the late-night quiet.', 'sage', ['Late night', 'Hopeful'], 'This discovery route is for listeners drawn to the meeting point between a memorable pop melody and a quieter mood. Let your own reaction guide where you go next.'],
    ['astrid-s', 'Astrid S', 'For the space between holding on and letting go.', 'sand', ['Hopeful', 'Heartbreak'], 'Use this profile as a starting point for exploring emotional pop with a little forward motion. There is no required way to feel about a song: curiosity is enough.'],
    ['sasha-alex-sloan', 'Sasha Alex Sloan', 'Company for the thoughts that arrive after midnight.', 'blue', ['Reflective', 'Late night'], 'A listening route for a quieter part of the day. Take a familiar feeling into an unfamiliar song, then decide for yourself which details make a connection.'],
    ['chelsea-cutler', 'Chelsea Cutler', 'For finding a little movement in a familiar feeling.', 'peach', ['Reflective', 'Hopeful'], 'This starter route invites you to explore the overlap between reflective listening and melodic pop. Keep the songs that connect, and leave room to be surprised by the ones that do not.'],
];
$artist_ids = [];
foreach ($artists as $a) {
    $content = '<h2>A place to begin.</h2><p>' . esc_html($a[5]) . '</p><p>This is an original editorial listening prompt for the demo, not a verified biography or a factual description of the artist’s intentions. Genre and mood tags are provisional editorial groupings. No country, career history, release date, quotation, or official social account has been asserted.</p><h2>Follow your own connection.</h2><p>Try listening once without looking for an explanation. On the second pass, notice the sound, the space, and your own response. The related artists below are hand-selected starting points for comparison, rather than a claim that the artists are affiliated.</p>';
    $artist_ids[$a[0]] = ag_seed_record('artist', $a[0], $a[1], $a[2], $content, ['palette' => $a[3], 'demo' => '1'], ['genre' => ['Emotional pop', 'Indie pop'], 'mood' => $a[4]]);
}
// Titles are representative listening references. No lyric, release date, quote,
// interview, official cover, or Spotify entity ID is fabricated.
$songs = [
    ['remember-that-night', 'Remember That Night?', 'sara-kays', 'A listening prompt for the memories that find their way back.', 'Reflective', 'Memory, reflection', 'lilac'],
    ['coping', 'Coping', 'rosie-darling', 'A quiet invitation to sit with what you are feeling.', 'Heartbreak', 'Change, finding space', 'rose'],
    ['sad-songs', 'sad songs', 'shy-martin', 'Sometimes the title alone is an invitation to press play.', 'Late night', 'Companionship, reflection', 'sage'],
    ['hurts-so-good', 'Hurts So Good', 'astrid-s', 'An opening for a conversation about complicated feelings.', 'Hopeful', 'Contradiction, connection', 'sand'],
    ['dancing-with-your-ghost', 'Dancing With Your Ghost', 'sasha-alex-sloan', 'A listening prompt for the things that linger.', 'Late night', 'Absence, memory', 'blue'],
    ['your-shirt', 'Your Shirt', 'chelsea-cutler', 'An invitation to think about the memories ordinary objects can hold.', 'Reflective', 'Memory, attachment', 'peach'],
];
$song_ids = [];
foreach ($songs as $s) {
    $content = '<h2>Before you listen.</h2><p>This starter note uses the title as a prompt for your own listening. It does not establish what the song is about, describe specific lyrics, or claim to know what the artist intended. The themes shown here are prompts for a future, fully researched editorial discussion.</p><h2>A question to take with you.</h2><p>What does the title bring to mind for you? Listen to the full track through Spotify, then notice whether your first impression changes. Sometimes a melody feels different from a title; that space can be a useful place to begin a personal interpretation.</p><h2>Room for the real story.</h2><p>We have not added a verified artist interview or public explanation to this demo page. Before publishing a finished song analysis, an editor should listen closely, verify the official track link, research original sources, and distinguish documented statements from their own interpretation. No lyrics are reproduced here.</p>';
    $song_ids[$s[0]] = ag_seed_record('song', $s[0], $s[1], $s[3], $content, ['artist_id' => (string) $artist_ids[$s[2]], 'palette' => $s[6], 'themes' => $s[5], 'meaning_type' => 'editorial', 'demo' => '1'], ['genre' => ['Emotional pop'], 'mood' => [$s[4]]]);
    if (in_array($artist_ids[$s[2]], $GLOBALS['ag_seed_created'], true)) {
        update_post_meta($artist_ids[$s[2]], '_ag_recommended', (string) $song_ids[$s[0]]);
    }
}
$relations = [
    'sara-kays' => ['rosie-darling' => 'A proposed editorial pairing for reflective listening. Compare the two in your own time; this is a discovery suggestion, not a documented artist influence.', 'sasha-alex-sloan' => 'A second route for your quieter listening hours. Follow the mood and make your own comparison.'],
    'rosie-darling' => ['sara-kays' => 'Try this connection when you want to stay with a reflective feeling. The pairing is a curator’s suggestion, not an artist endorsement.', 'chelsea-cutler' => 'A neighboring route through the demo’s emotional-pop collection. Notice where the mood changes for you.'],
    'shy-martin' => ['astrid-s' => 'A proposed path between two pop listening starting points. Compare the melodies and decide what connects for you.', 'chelsea-cutler' => 'An invitation to explore another corner of this mood-led collection.'],
    'astrid-s' => ['shy-martin' => 'A manually curated pop discovery pairing, inspired by the project’s starting references. No shared influence or collaboration is asserted.', 'sara-kays' => 'Take a turn toward a different part of the collection and see which feeling stays with you.'],
    'sasha-alex-sloan' => ['sara-kays' => 'A possible next stop for a reflective listening session.', 'rosie-darling' => 'Keep the quiet-hours mood and try another voice from the collection.'],
    'chelsea-cutler' => ['shy-martin' => 'A suggested route through the project’s pop reference points.', 'rosie-darling' => 'A hand-selected alternative for a slower discovery session.'],
];
foreach ($relations as $slug => $others) {
    if (!in_array($artist_ids[$slug], $GLOBALS['ag_seed_created'], true)) { continue; }
    $ids = []; $notes = [];
    foreach ($others as $other => $note) { $ids[] = $artist_ids[$other]; $notes[] = $artist_ids[$other] . ' | ' . $note; }
    update_post_meta($artist_ids[$slug], '_ag_related', implode(',', $ids));
    update_post_meta($artist_ids[$slug], '_ag_similarity', implode("\n", $notes));
}
$all_songs = array_values($song_ids);
foreach ($all_songs as $i => $id) {
    if (in_array($id, $GLOBALS['ag_seed_created'], true)) { update_post_meta($id, '_ag_related', implode(',', [$all_songs[($i+1)%count($all_songs)], $all_songs[($i+2)%count($all_songs)]])); }
}
$articles = [
    ['how-to-find-your-next-favorite-artist', 'Your next favorite artist is one small detour away.', 'A slower, more curious approach to finding music that feels like yours.', '<p>Discovery does not have to begin with an endless feed. Sometimes the best starting point is a single song you already love, and one honest question: what is it about this that keeps bringing me back?</p><h2>Name the thing you are listening for.</h2><p>It might be a voice that feels close, a melody that opens up at just the right moment, or the space around a quiet line. Try naming one quality rather than looking for an exact replacement. You are giving your curiosity a direction, not setting a rule.</p><h2>Take one small detour.</h2><p>Choose one artist connection and give it a few minutes of your attention. Read why the curator made the pairing, then decide whether that reasoning matches what you hear. Disagreeing is useful too: it helps you understand your own taste.</p><h2>Leave room for a second listen.</h2><p>First impressions can depend on where you are, what you are doing, and how your day has been. Save something that interests you, then return to it in a different setting. A song does not need to become a favorite immediately to be worth your time.</p><p><a href="/artists/">Start with the artist directory</a> and follow one connection. No hurry, no homework.</p>'],
    ['a-little-company-for-the-quiet-hours', 'A little company for the quiet hours.', 'A thoughtful way to build a late-night listening ritual, without turning a feeling into a formula.', '<p>There is a particular kind of quiet that arrives when the day stops asking things of you. Music can be company in that space, but it does not have to fill every corner of it.</p><h2>Start small.</h2><p>Pick a few songs rather than a whole evening’s worth. Let the first track settle before deciding what should come next. A short collection leaves room for silence and for changing your mind.</p><h2>Follow a feeling, loosely.</h2><p>Mood labels are invitations, not instructions. Something tagged reflective might make you feel hopeful; a song someone else finds sad might bring you comfort. Your response belongs to you.</p><h2>Save what you want to return to.</h2><p>You do not need to organize everything. Keep a few discoveries close, let others pass, and give yourself permission to listen without making a project out of it.</p><p><a href="/artists/?filter_mood=late-night">Explore the late-night discovery route</a> and see where your own listening takes you.</p>'],
    ['song-meaning-and-the-space-for-your-own-story', 'A song can mean something different to you.', 'On artist intent, personal interpretation, and keeping a song’s story honest.', '<p>A song can arrive at exactly the right moment in your life without having been written about anything like your situation. That connection is real. It is also different from knowing what the artist intended.</p><h2>Two stories can sit beside each other.</h2><p>An artist’s public explanation offers context. Your interpretation describes your experience. Neither needs to erase the other, but they should be clearly labeled so a reader knows which kind of story they are reading.</p><h2>Start with the original source.</h2><p>When an article attributes an explanation to an artist, look for a link to the interview, official commentary, or another original statement. A repeated claim is not automatically a documented one. If no reliable explanation is available, it is better to say so.</p><h2>Keep your own language.</h2><p>Thoughtful writing can discuss a song without reproducing its lyrics. Describe your response, explain your reasoning, and leave space for other listeners to disagree.</p><p>That is the approach behind our song pages: a clear distinction between editorial listening notes and sourced statements, with room for the music to keep meaning something personal.</p>'],
];
foreach ($articles as $a) { ag_seed_record('post', $a[0], $a[1], $a[2], str_replace(['href="/artists/'], ['href="' . esc_url(home_url('/artists/'))], $a[3])); }
$pages = [
    'home' => ['Discover', ''],
    'journal' => ['The journal', ''],
    'saved' => ['Your saved discoveries.', ''],
    'about' => ['A little closer to the music.', '<p>Afterglow is an independent discovery project for people who connect with emotional pop: the quiet details, the complicated feelings, and the melodies that stay a little longer.</p><h2>Less algorithm. More connection.</h2><p>We build small, deliberate routes between artists and songs. Recommendations are chosen by an editor, with room to explain why a connection might be worth exploring. Mood and genre labels reflect editorial judgment rather than an artist’s endorsement.</p><h2>A note on the first collection.</h2><p>This MVP includes clearly marked starter artist and song profiles. Their abstract artwork is original and does not depict the artists. Profiles remain excluded from search indexes until an editor completes fact-checking, sourcing, and rights review.</p><h2>Our editorial promise.</h2><p>Original writing. No copied biographies or song lyrics. No invented interviews, quotations, or artist intent. Listening happens through Spotify, and we are not affiliated with Spotify or the artists featured.</p>'],
    'privacy' => ['Privacy, in plain language.', '<p><strong>Pre-launch template — the site owner must review and complete this notice for the actual operator, jurisdiction, hosting setup, and vendors before launch. This is not legal advice.</strong></p><h2>What this MVP stores.</h2><p>The saved collection stores artist and song IDs in your browser’s local storage under the key afterglow:saved:v1. No account is required. You can remove items using the heart button or clear this site’s browser storage. These IDs are sent to this website when you open your saved page, so it can retrieve the selected public records.</p><h2>Hosting and administration.</h2><p>Hosting providers may process IP addresses and request logs. WordPress uses necessary cookies for authenticated administrators. This MVP does not set advertising or analytics cookies. Document your host’s actual logging, retention periods, legal basis, and processor details before publication.</p><h2>External services.</h2><p>Spotify links open an external site subject to its own policies. Official Spotify players load only when you select Load Spotify player. Loading a player connects your browser to Spotify, which may process request data and use storage under its policies. No player request is made before that choice. Fonts remain local, and no advertising or analytics trackers are installed.</p><h2>Your choices and contact.</h2><p>Before launching, add the operator’s name, contact address, privacy rights process, data retention periods, and effective date. Review this policy again before adding analytics, ads, forms, or embeds.</p>'],
    'contact' => ['Say hello.', '<p>Have a listening recommendation, a correction, or a thoughtful idea for the site? We would like to hear it.</p><h2>Corrections & sources.</h2><p>For a correction, include the page URL, the specific detail that needs attention, and an original source where possible. An editor should review submissions before making changes.</p><h2>Before launch.</h2><p>The owner must configure a public contact email. This MVP does not collect submissions through a form.</p>'],
    'copyright' => ['Copyright & permissions.', '<p>Afterglow does not host music or reproduce complete song lyrics. Listening links direct visitors to Spotify. Artist names and song titles identify the subjects of independent editorial coverage; no endorsement or affiliation is implied.</p><h2>Artwork.</h2><p>The default artwork is original abstract illustration and is not a photograph of any artist or an official album cover. Artist photography should only be uploaded after the site owner has verified and documented the necessary rights and attribution.</p><h2>Report a concern.</h2><p>Include the page URL, a description of the material, your relationship to the rights holder, and contact details so the owner can investigate. Do not send sensitive identity documents by email.</p><p><strong>Pre-launch template:</strong> complete the operator’s contact details and obtain jurisdiction-appropriate advice on your notice procedure before publication.</p>'],
];
$page_ids = [];
foreach ($pages as $slug => $p) { $page_ids[$slug] = ag_seed_record('page', $slug, $p[0], '', $p[1], in_array($slug, ['privacy', 'contact', 'copyright'], true) ? ['demo' => '1'] : []); }
update_option('show_on_front', 'page');
update_option('page_on_front', $page_ids['home']);
update_option('page_for_posts', $page_ids['journal']);
update_option('wp_page_for_privacy_policy', $page_ids['privacy']);
update_option('ag_demo_seed_version', '1');
