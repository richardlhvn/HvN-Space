import fs from 'node:fs';
import assert from 'node:assert/strict';
import {execFileSync} from 'node:child_process';
import {chromium} from 'playwright';
const wp=(...args)=>execFileSync('docker',['compose','run','--rm','cli','wp',...args],{encoding:'utf8'}).trim();
const base = process.env.TEST_URL || 'http://localhost:8080';
const id=wp('post','create','--post_type=artist','--post_title=Afterglow editor integration test','--post_status=draft','--porcelain');
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH || '/usr/bin/chromium',args:['--no-sandbox']});
try {
  const context=await browser.newContext({viewport:{width:1440,height:1000}});
  const page=await context.newPage();
  const password=fs.readFileSync('.env','utf8').match(/^AFTERGLOW_ADMIN_PASSWORD=(.+)$/m)[1];
  await page.goto(new URL('/wp-login.php',base).href);
  await page.locator('#user_login').fill('editor');
  await page.locator('#user_pass').fill(password);
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/');
  await page.goto(new URL(`/wp-admin/post.php?post=${Number(id)}&action=edit`,base).href);
  await page.waitForTimeout(2500);
  const welcome=page.getByRole('dialog').getByRole('button',{name:'Close',exact:true});
  if (await welcome.count()) await welcome.click();
  const boxes=page.getByRole('button',{name:'Meta Boxes',exact:true});
  // WordPress 7 positions a resize handle at this full-width button's center.
  // Activate the visible text on the left, as an editor would.
  if (await boxes.count()) await boxes.click({position:{x:60,y:16}});
  await page.locator('#ag-meta_description').fill('A profile saved through the normal WordPress editor.');
  await page.locator('#ag-demo').check();
  await page.getByRole('button',{name:'Save draft',exact:true}).click();
  await page.waitForTimeout(2000);
  assert.equal(wp('post','meta','get',id,'_ag_meta_description'),'A profile saved through the normal WordPress editor.');
  assert.equal(wp('post','meta','get',id,'_ag_demo'),'1');
  await page.screenshot({path:'test-results/admin-editor.png',fullPage:true});
  console.log('PASS: WordPress editor saves structured fields and demo status through the normal UI.');
} finally {
  await browser.close();
  wp('post','delete',id,'--force');
}
