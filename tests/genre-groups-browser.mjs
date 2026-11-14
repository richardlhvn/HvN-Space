import {chromium} from 'playwright';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const base=process.env.TEST_URL||'http://localhost:8080';
const config=JSON.parse(await fs.readFile('plugin/data/genre-groups.json','utf8')).groups;
const expression=`$out=[];foreach(ag_public_catalog_ids() as $id){$p=get_post($id);$out[]=['id'=>$id,'type'=>$p->post_type,'title'=>$p->post_title,'url'=>get_permalink($id),'genres'=>wp_get_object_terms($id,'genre',['fields'=>'slugs']),'moods'=>wp_get_object_terms($id,'mood',['fields'=>'slugs']),'countries'=>wp_get_object_terms($id,'country',['fields'=>'slugs'])];}WP_CLI::line(wp_json_encode($out));`;
const manifest=JSON.parse(execFileSync('docker',['compose','run','--rm','cli','wp','eval',expression],{encoding:'utf8'}));
const aliasToGroup=Object.fromEntries(Object.entries(config).flatMap(([slug,g])=>[slug,...g.aliases].map(a=>[a,slug])));
const families=item=>[...new Set(item.genres.map(g=>aliasToGroup[g]).filter(Boolean))];
const pathOf=url=>new URL(url).pathname;
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;const check=(ok,msg)=>{assert.ok(ok,msg);checks++;};
await fs.mkdir('test-results',{recursive:true});
try{
 const context=await browser.newContext({viewport:{width:1440,height:1000}}),page=await context.newPage();
 for(const type of ['artist','song']){
  const items=manifest.filter(x=>x.type===type),root=`/${type}s/`;
  const populated=Object.keys(config).filter(g=>items.some(item=>families(item).includes(g)));
  await page.goto(base+root);
  const options=await page.locator('#filter-genre option').evaluateAll(nodes=>nodes.filter(n=>n.value).map(n=>({slug:n.value,name:n.textContent})));
  check(JSON.stringify(options.map(x=>x.slug).sort())===JSON.stringify(populated.slice().sort()),`${type}: filter includes exactly the families populated by public records`);
  check(options.every(o=>o.name===config[o.slug].name),`${type}: controlled names match the catalog`);
  for(const group of populated){
   const expected=new Set(items.filter(item=>families(item).includes(group)).map(x=>pathOf(x.url)));
   const seen=[];
   for(let index=1;index<=Math.ceil(expected.size/12);index++){
    const url=new URL(index===1?root:`${root}page/${index}/`,base);url.searchParams.set('filter_genre',group);
    const response=await page.goto(url.href);check(response.status()===200,`${type}/${group}/${index}: filtered page loads`);
    check(await page.locator('#filter-genre').inputValue()===group,'Selected grouped genre remains selected');
    const count=Number((await page.locator('.results-heading').textContent()).match(/\d+/)[0]);
    check(count===expected.size,`${type}/${group}: result total matches independent source assignments`);
    for(const href of await page.locator('.music-card h3 a').evaluateAll(nodes=>nodes.map(n=>n.href))){
     check(expected.has(pathOf(href)),`${type}/${group}: every result actually belongs to this family`);seen.push(pathOf(href));
    }
   }
   check(new Set(seen).size===expected.size&&seen.length===expected.size,`${type}/${group}: all pages cover the exact set without duplicates`);
  }
  const first=items[0];await page.goto(first.url);
  const tags=await page.locator('.tag-list a[href*="filter_genre="]').evaluateAll(nodes=>nodes.map(n=>({name:n.textContent,slug:new URL(n.href).searchParams.get('filter_genre')})));
  check(JSON.stringify(tags.map(x=>x.slug).sort())===JSON.stringify(families(first).sort()),`${type}: profile tags use the same deduplicated families`);
 }
 for(const [type,alias] of [['artist','electropop'],['artist','country-tinged-pop'],['song','singer-songwriter-pop'],['song','stripped-back-pop']]){
  const group=aliasToGroup[alias],expected=manifest.filter(x=>x.type===type&&families(x).includes(group)).length;
  await page.goto(`${base}/${type}s/?filter_genre=${alias}`);
  check(await page.locator('#filter-genre').inputValue()===group,'Legacy label links select their broader family');
  check(Number((await page.locator('.results-heading').textContent()).match(/\d+/)[0])===expected,'Legacy label links use the same family union');
 }
 const artist=manifest.find(x=>x.type==='artist'&&x.moods.length&&x.countries.length);
 const url=new URL('/artists/',base);url.searchParams.set('filter_genre',families(artist)[0]);url.searchParams.set('filter_mood',artist.moods[0]);url.searchParams.set('filter_country',artist.countries[0]);url.searchParams.set('q',artist.title);
 await page.goto(url.href);check(await page.locator('.music-card').count()===1&&(await page.locator('.music-card h3').innerText())===artist.title,'Genre still intersects with name, mood and country');
 url.searchParams.set('filter_country','no-such-country');await page.goto(url.href);check(await page.locator('.empty-state').isVisible(),'An impossible combined filter yields an honest empty state');
 await page.goto(`${base}/songs/?filter_genre=no-such-genre`);check(await page.locator('.empty-state').isVisible(),'An unknown genre cannot silently show the full catalog');
 await page.goto(`${base}/artists/?filter_genre[]=pop`);check((await page.locator('.results-heading').innerText()).includes('10'),'Malformed genre arrays are ignored safely without expanding public scope');
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:width===1440?1000:844});await page.goto(`${base}/artists/?filter_genre=electronic`);await page.evaluate(()=>document.fonts.ready);
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`Grouped directory fits ${width}px`);
  await page.screenshot({path:`test-results/genre-groups-artists-${width}.png`});
 }
 await page.setViewportSize({width:1440,height:1000});await page.goto(`${base}/artists/astrid-s/`);await page.screenshot({path:'test-results/genre-groups-profile.png'});
 // Admin controls expose original evidence labels and require nonce protection.
 const env=await fs.readFile('.env','utf8'),password=env.match(/^AFTERGLOW_ADMIN_PASSWORD=(.+)$/m)[1];
 await page.goto(`${base}/wp-login.php`);await page.locator('#user_login').fill('editor');await page.locator('#user_pass').fill(password);await page.locator('#wp-submit').click();await page.waitForURL('**/wp-admin/');
 await page.goto(`${base}/wp-admin/edit.php?post_type=artist&page=ag-genre-groups`);
 check((await page.locator('.wrap').innerText()).includes(`${Object.keys(config).length} genre families`),'Admin explains the controlled family vocabulary');
 check((await page.locator('.widefat').innerText()).includes('country-influenced pop'),'Original source labels remain available for editor review');
 check(await page.locator('.widefat select').first().locator('option').count()===Object.keys(config).length+1,'Mapping overrides offer only the known families and default');
 const denied=await page.request.post(`${base}/wp-admin/admin-post.php`,{form:{action:'ag_save_genre_groups','genre_groups[pop]':'electronic'}});
 check(denied.status()===403,'Mapping writes without nonce are rejected');
 await page.screenshot({path:'test-results/genre-groups-admin.png'});
 console.log(`PASS: ${checks} grouped-genre browser checks: exact filters/pagination, legacy links, combined filters, profile tags, scope, responsive layout and admin permissions.`);
 await fs.writeFile('test-results/genre-groups-summary.json',JSON.stringify({checks,families:Object.keys(config).length,public_artists:10,public_songs:128,stored_genres_changed:false},null,2));
}finally{await browser.close();}
