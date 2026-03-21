import './bootstrap';

// Alpine.js is automatically loaded by Livewire 3.
// Do NOT import it here — duplicate Alpine instances break wire: directives.

// Chart.js — configured with WellCore theme, dark mode, exported globally
import './chart-init.js';

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
