import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

const COLUMN_MAP = {
    in_review: 'in_review',
    approved: 'approved',
    ready: 'ready',
    in_progress: 'published',
    completed: 'published',
    archived: 'archived',
};

const HIDDEN_BY_DEFAULT = new Set(['pending', 'generating']);

function isoWeekStartUtc() {
    const d = new Date();
    const day = d.getUTCDay() || 7;
    d.setUTCHours(0, 0, 0, 0);
    if (day !== 1) d.setUTCDate(d.getUTCDate() - (day - 1));
    return d.getTime();
}

export const useAdminMarketingQueueStore = defineStore('adminMarketingQueue', {
    state: () => ({
        rows: [],
        meta: {
            current_page: 1,
            total: 0,
            pending_review_count: 0,
            coaches_without_drop_this_week: 0,
        },
        loading: false,
        error: null,
        lastRefresh: null,
        polling: null,

        filters: {
            coach_id: '',
            iso_year: '',
            iso_week: '',
            include_drafts: false,
            search: '',
        },

        drawerOpen: false,
        drawerLoading: false,
        drawerError: null,
        drawerDrop: null,

        actionInFlight: null,
        flashRowId: null,
    }),

    getters: {
        visibleRows: (s) => {
            const search = (s.filters.search || '').trim().toLowerCase();
            return s.rows.filter((r) => {
                if (!s.filters.include_drafts && HIDDEN_BY_DEFAULT.has(r.status)) return false;
                if (search) {
                    const name = (r.coach?.name || '').toLowerCase();
                    if (!name.includes(search)) return false;
                }
                return true;
            });
        },

        columnFor: () => (status) => COLUMN_MAP[status] ?? null,

        rowsByColumn() {
            const buckets = { in_review: [], approved: [], ready: [], published: [], archived: [] };
            for (const row of this.visibleRows) {
                const col = COLUMN_MAP[row.status];
                if (col && buckets[col]) buckets[col].push(row);
            }
            return buckets;
        },

        kpis(s) {
            const inReview = Number(s.meta.pending_review_count ?? 0);
            const sinCobertura = Number(s.meta.coaches_without_drop_this_week ?? 0);
            const totalCola = Number(s.meta.total ?? s.rows.length);

            const weekStart = isoWeekStartUtc();
            let aprobadosSemana = 0;
            for (const row of s.rows) {
                if (!['approved', 'ready', 'in_progress', 'completed'].includes(row.status)) continue;
                if (!row.last_action_at) continue;
                const ts = Date.parse(row.last_action_at);
                if (Number.isFinite(ts) && ts >= weekStart) aprobadosSemana++;
            }

            return [
                {
                    id: 'in_review',
                    label: 'EN REVISION',
                    value: inReview,
                    sub: inReview === 1 ? '1 drop espera decision' : `${inReview} drops esperan decision`,
                    variant: inReview > 5 ? 'urgent' : 'warn',
                    ringPct: Math.min(100, inReview * 12),
                },
                {
                    id: 'aprobados',
                    label: 'APROBADOS ESTA SEMANA',
                    value: aprobadosSemana,
                    sub: 'movimiento productivo',
                    variant: 'healthy',
                    ringPct: Math.min(100, aprobadosSemana * 10),
                },
                {
                    id: 'sin_cobertura',
                    label: 'COACHES SIN DROP',
                    value: sinCobertura,
                    sub: 'esta semana ISO',
                    variant: sinCobertura > 0 ? 'warn' : 'healthy',
                    ringPct: Math.min(100, sinCobertura * 12),
                },
                {
                    id: 'total',
                    label: 'TOTAL EN COLA',
                    value: totalCola,
                    sub: 'todos los estados',
                    variant: 'info',
                    ringPct: Math.min(100, totalCola * 2),
                },
            ];
        },

        hasData: (s) => s.rows.length > 0,
        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,
    },

    actions: {
        async fetchQueue({ silent = false } = {}) {
            const api = useApi();
            if (!silent && this.rows.length === 0) this.loading = true;
            this.error = null;

            const params = {};
            if (this.filters.coach_id) params.coach_id = this.filters.coach_id;
            if (this.filters.iso_year) params.iso_year = this.filters.iso_year;
            if (this.filters.iso_week) params.iso_week = this.filters.iso_week;

            try {
                const { data } = await api.get('/api/v/admin/marketing/drops', { params });
                this.rows = Array.isArray(data?.data) ? data.data : [];
                this.meta = { ...this.meta, ...(data?.meta ?? {}) };
                this.lastRefresh = new Date();
            } catch (err) {
                this.error = err.response?.data?.message || 'No se pudo cargar la cola.';
            } finally {
                this.loading = false;
            }
        },

        startPolling(intervalMs = 60000) {
            this.stopPolling();
            this.polling = setInterval(() => this.fetchQueue({ silent: true }), intervalMs);
        },

        stopPolling() {
            if (this.polling) {
                clearInterval(this.polling);
                this.polling = null;
            }
        },

        async openDrawer(id) {
            this.drawerOpen = true;
            this.drawerLoading = true;
            this.drawerError = null;
            this.drawerDrop = null;
            const api = useApi();
            try {
                const { data } = await api.get(`/api/v/admin/marketing/drops/${id}`);
                this.drawerDrop = data?.data ?? data ?? null;
            } catch (err) {
                this.drawerError = err.response?.data?.message || 'No se pudo cargar el drop.';
            } finally {
                this.drawerLoading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.drawerDrop = null;
            this.drawerError = null;
        },

        async approve(id, notes = null) {
            const api = useApi();
            this.actionInFlight = `approve:${id}`;
            const idx = this.rows.findIndex((r) => r.id === id);
            const previousStatus = idx >= 0 ? this.rows[idx].status : null;
            if (idx >= 0) this.rows[idx] = { ...this.rows[idx], status: 'ready' };

            try {
                const body = notes ? { notes } : {};
                const { data } = await api.post(`/api/v/admin/marketing/drops/${id}/approve`, body);
                const fresh = data?.data ?? data;
                if (idx >= 0 && fresh?.status) {
                    this.rows[idx] = { ...this.rows[idx], status: fresh.status };
                }
                if (this.drawerDrop?.id === id) this.drawerDrop = fresh;
                this.flashRowId = id;
                setTimeout(() => { if (this.flashRowId === id) this.flashRowId = null; }, 1400);
                this.meta.pending_review_count = Math.max(0, Number(this.meta.pending_review_count ?? 1) - 1);
                return fresh;
            } catch (err) {
                if (idx >= 0 && previousStatus) {
                    this.rows[idx] = { ...this.rows[idx], status: previousStatus };
                }
                throw err;
            } finally {
                this.actionInFlight = null;
            }
        },

        async requestRegenerate(id, reason) {
            const api = useApi();
            this.actionInFlight = `reject:${id}`;
            const idx = this.rows.findIndex((r) => r.id === id);
            const previousStatus = idx >= 0 ? this.rows[idx].status : null;
            if (idx >= 0) this.rows[idx] = { ...this.rows[idx], status: 'pending' };

            try {
                const { data } = await api.post(
                    `/api/v/admin/marketing/drops/${id}/request-regenerate`,
                    reason ? { reason } : {},
                );
                const fresh = data?.data ?? data;
                if (idx >= 0 && fresh?.status) {
                    this.rows[idx] = { ...this.rows[idx], status: fresh.status };
                }
                if (this.drawerDrop?.id === id) this.drawerDrop = fresh;
                this.meta.pending_review_count = Math.max(0, Number(this.meta.pending_review_count ?? 1) - 1);
                return fresh;
            } catch (err) {
                if (idx >= 0 && previousStatus) {
                    this.rows[idx] = { ...this.rows[idx], status: previousStatus };
                }
                throw err;
            } finally {
                this.actionInFlight = null;
            }
        },

        setFilter(key, value) {
            if (!Object.prototype.hasOwnProperty.call(this.filters, key)) return;
            this.filters[key] = value;
        },

        clearFilters() {
            this.filters = {
                coach_id: '',
                iso_year: '',
                iso_week: '',
                include_drafts: false,
                search: '',
            };
        },

        $resetState() {
            this.stopPolling();
            this.rows = [];
            this.meta = {
                current_page: 1,
                total: 0,
                pending_review_count: 0,
                coaches_without_drop_this_week: 0,
            };
            this.loading = false;
            this.error = null;
            this.lastRefresh = null;
            this.drawerOpen = false;
            this.drawerDrop = null;
            this.drawerError = null;
            this.actionInFlight = null;
            this.flashRowId = null;
            this.clearFilters();
        },
    },
});
