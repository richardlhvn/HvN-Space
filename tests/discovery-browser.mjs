import {chromium} from 'playwright';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';

const base=process.env.TEST_URL || 'http://localhost:8080';
const song='/songs/shy-martin-are-you-happy/';
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH || '/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;const check=(value,message)=>{assert.ok(value,message);checks++;};
await fs.mkdir('test-results',{recursive:true});
try {
 const context=await browser.newContext({viewport:{width:1440,height:1050}}),page=await context.newPage();
 const errors=[],external=[];page.on('pageerror',e=>errors.push(e.message));
 page.on('request',r=>{const u=new URL(r.url());if(['http:','https:'].includes(u.protocol)&&u.origin!==new URL(base).origin)external.push(r.url());});
 await page.goto(new URL(song,base).href);await page.evaluate(()=>document.fonts.ready);
 check(await page.locator('.spotify-preview.has-artist-photo').isVisible(),'Cleared artist portrait fills the initial listening panel');
 check(await page.locator('.listening-portrait img').evaluate(async img=>{await img.decode();return img.naturalWidth>0;}),'Licensed preview photo must decode');
 check((await page.locator('.listening-photo-credit').textContent()).includes('CC BY-SA 4.0'),'ShareAlike attribution must accompany the portrait');
 check(await page.locator('iframe').count()===0&&external.length===0,'No Spotify or other third-party resource before choice');
 check((await page.locator('.next-discovery').boundingBox()).y<1050,'Next discovery must appear in the initial desktop viewport');
 check(await page.locator('.next-artist a').getAttribute('href')!==await page.locator('.artist-byline').getAttribute('href'),'A matching different artist is featured');
 check((await page.locator('.discovery-results').boundingBox()).y<(await page.locator('.song-story').boundingBox()).y,'Suggestions precede the longer research notes');
 await page.locator('.volume-help summary').click();
 check((await page.locator('.volume-help').textContent()).includes('Volume Mixer'),'Volume help explains real device/browser controls');
 check(await page.getByRole('slider').count()===0&&await page.locator('iframe').count()===0,'Volume help does not pretend to control the widget or start playback');
 await page.screenshot({path:'test-results/discovery-desktop.png',fullPage:true});
 await page.screenshot({path:'test-results/discovery-desktop-preview.png'});
 await page.route('https://open.spotify.com/embed/**',route=>route.abort());
 await page.locator('.spotify-load').click();
 check(await page.locator('iframe').count()===1&&await page.locator('.spotify-preview').isHidden(),'Explicit listening choice replaces the visual with one player');
 check(await page.locator('.spotify-load').isHidden(),'The load button disappears after loading');
 check(!((await page.locator('iframe').getAttribute('src')).includes('autoplay')),'No autoplay parameter is added');
 check(await page.locator('.spotify-player>.fine-print a').isVisible(),'Direct fallback stays usable when the embed is blocked');
 await page.evaluate(()=>window.testSpotifyFrame=document.querySelector('.spotify-player iframe'));
 await page.locator('.song-discovery-actions [data-discovery-open]').click();
 check(await page.locator('.discovery-controls').evaluate(d=>d.open),'Find another song opens the inline controls');
 check(await page.locator('#discovery-feeling').evaluate(e=>e===document.activeElement),'Opening controls gives keyboard users a useful focus target');
 const submit=async(feeling,theme)=>{
  await page.selectOption('#discovery-feeling',feeling);await page.selectOption('#discovery-theme',theme);
  await page.locator('.discovery-form button').click();
  await page.waitForFunction(()=>!document.querySelector('.discovery-form button').disabled);
 };
 await submit('hopeful','any');
 check(await page.locator('.discovery-card').count()>0,'Selecting hopeful produces real matching suggestions');
 for(const reason of await page.locator('.discovery-card .discovery-reason').allTextContents()){check(/hopeful/i.test(reason),'Reasons reflect the selected feeling');}
 check(await page.evaluate(()=>window.testSpotifyFrame===document.querySelector('.spotify-player iframe')),'Filtering must not replace the loaded Spotify iframe');
 check(new URL(page.url()).searchParams.get('ag_feeling')==='hopeful','Filter state remains in the shareable page URL');
 const save=page.locator('.discovery-card [data-save]').first(),savedId=await save.getAttribute('data-save');await save.click();
 check(await save.getAttribute('aria-pressed')==='true','Suggested songs can be saved');
 await submit('euphoric','body-image');
 check(await page.locator('.discovery-empty').isVisible()&&await page.locator('.next-discovery-link').count()===0,'Unsupported combinations show an honest empty state without a bogus next link');
 check(await page.evaluate(()=>window.testSpotifyFrame===document.querySelector('.spotify-player iframe')),'An empty search still leaves playback untouched');
 await page.route('**/*ag_feeling=hopeful*',route=>route.abort());await submit('hopeful','any');
 check((await page.locator('.discovery-status').textContent()).includes('weren’t updated'),'Failed updates explain that the old results were retained');
 check((await page.locator('.discovery-status a').getAttribute('href')).includes('ag_feeling=hopeful'),'Failed updates provide a normal full-page link');
 await page.unroute('**/*ag_feeling=hopeful*');await submit('hopeful','any');
 check(await page.locator(`.discovery-card [data-save="${savedId}"]`).getAttribute('aria-pressed')==='true','Saved state survives an inline results replacement');
 const paths=new Set([new URL(page.url()).pathname]);
 for(let i=0;i<4;i++){
  const next=await page.locator('.next-discovery-link').getAttribute('href');check(new URL(next).searchParams.has('ag_trail'),'Next links carry a bounded local discovery trail');
  await page.goto(next);const path=new URL(page.url()).pathname;check(!paths.has(path),'A short discovery journey must not immediately repeat tracks');paths.add(path);
  check(await page.locator('iframe').count()===0,'Moving to another song requires a new listening choice');
 }
 await page.goto(new URL('/songs/loote-this-is-the-last-song-about-you/',base).href);
 check(await page.locator('.spotify-preview.has-original-art').isVisible(),'Uncleared artist uses an original visual instead of an unlicensed portrait');
 check((await page.locator('.listening-art-caption').textContent()).includes('This Is the Last Song About You'),'Original visual identifies the actual song');
 check(await page.locator('.spotify-load,iframe').count()===0,'A missing track link never produces a fake listening player');
 check((await page.locator('.song-listening-fallback .spotify-button').getAttribute('href')).startsWith('https://open.spotify.com/artist/'),'Missing track retains its verified artist-profile fallback');
 await page.screenshot({path:'test-results/discovery-abstract-desktop.png',fullPage:true});
 await page.goto(new URL(song+'?ag_feeling[]=hopeful&ag_theme=%3Cscript%3Ealert(1)%3C/script%3E',base).href);
 check((await page.locator('.discovery-status').textContent()).includes('unavailable'),'Malformed filters are handled as safe defaults');
 for(const width of [320,390,768]){
  await page.setViewportSize({width,height:844});await page.goto(new URL(song,base).href);
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`No page overflow at ${width}px`);
  if(width===390){
   const intro=await page.locator('.song-introduction').boundingBox(),listen=await page.locator('.song-listening-panel').boundingBox(),next=await page.locator('.next-discovery').boundingBox();
   check(intro.y+intro.height<=listen.y&&listen.y+listen.height<=next.y,'Mobile order is introduction, listening, then next discovery');
   await page.screenshot({path:'test-results/discovery-mobile.png',fullPage:true});
   await page.locator('.spotify-load').focus();await page.keyboard.press('Enter');
   check(await page.locator('iframe').count()===1&&await page.locator('.spotify-preview').isHidden(),'Keyboard listening activation works on mobile');
   check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Loaded mobile frame fits its column');
  }
 }
 check(errors.length===0,'No browser script errors: '+errors.join(', '));
 const nojs=await browser.newContext({javaScriptEnabled:false,viewport:{width:1280,height:950}}),native=await nojs.newPage();
 await native.goto(new URL(song,base).href);
 check(await native.locator('iframe').count()===0&&await native.locator('.spotify-load').isHidden(),'Without JavaScript the page offers the direct link, not an inert button');
 await native.locator('.discovery-controls summary').click();await native.selectOption('#discovery-feeling','hopeful');await native.selectOption('#discovery-theme','any');
 await Promise.all([native.waitForURL('**/*ag_feeling=hopeful*'),native.locator('.discovery-form button').click()]);
 check(await native.locator('.discovery-card').count()>0,'The discovery form also works as a normal GET without JavaScript');
 check((await native.locator('.discovery-card .discovery-reason').first().textContent()).toLowerCase().includes('hopeful'),'Native results honor the same filter semantics');
 await nojs.close();
 await fs.writeFile('test-results/discovery-browser-summary.json',JSON.stringify({checks,playback_preserved_on_filter:true,third_party_before_choice:0,no_js_form:true,recent_repeat_prevention:true},null,2));
 console.log(`PASS: ${checks} discovery/listening browser checks, inline filters, playback preservation, empty/error states, recent trail, mobile and no-JS fallback.`);
}finally{await browser.close();}
