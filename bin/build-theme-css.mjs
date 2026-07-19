#!/usr/bin/env node

import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const repoRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const sourcePath = resolve(repoRoot, 'wp-content/themes/wichtelwerken-child/style.css');
const targetPath = resolve(repoRoot, 'wp-content/themes/wichtelwerken-child/style.min.css');

function protectStrings(css) {
  const strings = [];

  const protectedCss = css.replace(/(["'])(?:\\.|(?!\1)[\s\S])*\1/g, (match) => {
    const index = strings.push(match) - 1;
    return `___WW_STRING_${index}___`;
  });

  return {
    css: protectedCss,
    restore(value) {
      return value.replace(/___WW_STRING_(\d+)___/g, (_match, index) => strings[Number(index)] || '');
    },
  };
}

function minifyCss(css) {
  const protectedStrings = protectStrings(css);

  let output = protectedStrings.css
    .replace(/\/\*[\s\S]*?\*\//g, '')
    .replace(/\s+/g, ' ')
    .replace(/\s*([{}:;,>+~(),])\s*/g, '$1')
    .replace(/;}/g, '}')
    .trim();

  return `${protectedStrings.restore(output)}\n`;
}

const sourceCss = readFileSync(sourcePath, 'utf8');
const minifiedCss = minifyCss(sourceCss);

writeFileSync(targetPath, minifiedCss, 'utf8');

const sourceBytes = Buffer.byteLength(sourceCss);
const targetBytes = Buffer.byteLength(minifiedCss);
const savedBytes = sourceBytes - targetBytes;
const savedPercent = ((savedBytes / sourceBytes) * 100).toFixed(1);

console.log(`Wrote ${targetPath}`);
console.log(`Reduced ${sourceBytes} bytes to ${targetBytes} bytes (${savedPercent}% smaller).`);
