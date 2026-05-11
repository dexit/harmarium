#!/usr/bin/env node
/**
 * detect_block_themes.mjs
 * Locate all block theme roots under the given directory and report their
 * key folders, theme.json version, and template/part/pattern counts.
 * Outputs JSON to stdout.
 */

import { existsSync, readFileSync, readdirSync, statSync } from 'fs';
import { join, resolve, relative } from 'path';

const ROOT = process.argv[2] ? resolve(process.argv[2]) : process.cwd();

const IGNORE = new Set(['node_modules', 'vendor', '.git', 'dist', 'build', 'tmp']);

function readJson(path) {
  try { return JSON.parse(readFileSync(path, 'utf8')); } catch { return null; }
}

function countFiles(dir, ext) {
  if (!existsSync(dir)) return 0;
  try {
    return readdirSync(dir).filter((f) => !ext || f.endsWith(ext)).length;
  } catch { return 0; }
}

function findThemeRoots(dir, depth = 0) {
  if (depth > 4) return [];
  const results = [];
  let entries;
  try { entries = readdirSync(dir, { withFileTypes: true }); } catch { return []; }

  for (const e of entries) {
    if (!e.isDirectory() || IGNORE.has(e.name)) continue;
    const full = join(dir, e.name);
    const hasCss = existsSync(join(full, 'style.css'));
    const hasThemeJson = existsSync(join(full, 'theme.json'));
    const hasTemplates = existsSync(join(full, 'templates'));
    const hasParts = existsSync(join(full, 'parts'));

    if (hasCss && (hasThemeJson || hasTemplates)) {
      results.push(full);
    } else {
      results.push(...findThemeRoots(full, depth + 1));
    }
  }
  return results;
}

// Also check if ROOT itself is a theme
const roots = [];
const rootCss = existsSync(join(ROOT, 'style.css'));
const rootTj  = existsSync(join(ROOT, 'theme.json'));
const rootTpl = existsSync(join(ROOT, 'templates'));
if (rootCss && (rootTj || rootTpl)) {
  roots.push(ROOT);
} else {
  roots.push(...findThemeRoots(ROOT));
}

const themes = roots.map((themeRoot) => {
  const styleCss = existsSync(join(themeRoot, 'style.css'))
    ? readFileSync(join(themeRoot, 'style.css'), 'utf8') : '';
  const tj = readJson(join(themeRoot, 'theme.json'));

  const name    = styleCss.match(/Theme Name:\s*(.+)/i)?.[1]?.trim() ?? '(unknown)';
  const version = styleCss.match(/^Version:\s*(.+)/im)?.[1]?.trim();
  const reqWP   = styleCss.match(/Requires at least:\s*(.+)/i)?.[1]?.trim();
  const reqPHP  = styleCss.match(/Requires PHP:\s*(.+)/i)?.[1]?.trim();
  const isChild = /Template:\s*\S/i.test(styleCss);

  return {
    path:            relative(ROOT, themeRoot) || '.',
    name,
    version,
    requiresWP:      reqWP,
    requiresPHP:     reqPHP,
    isChildTheme:    isChild,
    hasThemeJson:    existsSync(join(themeRoot, 'theme.json')),
    themeJsonVersion: tj?.version ?? null,
    themeJsonSchema:  tj?.$schema ?? null,
    folders: {
      templates: existsSync(join(themeRoot, 'templates')),
      parts:     existsSync(join(themeRoot, 'parts')),
      patterns:  existsSync(join(themeRoot, 'patterns')),
      styles:    existsSync(join(themeRoot, 'styles')),
      fonts:     existsSync(join(themeRoot, 'assets', 'fonts')) || existsSync(join(themeRoot, 'fonts')),
    },
    counts: {
      templates: countFiles(join(themeRoot, 'templates'), '.html'),
      parts:     countFiles(join(themeRoot, 'parts'),     '.html'),
      patterns:  countFiles(join(themeRoot, 'patterns'),  '.php'),
      styles:    countFiles(join(themeRoot, 'styles'),    '.json'),
    },
  };
});

process.stdout.write(JSON.stringify({ root: ROOT, themes, count: themes.length }, null, 2) + '\n');
