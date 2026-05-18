import { defineStore } from 'pinia';
import { setI18nLocale } from '../i18n';

async function patchMeLocale(payload) {
    const token = typeof localStorage !== 'undefined' ? localStorage.getItem('wc_token') : null;
    const headers = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    };
    if (token) headers.Authorization = `Bearer ${token}`;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

    const response = await fetch('/api/v/me/locale', {
        method: 'PATCH',
        headers,
        credentials: 'same-origin',
        body: JSON.stringify(payload),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const err = new Error(data?.message ?? `HTTP ${response.status}`);
        err.status = response.status;
        err.payload = data;
        throw err;
    }

    return data;
}

/**
 * Estado global del idioma + sistema de unidades del usuario activo.
 *
 * Fuente de verdad: backend (columnas clients.locale / admins.locale /
 * clients.unit_system). El store mantiene sincronía con cookie wc_locale +
 * meta tag para resiliencia entre navegaciones.
 *
 * Ver docs/I18N_AUDIT_2026-05-17.md y docs/adr/0004-i18n-en-us.md
 */
export const useLocaleStore = defineStore('locale', {
    state: () => ({
        locale: readInitialLocale(),
        unitSystem: readInitialUnitSystem(),
        localeLocked: readInitialLock(),
        saving: false,
        lastError: null,
    }),

    actions: {
        /**
         * Cambia el locale y persiste al backend.
         * Si está locked, devuelve false sin escribir.
         */
        async changeLocale(locale) {
            if (this.localeLocked) {
                this.lastError = 'locale_locked';
                return false;
            }
            if (locale === this.locale) return true;

            const previousLocale = this.locale;
            this.locale = locale;
            this.saving = true;
            this.lastError = null;

            await setI18nLocale(locale);

            try {
                const result = await patchMeLocale({ locale });

                if (result?.ok) {
                    document.cookie = `wc_locale=${locale};path=/;max-age=31536000;samesite=lax`;
                    return true;
                }

                this.locale = previousLocale;
                await setI18nLocale(previousLocale);
                this.lastError = result?.message ?? 'unknown_error';
                return false;
            } catch (err) {
                this.locale = previousLocale;
                await setI18nLocale(previousLocale);
                this.lastError = err?.message ?? 'network_error';
                return false;
            } finally {
                this.saving = false;
            }
        },

        async changeUnitSystem(unitSystem) {
            if (!['metric', 'imperial'].includes(unitSystem)) return false;
            if (unitSystem === this.unitSystem) return true;

            const previous = this.unitSystem;
            this.unitSystem = unitSystem;
            this.saving = true;

            try {
                const result = await patchMeLocale({ locale: this.locale, unit_system: unitSystem });

                if (result?.ok) return true;

                this.unitSystem = previous;
                return false;
            } catch {
                this.unitSystem = previous;
                return false;
            } finally {
                this.saving = false;
            }
        },
    },
});

function readInitialLocale() {
    if (typeof window === 'undefined') return 'es';
    return window.__wcInitialLocale ?? 'es';
}

function readInitialUnitSystem() {
    if (typeof window === 'undefined') return 'metric';
    return window.__wcUnitSystem ?? 'metric';
}

function readInitialLock() {
    if (typeof window === 'undefined') return false;
    return Boolean(window.__wcLocaleLocked);
}
