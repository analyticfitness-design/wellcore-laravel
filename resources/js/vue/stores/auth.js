import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import { resetContractGate } from '../composables/useContractGate';

export const useAuthStore = defineStore('auth', () => {
    // Si Laravel inyecto auth data en sesion (login vía Livewire), sincronizar
    // a localStorage SOLO si localStorage esta vacio. En flow SPA-only (login
    // via /api/v/auth/login + impersonation API), localStorage es la fuente
    // de verdad — sobreescribir rompia la impersonacion coach -> cliente.
    if (window.__WC_SESSION) {
        const s = window.__WC_SESSION;
        const currentLocalToken = localStorage.getItem('wc_token');
        const hasLocalToken = !!currentLocalToken;
        const isImpersonationStart = s.impersonating && s.token && s.token !== currentLocalToken;

        if (isImpersonationStart) {
            // Admin/coach just impersonated: server-side session is authoritative.
            // Stash the current (admin) token as the backup before overwriting.
            if (currentLocalToken && !localStorage.getItem('wc_admin_token')) {
                localStorage.setItem('wc_admin_token', currentLocalToken);
            }
            localStorage.setItem('wc_token', s.token);
            localStorage.setItem('wc_user_type', s.userType || 'client');
            localStorage.setItem('wc_user_id', String(s.userId || ''));
            if (s.userName) {
                localStorage.setItem('wc_user_name', s.userName);
            }
        } else if (s.token && !hasLocalToken) {
            localStorage.setItem('wc_token', s.token);
            localStorage.setItem('wc_user_type', s.userType || 'client');
            localStorage.setItem('wc_user_id', String(s.userId || ''));
            if (s.userName) {
                localStorage.setItem('wc_user_name', s.userName);
            }
        }
        if (s.portal && !localStorage.getItem('wc_user_portal')) {
            localStorage.setItem('wc_user_portal', s.portal);
        }
        if (s.impersonating) {
            localStorage.setItem('wc_impersonating', 'true');
        }
        if (s.adminToken && !localStorage.getItem('wc_admin_token')) {
            localStorage.setItem('wc_admin_token', s.adminToken);
        }
    }

    const token = ref(localStorage.getItem('wc_token') || null);
    const userType = ref(localStorage.getItem('wc_user_type') || null);
    const userId = ref(localStorage.getItem('wc_user_id') || null);
    const userPortal = ref(localStorage.getItem('wc_user_portal') || null); // '/rise' or '/client'

    const isImpersonating = computed(() => localStorage.getItem('wc_impersonating') === 'true');
    const isAuthenticated = computed(() => !!token.value);
    // ref (not computed) so assigning forcePasswordChange.value = false is reactive immediately.
    // computed with no reactive deps caches forever → redirect loop after password change.
    const forcePasswordChange = ref(localStorage.getItem('wc_force_password_change') === 'true');

    function setAuth(data) {
        token.value = data.token;
        userType.value = data.userType;
        userId.value = data.userId;

        localStorage.setItem('wc_token', data.token);
        localStorage.setItem('wc_user_type', data.userType);
        localStorage.setItem('wc_user_id', data.userId);
        if (data.name) {
            localStorage.setItem('wc_user_name', data.name);
        }
        if (data.redirectUrl) {
            userPortal.value = data.redirectUrl;
            localStorage.setItem('wc_user_portal', data.redirectUrl);
        }
        if (typeof data.force_password_change !== 'undefined') {
            if (data.force_password_change) {
                localStorage.setItem('wc_force_password_change', 'true');
                forcePasswordChange.value = true;
            } else {
                localStorage.removeItem('wc_force_password_change');
                forcePasswordChange.value = false;
            }
        }
    }

    async function refreshMe() {
        try {
            const resp = await axios.get('/api/v/auth/me', {
                headers: { Authorization: `Bearer ${token.value}` },
            });
            if (resp.data && resp.data.authenticated) {
                if (resp.data.force_password_change) {
                    localStorage.setItem('wc_force_password_change', 'true');
                    forcePasswordChange.value = true;
                } else {
                    localStorage.removeItem('wc_force_password_change');
                    forcePasswordChange.value = false;
                }
            }
            return resp.data;
        } catch {
            return null;
        }
    }

    function clearAuth() {
        resetContractGate();
        token.value = null;
        userType.value = null;
        userId.value = null;

        localStorage.removeItem('wc_token');
        localStorage.removeItem('wc_user_type');
        localStorage.removeItem('wc_user_id');
        localStorage.removeItem('wc_user_name');
        localStorage.removeItem('wc_impersonating');
        localStorage.removeItem('wc_admin_token');
        localStorage.removeItem('wc_user_portal');
        localStorage.removeItem('wc_force_password_change');
        forcePasswordChange.value = false;
    }

    async function login(identity, password, rememberMe = false) {
        const response = await axios.post('/api/v/auth/login', {
            identity,
            password,
            remember_me: rememberMe,
        });

        setAuth(response.data);
        return response.data;
    }

    async function logout() {
        try {
            await axios.post('/api/v/auth/logout', null, {
                headers: { Authorization: `Bearer ${token.value}` },
            });
        } catch {
            // Silently fail — we clear local state regardless
        }
        clearAuth();
    }

    async function forgotPassword(email) {
        const response = await axios.post('/api/v/auth/forgot-password', { email });
        return response.data;
    }

    async function resetPassword(data) {
        const response = await axios.post('/api/v/auth/reset-password', data);
        return response.data;
    }

    return {
        token,
        userType,
        userId,
        isAuthenticated,
        login,
        logout,
        forgotPassword,
        resetPassword,
        userPortal,
        isImpersonating,
        forcePasswordChange,
        refreshMe,
        setAuth,
        clearAuth,
    };
});
