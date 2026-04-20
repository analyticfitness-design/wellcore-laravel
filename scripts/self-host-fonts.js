// Descarga Google Fonts WOFF2 + genera CSS local auto-hosted.
// Uso: node scripts/self-host-fonts.js
// Output: public/fonts/*.woff2 + public/fonts/wellcore-fonts.css
import fs from 'node:fs';
import path from 'node:path';

const GOOGLE_CSS_URL = 'https://fonts.googleapis.com/css2?family=Oswald:wght@600;700&family=Raleway:wght@400;600&family=Barlow:wght@400;700&display=swap';
const UA_CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
const OUT_DIR = 'public/fonts';

async function main() {
  fs.mkdirSync(OUT_DIR, { recursive: true });

  const res = await fetch(GOOGLE_CSS_URL, { headers: { 'User-Agent': UA_CHROME } });
  if (!res.ok) throw new Error(`Google Fonts CSS HTTP ${res.status}`);
  const cssRaw = await res.text();
  console.log(`Downloaded CSS: ${cssRaw.length} bytes`);

  // Buscar bloques @font-face y extraer (family, weight, style, woff2 url, subset comment)
  // El CSS de Google trae /* subset */ antes de cada @font-face
  const blocks = cssRaw.split(/(?=\/\*\s*[^*]+\s*\*\/\s*@font-face)/);
  let outCss = '/* WellCore self-hosted fonts — generado desde Google Fonts */\n\n';
  let downloaded = 0;

  for (const block of blocks) {
    if (!block.includes('@font-face')) continue;
    const subsetMatch = block.match(/\/\*\s*([\w-]+)\s*\*\//);
    const familyMatch = block.match(/font-family:\s*'([^']+)'/);
    const weightMatch = block.match(/font-weight:\s*(\d+)/);
    const styleMatch = block.match(/font-style:\s*(\w+)/);
    const urlMatch = block.match(/url\((https:\/\/fonts\.gstatic\.com\/[^)]+\.woff2)\)/);
    const unicodeMatch = block.match(/unicode-range:\s*([^;]+);/);

    if (!urlMatch || !familyMatch) continue;
    const subset = subsetMatch?.[1] || 'x';
    const family = familyMatch[1];
    const weight = weightMatch?.[1] || '400';
    const style = styleMatch?.[1] || 'normal';

    const slug = `${family.toLowerCase().replace(/\s+/g, '-')}-${weight}${style === 'italic' ? 'i' : ''}-${subset}`;
    const localPath = `${slug}.woff2`;
    const outFile = path.join(OUT_DIR, localPath);

    if (!fs.existsSync(outFile)) {
      const fontRes = await fetch(urlMatch[1], { headers: { 'User-Agent': UA_CHROME } });
      if (!fontRes.ok) {
        console.warn(`Skip ${slug}: HTTP ${fontRes.status}`);
        continue;
      }
      const buf = Buffer.from(await fontRes.arrayBuffer());
      fs.writeFileSync(outFile, buf);
      downloaded++;
      console.log(`  ${slug}: ${buf.length} bytes`);
    }

    outCss += `/* ${subset} */\n`;
    outCss += `@font-face {\n`;
    outCss += `  font-family: '${family}';\n`;
    outCss += `  font-style: ${style};\n`;
    outCss += `  font-weight: ${weight};\n`;
    outCss += `  font-display: swap;\n`;
    outCss += `  src: url('/fonts/${localPath}') format('woff2');\n`;
    if (unicodeMatch) outCss += `  unicode-range: ${unicodeMatch[1].trim()};\n`;
    outCss += `}\n\n`;
  }

  fs.writeFileSync(path.join(OUT_DIR, 'wellcore-fonts.css'), outCss);
  console.log(`\nDone. Downloaded ${downloaded} new WOFF2 files.`);
  console.log(`CSS written: public/fonts/wellcore-fonts.css (${outCss.length} bytes)`);
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
