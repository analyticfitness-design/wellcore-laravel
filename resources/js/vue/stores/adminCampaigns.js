import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

export const useAdminCampaignsStore = defineStore('adminCampaigns', {
    state: () => ({
        data: null,
        kpis: null,
        meta: null,
        loading: false,
        error: null,
        lastRefresh: null,
        polling: null,
        filters: {
            platform: '',
            status: '',
            search: '',
            sort_by: 'created_at',
            sort_dir: 'desc',
            page: 1,
        },
    }),

    getters: {
        campaigns:       (s) => s.data ?? [],
        hasData:         (s) => s.data !== null,
        totalPages:      (s) => s.meta?.last_page ?? 1,
        totalCount:      (s) => s.meta?.total ?? 0,
        spendMes:        (s) => s.kpis?.spend_mes ?? 0,
        conversionesMes: (s) => s.kpis?.conversiones_mes ?? 0,
        roasPromedio:    (s) => s.kpis?.roas_promedio ?? 0,
        cplPromedio:     (s) => s.kpis?.cpl_promedio ?? 0,
    },

    actions: {
        async fetchCampaigns({ silent = false } = {}) {
            const api = useApi();

            if (!silent && !this.data) {
                this.loading = true;
            }
            this.error = null;

            try {
                const params = {
                    platform: this.filters.platform || undefined,
                    status:   this.filters.status   || undefined,
                    search:   this.filters.search   || undefined,
                    sort_by:  this.filters.sort_by,
                    sort_dir: this.filters.sort_dir,
                    page:     this.filters.page,
                };

                const { data } = await api.get('/api/v/admin/campaigns', { params });

                this.data        = data.data;
                this.kpis        = data.kpis;
                this.meta        = data.meta;
                this.lastRefresh = new Date();
            } catch (e) {
                if (!silent) {
                    this.error = e?.response?.data?.message ?? 'Error al cargar campañas.';
                }
            } finally {
                this.loading = false;
            }
        },

        setFilter(key, value) {
            this.filters[key] = value;
            this.filters.page = 1;
            this.fetchCampaigns();
        },

        setSort(key) {
            if (this.filters.sort_by === key) {
                this.filters.sort_dir = this.filters.sort_dir === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sort_by  = key;
                this.filters.sort_dir = 'desc';
            }
            this.filters.page = 1;
            this.fetchCampaigns();
        },

        setPage(page) {
            this.filters.page = page;
            this.fetchCampaigns();
        },

        startPolling(intervalMs = 300_000) {
            this.stopPolling();
            this.polling = setInterval(() => this.fetchCampaigns({ silent: true }), intervalMs);
        },

        stopPolling() {
            if (this.polling) {
                clearInterval(this.polling);
                this.polling = null;
            }
        },
    },
});
