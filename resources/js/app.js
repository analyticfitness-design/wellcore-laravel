import './bootstrap';

// Alpine.js is automatically loaded by Livewire 3.
// Do NOT import it here — duplicate Alpine instances break wire: directives.

// Chart.js — lazy-loaded only on pages that actually render charts.
// This keeps chart.js (~170KB) out of the bundle for public/marketing pages.
// Pages that need it should add a <canvas data-chart> element, or set
// window.__wcNeedsCharts = true before DOMContentLoaded.
const loadChartsIfNeeded = () => {
    const needed = window.__wcNeedsCharts === true || document.querySelector('canvas[data-chart], [data-needs-chart]');
    if (needed) {
        import(/* webpackChunkName: "chart-init" */ './chart-init.js');
    }
};
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadChartsIfNeeded, { once: true });
} else {
    loadChartsIfNeeded();
}

// Scroll animations (IntersectionObserver)
import './animations.js';

// Push notification subscription
import { initPushSubscription } from './push-subscription.js';
initPushSubscription();

// PWA Service Worker registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
