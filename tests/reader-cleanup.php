<?php
// Read-only regressions for exact public boilerplate removal and preserved editor evidence.
$checks=0;$assert=function($ok,$message)use(&$checks){if(!$ok){throw new RuntimeException($message);}$checks++;};
$tail='For tunetofond, the attraction is the contrast between accessible pop songwriting and emotionally complicated stories; that final characterization is editorial, not an official genre designation.';
$input='<p>Verified biography with <em>formatting</em> and a source.</p><p>'.$tail.'</p><h2>A curated way into the music</h2><p>An editorial selection from the owner-approved launch catalog.</p><p>This selection is editorial, not a Spotify popularity ranking. Genre and mood tags reflect editorial judgments.</p>';
$out=ag_public_research_content($input);
$assert(str_contains($out,'Verified biography with <em>formatting</em> and a source.'),'Biography facts and inline formatting remain.');
$assert(!str_contains($out,'editorial')&&!str_contains($out,'A curated way')&&!str_contains($out,'owner-approved'),'Known first-party boilerplate is omitted completely.');
$custom='<h2>A curated way into the music</h2><p>A later owner introduction.</p><p>She worked on an editorial project in 2024.</p>';
$assert(ag_reader_content($custom)===$custom,'Unrecognized owner copy and factual uses of editorial are preserved.');
$quotes='<blockquote>'.$tail.'</blockquote><p><a href="https://example.test/source">'.$tail.'</a></p>';
$assert(ag_reader_content($quotes)===$quotes,'Quoted/source-linked text is never stripped.');
$assert(str_contains(ag_reader_content('<p>Biography. '.str_replace('tunetofond','Afterglow',$tail).'</p>'),'Biography.')&&!str_contains(ag_reader_content('<p>'.str_replace('tunetofond','Afterglow',$tail).'</p>'),'Afterglow'),'Historical brand variants get the same reader presentation.');
$html=ag_discovery_reason_html('tunetofond theme: regret.');
$assert(str_contains($html,'<svg')&&str_contains($html,'aria-hidden="true"')&&str_contains($html,'regret.')&&!str_contains($html,'tunetofond'),'Recommendation uses the existing decorative icon and actual theme.');
$assert(str_contains($html,'<span class="sr-only">Theme: </span>'),'Theme semantics remain accessible without a visible branded prefix.');
$assert(str_contains(ag_discovery_reason_html('tunetofond feeling: hopeful.'),'hopeful.'),'Feeling labels retain the real selected value.');
$assert(!str_contains(ag_discovery_reason_html('tunetofond theme: <script>'),'theme: <script>'),'Recommendation values are escaped.');
foreach(ag_reviewed_data()['packs'] as $slug=>$pack){
 $id=ag_reviewed_find($slug);$raw=get_post_field('post_content',$id);
 $assert(str_contains($raw,'An editorial selection from the owner-approved launch catalog.'),'Stored selection/evidence remains intact: '.$slug);
 $visible=ag_public_research_content($raw);
 $assert(!str_contains($visible,'owner-approved launch catalog')&&!str_contains($visible,'Spotify popularity ranking'),'Artist API/feed presentation omits the internal selection: '.$slug);
}
$id=ag_reviewed_find('astrid-s',['slug'=>'think-before-i-talk']);
$about=ag_about_song($id);$html=ag_public_song_document($id,get_post_field('post_content',$id));
$assert(!str_contains($html,'Artist statement · paraphrased')&&!str_contains($html,'interpretation · not an artist statement'),'Public song documents omit attribution badges.');
$assert(str_contains($html,esc_html($about['text']))&&str_contains($html,'Sources &amp; further reading'),'About prose and citations remain in public song documents.');
$assert(get_post_meta($id,'_ag_about_song_attribution',true)==='artist_statement','Internal attribution enum is unchanged.');
WP_CLI::success($checks.' reader-cleanup, evidence and presentation checks passed.');
