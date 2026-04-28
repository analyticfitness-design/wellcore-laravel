import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

const DEFAULT_FILTERS = () => ({
    status: '',
    method: '',
    search: '',
    dateFrom: '',
    dateTo: '',
    sortBy: 'created_at',
    sortDir: 'desc',
});

export const useAdminPaymentsStore = defineStore('adminPayments', {
    state: () => ({
        stats: null,
        payments: [],
        pagination: null,
        loading: false,
        error: null,
        lastRefresh: null,
        polling: null,
        filters: DEFAULT_FILTERS(),
        page: 1,
        perPage: 25,
        selectedPayment: null,
        refundTarget: null,
    }),

    getters: {
        hasActiveFilters: (s) =>
            s.filters.status !== '' ||
            s.filters.method !== '' ||
            s.filters.search !== '' ||
            s.filters.dateFrom !== '' ||
            s.filters.dateTo !== '',

        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,

        kpis: (s) => {
            const totalRevenue = s.stats?.totalRevenue ?? '0';
            const monthRevenue = s.stats?.monthRevenue ?? '0';
            const pendingPayments = Number(s.stats?.pendingPayments ?? 0);
            const avgPerClient = s.stats?.avgPerClient ?? '0';

            const declinedCount = (s.payments || []).filter(p =>
                p.status === 'declined' || p.status === 'error'
            ).length;
            const totalVisible = s.payments?.length || 1;
            const refundRate = totalVisible > 0
                ? ((declinedCount / totalVisible) * 100)
                : 0;

            return {
                monthRevenue,
                totalRevenue,
                pendingPayments,
                avgPerClient,
                declinedCount,
                refundRate,
            };
        },

        queryParams: (s) => {
            const p = { page: s.page, per_page: s.perPage };
            if (s.filters.status) p.status = s.filters.status;
            if (s.filters.method) p.method = s.filters.method;
            if (s.filters.search) p.search = s.filters.search;
            if (s.filters.dateFrom) p.date_from = s.filters.dateFrom;
            if (s.filters.dateTo) p.date_to = s.filters.dateTo;
            return p;
        },
    },

    actions: {
        async fetchPayments({ silent = false } = {}) {
            const api = useApi();
            if (!silent && !this.payments.length) {
                this.loading = true;
            }
            this.error = null;

            try {
                const { data } = await api.get('/api/v/admin/payments', {
                    params: this.queryParams,
                });
                this.stats = data.stats ?? null;
                this.payments = data.payments ?? [];
                this.pagination = data.pagination ?? null;
                this.lastRefresh = new Date();
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al cargar pagos';
                if (!silent) this.payments = [];
            } finally {
                this.loading = false;
            }
        },

        setFilters(partial) {
            this.filters = { ...this.filters, ...partial };
            this.page = 1;
            this.fetchPayments();
        },

        setSearch(value) {
            this.filters.search = value;
            this.page = 1;
            this.fetchPayments();
        },

        clearFilters() {
            this.filters = DEFAULT_FILTERS();
            this.page = 1;
            this.fetchPayments();
        },

        setSort(sortBy) {
            if (this.filters.sortBy === sortBy) {
                this.filters.sortDir = this.filters.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sortBy = sortBy;
                this.filters.sortDir = 'desc';
            }
        },

        goToPage(p) {
            if (!this.pagination) return;
            const last = this.pagination.last_page || 1;
            if (p < 1 || p > last) return;
            this.page = p;
            this.fetchPayments();
        },

        hydrateFromQuery(query) {
            const next = DEFAULT_FILTERS();
            if (query.status) next.status = String(query.status);
            if (query.method) next.method = String(query.method);
            if (query.search) next.search = String(query.search);
            if (query.date_from) next.dateFrom = String(query.date_from);
            if (query.date_to) next.dateTo = String(query.date_to);
            this.filters = next;
            this.page = Number(query.page) > 0 ? Number(query.page) : 1;
        },

        openDetail(payment) {
            this.selectedPayment = payment;
        },

        closeDetail() {
            this.selectedPayment = null;
        },

        openRefund(payment) {
            this.refundTarget = payment;
        },

        closeRefund() {
            this.refundTarget = null;
        },

        startPolling(intervalMs = 60000) {
            this.stopPolling();
            this.polling = setInterval(() => {
                this.fetchPayments({ silent: true });
            }, intervalMs);
        },

        stopPolling() {
            if (this.polling) {
                clearInterval(this.polling);
                this.polling = null;
            }
        },

        $resetState() {
            this.stopPolling();
            this.stats = null;
            this.payments = [];
            this.pagination = null;
            this.loading = false;
            this.error = null;
            this.lastRefresh = null;
            this.filters = DEFAULT_FILTERS();
            this.page = 1;
            this.selectedPayment = null;
            this.refundTarget = null;
        },
    },
});
