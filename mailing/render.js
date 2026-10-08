// Renderuje mailing/grafika.html do mailing/grafika.jpg (600 px, limit 300 KB).
// Użycie: node mailing/render.js
const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 600, height: 700 }, deviceScaleFactor: 1 });
  await page.goto('file://' + path.join(__dirname, 'grafika.html'), { waitUntil: 'networkidle' });
  await page.evaluate(() => document.fonts.ready);
  const out = path.join(__dirname, 'grafika.jpg');
  await page.locator('#ad').screenshot({ path: out, type: 'jpeg', quality: 88 });
  await browser.close();
  const kb = fs.statSync(out).size / 1024;
  console.log(`grafika.jpg: ${kb.toFixed(0)} KB` + (kb > 300 ? '  ← PRZEKRACZA 300 KB!' : ''));
})();
