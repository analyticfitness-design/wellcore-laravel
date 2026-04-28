#!/usr/bin/env node
// Smoke test de producción para WellCore Fitness — verifica regresiones del Sprint 0 v2.
// Ejecutado por .github/workflows/smoke-prod.yml diariamente a las 8:07 AM Colombia.
//
// Verifica:
//   1. HTTP status 200 OK
//   2. Console errors / page errors = 0
//   3. Fingerprints v2: body.public-page, fonts (Raleway primary), atmósfera ::before,
//      preconnect Google Fonts, --ease-out resuelve
//   4. Screenshot mobile 390x844 + desktop 1440x900
//   5. LCP del navigation (informativo, no bloqueante)
//
// Exit 0 = todo verde · Exit 1 = regresión detectada

import { chromium } from 'playwright';
import { mkdir, writeFile } from 'node:fs/promises';

const TARGET = process.env.SMOKE_TARGET_URL || 'https://wellcorefitness.com/';
const SHOTS_DIR = 'smoke-screenshots';
const REPORT_FILE = 'smoke-report.json';

const checks = [];
const errors = [];
const warnings = [];

function check(name, ok, detail = '') {
    checks.push({ name, ok, detail });
    const icon = ok ? '✅' : '❌';
    const tail = detail ? ` — ${detail}` : '';
    console.log(`${icon} ${name}${tail}`);
}

async function run() {
    await mkdir(SHOTS_DIR, { recursive: true });

    const browser = await chromium.launch({ headless: true });
    let regressions = 0;

    try {
        // ── Pase 1: mobile (iPhone 14 Pro-ish) ──────────────────────────────
        const mobileCtx = await browser.newContext({
            viewport: { width: 390, height: 844 },
            deviceScaleFactor: 3,
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            isMobile: true,
            hasTouch: true,
        });
        const page = await mobileCtx.newPage();

        page.on('console', (msg) => {
            const t = msg.type();
            if (t === 'error') errors.push(`[console] ${msg.text()}`);
            if (t === 'warning') warnings.push(`[console] ${msg.text()}`);
        });
        page.on('pageerror', (err) => errors.push(`[pageerror] ${err.message}`));
        page.on('requestfailed', (req) => {
            // 4xx/5xx en recursos críticos cuentan como errores
            const url = req.url();
            const failure = req.failure()?.errorText || 'unknown';
            if (/\.(css|js|woff2?)(\?|$)/i.test(url)) {
                errors.push(`[requestfailed] ${url} — ${failure}`);
            }
        });

        const response = await page.goto(TARGET, { waitUntil: 'networkidle', timeout: 30_000 });
        const status = response?.status() ?? 0;
        check('HTTP 200', status === 200, `status=${status}`);

        // Espera adicional para que estabilice el render (atmósfera CSS ::before)
        await page.waitForTimeout(800);

        const fingerprints = await page.evaluate(() => {
            const body = document.body;
            const html = document.documentElement;
            const styles = getComputedStyle(body);
            const before = getComputedStyle(body, '::before');
            return {
                title: document.title,
                bodyClasses: body.className,
                htmlClasses: html.className,
                bgColor: styles.backgroundColor,
                fontFamily: styles.fontFamily,
                isPublicPage: body.classList.contains('public-page'),
                cssBefore: (before.background || '') + ' | ' + (before.backgroundImage || ''),
                preconnectGoogle: !!document.querySelector('link[rel="preconnect"][href="https://fonts.googleapis.com"]'),
                preconnectGstatic: !!document.querySelector('link[rel="preconnect"][href="https://fonts.gstatic.com"]'),
                cssVarEaseOut: getComputedStyle(html).getPropertyValue('--ease-out').trim(),
                cssVarTopbarH: getComputedStyle(html).getPropertyValue('--topbar-h').trim(),
                cssVarColMax: getComputedStyle(html).getPropertyValue('--col-max').trim(),
                navTiming: (() => {
                    const e = performance.getEntriesByType('navigation')[0];
                    return e ? {
                        domContentLoaded: Math.round(e.domContentLoadedEventEnd),
                        loadEvent: Math.round(e.loadEventEnd),
                        transferSize: e.transferSize,
                    } : null;
                })(),
            };
        });

        check('body.public-page activa',
            fingerprints.isPublicPage,
            fingerprints.bodyClasses);
        check('Fonts: Raleway primary',
            /^['"]?Raleway['"]?/i.test(fingerprints.fontFamily),
            fingerprints.fontFamily.slice(0, 80));
        check('Atmósfera global ::before presente',
            /radial-gradient/i.test(fingerprints.cssBefore),
            'gradients=' + (fingerprints.cssBefore.match(/radial-gradient/g) || []).length);
        check('Preconnect Google Fonts',
            fingerprints.preconnectGoogle && fingerprints.preconnectGstatic,
            `googleapis=${fingerprints.preconnectGoogle} gstatic=${fingerprints.preconnectGstatic}`);
        check('Token --ease-out resuelve',
            !!fingerprints.cssVarEaseOut && fingerprints.cssVarEaseOut.includes('cubic-bezier'),
            fingerprints.cssVarEaseOut);
        check('Tokens layout (--topbar-h, --col-max)',
            !!fingerprints.cssVarTopbarH && !!fingerprints.cssVarColMax,
            `topbar=${fingerprints.cssVarTopbarH} col-max=${fingerprints.cssVarColMax}`);

        const today = new Date().toISOString().slice(0, 10);
        await page.screenshot({ path: `${SHOTS_DIR}/smoke_${today}_mobile.png`, fullPage: false });
        check('Screenshot mobile capturado', true, `${SHOTS_DIR}/smoke_${today}_mobile.png`);

        await mobileCtx.close();

        // ── Pase 2: desktop ────────────────────────────────────────────────
        const desktopCtx = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            deviceScaleFactor: 2,
        });
        const dpage = await desktopCtx.newPage();
        await dpage.goto(TARGET, { waitUntil: 'domcontentloaded', timeout: 30_000 });
        await dpage.waitForTimeout(500);
        await dpage.screenshot({ path: `${SHOTS_DIR}/smoke_${today}_desktop.png`, fullPage: false });
        check('Screenshot desktop capturado', true, `${SHOTS_DIR}/smoke_${today}_desktop.png`);
        await desktopCtx.close();

        // ── Console / pageerror tally ──────────────────────────────────────
        check('Console errors = 0', errors.length === 0, `${errors.length} errors`);
        check('Console warnings = 0', warnings.length === 0, `${warnings.length} warnings`);

        regressions = checks.filter((c) => !c.ok).length;

        // ── Reporte JSON estructurado ──────────────────────────────────────
        const report = {
            timestamp: new Date().toISOString(),
            target: TARGET,
            status,
            regressions,
            checks,
            errors,
            warnings,
            fingerprints,
        };
        await writeFile(REPORT_FILE, JSON.stringify(report, null, 2));

        console.log('\n' + '─'.repeat(60));
        if (regressions === 0) {
            console.log(`✅ SMOKE TEST OK — ${checks.length} checks pasan, 0 regresiones.`);
        } else {
            console.log(`🚨 REGRESIÓN DETECTADA — ${regressions}/${checks.length} checks fallan.`);
            errors.forEach((e) => console.log(`   ${e}`));
        }
        console.log('─'.repeat(60));
    } finally {
        await browser.close();
    }

    process.exit(regressions === 0 ? 0 : 1);
}

run().catch((err) => {
    console.error('🚨 SMOKE TEST CRASHED:', err);
    process.exit(2);
});
