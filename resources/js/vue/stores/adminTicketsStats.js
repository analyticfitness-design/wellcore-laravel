import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

export const useAdminTicketsStatsStore = defineStore('adminTicketsStats', {
    state: () => ({
        data: null,
        loading: false,
        error: null,
        period: 'month',
        lastRefresh: null,
    }),

    getters: {
        kpis:              (s) => s.data?.kpis || { created: 0, approved: 0, rejected: 0, avg_time_hours: null },
        throughput:        (s) => s.data?.throughput || [],
        coachRanking:      (s) => s.data?.coach_ranking || [],
        rejectionReasons:  (s) => s.data?.rejection_reasons || [],
        resolutionBuckets: (s) => s.data?.resolution_buckets || { buckets: [], stats: {}, total: 0 },
        hasData:           (s) => s.data !== null,
        isEmpty:           (s) => {
            if (!s.data) return false;
            const k = s.data.kpis || {};
            return (k.created || 0) === 0 && (k.approved || 0) === 0 && (k.rejected || 0) === 0;
        },
    },

    actions: {
        async fetch({ silent = false } = {}) {
            const api = useApi();
            if (!silent && !this.data) this.loading = true;
            this.error = null;

            try {
                const { data } = await api.get('/api/v/admin/plan-tickets/stats', {
                    params: { period: this.period },
                });
                this.data = data;
                this.lastRefresh = new Date();
            } catch (e) {
                this.error = e?.response?.data?.error || 'Error al cargar estadisticas.';
            } finally {
                this.loading = false;
            }
        },

        async setPeriod(p) {
            this.period = p;
            await this.fetch();
        },
    },
});
