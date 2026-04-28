import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

/**
 * adminDashboard — Pinia store del Dashboard admin v2.
 *
 * Centraliza:
 *  - data         : payload completo de GET /api/v/admin/dashboard
 *  - loading      : true mientras hace el primer fetch (sin data previa)
 *  - error        : string con mensaje si el fetch falla
 *  - lastRefresh  : Date del ultimo fetch exitoso
 *  - polling      : reference al setInterval activo
 *
 * Polling cada 30s arrancado con startPolling() desde el componente que monta
 * el dashboard, y detenido con stopPolling() en onBeforeUnmount.
 *
 * Patrón importante: el polling no setea loading=true (esos refrescos en
 * background no deben parpadear el UI). Solo el primer fetch o un retry
 * explicito muestra el skeleton.
 */
export const useAdminDashboardStore = defineStore('adminDashboard', {
    state: () => ({
        data: null,
        loading: false,
        error: null,
        lastRefresh: null,
        polling: null,
    }),

    getters: {
        // Atajos comunes consumidos por componentes
        production:        (s) => s.data?.production || {},
        financial:         (s) => s.data?.financial || {},
        operational:       (s) => s.data?.operational || {},
        alerts:            (s) => s.data?.alerts || [],
        recentPayments:    (s) => s.data?.recentPayments || [],
        recentInscriptions:(s) => s.data?.recentInscriptions || [],
        topCoaches:        (s) => s.data?.top_coaches_month || [],
        clientBreakdown:   (s) => s.data?.clientBreakdown || {},
        revenueChartData:  (s) => s.data?.revenueChartData || [],
        planDistribution:  (s) => s.data?.planDistributionData || [],
        greeting:          (s) => s.data?.greeting || 'Panel de Administracion',
        criticalAlerts:    (s) => (s.data?.alerts || []).length,
        pendingTickets:    (s) => Number(s.data?.production?.plan_tickets_pendientes || 0),
        reviewTickets:     (s) => Number(s.data?.production?.plan_tickets_en_revision || 0),
        hasData:           (s) => s.data !== null,
        secondsSinceRefresh: (s) => s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,
    },

    actions: {
        /**
         * Fetch del dashboard. silent=true evita el flash del skeleton — usado
         * por el polling automatico. silent=false (default) marca loading=true
         * si NO hay data previa, mostrando skeleton solo en el first paint.
         */
        async fetchDashboard({ silent = false } = {}) {
            const api = useApi();

            // Loading skeleton solo en el first paint o retry explicito
            if (!silent && !this.data) {
                this.loading = true;
            }
            this.error = null;

            try {
                const { data } = await api.get('/api/v/admin/dashboard');
                this.data = data;
                this.lastRefresh = new Date();
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al cargar el dashboard';
            } finally {
                this.loading = false;
            }
        },

        /**
         * Arranca el polling cada N ms (default 30s). Idempotente: si ya hay
         * un interval corriendo, lo limpia primero.
         */
        startPolling(intervalMs = 30000) {
            this.stopPolling();
            this.polling = setInterval(() => {
                this.fetchDashboard({ silent: true });
            }, intervalMs);
        },

        stopPolling() {
            if (this.polling) {
                clearInterval(this.polling);
                this.polling = null;
            }
        },

        /**
         * Reset usado en tests + onLogout.
         */
        $resetState() {
            this.stopPolling();
            this.data = null;
            this.loading = false;
            this.error = null;
            this.lastRefresh = null;
        },
    },
});
