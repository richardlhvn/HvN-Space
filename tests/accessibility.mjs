import {chromium} from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';

const base = process.env.TEST_URL || 'http://localhost:8080';
const browser = await chromium.launch({executablePath:process.env.CHROMIUM_PATH || '/usr/bin/chromium', args:['--no-sandbox']});
const context = await browser.newContext({viewport:{width:1440,height:1050}});
const page = await context.newPage();
const external = new Set();
const results = [];
page.on('request', request => { const url = new URL(request.url()); if (['http:', 'https:'].includes(url.protocol) && url.origin !== new URL(base).origin) external.add(url.origin); });
try {
  for (const width of [1440,390]) {
    await page.setViewportSize({width,height:width === 390 ? 844 : 1050});
    for (const path of ['/', '/artists/', '/artists/sara-kays/', '/songs/coping/', '/saved/', '/media-credits/', '/songs/loote-high-without-your-love/', '/songs/shy-martin-are-you-happy/?ag_feeling=hopeful&ag_theme=any', '/songs/shy-martin-are-you-happy/?ag_feeling=euphoric&ag_theme=body-image', '/artists/rosie/', '/songs/gabrielle-aplin-the-power-of-love/']) {
      await page.goto(new URL(path,base).href);
      await page.evaluate(() => document.fonts.ready);
      await page.locator('.discovery-controls, .volume-help').evaluateAll(details => details.forEach(detail => { detail.open = true; }));
      const report = await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21aa']).analyze();
      const violations = report.violations.map(v=>({id:v.id,impact:v.impact,nodes:v.nodes.map(n=>({target:n.target,summary:n.failureSummary}))}));
      results.push({path,width,violations});
    }
  }
  await fs.mkdir('test-results',{recursive:true});
  await fs.writeFile('test-results/accessibility.json',JSON.stringify(results,null,2));
  const failed = results.filter(r=>r.violations.length);
  if (failed.length) console.error(JSON.stringify(failed,null,2));
  assert.equal(failed.length,0,'Representative templates must pass automated WCAG checks');
  assert.equal(external.size,0,`No third-party resources should load: ${[...external].join(', ')}`);
  console.log(`PASS: ${results.length} desktop/mobile accessibility audits; no third-party resource requests. Automated checks do not replace manual accessibility review.`);
} finally { await browser.close(); }
