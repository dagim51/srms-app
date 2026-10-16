#!/usr/bin/env node
/**
 * SRMS Design Token Linter
 * Validates Blade views against design system tokens
 * Run: npm run lint:design-tokens
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const VIEWS_DIR = path.resolve(__dirname, '../resources/views');

const VIOLATION_TYPES = {
  ARBITRARY_PX: 'ARBITRARY_PX',
  HARDCODED_COLOR: 'HARDCODED_COLOR',
  MISSING_FOCUS_VISIBLE: 'MISSING_FOCUS_VISIBLE',
  SMALL_TOUCH_TARGET: 'SMALL_TOUCH_TARGET',
  INCONSISTENT_ICON_SIZE: 'INCONSISTENT_ICON_SIZE',
  MISSING_ARIA_LABEL: 'MISSING_ARIA_LABEL',
  NON_TOKEN_SPACING: 'NON_TOKEN_SPACING',
};

const RULES = [
  // Arbitrary pixel values in Tailwind classes
  {
    type: VIOLATION_TYPES.ARBITRARY_PX,
    regex: /(text|w|h|min-w|min-h|max-w|max-h|p|px|py|m|mx|my|mt|mb|ml|mr|gap|space-x|space-y|rounded|rounded-t|rounded-r|rounded-b|rounded-l)-\[[\d.]+\w+\]/g,
    message: 'Arbitrary pixel value — use design tokens (text-xs, text-sm, text-base, text-lg, text-xl, text-2xl, text-3xl, text-4xl, or spacing scale)',
    severity: 'error',
  },

  // Hardcoded gray/slate/zinc/neutral colors (should use design tokens)
  {
    type: VIOLATION_TYPES.HARDCODED_COLOR,
    regex: /(bg|border|text|divide|ring|placeholder|accent)-(gray|slate|zinc|neutral|stone)-\d{3}/g,
    message: 'Hardcoded neutral color — use design tokens (--color-surface, --color-dark-surface, --border-warm, --color-dark-border, --color-muted-foreground, etc.)',
    severity: 'error',
  },

  // Hardcoded white/black in dark mode contexts
  {
    type: VIOLATION_TYPES.HARDCODED_COLOR,
    regex: /dark:(bg|border|text|divide|ring)-(white|black|gray-50|gray-100|gray-900|gray-950)/g,
    message: 'Hardcoded dark mode color — use design token aliases',
    severity: 'error',
  },

  // Missing focus-visible on interactive elements
  {
    type: VIOLATION_TYPES.MISSING_FOCUS_VISIBLE,
    regex: /<(button|a|input|select|textarea|summary)[^>]*class="[^"]*(btn|sidebar-link|tab-item|property-card|dropdown-item)[^"]*"[^>]*>/g,
    checkFocus: true,
    message: 'Interactive element missing :focus-visible style',
    severity: 'warning',
  },

  // Touch targets smaller than 44px (11 Tailwind units)
  {
    type: VIOLATION_TYPES.SMALL_TOUCH_TARGET,
    regex: /<(button|a|input|select|textarea|summary|label)[^>]*class="[^"]*(btn-icon|p-1|p-1\.5|px-1|px-1\.5|py-1|py-1\.5|w-8|h-8|w-9|h-9)[^"]*"[^>]*>/g,
    message: 'Touch target likely < 44px — minimum 44×44px (11 units) required',
    severity: 'error',
  },

  // Inline styles (should use tokens)
  {
    type: VIOLATION_TYPES.NON_TOKEN_SPACING,
    regex: /style\s*=\s*["'][^"']*(padding|margin|width|height|font-size|color|background|border)[^"']*["']/g,
    message: 'Inline styles bypass design tokens — use utility classes',
    severity: 'warning',
  },

  // Missing aria-label on icon-only buttons
  {
    type: VIOLATION_TYPES.MISSING_ARIA_LABEL,
    regex: /<button[^>]*class="[^"]*btn-icon[^"]*"[^>]*>(?!\s*<span[^>]*aria-hidden="true"[^>]*>\s*<svg)(?:.(?!\s*aria-label))*?<svg/g,
    message: 'Icon-only button missing aria-label',
    severity: 'error',
  },
];

const ALLOWED_ARBITRARY = [
  // Grid/flex layouts that need specific values
  'grid-template-columns',
  'grid-template-rows',
];

const ICON_SIZE_PATTERN = /(w|h)-(\d+(?:\.\d+)?)/g;
const EXPECTED_ICON_SIZES = {
  'nav': ['5', '5.5'], // 20px, 22px
  'button-sm': ['4'], // 16px
  'button-md': ['5'], // 20px
  'button-lg': ['5'], // 20px
  'property-spec': ['3.5', '4'], // 14px, 16px
  'page-header': ['7.5'], // 30px
  'card-stat': ['5'], // 20px
};

function getAllBladeFiles(dir) {
  const files = [];
  function walk(d) {
    for (const entry of fs.readdirSync(d, { withFileTypes: true })) {
      const full = path.join(d, entry.name);
      if (entry.isDirectory()) {
        walk(full);
      } else if (entry.name.endsWith('.blade.php')) {
        files.push(full);
      }
    }
  }
  walk(dir);
  return files;
}

function checkFile(filePath) {
  const content = fs.readFileSync(filePath, 'utf-8');
  const lines = content.split('\n');
  const violations = [];

  for (const rule of RULES) {
    let match;
    while ((match = rule.regex.exec(content)) !== null) {
      const lineNum = content.substring(0, match.index).split('\n').length;
      const line = lines[lineNum - 1]?.trim() || '';

      // Skip if in comment
      if (line.startsWith('//') || line.startsWith('*') || line.includes('<!--')) continue;

      // Check focus-visible for interactive elements
      if (rule.checkFocus && rule.type === VIOLATION_TYPES.MISSING_FOCUS_VISIBLE) {
        const hasFocus = /focus-visible|focus:/.test(line);
        if (hasFocus) continue;
      }

      // Check touch target exceptions
      if (rule.type === VIOLATION_TYPES.SMALL_TOUCH_TARGET) {
        const hasMinSize = /min-h-11|min-w-11|w-11|h-11/.test(line);
        if (hasMinSize) continue;
      }

      violations.push({
        file: path.relative(VIEWS_DIR, filePath),
        line: lineNum,
        column: match.index - content.lastIndexOf('\n', match.index),
        type: rule.type,
        message: rule.message,
        severity: rule.severity,
        snippet: line.substring(0, 120),
        matched: match[0],
      });
    }
  }

  // Additional: Check icon size consistency
  const iconMatches = content.matchAll(/<svg[^>]*class="[^"]*(w|h)-(\d+(?:\.\d+)?)[^"]*"[^>]*>/g);
  for (const match of iconMatches) {
    const size = match[2];
    if (!['3.5', '4', '5', '5.5', '6', '7.5'].includes(size)) {
      const lineNum = content.substring(0, match.index).split('\n').length;
      violations.push({
        file: path.relative(VIEWS_DIR, filePath),
        line: lineNum,
        type: VIOLATION_TYPES.INCONSISTENT_ICON_SIZE,
        message: `Non-standard icon size: ${size} — use 3.5, 4, 5, 5.5, 6, or 7.5`,
        severity: 'warning',
        snippet: lines[lineNum - 1]?.trim().substring(0, 120) || '',
        matched: `w-${size} or h-${size}`,
      });
    }
  }

  return violations;
}

function main() {
  console.log('🔍 SRMS Design Token Linter');
  console.log('============================\n');

  const files = getAllBladeFiles(VIEWS_DIR);
  console.log(`Scanning ${files.length} Blade files...\n`);

  let totalViolations = 0;
  let errors = 0;
  let warnings = 0;
  const byType = {};
  const byFile = {};

  for (const file of files) {
    const violations = checkFile(file);
    if (violations.length > 0) {
      byFile[path.relative(VIEWS_DIR, file)] = violations;
      for (const v of violations) {
        totalViolations++;
        if (v.severity === 'error') errors++;
        else warnings++;
        byType[v.type] = (byType[v.type] || 0) + 1;
      }
    }
  }

  // Summary by type
  console.log('📊 VIOLATIONS BY TYPE:');
  for (const [type, count] of Object.entries(byType).sort((a, b) => b[1] - a[1])) {
    console.log(`  ${type}: ${count}`);
  }

  console.log(`\n📁 VIOLATIONS BY FILE (top 20):`);
  const sortedFiles = Object.entries(byFile)
    .sort((a, b) => b[1].length - a[1].length)
    .slice(0, 20);

  for (const [file, violations] of sortedFiles) {
    const errCount = violations.filter(v => v.severity === 'error').length;
    const warnCount = violations.filter(v => v.severity === 'warning').length;
    console.log(`  ${file}: ${errCount} errors, ${warnCount} warnings`);
  }

  console.log(`\n📈 SUMMARY:`);
  console.log(`  Total files scanned: ${files.length}`);
  console.log(`  Files with violations: ${Object.keys(byFile).length}`);
  console.log(`  Total violations: ${totalViolations} (${errors} errors, ${warnings} warnings)`);

  // Exit code
  if (errors > 0) {
    console.log('\n❌ FAILED: Design token violations found (errors)');
    process.exit(1);
  } else if (warnings > 0) {
    console.log('\n⚠️  PASSED WITH WARNINGS');
    process.exit(0);
  } else {
    console.log('\n✅ PASSED: No design token violations');
    process.exit(0);
  }
}

main();