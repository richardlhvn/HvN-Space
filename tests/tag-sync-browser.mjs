import {chromium} from 'playwright';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const base=process.env.TEST_URL||'http://localhost:8080';
const groups=JSON.parse(await fs.readFile('plugin/data/theme-groups.json','utf8')).groups;
const keys=Object.fromEntries(Object.entries(groups).map(([key,g])=>[g.name,key]));
const expression=`$out=[];foreach(ag_public_catalog_ids() as $id){if(get_post_type($id)==='song'){$out[]=['id'=>$id,'url'=>get_permalink($id),'themes'=>array_values(array_filter(array_map('trim',explode(',',get_post_meta($id,'_ag_themes',true)))))];}}WP_CLI::line(wp_json_encode($out));`;
const records=JSON.parse(execFileSync('docker',['compose','run','--rm','cli','wp','eval',expression],{encoding:'utf8'}));
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;const check=(ok,msg)=>{assert.ok(ok,msg);checks++;};
await fs.mkdir('test-results',{recursive:true});
try {
 const page=await browser.newPage({viewport:{width:1440,height:1050}});
 const song=records.find(r=>r.url.includes('think-before-i-talk'));
 await page.goto(song.url);await page.locator('.discovery-controls summary').click();
 const options=await page.locator('[name=ag_theme] option').evaluateAll(nodes=>nodes.filter(n=>!['same','any'].includes(n.value)).map(n=>({key:n.value,label:n.textContent})));
 check(options.length===20&&options.every(x=>groups[x.key]?.name===x.label),'Theme dropdown contains exactly the twenty canonical topics.');
 check(!options.some(x=>['breakup','breakups','adolescence','arguments'].includes(x.key)),'Granular labels no longer crowd the selector.');
 for(const r of records){check(r.themes.every(t=>keys[t]),'Every stored public theme uses a canonical name.');check(new Set(r.themes).size===r.themes.length,'Stored public themes are deduplicated.');}
 await page.route('https://open.spotify.com/embed/**',r=>r.abort());await page.locator('.spotify-load').click();await page.evaluate(()=>window.tagSyncFrame=document.querySelector('iframe'));
 for(const {key} of options){
  await page.selectOption('[name=ag_theme]',key);await page.selectOption('[name=ag_feeling]','any');
  await page.locator('.discovery-form button').click();await page.waitForFunction(()=>!document.querySelector('.discovery-form button').disabled);
  const ids=await page.locator('.discovery-card [data-save]').evaluateAll(nodes=>nodes.map(n=>Number(n.dataset.save)));
  const expected=new Set(records.filter(r=>r.id!==song.id&&r.themes.includes(groups[key].name)).map(r=>r.id));
  check(ids.length===Math.min(3,expected.size)&&ids.every(id=>expected.has(id))&&new Set(ids).size===ids.length,'Selected topic returns only actual matching songs: '+key);
  check(await page.evaluate(()=>window.tagSyncFrame===document.querySelector('iframe')),'Theme changes preserve the existing Spotify iframe.');
 }
 await page.goto(song.url+'?ag_theme=breakups&ag_feeling=any');
 check(await page.locator('[name=ag_theme]').inputValue()==='heartbreak-breakups','Old theme URLs select the canonical group.');
 check(await page.locator('.discovery-card').count()>0,'Historical theme URLs still return matching songs.');
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:width===1440?1050:844});await page.goto(song.url);await page.locator('.discovery-controls summary').click();await page.evaluate(()=>document.fonts.ready);await page.locator('#discover-more').scrollIntoViewIfNeeded();
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Theme controls fit desktop/mobile.');
  await page.screenshot({path:`test-results/tag-sync-themes-${width}.png`});
 }
 const native=await browser.newContext({javaScriptEnabled:false}),nojs=await native.newPage();
 await nojs.goto(song.url);await nojs.locator('.discovery-controls summary').click();await nojs.selectOption('[name=ag_theme]','friendship');await nojs.selectOption('[name=ag_feeling]','any');
 await Promise.all([nojs.waitForURL('**/*ag_theme=friendship*'),nojs.locator('.discovery-form button').click()]);
 check(await nojs.locator('.discovery-card').count()>0&&await nojs.locator('[name=ag_theme]').inputValue()==='friendship','Grouped themes work without JavaScript.');await native.close();
 for(const path of ['/genre/electropop/','/mood/introspective/']){
  const response=await page.request.get(base+path,{maxRedirects:0});check(response.status()===302,'Retired native taxonomy URLs redirect to grouped public results.');
 }
 console.log(`PASS: ${checks} tag-sync browser checks: twenty themes, actual stored labels, exact topic matches, legacy URLs, mobile, no-JS and player preservation.`);
} finally {await browser.close();}
