import fs from 'node:fs/promises';
import assert from 'node:assert/strict';
import {chromium} from 'playwright';

const base = process.env.TEST_URL || 'http://localhost:8080';
const browser = await chromium.launch({executablePath:process.env.CHROMIUM_PATH || '/usr/bin/chromium',args:['--no-sandbox']});
let checks=0;
const check=(value,message)=>{assert.ok(value,message);checks++;};
try {
  const page=await browser.newPage({viewport:{width:1440,height:1000}});
  const password=(await fs.readFile('.env','utf8')).match(/^AFTERGLOW_ADMIN_PASSWORD=(.+)$/m)[1];
  await page.goto(new URL('/wp-login.php',base).href);
  await page.locator('#user_login').fill('editor');await page.locator('#user_pass').fill(password);
  await page.locator('#wp-submit').click();await page.waitForURL('**/wp-admin/');
  const path='/wp-admin/edit.php?post_type=artist&page=ag-handoffs';
  await page.goto(new URL(path,base).href);
  check(await page.locator('#ag-research-collection').inputValue()==='batch5-7','Final 33-artist collection must be the safe default');
  check(await page.locator('.widefat tbody tr').count()===363,'Final intake must show 33 artists and 330 selection targets');
  check(!await page.locator('.widefat tbody').textContent().then(t=>t.includes('Astrid S')),'Earlier artists must be excluded from the default form');
  await page.selectOption('#ag-research-collection','all');await page.getByRole('button',{name:'Review collection',exact:true}).click();
  check(await page.locator('.widefat tbody tr').count()===583,'All research must explicitly include all 53 artists and 530 selections');
  await page.goto(new URL(path+'&collection=invalid',base).href);
  check(await page.locator('#ag-research-collection').inputValue()==='batch5-7','Invalid GET scope must fall back safely');
  check((await page.locator('.wrap').textContent()).includes('Artist intake paused at 53'),'Paused intake must remain visible');
  let response=await page.request.post(new URL('/wp-admin/admin-post.php',base).href,{form:{action:'ag_apply_handoffs',collection:'batch5-7',show_on_site:'1'}});
  check(response.status()===403,'Authenticated request without nonce must be rejected');
  const nonce=await page.locator('input[name=_wpnonce]').inputValue();
  response=await page.request.post(new URL('/wp-admin/admin-post.php',base).href,{form:{action:'ag_apply_handoffs',collection:'invalid',_wpnonce:nonce,show_on_site:'1'}});
  check(response.status()===400,'Invalid POST collection must be rejected');
  await page.getByRole('button',{name:'Apply supplied research',exact:true}).click();
  await page.waitForURL('**/wp-admin/edit.php?post_type=artist&page=ag-handoffs&collection=batch5-7');
  check((await page.locator('.ag-import-report').textContent()).includes('0 created, 362 updated, 0 preserved, 1 shared selections reused'),'Normal form must submit only the selected collection and reuse its shared song');
  await fs.mkdir('test-results',{recursive:true});await page.screenshot({path:'test-results/research-batch567-admin.png',fullPage:true});
  await page.goto(new URL('/wp-admin/edit.php?post_type=artist&page=ag-media',base).href);
  check(await page.locator('[data-review-status=live_verified]').count()===6,'Original six photo reviews must remain verified');
  check(await page.locator('[data-review-status=placeholder_permission_unresolved]').count()===47,'New and unresolved artists must retain forty-seven placeholders');
  console.log(`PASS: ${checks} research-admin scope, nonce, repeat-import, and media-review checks.`);
} finally {await browser.close();}
