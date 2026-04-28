import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

const COLUMN_KEYS = ['pendiente', 'en_revision', 'completado', 'rechazado'];

function startOfMonthMs() {
    const d = new Date();
    d.setHours(0, 0, 0, 0);
    d.setDate(1);
    return d.getTime();
}

export const useAdminPlanTicketsListStore = defineStore('adminPlanTicketsList', {
    state: () => ({
        rows: [],
        counts: { pendiente: 0, en_revision: 0, completado: 0, rechazado: 0, borrador: 0 },
        meta: { current_page: 1, total: 0 },
        loading: false,
        error: null,
        lastRefresh: null,
        polling: null,

        filters: {
            status: '',
            coach_id: '',
            plan_type: '',
            search: '',
        },

        flashRowId: null,
    }),

    getters: {
        visibleRows(s) {
            // backend ya filtra; el cliente solo refleja rows.
            return s.rows;
        },

        rowsByColumn(s) {
            const buckets = { pendiente: [], en_revision: [], completado: [], rechazado: [] };
            for (const row of s.rows) {
                const status = row.status;
                if (status && buckets[status]) buckets[status].push(row);
            }
            return buckets;
        },

        kpis(s) {
            const monthStart = startOfMonthMs();

            const completedThisMonth = s.rows.filter((r) => {
                if (r.status !== 'completado' || !r.completed_at) return false;
                const ts = Date.parse(r.completed_at);
                return Number.isFinite(ts) && ts >= monthStart;
            }).length;

            const rejectedThisMonth = s.rows.filter((r) => {
                if (r.status !== 'rechazado' || !r.rejected_at) return false;
                const ts = Date.parse(r.rejected_at);
                return Number.isFinite(ts) && ts >= monthStart;
            }).length;

            const pendientes = Number(s.counts.pendiente ?? 0);
            const enRevision = Number(s.counts.en_revision ?? 0);

            return [
                {
                    id: 'pendientes',
                    label: 'PENDIENTES',
                    value: pendientes,
                    sub: pendientes === 1 ? '1 ticket espera revision' : `${pendientes} tickets esperan revision`,
                    variant: pendientes > 5 ? 'urgent' : 'warn',
                    ringPct: Math.min(100, pendientes * 12),
                },
                {
                    id: 'en_revision',
                    label: 'EN REVISION',
                    value: enRevision,
                    sub: 'tickets activos en cola',
                    variant: 'info',
                    ringPct: Math.min(100, enRevision * 16),
                },
                {
                    id: 'aprobados_mes',
                    label: 'APROBADOS ESTE MES',
                    value: completedThisMonth,
                    sub: 'planes activados al cliente',
                    variant: 'healthy',
                    ringPct: Math.min(100, completedThisMonth * 8),
                },
                {
                    id: 'rechazados_mes',
                    label: 'RECHAZADOS ESTE MES',
                    value: rejectedThisMonth,
                    sub: rejectedThisMonth === 1 ? '1 vuelve al coach' : `${rejectedThisMonth} vuelven al coach`,
                    variant: rejectedThisMonth > 0 ? 'urgent' : 'healthy',
                    ringPct: Math.min(100, rejectedThisMonth * 14),
                },
            ];
        },

        hasData: (s) => s.rows.length > 0,
        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,
    },

    actions: {
        async fetchTickets({ silent = false } = {}) {
            const api = useApi();
            if (!silent && this.rows.length === 0) this.loading = true;
            this.error = null;

            const params = {};
            if (this.filters.status) params.status = this.filters.status;
            if (this.filters.coach_id) params.coach_id = this.filters.coach_id;
            if (this.filters.plan_type) params.plan_type = this.filters.plan_type;
            if (this.filters.search) params.search = this.filters.search;

            try {
                const { data } = await api.get('/api/v/admin/plan-tickets', { params });
                this.rows = Array.isArray(data?.tickets) ? data.tickets : [];
                this.counts = { ...this.counts, ...(data?.counts ?? {}) };
                this.meta = { ...this.meta, ...(data?.meta ?? {}) };
                this.lastRefresh = new Date();
            } catch (err) {
                this.error = err.response?.data?.error || err.response?.data?.message || 'No se pudo cargar la cola.';
            } finally {
                this.loading = false;
            }
        },

        startPolling(intervalMs = 30000) {
            this.stopPolling();
            this.polling = setInterval(() => this.fetchTickets({ silent: true }), intervalMs);
        },

        stopPolling() {
            if (this.polling) {
                clearInterval(this.polling);
                this.polling = null;
            }
        },

        setFilter(key, value) {
            if (!Object.prototype.hasOwnProperty.call(this.filters, key)) return;
            this.filters[key] = value;
        },

        clearFilters() {
            this.filters = { status: '', coach_id: '', plan_type: '', search: '' };
        },

        flashRow(id, ms = 1400) {
            this.flashRowId = id;
            setTimeout(() => {
                if (this.flashRowId === id) this.flashRowId = null;
            }, ms);
        },

        applyTicketUpdate(fresh) {
            if (!fresh?.id) return;
            const idx = this.rows.findIndex((r) => r.id === fresh.id);
            if (idx >= 0) {
                const prevStatus = this.rows[idx].status;
                this.rows[idx] = { ...this.rows[idx], ...fresh };
                if (prevStatus !== fresh.status) {
                    if (this.counts[prevStatus] !== undefined) {
                        this.counts[prevStatus] = Math.max(0, Number(this.counts[prevStatus]) - 1);
                    }
                    if (this.counts[fresh.status] !== undefined) {
                        this.counts[fresh.status] = Number(this.counts[fresh.status] ?? 0) + 1;
                    }
                }
            }
            this.flashRow(fresh.id);
        },

        $resetState() {
            this.stopPolling();
            this.rows = [];
            this.counts = { pendiente: 0, en_revision: 0, completado: 0, rechazado: 0, borrador: 0 };
            this.meta = { current_page: 1, total: 0 };
            this.loading = false;
            this.error = null;
            this.lastRefresh = null;
            this.flashRowId = null;
            this.clearFilters();
        },
    },
});

export const PLAN_TICKET_COLUMN_KEYS = COLUMN_KEYS;
