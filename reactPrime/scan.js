const fs = require('fs');
const glob = require('glob');
const pages = glob.sync('src/pages/**/*.{js,jsx}');
const iconNames = new Set();
const iconRegex = /icon\s*:\s*['\"]([^'\"]+)['\"]/g;
for (const file of pages) {
  const txt = fs.readFileSync(file, 'utf8');
  let m;
  while ((m = iconRegex.exec(txt)) !== null) iconNames.add(m[1]);
}
const comp = fs.readFileSync('src/components/Icons.js', 'utf8');
const keyRegex = /\s*([a-zA-Z0-9]+):\s*<svg/g;
const keys = new Set();
for (const line of comp.split('\n')) {
  const m = keyRegex.exec(line);
  if (m) keys.add(m[1]);
}
console.log('icon names in pages:');
console.log([...iconNames].sort().join(', '));
console.log('icons in component:');
console.log([...keys].sort().join(', '));
console.log('missing:', [...iconNames].filter(n => !keys.has(n)).sort().join(', '));
