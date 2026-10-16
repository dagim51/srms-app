import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

function countPattern(dir, pattern) {
  let count = 0;
  function walk(d) {
    for (const entry of fs.readdirSync(d, { withFileTypes: true })) {
      const full = path.join(d, entry.name);
      if (entry.isDirectory()) {
        walk(full);
      } else if (entry.name.endsWith('.blade.php')) {
        const content = fs.readFileSync(full, 'utf-8');
        const matches = content.match(pattern);
        if (matches) count += matches.length;
      }
    }
  }
  walk(dir);
  return count;
}

const viewsDir = path.join(__dirname, '../resources/views');

console.log('=== COMPONENT USAGE AUDIT ===\n');

console.log('CSS Utility Classes:');
console.log('  btn classes:', countPattern(viewsDir, /class="btn /g));
console.log('  card classes:', countPattern(viewsDir, /class="card/g));
console.log('  form-input classes:', countPattern(viewsDir, /class="form-input/g));
console.log('  badge classes:', countPattern(viewsDir, /class="badge /g));
console.log('  alert classes:', countPattern(viewsDir, /class="alert /g));
console.log('  empty-state classes:', countPattern(viewsDir, /class="empty-state/g));
console.log('  sidebar-link classes:', countPattern(viewsDir, /class="sidebar-link/g));
console.log('  tab-item classes:', countPattern(viewsDir, /class="tab-item/g));
console.log('  property-card classes:', countPattern(viewsDir, /class="property-card/g));

console.log('\nBlade Components (x-*):');
console.log('  x-button:', countPattern(viewsDir, /<x-button/g));
console.log('  x-card:', countPattern(viewsDir, /<x-card(?:\s|\/>)/g));
console.log('  x-input:', countPattern(viewsDir, /<x-input/g));
console.log('  x-badge:', countPattern(viewsDir, /<x-badge/g));
console.log('  x-card-stat:', countPattern(viewsDir, /<x-card-stat/g));
console.log('  x-alert:', countPattern(viewsDir, /<x-alert/g));
console.log('  x-empty-state:', countPattern(viewsDir, /<x-empty-state/g));
console.log('  x-property-card:', countPattern(viewsDir, /<x-property-card/g));
console.log('  x-property-list-card:', countPattern(viewsDir, /<x-property-list-card/g));