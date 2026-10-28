import {chromium} from 'playwright';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const base=process.env.TEST_URL || 'http://localhost:8080';
const expression=`$out=['artists'=>[],'songs'=>[],'orphaned'=>[]]; foreach(ag_handoff_packs() as $slug=>$pack){$a=ag_handoff_find($slug);$out['artists'][]=['slug'=>$slug,'url'=>get_permalink($a),'photo'=>(bool)ag_media_credit($a)];foreach($pack['songs'] as $s){$id=ag_handoff_find($slug,$s,$a);$out['songs'][]=['url'=>get_permalink($id),'embed'=>ag_spotify_embed_url($id),'source'=>get_post_meta($id,'_ag_spotify',true)];foreach(['related','recommended'] as $field){foreach(array_filter(explode(',',get_post_meta($id,'_ag_'.$field,true))) as $target){if(!get_post($target))$out['orphaned'][]=$target;}}}foreach(['recommended','related'] as $field){foreach(array_filter(explode(',',get_post_meta($a,'_ag_'.$field,true))) as $target){if(!get_post($target))$out['orphaned'][]=$target;}}} WP_CLI::line(wp_json_encode($out));`;
const catalog=JSON.parse(execFileSync('docker',['compose','run','--rm','cli','wp','eval',expression],{encoding:'utf8'}));
assert.equal(catalog.artists.length,10);assert.equal(catalog.songs.length,100);assert.equal(new Set(catalog.songs.map(s=>s.url)).size,100);assert.equal(catalog.orphaned.length,0);
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH || '/usr/bin/chromium',args:['--no-sandbox']});
const context=await browser.newContext({viewport:{width:1440,height:1050}});const page=await context.newPage();let checks=4;
const check=(v,m)=>{assert.ok(v,m);checks++;};
const external=[];page.on('request',r=>{const u=new URL(r.url());if(['http:','https:'].includes(u.protocol)&&u.origin!==new URL(base).origin)external.push(r.url());});
await fs.mkdir('test-results',{recursive:true});
try{
 for(let i=0;i<catalog.songs.length;i+=6){
  await Promise.all(catalog.songs.slice(i,i+6).map(async song=>{
   const response=await page.request.get(song.url);check(response.status()===200,'Song page must render');const html=await response.text();check(html.includes(`data-spotify-embed="${song.embed}"`),'Song must have its exact official embed');check(!html.includes('<iframe'),'No player may load before explicit choice');check(html.includes('Sources &amp; further reading')||html.includes('Sources & further reading'),'Song must retain source citations');
  }));
 }
 await page.goto(new URL('/artists/',base).href);await page.evaluate(()=>document.fonts.ready);
 check(await page.locator('.music-card').count()===10,'Ten artists in directory');check(await page.locator('.licensed-photo img').count()===6,'Exactly six real photos');
 check(await page.locator('.music-card .media-credit').count()===6,'Each directory photo has attribution');
 for(const photo of await page.locator('.licensed-photo img').all()){await photo.scrollIntoViewIfNeeded();check(await photo.evaluate(async image=>{await image.decode();return image.naturalWidth>0;}),'Licensed photo must decode successfully');}
 await page.evaluate(()=>scrollTo(0,0));
 await page.screenshot({path:'test-results/media-directory-desktop.png',fullPage:true});
 for(const artist of catalog.artists){await page.goto(artist.url);check(await page.locator('.profile-section .song-row').count()===10,'Ten recommendations per artist');check(await page.locator('.profile-art.licensed-photo').count()===(artist.photo?1:0),'Approved photos and four placeholders must match');}
 await page.goto(new URL('/artists/shy-martin/',base).href);check((await page.locator('.art-credit').textContent()).includes('CC BY-SA 4.0'),'ShareAlike credit visible near photo');
 await page.screenshot({path:'test-results/media-artist-desktop.png',fullPage:true});
 await page.goto(catalog.songs[0].url);check(await page.locator('iframe').count()===0,'No iframe before consent');check(external.length===0,'No third-party requests before choice');
 await page.screenshot({path:'test-results/media-song-desktop.png',fullPage:true});
 await page.route('https://open.spotify.com/embed/**',route=>route.abort());
 await page.locator('.spotify-load').click();check(await page.locator('iframe').count()===1,'Load choice creates one iframe');check(await page.locator('iframe').getAttribute('loading')==='lazy','Player is lazy loaded');
 check(await page.locator('iframe').getAttribute('src')===catalog.songs[0].embed,'Player uses exact approved origin/track');
 check(await page.locator('.spotify-player .fine-print a').isVisible(),'Fallback link remains visible when player is blocked');
 await page.locator('.spotify-load').dispatchEvent('click');check(await page.locator('iframe').count()===1,'Repeated activation does not duplicate player');
 await page.unroute('https://open.spotify.com/embed/**');
 await page.route('https://open.spotify.com/embed/**',async route=>{await new Promise(resolve=>setTimeout(resolve,1500));await route.fulfill({contentType:'text/html',body:'<!doctype html><title>Slow test fixture</title><p>Delayed test frame; no audio.</p>'});});
 await page.goto(catalog.songs[0].url);await page.locator('.spotify-load').focus();await page.keyboard.press('Enter');
 check(await page.locator('.spotify-player .fine-print a').isVisible(),'Fallback remains usable while a slow frame loads');
 check(await page.locator('iframe').getAttribute('height')==='352','Slow frame reserves its documented height');
 check(await page.frameLocator('.spotify-player iframe').locator('body').textContent().then(t=>t.includes('Delayed test frame')),'Delayed frame loads after keyboard activation');
 await page.goto(new URL('/media-credits/',base).href);check(await page.locator('.page-prose h2').count()===9,'Credits list six photos plus site/Spotify/correction sections');
 check(await page.getByRole('link',{name:'CC BY-SA 4.0',exact:true}).count()===1,'Media credits preserve ShareAlike');
 await page.screenshot({path:'test-results/media-credits-desktop.png',fullPage:true});
 await page.setViewportSize({width:390,height:844});
 for(const path of ['/artists/','/artists/shy-martin/','/songs/hurts-so-good/','/media-credits/']){await page.goto(new URL(path,base).href);check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'No mobile overflow');}
 await page.goto(new URL('/artists/shy-martin/',base).href);await page.screenshot({path:'test-results/media-artist-mobile.png',fullPage:true});
 await page.goto(new URL('/songs/hurts-so-good/',base).href);await page.screenshot({path:'test-results/media-song-mobile.png',fullPage:true});
 await fs.writeFile('test-results/media-browser-summary.json',JSON.stringify({checks,artist_count:10,song_count:100,photo_count:6,placeholders:4,external_before_choice:0,blocked_player_fallback:true},null,2));
 console.log(`PASS: ${checks} media browser checks, all 100 song pages, six credited photos, four placeholders, no orphaned links, lazy/blocked player, and mobile layouts.`);
}finally{await browser.close();}
