import { chromium } from 'playwright-core';
const b = await chromium.launch({ channel: 'chrome' });
const p = await b.newPage({ viewport: { width: 1500, height: 900 } });
await p.goto('http://127.0.0.1:8123/_editor.html');
await p.waitForLoadState('networkidle');
await p.click('.wc-nav-item[data-target="about"]');
await p.waitForTimeout(250);
console.log(JSON.stringify(await p.evaluate(() => {
  const bar = document.querySelector('.wc-panel.active .wc-actions');
  const out = { barPosition: getComputedStyle(bar).position, ancestors: [] };
  let e = bar.parentElement;
  while (e && e !== document.documentElement) {
    const c = getComputedStyle(e);
    if (c.overflow !== 'visible' || c.overflowY !== 'visible' || c.contain !== 'none' || c.transform !== 'none') {
      out.ancestors.push({ tag: e.tagName + '.' + (e.className.toString().split(' ')[0] || ''),
        overflow: c.overflow, overflowY: c.overflowY, transform: c.transform.slice(0,20), contain: c.contain });
    }
    e = e.parentElement;
  }
  return out;
}), null, 1));
await b.close();
