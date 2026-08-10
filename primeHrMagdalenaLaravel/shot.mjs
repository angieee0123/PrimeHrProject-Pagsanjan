import { chromium } from 'playwright-core';
const b = await chromium.launch({ channel: 'chrome' });
const p = await b.newPage({ viewport: { width: 1500, height: 1000 } });
await p.goto('http://127.0.0.1:8123/_editor.html');
await p.waitForLoadState('networkidle');
for (const s of ['announcements', 'contact', 'about', 'services']) {
  await p.click(`.wc-nav-item[data-target="${s}"]`);
  await p.waitForTimeout(250);
  await p.locator(`.wc-panel[data-section="${s}"]`).screenshot({ path: `ed-${s}.png` });
  const m = await p.evaluate((sec) => {
    const panel = document.querySelector(`.wc-panel[data-section="${sec}"]`);
    return { height: Math.round(panel.getBoundingClientRect().height),
             inputs: panel.querySelectorAll('.wc-input').length,
             labels: panel.querySelectorAll('.wc-label').length,
             hints: panel.querySelectorAll('.wc-hint').length };
  }, s);
  console.log(s.padEnd(14), JSON.stringify(m));
}
await b.close();
