import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

const DEFAULT_FILTERS = () => ({
    search: '',
    status: 'active', // active | inactive | all
    sortBy: 'name',   // name | clients | last_login
    sortDir: 'asc',
});

/**
 * adminCoachList — Pinia store de la pestaña Coaches v2 (lista + KPIs + podium).
 *
 * Lectura canónica: GET /api/v/admin/coaches/manage (devuelve solo role=coach con
 * client_count real, soporta search + status). Las stats globales vienen de
 * GET /api/v/admin/coaches/stats. El podium del mes viene de top_coaches_month
 * dentro de /api/v/admin/dashboard (mismo payload del Dashboard) — fetch
 * one-shot, sin polling.
 *
 * Polling: NO en lista. La detail tiene su propio store con polling 60s.
 */
export const useAdminCoachListStore = defineStore('adminCoachList', {
    state: () => ({
        coaches: [],
        stats: null,           // { total, coaches, with_profile, clients }
        topMonth: [],          // [{ coach_id, name, tickets_completados, clients }]
        loading: false,
        loadingStats: false,
        error: null,
        lastRefresh: null,
        filters: DEFAULT_FILTERS(),
    }),

    getters: {
        hasActiveFilters: (s) =>
            s.filters.search !== '' || s.filters.status !== 'active',

        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,

        sortedCoaches: (s) => {
            const list = [...(s.coaches || [])];
            const { sortBy, sortDir } = s.filters;
            list.sort((a, b) => {
                let av, bv;
                if (sortBy === 'clients') {
                    av = Number(a.client_count || 0);
                    bv = Number(b.client_count || 0);
                } else if (sortBy === 'last_login') {
                    av = a.last_login_at || '';
                    bv = b.last_login_at || '';
                } else {
                    av = (a.name || '').toLowerCase();
                    bv = (b.name || '').toLowerCase();
                }
                if (av < bv) return sortDir === 'asc' ? -1 : 1;
                if (av > bv) return sortDir === 'asc' ? 1 : -1;
                return 0;
            });
            return list;
        },

        kpis: (s) => {
            const total = Number(s.stats?.coaches ?? 0);
            const withProfile = Number(s.stats?.with_profile ?? 0);
            const clients = Number(s.stats?.clients ?? 0);
            const topTickets = (s.topMonth || [])
                .reduce((sum, c) => sum + Number(c.tickets_completados || 0), 0);
            const topName = s.topMonth?.[0]?.name || null;
            const avgClients = total > 0 ? (clients / total) : 0;

            return {
                totalCoaches: total,
                withProfile,
                totalClients: clients,
                avgClientsPerCoach: avgClients,
                ticketsThisMonth: topTickets,
                topPerformer: topName,
                profileCoverage: total > 0 ? Math.round((withProfile / total) * 100) : 0,
            };
        },

        // Para el modal de suspender: lista de coaches activos (excluyendo el target)
        activeCoachOptions: (s) => (s.coaches || []).filter((c) => c.active),

        // Load balance hint: coaches activos ordenados ascendente por client_count
        loadBalancedOptions: (s) =>
            (s.coaches || [])
                .filter((c) => c.active)
                .sort((a, b) => Number(a.client_count || 0) - Number(b.client_count || 0)),
    },

    actions: {
        async fetchCoaches({ silent = false } = {}) {
            const api = useApi();
            if (!silent && this.coaches.length === 0) {
                this.loading = true;
            }
            this.error = null;

            try {
                const params = new URLSearchParams();
                if (this.filters.search) params.set('search', this.filters.search);
                if (this.filters.status && this.filters.status !== 'all') {
                    params.set('status', this.filters.status);
                }
                const { data } = await api.get(`/api/v/admin/coaches/manage?${params}`);
                this.coaches = data.coaches ?? [];
                this.lastRefresh = new Date();
            } catch (err) {
                this.error = err.response?.data?.message || err.message || 'Error al cargar coaches';
                if (!silent) this.coaches = [];
            } finally {
                this.loading = false;
            }
        },

        async fetchStats() {
            const api = useApi();
            this.loadingStats = true;
            try {
                const { data } = await api.get('/api/v/admin/coaches/stats');
                this.stats = data ?? null;
            } catch {
                this.stats = null;
            } finally {
                this.loadingStats = false;
            }
        },

        async fetchTopMonth() {
            // Reusamos el mismo endpoint de Dashboard. One-shot, sin polling.
            const api = useApi();
            try {
                const { data } = await api.get('/api/v/admin/dashboard');
                this.topMonth = data?.top_coaches_month || [];
            } catch {
                this.topMonth = [];
            }
        },

        async fetchAll() {
            await Promise.all([
                this.fetchCoaches(),
                this.fetchStats(),
                this.fetchTopMonth(),
            ]);
        },

        setSearch(value) {
            this.filters.search = value;
            this.fetchCoaches();
        },

        setStatus(value) {
            this.filters.status = value;
            this.fetchCoaches();
        },

        setSort(field) {
            if (this.filters.sortBy === field) {
                this.filters.sortDir = this.filters.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sortBy = field;
                this.filters.sortDir = field === 'clients' ? 'desc' : 'asc';
            }
        },

        clearFilters() {
            this.filters = DEFAULT_FILTERS();
            this.fetchCoaches();
        },

        // Optimistic patch: cuando un edit/suspend/create cierra, refrescamos
        // sin tocar loading para evitar flash. El polling no aplica acá.
        refreshSilent() {
            return Promise.all([
                this.fetchCoaches({ silent: true }),
                this.fetchStats(),
            ]);
        },

        $resetState() {
            this.coaches = [];
            this.stats = null;
            this.topMonth = [];
            this.loading = false;
            this.loadingStats = false;
            this.error = null;
            this.lastRefresh = null;
            this.filters = DEFAULT_FILTERS();
        },
    },
});
