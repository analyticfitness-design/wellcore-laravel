import './bootstrap';

// Alpine.js is automatically loaded by Livewire 3.
// Do NOT import it here — duplicate Alpine instances break wire: directives.

// Chart.js — expose globally so Alpine.js components in Blade views can use it
import Chart from 'chart.js/auto';
window.Chart = Chart;

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
