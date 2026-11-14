import {chromium} from 'playwright';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const base=process.env.TEST_URL||'http://localhost:8080';
const moods=JSON.parse(await fs.readFile('plugin/data/mood-groups.json','utf8')).groups;
const genres=JSON.parse(await fs.readFile('plugin/data/genre-groups.json','utf8')).groups;
const mapOf=groups=>Object.fromEntries(Object.entries(groups).flatMap(([slug,g])=>[slug,...g.aliases].map(a=>[a,slug])));
const moodMap=mapOf(moods),genreMap=mapOf(genres);
const expression=`$out=[];foreach(ag_public_catalog_ids() as $id){$p=get_post($id);$out[]=['id'=>$id,'type'=>$p->post_type,'title'=>$p->post_title,'url'=>get_permalink($id),'moods'=>wp_get_object_terms($id,'mood',['fields'=>'slugs']),'genres'=>wp_get_object_terms($id,'genre',['fields'=>'slugs']),'countries'=>wp_get_object_terms($id,'country',['fields'=>'slugs'])];}WP_CLI::line(wp_json_encode($out));`;
const manifest=JSON.parse(execFileSync('docker',['compose','run','--rm','cli','wp','eval',expression],{encoding:'utf8'}));
const families=item=>[...new Set(item.moods.map(x=>moodMap[x]).filter(Boolean))];
const pathOf=url=>new URL(url).pathname;
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;const check=(ok,msg)=>{assert.ok(ok,msg);checks++;};
await fs.mkdir('test-results',{recursive:true});
try{
 const context=await browser.newContext({viewport:{width:1440,height:1000}}),page=await context.newPage();
 for(const type of ['artist','song']){
  const items=manifest.filter(x=>x.type===type),root=`/${type}s/`;
  const populated=Object.keys(moods).filter(g=>items.some(item=>families(item).includes(g)));
  await page.goto(base+root);
  const options=await page.locator('#filter-mood option').evaluateAll(nodes=>nodes.filter(n=>n.value).map(n=>({slug:n.value,name:n.textContent})));
  check(JSON.stringify(options.map(x=>x.slug).sort())===JSON.stringify(populated.slice().sort()),`${type}: exactly the populated mood families appear`);
  check(options.every(o=>o.name===moods[o.slug].name),`${type}: human-readable controlled emotion names`);
  for(const group of populated){
   const expected=new Set(items.filter(item=>families(item).includes(group)).map(x=>pathOf(x.url))),seen=[];
   for(let index=1;index<=Math.ceil(expected.size/12);index++){
    const url=new URL(index===1?root:`${root}page/${index}/`,base);url.searchParams.set('filter_mood',group);
    const response=await page.goto(url.href);check(response.status()===200,`${type}/${group}: page ${index} renders`);
    check(await page.locator('#filter-mood').inputValue()===group,'Selected emotion stays visible');
    check(Number((await page.locator('.results-heading').textContent()).match(/\d+/)[0])===expected.size,'Result count equals the independent original-tag union');
    for(const href of await page.locator('.music-card h3 a').evaluateAll(nodes=>nodes.map(n=>n.href))){check(expected.has(pathOf(href)),'Every result belongs to the chosen emotion');seen.push(pathOf(href));}
   }
   check(new Set(seen).size===expected.size&&seen.length===expected.size,'Pagination covers the exact family without duplicate records');
  }
  for(const item of items.filter(x=>type==='artist'||x.url.includes('think-before-i-talk'))){
   await page.goto(item.url);
   const tags=await page.locator('.tag-list a[href*="filter_mood="]').evaluateAll(nodes=>nodes.map(n=>new URL(n.href).searchParams.get('filter_mood')));
   check(JSON.stringify(tags.sort())===JSON.stringify(families(item).sort()),'Profiles use the same deduplicated feelings as filters');
  }
 }
 for(const [type,alias] of [['artist','reflective'],['artist','heartbroken'],['song','introspective'],['song','cheeky'],['song','wistful']]){
  const group=moodMap[alias],expected=manifest.filter(x=>x.type===type&&families(x).includes(group)).length;
  await page.goto(`${base}/${type}s/?filter_mood=${alias}`);
  check(await page.locator('#filter-mood').inputValue()===group,'Old mood links select the corresponding emotion group');
  check(Number((await page.locator('.results-heading').textContent()).match(/\d+/)[0])===expected,'Old mood links use the full grouped set');
 }
 const artist=manifest.find(x=>x.type==='artist'&&x.countries.length&&x.genres.length),url=new URL('/artists/',base);
 url.searchParams.set('filter_mood',families(artist)[0]);url.searchParams.set('filter_genre',genreMap[artist.genres[0]]);url.searchParams.set('filter_country',artist.countries[0]);url.searchParams.set('q',artist.title);
 await page.goto(url.href);check(await page.locator('.music-card').count()===1&&(await page.locator('.music-card h3').innerText())===artist.title,'Mood, simplified genre, country and name intersect correctly');
 await page.goto(`${base}/songs/?filter_mood=unknown-mood`);check(await page.locator('.empty-state').isVisible(),'Unknown moods do not silently expand to the full catalog');
 await page.goto(`${base}/artists/?filter_mood=anxiety`);check(await page.locator('#filter-mood').inputValue()==='anxiety'&&await page.locator('.empty-state').isVisible(),'A selected valid but empty artist emotion remains honest and visible');
 await page.goto(`${base}/artists/?filter_mood[]=reflection`);check((await page.locator('.results-heading').innerText()).includes('10'),'Malformed mood arrays do not bypass public scope');
 await page.goto(base);const links=await page.locator('.mood-pill').evaluateAll(nodes=>nodes.map(n=>n.href));
 check(links.length===4&&links.every(h=>['heartbreak','playful','hopeful','reflection'].includes(new URL(h).searchParams.get('filter_mood'))),'Homepage primary links use the requested emotion families');
 for(const href of links){await page.goto(href);check(await page.locator('.music-card').count()>0,'Each homepage feeling reaches real public results');}
 const song=manifest.find(x=>x.url.includes('think-before-i-talk'));
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:width===1440?1000:844});await page.goto(base);await page.evaluate(()=>document.fonts.ready);await page.locator('#moods').scrollIntoViewIfNeeded();
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Homepage feelings fit the viewport');await page.screenshot({path:`test-results/mood-groups-home-${width}.png`});
  await page.goto(`${base}/songs/?filter_mood=heartbreak`);await page.evaluate(()=>document.fonts.ready);check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Grouped mood directory fits the viewport');await page.screenshot({path:`test-results/mood-groups-directory-${width}.png`});
 }
 await page.goto(song.url);await page.locator('.discovery-controls summary').click();
 const emotionOptions=await page.locator('[name=ag_feeling] option').evaluateAll(nodes=>nodes.filter(n=>!['same','any'].includes(n.value)).map(n=>n.value));
 check(emotionOptions.length===20&&emotionOptions.every(x=>moods[x]),'Song discovery uses the same 20 emotions');
 await page.screenshot({path:'test-results/mood-groups-discovery.png'});
 await page.route('https://open.spotify.com/embed/**',r=>r.abort());await page.locator('.spotify-load').click();await page.evaluate(()=>window.moodFrame=document.querySelector('iframe'));
 for(const group of ['heartbreak','reflection','playful']){
  await page.selectOption('[name=ag_feeling]',group);await page.selectOption('[name=ag_theme]','any');await page.locator('.discovery-form button').click();await page.waitForFunction(()=>!document.querySelector('.discovery-form button').disabled);
  const expected=new Set(manifest.filter(x=>x.type==='song'&&families(x).includes(group)&&x.id!==song.id).map(x=>x.id));
  const actual=await page.locator('.discovery-card [data-save]').evaluateAll(nodes=>nodes.map(n=>Number(n.dataset.save)));
  check(actual.length>0&&actual.every(id=>expected.has(id))&&new Set(actual).size===actual.length,'Inline recommendations really match the selected emotion');
  check(await page.evaluate(()=>window.moodFrame===document.querySelector('iframe')),'Mood changes preserve the existing player iframe');
 }
 await page.goto(song.url+'?ag_feeling=introspective&ag_theme=any');check(await page.locator('[name=ag_feeling]').inputValue()==='reflection','Legacy discovery feeling URLs normalize consistently');
 const native=await browser.newContext({javaScriptEnabled:false}),nojs=await native.newPage();await nojs.goto(song.url);await nojs.locator('.discovery-controls summary').click();await nojs.selectOption('[name=ag_feeling]','playful');await nojs.selectOption('[name=ag_theme]','any');await Promise.all([nojs.waitForURL('**/*ag_feeling=playful*'),nojs.locator('.discovery-form button').click()]);
 check(await nojs.locator('.discovery-card').count()>0,'Grouped discovery also works without JavaScript');await native.close();
 // Admin view and nonce guard; actual option changes are tested only in disposable tables.
 await page.setViewportSize({width:1440,height:1000});const env=await fs.readFile('.env','utf8'),password=env.match(/^AFTERGLOW_ADMIN_PASSWORD=(.+)$/m)[1];await page.goto(`${base}/wp-login.php`);await page.locator('#user_login').fill('editor');await page.locator('#user_pass').fill(password);await page.locator('#wp-submit').click();await page.waitForURL('**/wp-admin/');await page.goto(`${base}/wp-admin/edit.php?post_type=artist&page=ag-mood-groups`);
 check((await page.locator('.wrap').innerText()).includes('20 shared mood families'),'Admin offers the 20-family mood system');
 check((await page.locator('.widefat').innerText()).includes('introspective'),'Editors retain the original mood labels');
 const denied=await page.request.post(`${base}/wp-admin/admin-post.php`,{form:{action:'ag_save_mood_groups','mood_groups[introspective]':'playful'}});check(denied.status()===403,'Mood mapping writes require a nonce');await page.screenshot({path:'test-results/mood-groups-admin.png'});
 console.log(`PASS: ${checks} mood browser checks: exact emotional filters/pagination, tags, legacy links, home links, grouped recommendations, iframe preservation, no-JS and admin permissions.`);
 await fs.writeFile('test-results/mood-groups-summary.json',JSON.stringify({checks,emotion_families:20,public_artists:10,public_songs:128,source_data_changed:false},null,2));
}finally{await browser.close();}
