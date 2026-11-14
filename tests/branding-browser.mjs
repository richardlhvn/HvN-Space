import {chromium} from 'playwright';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const base=process.env.TEST_URL||'http://localhost:8080';
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;const check=(ok,message)=>{assert.ok(ok,message);checks++;};
await fs.mkdir('test-results',{recursive:true});
try {
 const context=await browser.newContext();const page=await context.newPage();
 const artist=await context.request.get(`${base}/wp-json/wp/v2/artist?per_page=1`);
 const songs=await context.request.get(`${base}/wp-json/wp/v2/song?per_page=1`);
 check(Number(artist.headers()['x-wp-total'])===10&&Number(songs.headers()['x-wp-total'])===128,'Public catalog stays ten/128');
 const selectedSong=(await songs.json())[0];
 for (const width of [1440,390,320]) {
  await page.setViewportSize({width,height:width===1440?1050:844});
  for (const path of ['/','/about/','/contact/','/privacy/','/copyright/','/media-credits/','/journal/','/artists/sara-kays/','/songs/shy-martin-make-us-never-happen/']) {
   const response=await page.goto(new URL(path,base).href);
   check(response.status()===200,`${width} ${path}: page loads`);
   check((await page.locator('.site-header .wordmark').innerText()).replace(/\s/g,'')==='tunetofond.',`${path}: exact lowercase wordmark`);
   check((await page.locator('.site-header .wordmark').getAttribute('aria-label'))==='tunetofond home',`${path}: accessible home identity`);
   check((await page.title()).includes('tunetofond'),`${path}: browser title identity`);
   check(await page.locator('meta[property="og:site_name"]').getAttribute('content')==='tunetofond',`${path}: social site name`);
   check(!/afterglow/i.test((await page.locator('body').innerText()).replaceAll('afterglow:saved:v1','')),`${path}: old public brand references removed`);
   check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`${width} ${path}: no horizontal overflow`);
   check(await page.locator('.site-header').evaluate(el=>el.scrollWidth<=el.clientWidth),`${width}: header fits`);
   check(await page.locator('meta[name="robots"]').getAttribute('content').then(v=>v.includes('noindex')),`${path}: indexing unchanged`);
   if(width!==320 && ['/','/about/','/songs/shy-martin-make-us-never-happen/'].includes(path)) {
    const name=path==='/'?'home':path==='/about/'?'about':'song';
    await page.evaluate(()=>document.fonts.ready);
    await page.screenshot({path:`test-results/tunetofond-${name}-${width}.png`,fullPage:true});
   }
  }
 }
 // Existing browser-local saved IDs continue to work through the old namespace.
 await page.evaluate(id=>localStorage.setItem('afterglow:saved:v1',JSON.stringify([id])),selectedSong.id);
 await page.goto(`${base}/saved/`);await page.waitForSelector('#saved-items .music-card');
 check(await page.locator('#saved-items .music-card').count()===1,'Saved collection survives product rename');
 check(await page.locator('#saved-items h3').innerText()===selectedSong.title.rendered,'Same saved song identity');
 await page.setViewportSize({width:1440,height:1050});
 const env=await fs.readFile('.env','utf8');const password=env.match(/^AFTERGLOW_ADMIN_PASSWORD=(.+)$/m)[1];
 await page.goto(`${base}/wp-login.php`);await page.locator('#user_login').fill('editor');await page.locator('#user_pass').fill(password);await page.locator('#wp-submit').click();await page.waitForURL('**/wp-admin/');
 await page.goto(`${base}/wp-admin/plugins.php`);
 check((await page.locator('[data-plugin="afterglow-core/afterglow-core.php"]').innerText()).includes('tunetofond Core'),'Plugin display identity');
 check((await page.locator('[data-plugin="afterglow-core/afterglow-core.php"]').innerText()).includes('0.13.0'),'Plugin version');
 await page.goto(`${base}/wp-admin/themes.php`);
 check((await page.locator('[data-slug="afterglow"] .theme-name').innerText()).includes('tunetofond'),'Theme display identity with compatible slug');
 await page.goto(`${base}/wp-admin/edit.php?post_type=artist&page=ag-guide`);
 check((await page.locator('.wrap h1').innerText())==='Publishing with tunetofond','Editorial admin guide');
 await page.goto(`${base}/wp-admin/tools.php?page=ag-branding`);
 check((await page.locator('.wrap').innerText()).includes('0 pending changes.'),'Admin migration is idempotent');
 const forbidden=await page.request.post(`${base}/wp-admin/admin-post.php`,{form:{action:'ag_migrate_branding'}});
 check(forbidden.status()===403,'Branding admin action requires nonce');
 await page.screenshot({path:'test-results/tunetofond-branding-admin.png',fullPage:true});
 console.log(`PASS: ${checks} branding, responsive, metadata, admin, saved-item and scope browser checks.`);
 await fs.writeFile('test-results/tunetofond-branding-summary.json',JSON.stringify({checks,public_artists:10,public_songs:128,viewport_widths:[1440,390,320],audio_verified:false},null,2));
} finally {await browser.close();}
