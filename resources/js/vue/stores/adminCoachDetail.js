import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

/**
 * adminCoachDetail — Pinia store del drawer detail de un coach.
 *
 * Lifecycle:
 *  - openCoach(coach): copia el shallow snapshot de la lista al state, abre
 *    drawer, lanza fetch del payload completo y arranca polling 60s.
 *  - close(): para polling y limpia el coach activo.
 *
 * El polling solo corre mientras hay coachId activo (drawer abierto). silent=true
 * en cada tick evita parpadeo del skeleton — los datos siguen visibles mientras
 * se actualiza en background.
 *
 * Tab activa: gestionada en el state para que el drawer recuerde dónde estaba
 * el admin si vuelve a abrirlo en la misma sesión.
 */
export const useAdminCoachDetailStore = defineStore('adminCoachDetail', {
    state: () => ({
        coachId: null,
        snapshot: null,        // shallow data desde la lista (mientras carga el detail completo)
        detail: null,          // payload completo de GET /coaches/{id}
        loading: false,
        error: null,
        lastRefresh: null,
        polling: null,
        activeTab: 'resumen',  // resumen | clientes | activity | pagos
    }),

    getters: {
        isOpen: (s) => s.coachId !== null,
        // Mientras carga el detail, mostramos lo que vino de la lista
        view: (s) => s.detail || s.snapshot || null,
        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,
    },

    actions: {
        openCoach(coach) {
            if (!coach || !coach.id) return;
            this.coachId = coach.id;
            this.snapshot = { ...coach };
            this.detail = null;
            this.error = null;
            this.activeTab = 'resumen';
            this.fetchDetail();
            this.startPolling();
        },

        async fetchDetail({ silent = false } = {}) {
            if (!this.coachId) return;
            const api = useApi();
            if (!silent && !this.detail) {
                this.loading = true;
            }
            this.error = null;

            try {
                const { data } = await api.get(`/api/v/admin/coaches/${this.coachId}`);
                this.detail = data;
                this.lastRefresh = new Date();
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al cargar coach';
            } finally {
                this.loading = false;
            }
        },

        setTab(tab) {
            this.activeTab = tab;
        },

        startPolling(intervalMs = 60000) {
            this.stopPolling();
            this.polling = setInterval(() => {
                this.fetchDetail({ silent: true });
            }, intervalMs);
        },

        stopPolling() {
            if (this.polling) {
                clearInterval(this.polling);
                this.polling = null;
            }
        },

        close() {
            this.stopPolling();
            this.coachId = null;
            this.snapshot = null;
            this.detail = null;
            this.error = null;
            this.lastRefresh = null;
            this.activeTab = 'resumen';
        },

        async toggleVisibility() {
            if (!this.coachId) return;
            const api = useApi();
            try {
                const { data } = await api.patch(`/api/v/admin/coaches/${this.coachId}/visibility`);
                if (this.detail) {
                    this.detail = { ...this.detail, public_visible: data.public_visible };
                }
            } catch (err) {
                // silenciar — UI no cambia si falla
            }
        },

        $resetState() {
            this.close();
            this.loading = false;
        },
    },
});
