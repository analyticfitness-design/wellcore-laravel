import './bootstrap';
import './echo.js';

// ─── Safety net: si animations.js no carga (chunk 404, error de red, CSP),
// aseguramos que ningun [data-animate] quede invisible mas de 600ms.
// Es defensa en profundidad: el CSS ya tiene un keyframe fallback con el mismo
// timing, esto es la red JS que ademas funciona en navegadores sin animation.
setTimeout(() => {
    document.querySelectorAll('[data-animate]:not(.animate-in)').forEach(el => {
        el.classList.add('animate-in');
    });
}, 600);

// Nota: el plugin @alpinejs/collapse se bundlea en resources/js/alpine-public.js
// y se carga via @vite en el layout publico. Para Livewire, el plugin se debe
// registrar dentro del Alpine bundleado por Livewire — eso queda fuera de este
// bundle.

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

// ─── Animations — carga inmediata (no via requestIdleCallback): los elementos
// [data-animate] arrancan invisibles via CSS y se revelan con IntersectionObserver
// dentro de animations.js. Si lo difieramos, el primer fold se queda negro hasta
// que el browser este "idle" — perceptible como lentitud.
import('./animations.js');

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
