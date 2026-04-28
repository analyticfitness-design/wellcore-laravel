import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

const SUPERADMIN_ONLY = ['pagos', 'email', 'seguridad', 'integraciones', 'mantenimiento'];
const SECRET_PLACEHOLDER_RE = /^•{4,}/;

export const useAdminSettingsStore = defineStore('adminSettings', {
    state: () => ({
        loading: false,
        saving: false,
        error: null,
        toast: null,

        // Seccion activa en el sidebar (desktop) / acordeon (mobile)
        activeSection: 'general',

        // Datos originales (snapshot del API response — nunca mutar)
        originalData: {},

        // Datos en edicion (clon mutable del original)
        formData: {},

        // Info del admin
        role: 'admin',
        secretKeys: [],
        superadminOnlySections: SUPERADMIN_ONLY,
        warnings: {},
    }),

    getters: {
        isSuperAdmin: (s) => ['superadmin', 'jefe'].includes(s.role),

        sectionReadonly: (s) => (sectionId) => {
            if (['superadmin', 'jefe'].includes(s.role)) return false;
            return SUPERADMIN_ONLY.includes(sectionId);
        },

        // Devuelve el numero total de campos que difieren del original
        dirtyCount: (s) => {
            let count = 0;
            for (const section of Object.keys(s.formData)) {
                const orig = s.originalData[section] ?? {};
                const curr = s.formData[section] ?? {};
                for (const key of Object.keys(curr)) {
                    const currVal = curr[key];
                    const origVal = orig[key];
                    // Secrets enmascarados que no cambiaron no cuentan
                    if (typeof currVal === 'string' && SECRET_PLACEHOLDER_RE.test(currVal)) continue;
                    if (JSON.stringify(currVal) !== JSON.stringify(origVal)) {
                        count++;
                    }
                }
            }
            return count;
        },

        dirty: (s) => s.dirtyCount > 0,

        sectionDirty: (s) => (sectionId) => {
            const orig = s.originalData[sectionId] ?? {};
            const curr = s.formData[sectionId] ?? {};
            for (const key of Object.keys(curr)) {
                const currVal = curr[key];
                if (typeof currVal === 'string' && SECRET_PLACEHOLDER_RE.test(currVal)) continue;
                if (JSON.stringify(currVal) !== JSON.stringify(orig[key])) return true;
            }
            return false;
        },
    },

    actions: {
        async fetch() {
            const api = useApi();
            this.loading = true;
            this.error = null;
            try {
                const { data } = await api.get('/api/v/admin/settings');
                this.role = data.role ?? 'admin';
                this.secretKeys = data.secret_keys ?? [];
                this.warnings = data.warnings ?? {};
                this.originalData = JSON.parse(JSON.stringify(data.sections ?? {}));
                this.formData = JSON.parse(JSON.stringify(data.sections ?? {}));
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al cargar la configuracion.';
            } finally {
                this.loading = false;
            }
        },

        // Actualiza un campo en formData (solo el estado editable)
        setField(section, key, value) {
            if (!this.formData[section]) this.formData[section] = {};
            this.formData[section][key] = value;
        },

        async saveSection(section) {
            if (this.saving) return;
            const api = useApi();
            this.saving = true;
            this.error = null;

            // Construir el diff — solo campos que cambiaron
            const orig = this.originalData[section] ?? {};
            const curr = this.formData[section] ?? {};
            const changedFields = {};

            for (const key of Object.keys(curr)) {
                const currVal = curr[key];
                // Omitir placeholders de secret sin modificar
                if (typeof currVal === 'string' && SECRET_PLACEHOLDER_RE.test(currVal)) continue;
                if (JSON.stringify(currVal) !== JSON.stringify(orig[key])) {
                    changedFields[key] = currVal;
                }
            }

            if (Object.keys(changedFields).length === 0) {
                this.saving = false;
                return;
            }

            try {
                await api.put('/api/v/admin/settings', { section, fields: changedFields });

                // Sincronizar el snapshot con lo guardado
                this.originalData[section] = JSON.parse(JSON.stringify(curr));

                this.showToast('Configuracion actualizada.');
            } catch (err) {
                const msg = err.response?.data?.error || err.response?.data?.message || 'Error al guardar.';
                this.error = msg;
                this.showToast(msg, 'error');
            } finally {
                this.saving = false;
            }
        },

        async saveAll() {
            // Guarda todas las secciones que tengan cambios
            for (const section of Object.keys(this.formData)) {
                if (this.sectionDirty(section)) {
                    await this.saveSection(section);
                    if (this.error) break;
                }
            }
        },

        discardAll() {
            this.formData = JSON.parse(JSON.stringify(this.originalData));
            this.error = null;
        },

        discardSection(section) {
            if (this.originalData[section]) {
                this.formData[section] = JSON.parse(JSON.stringify(this.originalData[section]));
            }
        },

        setActiveSection(section) {
            this.activeSection = section;
        },

        showToast(message, type = 'success') {
            this.toast = { message, type, id: Date.now() };
            setTimeout(() => { this.toast = null; }, 3500);
        },

        async testSmtp(payload) {
            const api = useApi();
            const { data } = await api.post('/api/v/admin/settings/test-smtp', payload);
            return data;
        },

        async verifyPaymentGateway(payload) {
            const api = useApi();
            const { data } = await api.post('/api/v/admin/settings/verify-payment-gateway', payload);
            return data;
        },
    },
});
