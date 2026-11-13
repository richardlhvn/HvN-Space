import {chromium} from 'playwright';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const base=process.env.TEST_URL||'http://localhost:8080';
const expression=`$out=['allowed'=>[],'hidden'=>[]];foreach(get_posts(['post_type'=>['artist','song'],'post_status'=>'publish','numberposts'=>-1]) as $p){$out[ag_public_catalog_visible($p->ID)?'allowed':'hidden'][]=['id'=>$p->ID,'type'=>$p->post_type,'url'=>get_permalink($p),'slug'=>$p->post_name];}WP_CLI::line(wp_json_encode($out));`;
const manifest=JSON.parse(execFileSync('docker',['compose','run','--rm','cli','wp','eval',expression],{encoding:'utf8'}));
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;const check=(ok,message)=>{assert.ok(ok,message);checks++;};
await fs.mkdir('test-results',{recursive:true});
try{
 const context=await browser.newContext({viewport:{width:1440,height:1050}}),page=await context.newPage();
 const hiddenURLs=new Set(manifest.hidden.map(p=>new URL(p.url).pathname));
 check(manifest.allowed.filter(p=>p.type==='artist').length===10,'Exactly ten allowed artist profiles');
 check(manifest.allowed.filter(p=>p.type==='song').length===128,'Exactly 128 allowed song pages');
 const visibleIDs=new Set(manifest.allowed.map(p=>p.id));
 // Exercise every hidden permalink; limiting concurrency avoids overloading PHP.
 for(let i=0;i<manifest.hidden.length;i+=8){
  await Promise.all(manifest.hidden.slice(i,i+8).map(async item=>{
   const response=await context.request.get(item.url);
   check(response.status()===404,`Hidden permalink returns 404: ${item.slug}`);
  }));
 }
 for(const type of ['artist','song']){
  const expected=type==='artist'?10:128;
  const response=await context.request.get(`${base}/wp-json/wp/v2/${type}?per_page=100`);
  check(response.status()===200&&Number(response.headers()['x-wp-total'])===expected,`${type}: public API total matches scope`);
  check((await response.json()).every(p=>visibleIDs.has(p.id)),`${type}: API list contains no hidden records`);
  const hidden=manifest.hidden.find(p=>p.type===type);
  check((await context.request.get(`${base}/wp-json/wp/v2/${type}/${hidden.id}`)).status()===404,`${type}: direct API blocked`);
  const included=await context.request.get(`${base}/wp-json/wp/v2/${type}?include=${hidden.id}`);
  check((await included.json()).length===0,`${type}: explicit API include blocked`);
  const idPage=await context.request.get(`${base}/?post_type=${type}&p=${hidden.id}`);
  check(idPage.status()===404,`${type}: ID-based public URL blocked`);
 }
 const saved=await context.request.get(`${base}/wp-json/afterglow/v1/saved?ids=${manifest.hidden.slice(0,20).map(p=>p.id).join(',')}`);
 check((await saved.json()).length===0,'Previously saved hidden IDs return no cards');
 const oembed=new URL('/wp-json/oembed/1.0/embed',base);oembed.searchParams.set('url',manifest.hidden[0].url);
 check((await context.request.get(oembed.href)).status()===404,'Hidden oEmbed metadata blocked');
 for(const path of ['/','/artists/','/songs/',...manifest.allowed.map(p=>new URL(p.url).pathname)]){
  const response=await page.goto(new URL(path,base).href);
  check(response.status()===200,`Public page loads: ${path}`);
  const links=await page.locator('a[href]').evaluateAll(as=>as.map(a=>new URL(a.href).pathname));
  check(links.every(p=>!hiddenURLs.has(p)),`No links to hidden records: ${path}`);
  if(path==='/artists/'){
   check(await page.locator('.music-card').count()===10,'Artist directory shows ten cards');
   await page.evaluate(()=>document.fonts.ready);
   await page.screenshot({path:'test-results/core-catalog-artists.png',fullPage:true});
   await page.screenshot({path:'test-results/core-catalog-preview.png'});
  }
  if(path==='/songs/')check((await page.locator('.results-heading').textContent()).includes('128'),'Song directory reports 128 records');
 }
 await page.goto(`${base}/?s=NERIAH`);
 check(await page.locator('.search-list article').count()===0,'Global search excludes unfinished artists');
 await page.setViewportSize({width:390,height:844});await page.goto(`${base}/artists/`);
 check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Ten-artist directory fits mobile');
 await page.screenshot({path:'test-results/core-catalog-mobile.png',fullPage:true});
 await fs.writeFile('test-results/core-catalog-summary.json',JSON.stringify({checks,public_artists:10,public_songs:128,hidden_records:manifest.hidden.length,hidden_permalink_status:404,production_deployed:false},null,2));
 console.log(`PASS: ${checks} public-catalog browser/HTTP checks; 10 artists, 128 songs, ${manifest.hidden.length} hidden URLs return 404.`);
}finally{await browser.close();}
