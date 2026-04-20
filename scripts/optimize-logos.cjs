#!/usr/bin/env node
// Generate resized AVIF + WebP versions of logos for nav/footer usage.
// Runs via: node scripts/optimize-logos.js
const sharp = require('sharp');
const path = require('path');
const fs = require('fs');

const SRC_DIR = path.join(__dirname, '..', 'public', 'images');
const logos = ['logo-dark', 'logo-light'];
const widths = [320, 640]; // 2x/4x of 158px display

(async () => {
    for (const name of logos) {
        const src = path.join(SRC_DIR, `${name}.webp`);
        if (!fs.existsSync(src)) {
            console.warn(`skip: ${src} not found`);
            continue;
        }
        for (const w of widths) {
            const base = `${name}-${w}`;
            await sharp(src).resize({width: w, withoutEnlargement: true}).avif({quality: 65, effort: 6}).toFile(path.join(SRC_DIR, `${base}.avif`));
            await sharp(src).resize({width: w, withoutEnlargement: true}).webp({quality: 82, effort: 6}).toFile(path.join(SRC_DIR, `${base}.webp`));
            const avif = fs.statSync(path.join(SRC_DIR, `${base}.avif`)).size;
            const webp = fs.statSync(path.join(SRC_DIR, `${base}.webp`)).size;
            console.log(`${base}: avif=${avif}B  webp=${webp}B`);
        }
    }
    console.log('done.');
})();
