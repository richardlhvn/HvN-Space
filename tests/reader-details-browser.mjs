import {chromium} from 'playwright';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const base=process.env.TEST_URL||'http://localhost:8080';
const expression=`$out=[];foreach(ag_public_catalog_ids() as $id){$p=get_post($id);$record=get_post_meta($id,'_ag_handoff_record',true);$out[]=['id'=>$id,'type'=>$p->post_type,'url'=>get_permalink($id),'body'=>get_post_field('post_content',$id),'description'=>$p->post_type==='song'?(get_post_meta($id,'_ag_meta_description',true)?:ag_about_song($id)['text']):null,'biography'=>$p->post_type==='artist'?html_entity_decode(wp_strip_all_tags(apply_filters('the_content',ag_public_research_content($p->post_content))),ENT_QUOTES|ENT_HTML5,'UTF-8'):null];}WP_CLI::line(wp_json_encode($out));`;
const manifest=JSON.parse(execFileSync('docker',['compose','run','--rm','cli','wp','eval',expression],{encoding:'utf8'}));
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;const check=(ok,msg)=>{assert.ok(ok,msg);checks++;};
const normal=s=>s.replace(/\s+/g,' ').trim();
try{
 const context=await browser.newContext({viewport:{width:1440,height:1050}}),page=await context.newPage();
 for(const item of manifest){
  const response=await page.goto(item.url);check(response.status()===200,'Public record renders');
  check(await page.locator('.editorial-notice,.song-review-status').count()===0,'No QA banners or review links');
  const body=await page.locator('main').innerText();
  check(!/\bAfterglow\b/i.test(body),'All current public brand references use tunetofond');
  check(!/Research review pending|No independent audio listening is claimed|Based on the supplied research dated|Earlier original single day not established|independent check pending|source metadata pending|primary-audio verification pending|\(day precision\)/i.test(body),'No internal QA wording in the public body');
  check((await page.locator('meta[name=robots]').getAttribute('content')).includes('noindex'),'Removing visible QA does not enable indexing');
  const api=await context.request.get(`${base}/wp-json/wp/v2/${item.type}/${item.id}`);
  const apiData=await api.json();
  check(!/\bAfterglow\b/i.test(apiData.content.rendered + apiData.excerpt.rendered),'Public REST prose uses the current brand too');
  check(api.status()===200&&!/Basis:|Research notes|Based on the supplied research dated|No independent audio listening is claimed/i.test(apiData.content.rendered),'Public REST also omits internal working notes');
  if(item.type==='artist')check(normal(await page.locator('.artist-full-biography .prose').textContent())===normal(item.biography),'Public biography retains its editor text and omits only internal sections');
  else{
   check(await page.locator('.song-about').count()===1,'About remains visible');
   check(await page.locator('.song-release-details').count()===1,'Reader release details remain visible');
   check(await page.locator('.source-list a').count()>0,'Source citations remain visible');
   check(normal(await page.locator('meta[property="og:description"]').getAttribute('content'))===normal(item.description),'Social description uses About or the explicit editor override');
  }
 }
 const shy=manifest.find(x=>x.url.includes('shy-martin-make-us-never-happen'));
 await page.goto(shy.url);
 check((await page.locator('.song-release-details').textContent()).includes('8 November 2019'),'Screenshot song has a readable date');
 check(await page.locator('.about-attribution,.attribution-label').count()===0,'Reader-facing attribution labels are omitted while sources remain');
 check((await page.locator('.listening-photo-credit').textContent()).includes('CC BY-SA 4.0'),'Required photo license remains visible');
 await page.evaluate(()=>document.fonts.ready);await page.screenshot({path:'test-results/reader-details-shy-desktop.png'});
 await page.locator('#song-notes').scrollIntoViewIfNeeded();await page.screenshot({path:'test-results/reader-details-shy-sources.png'});
 await page.setViewportSize({width:390,height:844});await page.goto(shy.url);
 check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Clean song page fits mobile');
 await page.screenshot({path:'test-results/reader-details-shy-mobile.png',fullPage:true});
 await fs.writeFile('test-results/reader-details-summary.json',JSON.stringify({checks,public_records:manifest.length,qa_visible:false,source_and_license_credits_retained:true,indexing_changed:false},null,2));
 console.log(`PASS: ${checks} public-details browser checks across ten artists and 128 songs.`);
}finally{await browser.close();}
