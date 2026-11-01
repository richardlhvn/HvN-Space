import {chromium} from 'playwright';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const base=process.env.TEST_URL||'http://localhost:8080';
const wp=expression=>execFileSync('docker',['compose','run','--rm','cli','wp','eval',expression],{encoding:'utf8'}).trim();
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
let fixture=0,checks=0;
const check=(v,m)=>{assert.ok(v,m);checks++;};
try {
 const page=await browser.newPage({viewport:{width:1440,height:1050}}),results=[];
 for(const [path,expected] of [
  ['/songs/shy-martin-forget-to-forget/','pending'],
  ['/songs/astrid-s-i-dont-know-why/','collaboration'],
  ['/songs/clara-mae-taped-up-heart-feat-clara-mae/','production'],
  ['/songs/rosie-never-the-1/','empty']
 ]){
  const r=await page.goto(new URL(path,base).href);check(r.status()===200,'Existing example URL and song ID must survive');
  const count=await page.locator('.song-about').count();
  if(expected==='pending'||expected==='empty'){
   check(count===0,'Missing or unverified background must not produce a reader-facing placeholder');
   check(await page.locator('.song-listening-panel').count()===1&&await page.locator('#discover-more').count()===1,'Listening and discovery remain available without About the Song');
  }else{
   check(count===1&&await page.locator('#about-song-heading').textContent()==='About the Song','Exactly one natural editorial section renders');
   const text=await page.locator('.song-about .prose').textContent();
   check(expected==='collaboration'?text.includes('NOTD')&&text.includes('topline'):text.includes('KREAM')&&text.includes('vocal'),'Documented creation/collaboration works without an explanation of the lyrics');
   check(await page.locator('.about-source-links a').count()>0,'Exact supporting sources accompany the narrative');
   check((await page.locator('.about-attribution').textContent()).includes('Artist statement'),'Producer/collaborator accounts are accurately attributed');
   check(!text.includes('piano'),'Unverified additional demo details must not be invented');
  }
  check((await page.locator('meta[name=robots]').getAttribute('content')).includes('noindex'),'Existing research review status is preserved');
  results.push({path,status:expected==='pending'?'blocked_source_verification': 'passed'});
 }
 // Temporary local rendering fixture only: marked demo/noindex and excluded
 // from recommendations because it has no research key. Always deleted below.
 fixture=Number(wp(`$artist=get_page_by_path('astrid-s',OBJECT,'artist');$id=wp_insert_post(['post_type'=>'song','post_status'=>'publish','post_title'=>'Temporary About the Song rendering fixture','post_content'=>'','meta_input'=>['_ag_artist_id'=>(string)$artist->ID,'_ag_demo'=>'1','_ag_about_song'=>"The fixture artist described the relationship theme.\\n\\nThe fixture producer described developing the demo.",'_ag_about_song_type'=>"song-meaning\\ncreative-process",'_ag_about_song_attribution'=>'mixed','_ag_about_song_source_urls'=>'https://example.com/test-source']]);WP_CLI::line($id);`));
 // PHP double-quoted fixture strings should contain real paragraph separators.
 wp(`update_post_meta(${fixture},'_ag_about_song',"The fixture artist described the relationship theme.\n\nThe fixture producer described developing the demo.");`);
 wp(`update_post_meta(${fixture},'_ag_artist_comment','The fixture artist described the relationship theme.');update_post_meta(${fixture},'_ag_meaning_type','sourced');update_post_meta(${fixture},'_ag_sources','Rendering fixture | https://example.com/test-source');`);
 const url=wp(`WP_CLI::line(get_permalink(${fixture}));`);await page.goto(url);
 check(await page.locator('.song-about').count()===1&&await page.locator('.song-about .prose p').count()===2,'Meaning and process details render naturally in one section');
 const body=await page.locator('main').textContent();
 check(body.split('The fixture artist described').length===2&&body.split('The fixture producer described').length===2,'Each narrative detail is displayed only once');
 check((await page.locator('.about-attribution').textContent()).includes('attributed in the text'),'Mixed attribution is not mislabeled as a single artist statement');
 await page.setViewportSize({width:390,height:844});await page.goto(url);
 check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Combined details preserve mobile layout');
 results.push({path:'temporary mixed narrative fixture',status:'passed'});
 wp(`foreach(ag_about_fields() as $field){delete_post_meta(${fixture},'_ag_'.$field);}update_post_meta(${fixture},'_ag_artist_comment','');update_post_meta(${fixture},'_ag_meaning_type','editorial');wp_update_post(['ID'=>${fixture},'post_content'=>'<p>Our <em>fixture listening note</em> includes <a href="https://example.com/context">inline context</a>.</p>']);`);
 await page.goto(url);
 check(await page.locator('.song-about .prose em').count()===1&&await page.locator('.song-about .prose a').getAttribute('href')==='https://example.com/context','Existing editorial formatting and inline citations remain intact');
 check((await page.locator('main').textContent()).split('fixture listening note').length===2,'A matching autogenerated introduction must not duplicate the editorial narrative');
 await page.goto(new URL('/songs/astrid-s-i-dont-know-why/',base).href);await page.screenshot({path:'test-results/about-song-mobile.png',fullPage:true});
 await page.setViewportSize({width:1440,height:1050});await page.goto(new URL('/songs/clara-mae-taped-up-heart-feat-clara-mae/',base).href);await page.evaluate(()=>document.fonts.ready);await page.locator('.song-discovery-hero').screenshot({path:'test-results/about-song-collaboration.png'});
 await fs.writeFile('test-results/about-song-summary.json',JSON.stringify({checks,cases:results},null,2));
 console.log(`PASS: ${checks} About the Song browser checks. Four cases pass; SHY Martin meaning remains withheld pending source verification.`);
}finally{
 if(fixture)wp(`wp_delete_post(${fixture},true);`);
 await browser.close();
}
