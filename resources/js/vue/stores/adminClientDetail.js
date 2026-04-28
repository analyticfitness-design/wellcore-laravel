import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

const TABS = ['resumen', 'plan', 'checkins', 'pagos', 'comunicacion', 'notas'];
const POLL_TABS = new Set(['resumen', 'checkins']);

/**
 * adminClientDetail — Pinia store de la página detalle de un cliente.
 *
 * Lectura canónica: GET /api/v/admin/clients/:id
 *   Devuelve: { client, plans, coaches, statusOptions, planOptions }
 *   client: { id, name, email, phone, plan, status, kpis, alerts, checkins,
 *             payments, planDetails, metrics, lastLogin, ... }
 *
 * Polling: 60s mientras la pestaña activa está en un POLL_TAB
 * (resumen | checkins). Si el admin pasa a Plan/Pagos/Comunicación/Notas
 * se pausa automáticamente — esos paneles no necesitan refresh continuo y
 * gastar requests es perder presupuesto sin ganar nada.
 *
 * URL sync: ?tab=plan se hidrata al mount; cambiar tab actualiza query
 * param sin push (replaceState) para no contaminar history.
 */
export const useAdminClientDetailStore = defineStore('adminClientDetail', {
    state: () => ({
        clientId: null,
        client: null,
        plans: [],
        coaches: [],
        statusOptions: [],
        planOptions: [],
        loading: false,
        error: null,
        lastRefresh: null,
        polling: null,
        activeTab: 'resumen',
        // Acciones en vuelo
        savingStatus: false,
        savingPlan: false,
        savingCoach: false,
        actionMessage: null,
    }),

    getters: {
        kpis: (s) => s.client?.stats || s.client?.kpis || {},
        metrics: (s) => s.client?.metrics || {},
        planDetails: (s) => s.client?.planDetails || {},
        checkins: (s) => s.client?.checkins || [],
        payments: (s) => s.client?.payments || [],
        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,
        // Coaches ordenados por load balance ascendente cuando se quiera asignar
        coachesByLoad: (s) => {
            const list = [...(s.coaches || [])];
            list.sort((a, b) => Number(a.client_count || 0) - Number(b.client_count || 0));
            return list;
        },
    },

    actions: {
        hydrateFromUrl() {
            if (typeof window === 'undefined') return;
            const q = new URLSearchParams(window.location.search);
            const t = q.get('tab');
            if (t && TABS.includes(t)) this.activeTab = t;
        },

        syncTabToUrl() {
            if (typeof window === 'undefined') return;
            const q = new URLSearchParams(window.location.search);
            if (this.activeTab && this.activeTab !== 'resumen') {
                q.set('tab', this.activeTab);
            } else {
                q.delete('tab');
            }
            const qs = q.toString();
            const url = window.location.pathname + (qs ? `?${qs}` : '');
            window.history.replaceState({}, '', url);
        },

        async openClient(id) {
            const numId = Number(id);
            if (!numId) return;
            const isSame = this.clientId === numId;
            this.clientId = numId;
            if (!isSame) {
                this.client = null;
                this.plans = [];
                this.coaches = [];
                this.error = null;
            }
            await this.fetchDetail();
            this.startPolling();
        },

        async fetchDetail({ silent = false } = {}) {
            if (!this.clientId) return;
            const api = useApi();
            if (!silent && !this.client) this.loading = true;
            this.error = null;

            try {
                const { data } = await api.get(`/api/v/admin/clients/${this.clientId}`);
                this.client = data.client || data;
                this.plans = data.plans || [];
                this.coaches = data.coaches || [];
                this.statusOptions = data.statusOptions || [];
                this.planOptions = data.planOptions || [];
                this.lastRefresh = new Date();
            } catch (err) {
                this.error = err.response?.data?.message || err.message || 'Error al cargar cliente';
            } finally {
                this.loading = false;
            }
        },

        setTab(tab) {
            if (!TABS.includes(tab)) return;
            this.activeTab = tab;
            this.syncTabToUrl();
            this.adjustPolling();
        },

        startPolling(intervalMs = 60000) {
            this.stopPolling();
            // Solo arrancamos si la tab activa lo justifica
            if (!POLL_TABS.has(this.activeTab)) return;
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

        adjustPolling() {
            // Cuando el admin cambia a una tab que no requiere live data,
            // pausamos. Cuando vuelve a resumen/checkins, reanudamos.
            if (POLL_TABS.has(this.activeTab)) {
                if (!this.polling) this.startPolling();
            } else {
                this.stopPolling();
            }
        },

        // ─── Mutaciones (conectan con PUT /:id ya existente) ───────────
        async updateField(payload) {
            if (!this.clientId) return false;
            const api = useApi();
            try {
                const { data } = await api.put(`/api/v/admin/clients/${this.clientId}`, payload);
                this.actionMessage = (data?.messages || []).join(' · ') || 'Actualizado';
                await this.fetchDetail({ silent: true });
                return true;
            } catch (err) {
                this.actionMessage = err.response?.data?.message || 'Error al actualizar';
                return false;
            }
        },

        async assignCoach({ coachId, planType = 'entrenamiento' }) {
            if (!coachId) return false;
            this.savingCoach = true;
            const ok = await this.updateField({ coach_id: coachId, assign_plan_type: planType });
            this.savingCoach = false;
            return ok;
        },

        async changeStatus(newStatus) {
            if (!newStatus) return false;
            this.savingStatus = true;
            const ok = await this.updateField({ status: newStatus });
            this.savingStatus = false;
            return ok;
        },

        async changePlan(newPlan) {
            if (!newPlan) return false;
            this.savingPlan = true;
            const ok = await this.updateField({ plan: newPlan });
            this.savingPlan = false;
            return ok;
        },

        clearMessage() { this.actionMessage = null; },

        close() {
            this.stopPolling();
            this.clientId = null;
            this.client = null;
            this.plans = [];
            this.coaches = [];
            this.statusOptions = [];
            this.planOptions = [];
            this.error = null;
            this.lastRefresh = null;
            this.activeTab = 'resumen';
            this.actionMessage = null;
        },

        $resetState() {
            this.close();
            this.loading = false;
            this.savingStatus = false;
            this.savingPlan = false;
            this.savingCoach = false;
        },
    },
});
