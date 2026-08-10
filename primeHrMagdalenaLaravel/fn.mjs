import { chromium } from 'playwright-core';
const b = await chromium.launch({ channel: 'chrome' });
const p = await b.newPage({ viewport: { width: 1500, height: 900 } });
const errs = []; p.on('pageerror', e => errs.push(e.message));
await p.goto('http://127.0.0.1:8123/_editor.html');
await p.waitForLoadState('networkidle');

// 1. Collapsed categories must still submit every field.
await p.click('.wc-nav-item[data-target="services"]');
await p.waitForTimeout(250);
const svc = await p.evaluate(() => {
  const panel = document.querySelector('.wc-panel[data-section="services"]');
  const cards = [...panel.querySelectorAll('details.wc-card')];
  const keys = [...new FormData(panel).keys()];
  return {
    allClosed: cards.every(c => !c.open),
    categoryKeys: keys.filter(k => k.startsWith('categories')).length,
    nestedNames: [...panel.querySelectorAll('[data-name="title"]')].map(e => e.name).slice(0, 4),
  };
});
console.log('SERVICES (all collapsed):', JSON.stringify(svc));

// 2. Adding a service inside a collapsed category still indexes right.
await p.evaluate(() => document.querySelectorAll('.wc-panel[data-section="services"] details.wc-card')[1].open = true);
await p.waitForTimeout(150);
await p.evaluate(() => document.querySelectorAll('.wc-panel[data-section="services"] details.wc-card')[1]
  .querySelector('[data-repeat="items"] [data-add]').click());
await p.waitForTimeout(200);
console.log('after add in cat 1:', JSON.stringify(await p.evaluate(() =>
  [...document.querySelectorAll('.wc-panel[data-section="services"] [data-name="title"]')].map(e => e.name))));

// 3. Sticky save bar stays on screen at the bottom of a long panel.
await p.click('.wc-nav-item[data-target="about"]');
await p.waitForTimeout(250);
await p.evaluate(() => window.scrollTo(0, 400));
await p.waitForTimeout(300);
const sticky = await p.evaluate(() => {
  const bar = document.querySelector('.wc-panel.active .wc-actions');
  const save = bar.querySelector('[data-role="save"]');
  const r = bar.getBoundingClientRect(), sr = save.getBoundingClientRect();
  const el = document.elementFromPoint((sr.left + sr.right) / 2, (sr.top + sr.bottom) / 2);
  return { barBottom: Math.round(r.bottom), viewportH: innerHeight,
           inView: r.bottom <= innerHeight + 1 && r.top >= 0,
           saveClickable: !!el?.closest('[data-role="save"]') };
});
console.log('STICKY BAR:', JSON.stringify(sticky));
console.log('JS ERRORS:', errs);
await b.close();
