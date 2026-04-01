import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    // If the Laravel session injected auth data (e.g. after impersonation),
    // sync it to localStorage so the SPA uses the correct token.
    if (window.__WC_SESSION) {
        const s = window.__WC_SESSION;
        if (s.token && s.token !== localStorage.getItem('wc_token')) {
            localStorage.setItem('wc_token', s.token);
            localStorage.setItem('wc_user_type', s.userType || 'client');
            localStorage.setItem('wc_user_id', String(s.userId || ''));
            if (s.impersonating) {
                localStorage.setItem('wc_impersonating', 'true');
                localStorage.setItem('wc_admin_token', localStorage.getItem('wc_token') || '');
            }
        }
    }

    const token = ref(localStorage.getItem('wc_token') || null);
    const userType = ref(localStorage.getItem('wc_user_type') || null);
    const userId = ref(localStorage.getItem('wc_user_id') || null);

    const isImpersonating = computed(() => localStorage.getItem('wc_impersonating') === 'true');
    const isAuthenticated = computed(() => !!token.value);

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
    }

    function clearAuth() {
        token.value = null;
        userType.value = null;
        userId.value = null;

        localStorage.removeItem('wc_token');
        localStorage.removeItem('wc_user_type');
        localStorage.removeItem('wc_user_id');
        localStorage.removeItem('wc_user_name');
        localStorage.removeItem('wc_impersonating');
        localStorage.removeItem('wc_admin_token');
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
        isImpersonating,
        setAuth,
        clearAuth,
    };
});
