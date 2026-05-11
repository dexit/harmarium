#!/usr/bin/env node
/**
 * detect_wp_project.mjs
 * Deterministic WordPress project triage.
 * Outputs a structured JSON report to stdout.
 */

import { existsSync, readFileSync, readdirSync, statSync } from 'fs';
import { join, resolve } from 'path';
import { execSync } from 'child_process';

const ROOT = process.argv[2] ? resolve(process.argv[2]) : process.cwd();

const IGNORE_DIRS = new Set([
  'node_modules', 'vendor', '.git', '.github', 'dist', 'build',
  'coverage', '.nyc_output', 'tmp', '.cache', 'artifacts',
]);

/* ── helpers ── */
function exists(...parts) { return existsSync(join(ROOT, ...parts)); }

function read(...parts) {
  try { return readFileSync(join(ROOT, ...parts), 'utf8'); } catch { return ''; }
}

function readJson(...parts) {
  try { return JSON.parse(read(...parts)); } catch { return null; }
}

function glob(dir, pattern, maxDepth = 3, _depth = 0) {
  if (_depth > maxDepth) return [];
  const results = [];
  let entries;
  try { entries = readdirSync(dir, { withFileTypes: true }); } catch { return []; }
  for (const e of entries) {
    if (IGNORE_DIRS.has(e.name)) continue;
    const full = join(dir, e.name);
    if (e.isDirectory()) {
      results.push(...glob(full, pattern, maxDepth, _depth + 1));
    } else if (pattern.test(e.name)) {
      results.push(full);
    }
  }
  return results;
}

function grepFirst(files, pattern) {
  for (const f of files) {
    const content = readFileSync(f, 'utf8');
    const m = content.match(pattern);
    if (m) return { file: f, match: m[0], groups: m.groups ?? {} };
  }
  return null;
}

function cmd(command) {
  try { return execSync(command, { cwd: ROOT, stdio: ['ignore', 'pipe', 'ignore'], timeout: 5000 }).toString().trim(); }
  catch { return null; }
}

/* ── project kind detection ── */
const kinds = [];
const signals = {};
const versionHints = [];

// 1. WP Core
if (exists('wp-includes', 'version.php') && exists('wp-admin')) {
  kinds.push('wp-core');
  const ver = read('wp-includes', 'version.php').match(/\$wp_version\s*=\s*'([^']+)'/);
  if (ver) versionHints.push({ source: 'wp-includes/version.php', wpVersion: ver[1] });
}

// 2. Plugin
const phpFiles = glob(ROOT, /\.php$/, 1);
let pluginHeader = null;
for (const f of phpFiles) {
  const content = readFileSync(f, 'utf8');
  if (/Plugin Name:/i.test(content)) {
    pluginHeader = f;
    kinds.push('plugin');
    const name = content.match(/Plugin Name:\s*(.+)/i)?.[1]?.trim();
    const ver  = content.match(/Version:\s*(.+)/i)?.[1]?.trim();
    const req  = content.match(/Requires at least:\s*(.+)/i)?.[1]?.trim();
    const reqPHP = content.match(/Requires PHP:\s*(.+)/i)?.[1]?.trim();
    if (name) signals.pluginName = name;
    if (ver)  versionHints.push({ source: f.replace(ROOT, '.'), pluginVersion: ver });
    if (req)  versionHints.push({ source: f.replace(ROOT, '.'), requiresWP: req });
    if (reqPHP) versionHints.push({ source: f.replace(ROOT, '.'), requiresPHP: reqPHP });
    break;
  }
}

// 3. Theme (classic + FSE)
const styleCss = read('style.css');
if (/Theme Name:/i.test(styleCss)) {
  const isFSE = exists('theme.json') || exists('templates') || exists('parts');
  kinds.push(isFSE ? 'block-theme' : 'classic-theme');
  signals.themeName = styleCss.match(/Theme Name:\s*(.+)/i)?.[1]?.trim();
  const themeVer = styleCss.match(/Version:\s*(.+)/i)?.[1]?.trim();
  const themeReq = styleCss.match(/Requires at least:\s*(.+)/i)?.[1]?.trim();
  const themeReqPHP = styleCss.match(/Requires PHP:\s*(.+)/i)?.[1]?.trim();
  if (themeVer) versionHints.push({ source: 'style.css', themeVersion: themeVer });
  if (themeReq) versionHints.push({ source: 'style.css', requiresWP: themeReq });
  if (themeReqPHP) versionHints.push({ source: 'style.css', requiresPHP: themeReqPHP });
}

// FSE signals
if (exists('theme.json')) {
  signals.hasThemeJson = true;
  const tj = readJson('theme.json');
  if (tj?.$schema) signals.themeJsonSchema = tj.$schema;
  const schemaVer = tj?.$schema?.match(/\/(\d+)\//)?.[1];
  if (schemaVer) versionHints.push({ source: 'theme.json', schemaVersion: schemaVer });
}
if (exists('templates')) signals.hasTemplates = true;
if (exists('parts'))     signals.hasTemplateParts = true;
if (exists('patterns'))  signals.hasPatterns = true;

// 4. Block / Gutenberg
const blockJsonFiles = glob(ROOT, /^block\.json$/, 4);
if (blockJsonFiles.length) {
  kinds.push('gutenberg-blocks');
  signals.blockCount = blockJsonFiles.length;
  const bj = readJson(blockJsonFiles[0].replace(ROOT + '/', ''));
  if (bj?.apiVersion) signals.blockApiVersion = bj.apiVersion;
}

// 5. Interactivity API
const interactivityFiles = glob(ROOT, /\.(js|ts|mjs|tsx)$/, 4);
signals.usesInteractivityApi = interactivityFiles.some((f) => {
  try { return /@wordpress\/interactivity|data-wp-interactive|viewScriptModule/.test(readFileSync(f, 'utf8')); }
  catch { return false; }
});

// WP-CLI mu-plugin / drop-in
if (exists('wp-cli.yml') || exists('wp-cli.local.yml')) signals.hasWpCli = true;

/* ── tooling ── */
const tooling = {};

// PHP
const phpVer = cmd('php --version');
if (phpVer) tooling.php = { present: true, version: phpVer.split('\n')[0] };
else         tooling.php = { present: false };

// Composer
if (exists('composer.json')) {
  tooling.composer = { present: true };
  const cj = readJson('composer.json');
  if (cj?.require?.php) tooling.composer.requiresPhp = cj.require.php;
  if (cj?.['require-dev']?.['phpunit/phpunit']) tooling.composer.hasPhpunit = true;
  if (cj?.['require-dev']?.['wp-phpunit/wp-phpunit'] || cj?.['require-dev']?.['yoast/phpunit-polyfills']) {
    tooling.composer.hasWpTestSuite = true;
  }
}

// Node / npm
const nodeVer = cmd('node --version');
if (nodeVer) tooling.node = { present: true, version: nodeVer };
else          tooling.node = { present: false };

if (exists('package.json')) {
  tooling.npm = { present: true };
  const pj = readJson('package.json');
  if (pj?.scripts) tooling.npm.scripts = Object.keys(pj.scripts);
  if (pj?.devDependencies?.['@wordpress/scripts']) tooling.npm.usesWpScripts = true;
  if (pj?.devDependencies?.['@wordpress/env'])     tooling.npm.hasWpEnv = true;
  if (pj?.devDependencies?.['jest'] || pj?.devDependencies?.['@wordpress/jest-preset-default']) {
    tooling.npm.hasJest = true;
  }
  if (pj?.devDependencies?.['@playwright/test'] || pj?.devDependencies?.['puppeteer']) {
    tooling.npm.hasE2e = true;
  }
}

// WP-CLI
const wpCliVer = cmd('wp --version --allow-root');
if (wpCliVer) tooling.wpCli = { present: true, version: wpCliVer };
else           tooling.wpCli = { present: false };

/* ── tests ── */
const tests = {};
tests.phpunit  = exists('phpunit.xml') || exists('phpunit.xml.dist');
tests.jest     = exists('jest.config.js') || exists('jest.config.ts') || exists('jest.config.mjs') || !!tooling.npm?.hasJest;
tests.e2e      = exists('playwright.config.js') || exists('playwright.config.ts') || !!tooling.npm?.hasE2e;
tests.wpEnv    = exists('.wp-env.json') || !!tooling.npm?.hasWpEnv;
tests.hasTests = tests.phpunit || tests.jest || tests.e2e;

/* ── final report ── */
const report = {
  root: ROOT,
  project: {
    kind: kinds.length === 1 ? kinds[0] : kinds.length > 1 ? kinds : 'unknown',
    kinds,
  },
  signals,
  tooling,
  tests,
  versionHints,
  generatedAt: new Date().toISOString(),
};

process.stdout.write(JSON.stringify(report, null, 2) + '\n');
