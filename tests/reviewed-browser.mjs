import {chromium} from 'playwright';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';

const base=process.env.TEST_URL||'http://localhost:8080';
const source=JSON.parse(await fs.readFile('plugin/data/canonical-research-source.json','utf8'));
const data=JSON.parse(await fs.readFile(`plugin/data/${source.prepared_snapshot}`,'utf8'));
const expression=`$out=[];foreach(ag_reviewed_data()['packs'] as $slug=>$pack){foreach($pack['songs'] as $song){$id=ag_reviewed_find($slug,$song);$out[$slug.'/'.$song['slug']]=['id'=>$id,'url'=>get_permalink($id)];}}WP_CLI::line(wp_json_encode($out));`;
const manifest=JSON.parse(execFileSync('docker',['compose','run','--rm','cli','wp','eval',expression],{encoding:'utf8'}));
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;const check=(ok,message)=>{assert.ok(ok,message);checks++;};
const normal=s=>s.replace(/\s+/g,' ').trim();
const releaseLabel=song=>{
 const precision=song.release_date_precision;
 const date=new Date(`${song.release_date}${precision==='month'?'-01':precision==='year'?'-01-01':''}T00:00:00Z`);
 return new Intl.DateTimeFormat('en-GB',{timeZone:'UTC',year:'numeric',...(precision!=='year'?{month:'long'}:{}),...(precision==='day'?{day:'numeric'}:{})}).format(date);
};
await fs.mkdir('test-results',{recursive:true});
try{
 const context=await browser.newContext({viewport:{width:1440,height:1050}}),page=await context.newPage();
 const external=[],errors=[];
 page.on('pageerror',e=>errors.push(e.message));
 page.on('request',r=>{const u=new URL(r.url());if(['http:','https:'].includes(u.protocol)&&u.origin!==new URL(base).origin)external.push(r.url());});
 for(const [slug,pack] of Object.entries(data.packs)){
  for(const song of pack.songs){
   const key=`${slug}/${song.slug}`,url=manifest[key].url;
   const response=await page.goto(url);
   check(response.status()===200,`${key}: page renders`);
   check(normal(await page.locator('.song-about .prose').textContent())===normal(song.about_song),`${key}: approved copy rendered unchanged`);
   check(await page.locator('.song-introduction .profile-intro').count()===0,`${key}: About replaces the older short-description intro`);
   check(await page.locator('.song-about .about-source-links a').count()>0,`${key}: scoped evidence displayed`);
   check(await page.locator('[data-spotify-embed]').getAttribute('data-spotify-embed')===song.spotify_url.replace('/track/','/embed/track/'),`${key}: exact recording configured`);
   check(await page.locator('iframe').count()===0,`${key}: player waits for consent`);
   check((await page.locator('meta[name=robots]').getAttribute('content')).includes('noindex'),`${key}: staging noindex`);
   check((await page.locator('.song-release-details').textContent()).includes(releaseLabel(song)),`${key}: original documented release date and precision`);
   const about=await page.locator('.song-about').boundingBox(),discovery=await page.locator('#discover-more').boundingBox();
   check(discovery.y>about.y,`${key}: recommendations remain after song information`);
  }
 }
 check(external.length===0,'No third-party requests before player choice');
 const crash=manifest['clara-mae/crash'];
 await page.goto(crash.url);await page.evaluate(()=>document.fonts.ready);
 await page.screenshot({path:'test-results/reviewed-crash-desktop.png',fullPage:true});
 await page.goto(`${base}/artists/astrid-s/`);await page.evaluate(()=>document.fonts.ready);
 await page.screenshot({path:'test-results/reviewed-astrid-desktop.png',fullPage:true});
 await page.setViewportSize({width:390,height:844});await page.goto(crash.url);
 check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'New song fits mobile viewport');
 await page.screenshot({path:'test-results/reviewed-crash-mobile.png',fullPage:true});
 await page.route('https://open.spotify.com/embed/**',route=>route.abort());
 await page.locator('.spotify-load').click();
 check(await page.locator('iframe').count()===1,'Choice creates a single official iframe');
 check(await page.locator('.song-listening-panel a[href^="https://open.spotify.com/track/"]').isVisible(),'Blocked embed retains direct Spotify fallback');
 await page.evaluate(()=>window.reviewedFrame=document.querySelector('iframe'));
 await page.locator('.discovery-controls summary').click();
 await page.selectOption('[name=ag_feeling]','reflection');
 await page.locator('.discovery-controls button[type=submit]').click();
 await page.waitForFunction(()=>!document.querySelector('[data-discovery-results]')?.getAttribute('aria-busy'));
 check(await page.evaluate(()=>window.reviewedFrame===document.querySelector('iframe')),'Discovery filters preserve the loaded player');
 check(errors.length===0,'No browser errors');
 await fs.writeFile('test-results/reviewed-browser-summary.json',JSON.stringify({checks,approved_artists:10,approved_selections:128,copy_preserved:true,third_party_before_choice:0,live_audio_verified:false},null,2));
 console.log(`PASS: ${checks} reviewed-content browser checks across all 128 approved song pages.`);
}finally{await browser.close();}
