import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

import '../../css/app.css';

// Legacy /v/* redirect — nginx rewrites /v/* internally so the browser URL
// may still show /v/... after loading. Redirect client-side to the clean path.
if (window.location.pathname.startsWith('/v/')) {
    const clean = window.location.pathname.slice(2) + window.location.search + window.location.hash;
    window.location.replace(clean || '/');
}

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.mount('#vue-app');

// Service Worker — registro opt-in post-mount (no bloquea render inicial).
// El SW está en /public/sw.js (compartido con el portal Livewire).
// Cache-first para assets; network-first para HTML; skip /api/*.
if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Silent fail — PWA no es crítico para el funcionamiento del app.
        });
    });
}
