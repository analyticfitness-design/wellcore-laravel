import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

const PER_PAGE = 25;

const DEFAULT_FILTERS = () => ({
    search: '',
    status: '',
    plan: '',
    sortBy: 'created_at',
    sortDir: 'desc',
    page: 1,
});

/**
 * adminClientList — Pinia store de la pestaña Clientes v2 (lista paginada).
 *
 * Lectura canónica: GET /api/v/admin/clients
 *   Params: search, status, plan, sort_by, sort_dir, page, per_page
 *   Response: { clients[], pagination: { current_page, last_page, per_page, total }, isSuperadmin }
 *
 * Polling: NO. La lista se refresca on-demand al cambiar filtros, paginar, o
 * después de una acción (delete, toggle status). El detail tiene su propio
 * store con polling 60s.
 *
 * URL sync: persistimos search/status/plan/page en la query string del browser
 * via replaceState. Permite compartir links filtrados ("envíame los suspendidos
 * del plan Método") sin contaminar history.
 */
export const useAdminClientListStore = defineStore('adminClientList', {
    state: () => ({
        clients: [],
        pagination: { currentPage: 1, lastPage: 1, perPage: PER_PAGE, total: 0 },
        isSuperadmin: false,
        loading: false,
        error: null,
        lastRefresh: null,
        filters: DEFAULT_FILTERS(),
        // Token para "último gana" en search rápido (cancela responses antiguas)
        requestToken: 0,
    }),

    getters: {
        hasActiveFilters: (s) =>
            s.filters.search !== '' || s.filters.status !== '' || s.filters.plan !== '',

        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,

        rangeFrom: (s) => {
            if (s.pagination.total === 0) return 0;
            return (s.pagination.currentPage - 1) * s.pagination.perPage + 1;
        },

        rangeTo: (s) => Math.min(s.pagination.currentPage * s.pagination.perPage, s.pagination.total),
    },

    actions: {
        /**
         * Hidrata filtros desde URL al montar la página. Sin esto un reload
         * pierde el contexto que el admin ya seleccionó.
         */
        hydrateFromUrl() {
            if (typeof window === 'undefined') return;
            const q = new URLSearchParams(window.location.search);
            const next = { ...DEFAULT_FILTERS() };
            if (q.has('search')) next.search = q.get('search') || '';
            if (q.has('status')) next.status = q.get('status') || '';
            if (q.has('plan')) next.plan = q.get('plan') || '';
            if (q.has('page')) next.page = Math.max(1, parseInt(q.get('page'), 10) || 1);
            if (q.has('sort_by')) next.sortBy = q.get('sort_by') || 'created_at';
            if (q.has('sort_dir')) next.sortDir = q.get('sort_dir') === 'asc' ? 'asc' : 'desc';
            this.filters = next;
        },

        /**
         * Sincroniza filtros con la URL. replaceState (no push) para no
         * romper el back button — un usuario que clickea "Activos" no debería
         * generar 5 entradas en el history del browser.
         */
        syncToUrl() {
            if (typeof window === 'undefined') return;
            const q = new URLSearchParams();
            const f = this.filters;
            if (f.search) q.set('search', f.search);
            if (f.status) q.set('status', f.status);
            if (f.plan) q.set('plan', f.plan);
            if (f.page > 1) q.set('page', String(f.page));
            if (f.sortBy !== 'created_at') q.set('sort_by', f.sortBy);
            if (f.sortDir !== 'desc') q.set('sort_dir', f.sortDir);
            const qs = q.toString();
            const url = window.location.pathname + (qs ? `?${qs}` : '');
            window.history.replaceState({}, '', url);
        },

        async fetchClients({ silent = false } = {}) {
            const api = useApi();
            const myToken = ++this.requestToken;
            if (!silent && this.clients.length === 0) {
                this.loading = true;
            }
            this.error = null;

            try {
                const { data } = await api.get('/api/v/admin/clients', {
                    params: {
                        search: this.filters.search || undefined,
                        status: this.filters.status || undefined,
                        plan: this.filters.plan || undefined,
                        sort_by: this.filters.sortBy,
                        sort_dir: this.filters.sortDir,
                        page: this.filters.page,
                        per_page: PER_PAGE,
                    },
                });

                // Si llegó tarde (otro fetch ya disparó), descartar
                if (myToken !== this.requestToken) return;

                this.clients = data.clients || [];
                this.isSuperadmin = !!data.isSuperadmin;
                const p = data.pagination || {};
                this.pagination = {
                    currentPage: p.current_page ?? 1,
                    lastPage: p.last_page ?? 1,
                    perPage: p.per_page ?? PER_PAGE,
                    total: p.total ?? 0,
                };
                this.lastRefresh = new Date();
            } catch (err) {
                if (myToken !== this.requestToken) return;
                this.error = err.response?.data?.message || err.message || 'Error al cargar clientes';
                if (!silent) this.clients = [];
            } finally {
                if (myToken === this.requestToken) {
                    this.loading = false;
                }
            }
        },

        setSearch(value) {
            this.filters.search = value || '';
            this.filters.page = 1;
            this.syncToUrl();
            this.fetchClients();
        },

        setStatus(value) {
            this.filters.status = value || '';
            this.filters.page = 1;
            this.syncToUrl();
            this.fetchClients();
        },

        setPlan(value) {
            this.filters.plan = value || '';
            this.filters.page = 1;
            this.syncToUrl();
            this.fetchClients();
        },

        setSort(field) {
            if (this.filters.sortBy === field) {
                this.filters.sortDir = this.filters.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sortBy = field;
                // numéricos / fechas → desc por default; textos → asc
                this.filters.sortDir = ['created_at', 'fecha_inicio'].includes(field) ? 'desc' : 'asc';
            }
            this.filters.page = 1;
            this.syncToUrl();
            this.fetchClients();
        },

        setPage(page) {
            const p = Math.max(1, Math.min(page, this.pagination.lastPage || 1));
            if (p === this.filters.page) return;
            this.filters.page = p;
            this.syncToUrl();
            this.fetchClients();
        },

        clearFilters() {
            this.filters = DEFAULT_FILTERS();
            this.syncToUrl();
            this.fetchClients();
        },

        refreshSilent() {
            return this.fetchClients({ silent: true });
        },

        $resetState() {
            this.clients = [];
            this.pagination = { currentPage: 1, lastPage: 1, perPage: PER_PAGE, total: 0 };
            this.isSuperadmin = false;
            this.loading = false;
            this.error = null;
            this.lastRefresh = null;
            this.filters = DEFAULT_FILTERS();
            this.requestToken = 0;
        },
    },
});
