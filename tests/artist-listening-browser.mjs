import {chromium} from 'playwright';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';

const base=process.env.TEST_URL||'http://localhost:8080';
const expression=`$out=[];foreach(ag_handoff_packs() as $slug=>$pack){$id=ag_handoff_find($slug);$GLOBALS['post']=get_post($id);setup_postdata($GLOBALS['post']);$songs=ag_related_ids($id,'recommended');$out[]=['slug'=>$slug,'url'=>get_permalink($id),'embed'=>ag_spotify_embed_url($id),'source'=>ag_meta('spotify',$id),'photo'=>(bool)ag_media_credit($id),'first'=>array_map(function($song){return html_entity_decode(get_the_title($song),ENT_QUOTES|ENT_HTML5,'UTF-8');},array_slice($songs,0,3)),'biography'=>html_entity_decode(wp_strip_all_tags(apply_filters('the_content',get_post_field('post_content',$id))),ENT_QUOTES|ENT_HTML5,'UTF-8')];}wp_reset_postdata();WP_CLI::line(wp_json_encode($out));`;
const artists=JSON.parse(execFileSync('docker',['compose','run','--rm','cli','wp','eval',expression],{encoding:'utf8'}));
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;const check=(v,m)=>{assert.ok(v,m);checks++;};
const normalize=s=>s.replace(/\s+/g,' ').trim();
await fs.mkdir('test-results',{recursive:true});
try{
 const context=await browser.newContext({viewport:{width:1440,height:1080}}),page=await context.newPage();
 const errors=[],external=[];page.on('pageerror',e=>errors.push(e.message));page.on('request',r=>{const u=new URL(r.url());if(['https:','http:'].includes(u.protocol)&&u.origin!==new URL(base).origin)external.push(r.url());});
 check(artists.length===53,'All existing artists must remain in the catalog');
 for(const artist of artists){
  const response=await page.goto(artist.url);check(response.status()===200,'Every artist profile must render');
  check(await page.locator('.artist-listening-panel').count()===1,'Exactly one unified listening panel');
  check(await page.locator('.profile-art-column .profile-art,.profile-art-column .listen-panel').count()===0,'Separate oversized artwork and link panels are removed');
  check(await page.locator('iframe').count()===0,'Artist embeds never load before choice');
  check(await page.locator('.artist-listening-preview').isVisible(),'Original artist-name preview fills the listening stage');
  assert.deepEqual(await page.locator('.artist-starter-card h3').allTextContents(),artist.first);checks++;
  check(await page.locator('.curated-song-section .song-row').count()===10,'Full ten-song selection is preserved');
  check(normalize(await page.locator('.artist-full-biography .prose').textContent())===normalize(artist.biography),'Full current editor biography and research text remain intact');
  check(await page.locator('.artist-identity-portrait.licensed-photo').count()===(artist.photo?1:0),'Only six cleared portraits appear near artist names');
  if(artist.photo){check(await page.locator('.artist-identity-portrait img').evaluate(async img=>{await img.decode();return img.naturalWidth>0;}),'Cleared identity portrait decodes');check(await page.locator('.artist-identity-credit a').count()>=2,'Portrait retains license and full-credit links');}
  if(artist.embed){
   check(await page.locator('.artist-listening-panel').getAttribute('data-spotify-embed')===artist.embed,'Player uses the exact verified artist ID');
   check(await page.locator('.artist-listening-panel .spotify-load').isVisible(),'Verified artist has one explicit listening action');
   check(await page.locator('.artist-listening-panel>.fine-print a').getAttribute('href')===artist.source,'Direct artist link is retained');
  }else{
   check(await page.locator('.artist-listening-panel .spotify-load,.artist-listening-panel iframe').count()===0,'Missing artist ID never produces an invented player');
   check((await page.locator('.artist-listening-fallback .spotify-button').getAttribute('href')).startsWith('https://open.spotify.com/search/'),'Missing verified artist ID has a labeled search fallback');
  }
 }
 check(external.length===0,'All 53 profiles make no third-party requests before choice');
 const caleb=artists.find(a=>a.slug==='caleb-hearn'),sara=artists.find(a=>a.slug==='sara-kays');
 await page.goto(caleb.url);await page.evaluate(()=>document.fonts.ready);
 const panel=await page.locator('.artist-listening-panel').boundingBox(),copy=await page.locator('.profile-copy').boundingBox(),starters=await page.locator('.artist-starters').boundingBox(),biography=await page.locator('.artist-full-biography').boundingBox();
 check(panel.x<copy.x&&Math.abs(panel.y-copy.y)<2,'Desktop listening panel aligns beside the introduction');
 check(starters.y>panel.y+panel.height&&starters.y<biography.y,'Three song introductions precede the full biography');
 check(starters.y<1050,'Starter-song section starts near the initial desktop viewport');
 await page.screenshot({path:'test-results/artist-listening-caleb.png',clip:{x:0,y:0,width:1440,height:Math.ceil(starters.y+starters.height+20)}});
 await page.goto(sara.url);await page.evaluate(()=>document.fonts.ready);await page.locator('.artist-discovery-hero').screenshot({path:'test-results/artist-listening-sara.png'});
 await page.route('https://open.spotify.com/embed/**',route=>route.abort());
 await page.locator('.spotify-load').focus();await page.keyboard.press('Enter');
 check(await page.locator('iframe').count()===1,'Keyboard listening loads exactly one official artist iframe');
 check(await page.locator('iframe').getAttribute('src')===sara.embed,'Loaded frame preserves the verified artist ID');
 check(await page.locator('.artist-listening-preview').isHidden()&&await page.locator('.spotify-load').isHidden(),'Player replaces preview and hides the completed load button');
 check(await page.locator('.artist-art-disclosure').isHidden(),'Abstract-art caption is not applied to Spotify artwork');
 check(await page.locator('.artist-identity-portrait img').isVisible(),'Identity portrait remains outside the untouched iframe');
 check(await page.locator('.artist-listening-panel>.fine-print a').isVisible(),'Direct link remains available when Spotify is blocked');
 check(!(await page.locator('iframe').getAttribute('src')).includes('autoplay'),'No autoplay parameter is added');
 await page.locator('.spotify-load').dispatchEvent('click');check(await page.locator('iframe').count()===1,'Repeat activation does not duplicate players');
 await page.evaluate(()=>window.artistFrame=document.querySelector('iframe'));
 await page.locator('.artist-starters .section-heading a').click();
 await page.waitForFunction(()=>{const r=document.querySelector('#artist-songs').getBoundingClientRect();return r.top>=0&&r.top<innerHeight;});
 check(new URL(page.url()).hash==='#artist-songs','Full-selection shortcut reaches the original ten-song anchor');
 check(await page.evaluate(()=>window.artistFrame===document.querySelector('iframe')),'In-page exploration preserves the same player');
 const save=page.locator('.artist-starter-card [data-save]').first(),songId=await save.getAttribute('data-save');await save.click();
 check((await page.locator(`[data-save="${songId}"]`).all()).length===2,'Starter and full-selection rows refer to the same existing song');
 check((await page.locator(`[data-save="${songId}"]`).evaluateAll(buttons=>buttons.every(b=>b.getAttribute('aria-pressed')==='true'))),'Saving a starter synchronizes its full-list button');
 for(const width of [320,390,768]){
  await page.setViewportSize({width,height:844});await page.goto(sara.url);
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`No artist profile overflow at ${width}px`);
  if(width===390){
   const intro=await page.locator('.artist-introduction').boundingBox(),listen=await page.locator('.artist-listening-panel').boundingBox(),bio=await page.locator('.artist-biography-preview').boundingBox(),picks=await page.locator('.artist-starters').boundingBox();
   check(intro.y+intro.height<=listen.y&&listen.y+listen.height<=bio.y&&bio.y+bio.height<picks.y,'Mobile order is identity, listening, biography preview and starter songs');
   await page.screenshot({path:'test-results/artist-listening-mobile.png',fullPage:true});
   await page.locator('.spotify-load').click();check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Loaded artist frame fits the mobile column');
  }
 }
 const nojs=await browser.newContext({javaScriptEnabled:false,viewport:{width:390,height:844}}),native=await nojs.newPage();await native.goto(caleb.url);
 check(await native.locator('.spotify-load').isHidden()&&await native.locator('iframe').count()===0,'No-JavaScript page offers no inert load button');
 check(await native.locator('.artist-listening-panel>.fine-print a').isVisible(),'Direct Spotify link works without JavaScript');
 await native.locator('.artist-profile-actions a[href="#artist-start"]').click();
 await native.waitForFunction(()=>{const r=document.querySelector('#artist-start').getBoundingClientRect();return r.top>=0&&r.top<innerHeight;});
 check(new URL(native.url()).hash==='#artist-start','Native first-song shortcut works without JavaScript');await nojs.close();
 check(errors.length===0,'No browser script errors: '+errors.join(', '));
 const verified=artists.filter(a=>a.embed).length;
 await fs.writeFile('test-results/artist-listening-summary.json',JSON.stringify({checks,artists:artists.length,verified_players:verified,search_fallbacks:artists.length-verified,cleared_portraits:artists.filter(a=>a.photo).length,third_party_before_choice:0,native_fallback:true,live_audio_verified:false},null,2));
 console.log(`PASS: ${checks} artist-listening checks; ${artists.length} profiles, ${verified} verified players, ${artists.length-verified} honest search fallbacks, intact biographies and selections, mobile, keyboard and no-JS behavior.`);
}finally{await browser.close();}
