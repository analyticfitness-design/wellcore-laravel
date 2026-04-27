import './bootstrap';
import './echo.js';

// ─── Chart.js — lazy, solo en páginas que lo necesitan ───────────────────────
const loadChartsIfNeeded = () => {
    if (window.__wcNeedsCharts === true || document.querySelector('canvas[data-chart], [data-needs-chart]')) {
        import(/* webpackChunkName: "chart-init" */ './chart-init.js');
    }
};
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadChartsIfNeeded, { once: true });
} else {
    loadChartsIfNeeded();
}

// ─── Animations — lazy on idle (no es crítico para LCP) ──────────────────────
if ('requestIdleCallback' in window) {
    requestIdleCallback(() => import('./animations.js'), { timeout: 2000 });
} else {
    setTimeout(() => import('./animations.js'), 1500);
}

// ─── Push subscription — solo en páginas autenticadas ─────────────────────────
// Las páginas públicas (/, /planes, /nosotros, etc.) no necesitan push SW.
// Las páginas privadas están bajo /client, /coach, /admin, /rise.
const isPrivatePage = /^\/(client|coach|admin|rise)/.test(location.pathname);
if (isPrivatePage) {
    import('./push-subscription.js').then(m => m.initPushSubscription());
}

// ─── Coach Dashboard — solo en /coach/* ──────────────────────────────────────
if (location.pathname.startsWith('/coach')) {
    import('./coach-dashboard');
}

// ─── Voice Logger (SP-3) — solo en workout player ────────────────────────────
if (/\/client\/workout/.test(location.pathname)) {
    import('./voice/voice-logger.js');
}

// ─── PWA Service Worker ───────────────────────────────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
