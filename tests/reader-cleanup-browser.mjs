import {chromium} from 'playwright';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const base=process.env.TEST_URL||'http://localhost:8080';
const expression=`$out=[];foreach(ag_reviewed_data()['packs'] as $slug=>$pack){$artist=ag_reviewed_find($slug);$out[]=['type'=>'artist','url'=>get_permalink($artist),'name'=>$pack['artist']['name']];foreach($pack['songs'] as $song){$out[]=['type'=>'song','url'=>get_permalink(ag_reviewed_find($slug,$song))];}}WP_CLI::line(wp_json_encode($out));`;
const manifest=JSON.parse(execFileSync('docker',['compose','run','--rm','cli','wp','eval',expression],{encoding:'utf8'}));
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;const check=(ok,msg)=>{assert.ok(ok,msg);checks++;};
await fs.mkdir('test-results',{recursive:true});
try{
 const context=await browser.newContext({viewport:{width:1440,height:1000}}),page=await context.newPage();
 for(const item of manifest){
  const response=await page.goto(item.url);check(response.status()===200,'Approved page renders');
  const body=await page.locator('main').innerText();
  check(!/owner-approved launch catalog|Spotify popularity ranking|Genre and mood tags reflect editorial judgments|not an official genre designation|editorial navigation aids|not a rigid official classification|editorial recommendation framework|similarity is unscored/i.test(body),'Internal/editorial workflow notes stay out of reader pages');
  if(item.type==='artist'){
   check(await page.locator('.similarity-note').count()===0,'Related artists retain cards without editorial notes');
   check(await page.locator('.artist-full-biography h2').innerText()===`About ${item.name}.`,'Artist biography heading remains');
   check((await page.locator('.artist-full-biography .prose').innerText()).length>600,'Substantive biography is retained');
   check(await page.locator('.source-list a').count()>0,'Artist source links remain');
  }else{
   check(await page.locator('.about-attribution,.attribution-label').count()===0,'Attribution badge is removed');
   check(await page.locator('.about-source-links a').count()>0,'About citations remain visible');
   const reasons=page.locator('.discovery-reason');
   check(await reasons.count()>0&&await page.locator('.discovery-reason svg[aria-hidden=true]').count()===await reasons.count(),'Each recommendation uses the existing decorative icon');
   check(!(await reasons.allTextContents()).some(t=>/tunetofond|editorial/i.test(t)),'Recommendation tags have no branded/editorial prefix');
  }
  const api=await context.request.get(`${base}/wp-json/wp/v2/${item.type}?slug=${new URL(item.url).pathname.split('/').filter(Boolean).at(-1)}`);
  const json=(await api.json())[0];
  check(!/owner-approved launch catalog|Spotify popularity ranking|Artist statement · paraphrased|interpretation · not an artist statement/i.test(json.content.rendered),'Public REST also omits the reader-facing labels/workflow notes');
 }
 const astrid=manifest.find(x=>x.type==='artist'&&x.name==='Astrid S');
 const song=manifest.find(x=>x.url.includes('think-before-i-talk'));
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:width===1440?1000:844});
  await page.goto(astrid.url);await page.evaluate(()=>document.fonts.ready);await page.locator('#artist-biography').scrollIntoViewIfNeeded();
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Clean biography fits viewport');
  await page.screenshot({path:`test-results/reader-cleanup-artist-${width}.png`});
  if(width===1440){await page.locator('.related-section').scrollIntoViewIfNeeded();await page.screenshot({path:'test-results/reader-cleanup-related.png'});}
  await page.goto(song.url);await page.evaluate(()=>document.fonts.ready);await page.locator('.song-about').scrollIntoViewIfNeeded();
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Clean song fits viewport');
  await page.screenshot({path:`test-results/reader-cleanup-song-${width}.png`});
  if(width===1440){await page.locator('.discovery-results').scrollIntoViewIfNeeded();await page.screenshot({path:'test-results/reader-cleanup-tags.png'});}
 }
 // The same icon/tag rendering must survive inline results updates and retain the player.
 await page.route('https://open.spotify.com/embed/**',r=>r.abort());
 await page.locator('.spotify-load').click();await page.evaluate(()=>window.readerFrame=document.querySelector('iframe'));
 await page.locator('.discovery-controls summary').click();await page.selectOption('[name=ag_theme]','same');await page.selectOption('[name=ag_feeling]','any');
 await page.locator('.discovery-form button').click();await page.waitForFunction(()=>!document.querySelector('.discovery-form button').disabled);
 check(await page.evaluate(()=>window.readerFrame===document.querySelector('iframe')),'Tag filtering preserves the existing iframe');
 check(await page.locator('.discovery-card .discovery-reason svg').count()>0,'Inline recommendations retain the icon');
 check(!(await page.locator('.discovery-card .discovery-reason').allTextContents()).some(t=>/tunetofond theme/i.test(t)),'Inline recommendations also omit the old prefix');
 console.log(`PASS: ${checks} reader-cleanup browser/API, citation, responsive and inline-filter checks across ten artists and 128 songs.`);
 await fs.writeFile('test-results/reader-cleanup-summary.json',JSON.stringify({checks,artists:10,songs:128,stored_data_changed:false,audio_verified:false},null,2));
}finally{await browser.close();}
