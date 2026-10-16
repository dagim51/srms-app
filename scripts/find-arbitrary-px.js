import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const VIEWS_DIR = path.resolve(__dirname, '../resources/views');

function findArbitraryPx(dir) {
  const results = [];
  function walk(d) {
    for (const entry of fs.readdirSync(d, { withFileTypes: true })) {
      const full = path.join(d, entry.name);
      if (entry.isDirectory()) {
        walk(full);
      } else if (entry.name.endsWith('.blade.php')) {
        const content = fs.readFileSync(full, 'utf-8');
        const lines = content.split('\n');
        for (let i = 0; i < lines.length; i++) {
          const matches = lines[i].match(/text-\[[\d.]+\w+\]|w-\[[\d.]+\w+\]|h-\[[\d.]+\w+\]|min-w-\[[\d.]+\w+\]|min-h-\[[\d.]+\w+\]|max-w-\[[\d.]+\w+\]|max-h-\[[\d.]+\w+\]|p-\[[\d.]+\w+\]|px-\[[\d.]+\w+\]|py-\[[\d.]+\w+\]|m-\[[\d.]+\w+\]|mx-\[[\d.]+\w+\]|my-\[[\d.]+\w+\]|mt-\[[\d.]+\w+\]|mb-\[[\d.]+\w+\]|ml-\[[\d.]+\w+\]|mr-\[[\d.]+\w+\]|gap-\[[\d.]+\w+\]|space-x-\[[\d.]+\w+\]|space-y-\[[\d.]+\w+\]|rounded-\[[\d.]+\w+\]/g);
          if (matches) {
            results.push({ file: path.relative(dir, full), line: i + 1, matches });
          }
        }
      }
    }
  }
  walk(dir);
  return results;
}

const results = findArbitraryPx(VIEWS_DIR);

console.log(`Total arbitrary PX occurrences: ${results.reduce((sum, r) => sum + r.matches.length, 0)}`);
console.log(`Files affected: ${results.length}\n`);

const byFile = {};
for (const r of results) {
  for (const m of r.matches) {
    byFile[r.file] = byFile[r.file] || [];
    byFile[r.file].push({ line: r.line, match: m });
  }
}

Object.entries(byFile)
  .sort((a, b) => b[1].length - a[1].length)
  .slice(0, 15)
  .forEach(([file, matches]) => {
    console.log(`\n${file} (${matches.length}):`);
    matches.slice(0, 5).forEach(m => console.log(`  L${m.line}: ${m.match}`));
    if (matches.length > 5) console.log(`  ... and ${matches.length - 5} more`);
  });