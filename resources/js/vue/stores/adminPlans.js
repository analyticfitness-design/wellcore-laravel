import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

const DEFAULT_FILTERS = () => ({
    search: '',
    type: 'all',
    coach: 'all',
    public: 'all',
    ai: 'all',
});

export const useAdminPlansStore = defineStore('adminPlans', {
    state: () => ({
        plans: [],
        stats: { total: 0, entrenamiento: 0, nutricion: 0, habitos: 0, suplementacion: 0, ciclo: 0, ai_generated: 0 },
        coaches: [],
        pagination: { current_page: 1, last_page: 1, total: 0 },
        loading: false,
        error: null,
        filters: DEFAULT_FILTERS(),
        sort: { by: 'created_at', dir: 'desc' },
        page: 1,
    }),

    getters: {
        hasActiveFilters: (s) =>
            s.filters.search !== '' ||
            s.filters.type !== 'all' ||
            s.filters.coach !== 'all' ||
            s.filters.public !== 'all' ||
            s.filters.ai !== 'all',
    },

    actions: {
        async fetchPlans({ silent = false } = {}) {
            const api = useApi();
            if (!silent) this.loading = true;
            this.error = null;
            try {
                const params = new URLSearchParams({
                    search:   this.filters.search,
                    type:     this.filters.type,
                    coach:    this.filters.coach,
                    public:   this.filters.public,
                    ai:       this.filters.ai,
                    sort_by:  this.sort.by,
                    sort_dir: this.sort.dir,
                    page:     this.page,
                });
                const res = await api.get(`/api/v/admin/plans?${params}`);
                this.plans      = res.data.plans      ?? [];
                this.stats      = res.data.stats      ?? this.stats;
                this.coaches    = res.data.coaches    ?? [];
                this.pagination = res.data.pagination ?? this.pagination;
            } catch (e) {
                this.error = e.response?.data?.message ?? 'Error al cargar los templates de planes.';
            } finally {
                if (!silent) this.loading = false;
            }
        },

        setFilter(key, value) {
            this.filters[key] = value;
            this.page = 1;
            this.fetchPlans();
        },

        setSort(col) {
            if (this.sort.by === col) {
                this.sort.dir = this.sort.dir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort.by  = col;
                this.sort.dir = 'desc';
            }
            this.page = 1;
            this.fetchPlans();
        },

        clearFilters() {
            this.filters = DEFAULT_FILTERS();
            this.page    = 1;
            this.fetchPlans();
        },

        goToPage(p) {
            if (p < 1 || p > this.pagination.last_page) return;
            this.page = p;
            this.fetchPlans();
        },
    },
});
