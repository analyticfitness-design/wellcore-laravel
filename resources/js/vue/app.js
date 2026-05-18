import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import { useAuthStore } from './stores/auth';
import { i18n, loadLocaleMessages } from './i18n';

import '../../css/app.css';

// Legacy /v/* redirect — nginx rewrites /v/* internally so the browser URL
// may still show /v/... after loading. Redirect client-side to the clean path.
if (window.location.pathname.startsWith('/v/')) {
    const clean = window.location.pathname.slice(2) + window.location.search + window.location.hash;
    window.location.replace(clean || '/');
}

// ─────────────────────────────────────────────────────────────────────────
// PWA boot-time impersonation recovery
// ─────────────────────────────────────────────────────────────────────────
// Caso: coach impersona cliente → cierra PWA → vuelve después de horas →
// el token cliente (8h) puede haber expirado mientras el wc_token_backup
// (token coach, 30 días) sigue válido.
//
// Si arrancamos en /client/* con flags de impersonación pero el token cliente
// ya expiró por timestamp, restauramos la sesión coach SILENCIOSAMENTE antes
// de que router/componentes hagan fetch. Esto evita el error "Acceso solo
// para clientes" que dejaba al coach atrapado sin opción visible para volver.
(function bootImpersonationRecovery() {
    try {
        const isImpersonating = localStorage.getItem('wc_impersonating_by_coach') === '1';
        if (!isImpersonating) return;

        const backupToken = localStorage.getItem('wc_token_backup');
        if (!backupToken) return;

        const expiresAt = parseInt(localStorage.getItem('wc_impersonation_expires_at') || '0', 10);
        if (expiresAt > 0 && Date.now() < expiresAt) return; // Token cliente sigue válido

        // Si llegamos aquí: token cliente expirado (o sin timestamp en
        // impersonations viejas). Restaurar coach session.

        // Best-effort: cerrar sesión en backend (no bloquear ni esperar)
        const expiredToken = localStorage.getItem('wc_impersonating_token_key');
        if (expiredToken) {
            fetch('/api/v/coach/impersonate/end', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${backupToken}`,
                },
                body: JSON.stringify({ token: expiredToken }),
                keepalive: true,
            }).catch(() => { /* noop */ });
        }

        // Restaurar coach session desde backups
        const userType = localStorage.getItem('wc_user_type_backup');
        const userId   = localStorage.getItem('wc_user_id_backup');
        const userName = localStorage.getItem('wc_user_name_backup') || '';
        const portal   = localStorage.getItem('wc_user_portal_backup');

        localStorage.setItem('wc_token', backupToken);
        if (userType) localStorage.setItem('wc_user_type', userType);
        if (userId)   localStorage.setItem('wc_user_id', userId);
        localStorage.setItem('wc_user_name', userName);
        if (portal)   localStorage.setItem('wc_user_portal', portal);

        [
            'wc_token_backup', 'wc_user_type_backup', 'wc_user_id_backup',
            'wc_user_name_backup', 'wc_user_portal_backup',
            'wc_impersonating_by_coach', 'wc_impersonating_token_key',
            'wc_impersonation_client_id', 'wc_impersonation_expires_at',
        ].forEach((k) => localStorage.removeItem(k));

        // Hard redirect a /coach si estamos en /client/* (la URL cached por la PWA)
        const path = window.location.pathname;
        if (path.startsWith('/client/') || path === '/client') {
            window.location.replace('/coach');
        }
    } catch { /* noop — best effort */ }
})();

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(i18n);

// Carga el bundle del locale activo en background. El Blade root ya inyectó
// namespaces críticos via window.__wcMessages para evitar FOUC.
loadLocaleMessages(i18n.global.locale.value).catch(() => { /* noop */ });

// Hidratar coachStrategy store si el usuario es coach
const authStore = useAuthStore();
if (authStore.isAuthenticated && authStore.userType === 'coach') {
    import('./stores/coachStrategy').then(({ useCoachStrategyStore }) => {
        useCoachStrategyStore().fetchProfile().catch(() => {
            // Silent fail — el guard del router se encargará si es necesario
        });
    });
}

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
