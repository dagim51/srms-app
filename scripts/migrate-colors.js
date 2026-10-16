#!/usr/bin/env node
/**
 * SRMS Blade Color Token Migration Codemod
 * Replaces hardcoded gray/slate/zinc/neutral colors with design token utilities
 * Run: node scripts/migrate-colors.js [--dry-run]
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const VIEWS_DIR = path.resolve(__dirname, '../resources/views');

const DRY_RUN = process.argv.includes('--dry-run');

const COLOR_REPLACEMENTS = [
  // Background colors - Light mode (with optional fractions /XX)
  { from: /bg-gray-50(\/[\d]+)?/g, to: 'bg-surface' },
  { from: /bg-gray-100(\/[\d]+)?/g, to: 'bg-surface-subtle' },
  { from: /bg-gray-200(\/[\d]+)?/g, to: 'bg-surface-subtle' },
  { from: /bg-gray-300(\/[\d]+)?/g, to: 'bg-surface-subtle' },
  { from: /bg-gray-400(\/[\d]+)?/g, to: 'bg-surface-subtle' },
  { from: /bg-gray-500(\/[\d]+)?/g, to: 'bg-surface-subtle' },
  { from: /bg-gray-600(\/[\d]+)?/g, to: 'bg-surface-subtle' },
  { from: /bg-gray-700(\/[\d]+)?/g, to: 'bg-surface' },
  { from: /bg-gray-800(\/[\d]+)?/g, to: 'bg-surface' },
  { from: /bg-gray-900(\/[\d]+)?/g, to: 'bg-surface' },
  { from: /bg-gray-950(\/[\d]+)?/g, to: 'bg-surface' },

  // Background colors - Dark mode (with optional fractions /XX)
  { from: /dark:bg-gray-50(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /dark:bg-gray-100(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /dark:bg-gray-200(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /dark:bg-gray-300(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /dark:bg-gray-400(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /dark:bg-gray-500(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /dark:bg-gray-600(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /dark:bg-gray-700(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /dark:bg-gray-800(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /dark:bg-gray-900(\/[\d]+)?/g, to: 'dark:bg-dark-bg' },
  { from: /dark:bg-gray-950(\/[\d]+)?/g, to: 'dark:bg-dark-bg' },

  // Standalone gray values (for gradients without bg- prefix, with optional fractions /XX)
  { from: /gray-50(\/[\d]+)?/g, to: 'var(--color-surface)' },
  { from: /gray-100(\/[\d]+)?/g, to: 'var(--color-surface-subtle)' },
  { from: /gray-200(\/[\d]+)?/g, to: 'var(--color-surface-subtle)' },
  { from: /gray-300(\/[\d]+)?/g, to: 'var(--color-surface-subtle)' },
  { from: /gray-400(\/[\d]+)?/g, to: 'var(--color-surface-subtle)' },
  { from: /gray-500(\/[\d]+)?/g, to: 'var(--color-surface-subtle)' },
  { from: /gray-600(\/[\d]+)?/g, to: 'var(--color-surface-subtle)' },
  { from: /gray-700(\/[\d]+)?/g, to: 'var(--color-surface)' },
  { from: /gray-800(\/[\d]+)?/g, to: 'var(--color-surface)' },
  { from: /gray-900(\/[\d]+)?/g, to: 'var(--color-surface)' },
  { from: /gray-950(\/[\d]+)?/g, to: 'var(--color-surface)' },

  // Border colors - Light mode (with optional fractions /XX)
  { from: /border-gray-100(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-gray-200(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-gray-300(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-gray-400(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-gray-500(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-gray-600(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-gray-700(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-gray-800(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-gray-900(\/[\d]+)?/g, to: 'border-warm' },

  // Border colors - Dark mode (with optional fractions /XX)
  { from: /dark:border-gray-100(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-gray-200(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-gray-300(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-gray-400(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-gray-500(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-gray-600(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-gray-700(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-gray-800(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-gray-900(\/[\d]+)?/g, to: 'dark:border-dark-border' },

  // Border direction variants - Light mode (with optional fractions /XX)
  { from: /border-(l|r|t|b)-gray-100(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-(l|r|t|b)-gray-200(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-(l|r|t|b)-gray-300(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-(l|r|t|b)-gray-400(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-(l|r|t|b)-gray-500(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-(l|r|t|b)-gray-600(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-(l|r|t|b)-gray-700(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-(l|r|t|b)-gray-800(\/[\d]+)?/g, to: 'border-warm' },
  { from: /border-(l|r|t|b)-gray-900(\/[\d]+)?/g, to: 'border-warm' },

  // Border direction variants - Dark mode (with optional fractions /XX)
  { from: /dark:border-(l|r|t|b)-gray-100(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-(l|r|t|b)-gray-200(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-(l|r|t|b)-gray-300(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-(l|r|t|b)-gray-400(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-(l|r|t|b)-gray-500(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-(l|r|t|b)-gray-600(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-(l|r|t|b)-gray-700(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-(l|r|t|b)-gray-800(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /dark:border-(l|r|t|b)-gray-900(\/[\d]+)?/g, to: 'dark:border-dark-border' },

  // Text colors - Light mode (all gray shades 100-900, with optional fractions /XX)
  { from: /text-gray-[123456789]00(\/[\d]+)?/g, to: 'text-muted' },

  // Text colors - Dark mode (all gray shades, with optional fractions /XX)
  { from: /dark:text-gray-[123456789]00(\/[\d]+)?/g, to: 'dark:text-dark-subtle' },

  // Placeholder colors - Light mode (all gray shades, with optional fractions /XX)
  { from: /placeholder:text-gray-[123456789]00(\/[\d]+)?/g, to: 'placeholder:text-muted' },

  // Placeholder colors - Dark mode (all gray shades, with optional fractions /XX)
  { from: /dark:placeholder:text-gray-[123456789]00(\/[\d]+)?/g, to: 'dark:placeholder:text-dark-subtle' },

  // Divide colors - Light mode (with optional fractions /XX)
  { from: /divide-gray-[123456789]00(\/[\d]+)?/g, to: 'divide-warm' },

  // Divide colors - Dark mode (with optional fractions /XX)
  { from: /dark:divide-gray-[123456789]00(\/[\d]+)?/g, to: 'dark:divide-dark-border' },

  // Ring colors - Light mode (with optional fractions /XX)
  { from: /ring-gray-[123456789]00(\/[\d]+)?/g, to: 'ring-warm' },

  // Ring colors - Dark mode (with optional fractions /XX)
  { from: /dark:ring-gray-[123456789]00(\/[\d]+)?/g, to: 'dark:ring-dark-border' },

  // Hover/focus variants (light mode gray shades, with optional fractions /XX)
  { from: /hover:bg-gray-[123456789]00(\/[\d]+)?/g, to: 'hover:bg-surface-subtle' },
  { from: /hover:bg-gray-[123456789]50(\/[\d]+)?/g, to: 'hover:bg-surface-subtle' },
  { from: /hover:bg-gray-[123456789]950(\/[\d]+)?/g, to: 'hover:bg-dark-bg' },

  // Hover/focus variants (dark mode gray shades, with optional fractions /XX)
  { from: /dark:hover:bg-gray-[123456789]00(\/[\d]+)?/g, to: 'dark:hover:bg-dark-surface' },
  { from: /dark:hover:bg-gray-[123456789]50(\/[\d]+)?/g, to: 'dark:hover:bg-dark-surface' },
  { from: /dark:hover:bg-gray-[123456789]950(\/[\d]+)?/g, to: 'dark:hover:bg-dark-bg' },

  // Focus ring variants - Light mode (with optional fractions /XX)
  { from: /focus:ring-gray-[123456789]00(\/[\d]+)?/g, to: 'focus:ring-primary-500' },
  { from: /focus:ring-gray-[123456789]50(\/[\d]+)?/g, to: 'focus:ring-primary-500' },

  // Focus ring variants - Dark mode (with optional fractions /XX)
  { from: /dark:focus:ring-gray-[123456789]00(\/[\d]+)?/g, to: 'dark:focus:ring-primary-500' },
  { from: /dark:focus:ring-gray-[123456789]50(\/[\d]+)?/g, to: 'dark:focus:ring-primary-500' },

  // Slate variants (less common but present, with optional fractions /XX)
  { from: /bg-slate-[123456789]00(\/[\d]+)?/g, to: 'bg-surface' },
  { from: /dark:bg-slate-[123456789]00(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /border-slate-[123456789]00(\/[\d]+)?/g, to: 'border-warm' },
  { from: /dark:border-slate-[123456789]00(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /text-slate-[123456789]00(\/[\d]+)?/g, to: 'text-muted' },
  { from: /dark:text-slate-[123456789]00(\/[\d]+)?/g, to: 'dark:text-dark-subtle' },

  // Zinc variants (with optional fractions /XX)
  { from: /bg-zinc-[123456789]00(\/[\d]+)?/g, to: 'bg-surface' },
  { from: /dark:bg-zinc-[123456789]00(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /border-zinc-[123456789]00(\/[\d]+)?/g, to: 'border-warm' },
  { from: /dark:border-zinc-[123456789]00(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /text-zinc-[123456789]00(\/[\d]+)?/g, to: 'text-muted' },
  { from: /dark:text-zinc-[123456789]00(\/[\d]+)?/g, to: 'dark:text-dark-subtle' },

  // Neutral variants (with optional fractions /XX)
  { from: /bg-neutral-[123456789]00(\/[\d]+)?/g, to: 'bg-surface' },
  { from: /dark:bg-neutral-[123456789]00(\/[\d]+)?/g, to: 'dark:bg-dark-surface' },
  { from: /border-neutral-[123456789]00(\/[\d]+)?/g, to: 'border-warm' },
  { from: /dark:border-neutral-[123456789]00(\/[\d]+)?/g, to: 'dark:border-dark-border' },
  { from: /text-neutral-[123456789]00(\/[\d]+)?/g, to: 'text-muted' },
  { from: /dark:text-neutral-[123456789]00(\/[\d]+)?/g, to: 'dark:text-dark-subtle' },
];

const SKIP_PATTERNS = [
  /<x-/, // Skip Blade component tags
  /@svg/, // Skip SVG helpers
  /class="[^"]*sr-only/, // Skip screen reader only
  /storage\\framework\\views/, // Skip compiled views
];

function shouldSkip(line) {
  return SKIP_PATTERNS.some(p => p.test(line));
}

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

function migrateFile(filePath) {
  const content = fs.readFileSync(filePath, 'utf-8');
  const lines = content.split('\n');
  let changed = false;
  const changes = [];

  for (let i = 0; i < lines.length; i++) {
    const originalLine = lines[i];
    if (shouldSkip(originalLine)) continue;

    let newLine = originalLine;
    for (const { from, to } of COLOR_REPLACEMENTS) {
      if (from.test(newLine)) {
        newLine = newLine.replace(from, to);
        changed = true;
      }
    }

    if (newLine !== originalLine) {
      changes.push({ line: i + 1, from: originalLine.trim(), to: newLine.trim() });
      lines[i] = newLine;
    }
  }

  if (changed && !DRY_RUN) {
    fs.writeFileSync(filePath, lines.join('\n'), 'utf-8');
  }

  return { changed, changes };
}

function main() {
  console.log(`SRMS Color Token Migration${DRY_RUN ? ' (DRY RUN)' : ''}`);
  console.log('=====================================\n');

  const files = getAllBladeFiles(VIEWS_DIR);
  console.log(`Scanning ${files.length} Blade files...\n`);

  let totalChanged = 0;
  let totalChanges = 0;
  const fileChanges = [];

  for (const file of files) {
    const result = migrateFile(file);
    if (result.changed) {
      totalChanged++;
      totalChanges += result.changes.length;
      fileChanges.push({ file: path.relative(VIEWS_DIR, file), changes: result.changes.length });
    }
  }

  console.log('\nSUMMARY:');
  console.log(`  Files modified: ${totalChanged}`);
  console.log(`  Total replacements: ${totalChanges}`);

  if (fileChanges.length > 0) {
    console.log('\nFiles with changes:');
    fileChanges
      .sort((a, b) => b.changes - a.changes)
      .slice(0, 20)
      .forEach(f => console.log(`  ${f.file}: ${f.changes} changes`));
  }

  if (DRY_RUN) {
    console.log('\nDRY RUN - No files were modified. Run without --dry-run to apply.');
  } else {
    console.log('\nMigration complete!');
  }
}

main();