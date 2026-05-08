import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';
import { useToast } from '../composables/useToast';

const COLUMN_STATUSES = {
    sin_contactar: ['pendiente', 'nuevo', 'pending_contact'],
    contactado:    ['contactado', 'contacted'],
    plan_enviado:  ['convertido', 'payment_sent'],
    activo:        ['pagado', 'activo', 'paid'],
};

const CANONICAL_STATUS = {
    sin_contactar: 'pendiente',
    contactado:    'contactado',
    plan_enviado:  'convertido',
    activo:        'pagado',
};

export const useAdminInscriptionsStore = defineStore('adminInscriptions', {
    state: () => ({
        all: [],
        loading: false,
        error: null,
        lastRefresh: null,
        polling: null,
        prevPendingCount: 0,
        newLeadsCount: 0,
        filters: { plan: '', search: '' },
        contactTarget: null,
        detailTarget: null,
    }),

    getters: {
        kanban: (s) => {
            let items = s.all.filter(i => !['rechazado', 'rejected'].includes(i.status));
            if (s.filters.plan) {
                items = items.filter(i => i.plan_raw === s.filters.plan);
            }
            if (s.filters.search) {
                const q = s.filters.search.toLowerCase();
                items = items.filter(i =>
                    (i.nombre || '').toLowerCase().includes(q) ||
                    (i.email || '').toLowerCase().includes(q)
                );
            }
            const cols = {
                sin_contactar: [],
                contactado:    [],
                plan_enviado:  [],
                activo:        [],
            };
            for (const item of items) {
                for (const [col, statuses] of Object.entries(COLUMN_STATUSES)) {
                    if (statuses.includes(item.status)) {
                        cols[col].push(item);
                        break;
                    }
                }
            }
            return cols;
        },

        totalLeads: (s) => s.all.filter(i => !['rechazado', 'rejected'].includes(i.status)).length,

        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,
    },

    actions: {
        async fetchAll({ silent = false } = {}) {
            const api = useApi();
            if (!silent) this.loading = true;
            this.error = null;
            try {
                const { data } = await api.get('/api/v/admin/inscriptions', {
                    params: { per_page: 100 },
                });
                const incoming = data.inscriptions ?? [];
                const PENDING_STATUSES = ['pendiente', 'nuevo', 'pending_contact'];

                if (silent) {
                    const newPending = incoming.filter(i =>
                        PENDING_STATUSES.includes(i.status)
                    ).length;
                    this.newLeadsCount = Math.max(0, newPending - this.prevPendingCount);
                    this.prevPendingCount = newPending;
                } else {
                    this.prevPendingCount = incoming.filter(i =>
                        PENDING_STATUSES.includes(i.status)
                    ).length;
                    this.newLeadsCount = 0;
                }

                this.all = incoming;
                this.lastRefresh = new Date();
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al cargar inscripciones';
            } finally {
                this.loading = false;
            }
        },

        async moveCard(id, toColumn) {
            const api = useApi();
            const toast = useToast();
            const status = CANONICAL_STATUS[toColumn];
            if (!status) return;
            const idx = this.all.findIndex(i => i.id === id);
            const prevStatus = idx !== -1 ? this.all[idx].status : null;
            if (idx !== -1) this.all[idx] = { ...this.all[idx], status };
            try {
                await api.put(`/api/v/admin/inscriptions/${id}`, { status });
            } catch {
                // Rollback to original status
                if (idx !== -1 && prevStatus !== null) {
                    this.all[idx] = { ...this.all[idx], status: prevStatus };
                }
                toast.show('Error al mover la inscripción.', 'error');
            }
        },

        async markRejected(id) {
            const api = useApi();
            const toast = useToast();
            const idx = this.all.findIndex(i => i.id === id);
            const prevStatus = idx !== -1 ? this.all[idx].status : null;
            if (idx !== -1) this.all[idx] = { ...this.all[idx], status: 'rechazado' };
            try {
                await api.put(`/api/v/admin/inscriptions/${id}`, { status: 'rechazado' });
            } catch {
                // Rollback to original status
                if (idx !== -1 && prevStatus !== null) {
                    this.all[idx] = { ...this.all[idx], status: prevStatus };
                }
                toast.show('Error al rechazar la inscripción.', 'error');
            }
        },

        setFilters(partial) {
            this.filters = { ...this.filters, ...partial };
        },

        clearFilters() {
            this.filters = { plan: '', search: '' };
        },

        openContact(inscription) {
            this.contactTarget = inscription;
        },

        closeContact() {
            this.contactTarget = null;
        },

        openDetail(inscription) {
            this.detailTarget = inscription;
        },

        closeDetail() {
            this.detailTarget = null;
        },

        dismissNewLeads() {
            this.newLeadsCount = 0;
        },

        startPolling(intervalMs = 30000) {
            this.stopPolling();
            this.polling = setInterval(() => {
                this.fetchAll({ silent: true });
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
            this.$patch({
                all: [],
                loading: false,
                error: null,
                lastRefresh: null,
                prevPendingCount: 0,
                newLeadsCount: 0,
                filters: { plan: '', search: '' },
                contactTarget: null,
                detailTarget: null,
            });
        },
    },
});
