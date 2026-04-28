import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

export const useAdminClientRequestsStore = defineStore('adminClientRequests', {
    state: () => ({
        requests: [],
        counts: { pendiente: 0, aprobado: 0, rechazado: 0 },
        loading: false,
        error: null,
        lastRefresh: null,
        _polling: null,

        statusFilter: 'pendiente',
        actionFilter: '',
        coachFilter:  '',
        search:       '',

        drawerOpen:    false,
        drawerRequest: null,
        drawerLoading: false,

        rejectOpen:   false,
        rejectTarget: null,

        approveOpen:   false,
        approveTarget: null,

        toast: { show: false, type: 'success', message: '' },
    }),

    getters: {
        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh) / 1000) : null,

        uniqueCoaches: (s) => {
            const seen = new Map();
            for (const r of s.requests) {
                if (r.coach_id && !seen.has(r.coach_id)) {
                    seen.set(r.coach_id, r.coach_name || 'Coach');
                }
            }
            return Array.from(seen.entries()).map(([id, name]) => ({ id, name }));
        },

        pendingCount: (s) => s.counts.pendiente ?? 0,
        totalCount:   (s) =>
            (s.counts.pendiente ?? 0) + (s.counts.aprobado ?? 0) + (s.counts.rechazado ?? 0),
    },

    actions: {
        async fetch({ silent = false } = {}) {
            if (!silent) this.loading = true;
            const api = useApi();
            try {
                const params = new URLSearchParams();
                if (this.statusFilter) params.set('status', this.statusFilter);
                if (this.actionFilter) params.set('action', this.actionFilter);
                if (this.coachFilter)  params.set('coach_id', this.coachFilter);
                if (this.search)       params.set('search', this.search);

                const { data } = await api.get(`/api/v/admin/client-requests?${params}`);
                this.requests = data.requests || [];
                if (data.counts) this.counts = data.counts;
                this.lastRefresh = Date.now();
                this.error = null;
            } catch {
                if (!silent) this.error = 'No se pudieron cargar las solicitudes.';
            } finally {
                if (!silent) this.loading = false;
            }
        },

        startPolling(ms = 30000) {
            this.stopPolling();
            this._polling = setInterval(() => this.fetch({ silent: true }), ms);
        },

        stopPolling() {
            if (this._polling) {
                clearInterval(this._polling);
                this._polling = null;
            }
        },

        async openDetail(req) {
            this.drawerOpen    = true;
            this.drawerRequest = req;
            this.drawerLoading = true;
            const api = useApi();
            try {
                const { data } = await api.get(`/api/v/admin/client-requests/${req.id}`);
                this.drawerRequest = data.request || data;
            } catch {
                // conserva datos del listado
            } finally {
                this.drawerLoading = false;
            }
        },

        closeDetail() {
            this.drawerOpen    = false;
            this.drawerRequest = null;
        },

        openReject(req) {
            this.rejectTarget = req;
            this.rejectOpen   = true;
        },
        closeReject() {
            this.rejectOpen   = false;
            this.rejectTarget = null;
        },

        openApprove(req) {
            this.approveTarget = req;
            this.approveOpen   = true;
        },
        closeApprove() {
            this.approveOpen   = false;
            this.approveTarget = null;
        },

        async doApprove(id) {
            const api = useApi();
            await api.post(`/api/v/admin/client-requests/${id}/approve`);
            this.approveOpen = false;
            this.drawerOpen  = false;
            this.showToast('Solicitud aprobada.');
            await this.fetch({ silent: true });
        },

        async doReject(id, notas) {
            const api = useApi();
            await api.post(`/api/v/admin/client-requests/${id}/reject`, {
                admin_notas: notas,
            });
            this.rejectOpen = false;
            this.drawerOpen = false;
            this.showToast('Solicitud rechazada.');
            await this.fetch({ silent: true });
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, type, message };
            setTimeout(() => { this.toast.show = false; }, 4000);
        },
    },
});
