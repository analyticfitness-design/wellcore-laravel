import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

export const useAdminReferralsStore = defineStore('adminReferrals', {
    state: () => ({
        data: null,
        meta: null,
        kpis: null,
        topReferidores: [],
        loading: false,
        error: null,
        lastRefresh: null,
        polling: null,
        payoutLoading: false,
        filters: {
            status: 'all',
            periodo: 'month',
            search: '',
            page: 1,
        },
    }),

    getters: {
        referrals:      (s) => s.data ?? [],
        hasData:        (s) => s.data !== null,
        totalPages:     (s) => s.meta?.last_page ?? 1,
        totalCount:     (s) => s.meta?.total ?? 0,
        totalReferidos: (s) => s.kpis?.total_referidos ?? 0,
        qualified:      (s) => s.kpis?.qualified ?? 0,
        paid:           (s) => s.kpis?.paid ?? 0,
        roi:            (s) => s.kpis?.roi ?? '0x',
    },

    actions: {
        async fetchReferrals({ silent = false } = {}) {
            const api = useApi();
            if (!silent && !this.data) this.loading = true;
            this.error = null;

            try {
                const { data } = await api.get('/api/v/admin/referrals', {
                    params: {
                        status:  this.filters.status  !== 'all' ? this.filters.status : undefined,
                        periodo: this.filters.periodo,
                        search:  this.filters.search || undefined,
                        page:    this.filters.page,
                    },
                });
                this.data            = data.data;
                this.meta            = data.meta;
                this.kpis            = data.kpis;
                this.topReferidores  = data.top_referidores ?? [];
                this.lastRefresh     = new Date();
            } catch (e) {
                if (!silent) this.error = e?.response?.data?.error ?? 'Error al cargar referidos.';
            } finally {
                this.loading = false;
            }
        },

        setFilter(key, value) {
            this.filters[key] = value;
            this.filters.page = 1;
            this.fetchReferrals();
        },

        setPage(page) {
            this.filters.page = page;
            this.fetchReferrals();
        },

        async markPaid(id, method, reference) {
            const api = useApi();
            this.payoutLoading = true;
            try {
                await api.post(`/api/v/admin/referrals/${id}/mark-paid`, { method, reference });
                await this.fetchReferrals({ silent: true });
                return true;
            } catch (e) {
                return false;
            } finally {
                this.payoutLoading = false;
            }
        },

        async expire(id) {
            const api = useApi();
            try {
                await api.post(`/api/v/admin/referrals/${id}/expire`);
                await this.fetchReferrals({ silent: true });
                return true;
            } catch {
                return false;
            }
        },

        startPolling(intervalMs = 60_000) {
            this.stopPolling();
            this.polling = setInterval(() => this.fetchReferrals({ silent: true }), intervalMs);
        },

        stopPolling() {
            if (this.polling) { clearInterval(this.polling); this.polling = null; }
        },
    },
});
