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
