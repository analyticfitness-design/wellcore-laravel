import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

const DEFAULT_FILTERS = () => ({
    status: 'pendiente',
    search: '',
});

const TABS = ['all', 'pendiente', 'aprobado', 'rechazado', 'expirado'];

function todayISODate() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

export const useAdminProofsStore = defineStore('adminProofs', {
    state: () => ({
        proofs: [],
        pagination: { currentPage: 1, perPage: 20, total: 0, lastPage: 1 },
        loading: false,
        error: null,
        lastRefresh: null,
        polling: null,
        filters: DEFAULT_FILTERS(),
        page: 1,

        // Drawer state
        selectedProof: null,
        fileUrl: null,
        fileLoading: false,

        // Idempotencia: ids con accion en vuelo (approve o reject)
        inFlightActions: new Set(),

        // Detector de nuevos comprobantes durante polling silent
        previousPendingIds: new Set(),
        newSinceLastRefresh: 0,
    }),

    getters: {
        pendingCount: (s) => s.proofs.filter(p => p.status === 'pendiente').length,

        approvedTodayCount: (s) => {
            const today = todayISODate();
            return s.proofs.filter(p => p.status === 'aprobado' && p.reviewedAt?.startsWith(today)).length;
        },

        rejectedTodayCount: (s) => {
            const today = todayISODate();
            return s.proofs.filter(p => p.status === 'rechazado' && p.reviewedAt?.startsWith(today)).length;
        },

        // Tiempo medio de revision en horas, sobre proofs revisados (todos los visibles)
        avgReviewHours: (s) => {
            const reviewed = s.proofs.filter(p => p.reviewedAt && p.submittedAt);
            if (!reviewed.length) return 0;
            const totalMs = reviewed.reduce((acc, p) => {
                return acc + (new Date(p.reviewedAt).getTime() - new Date(p.submittedAt).getTime());
            }, 0);
            return Math.round((totalMs / reviewed.length) / (1000 * 60 * 60) * 10) / 10;
        },

        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,

        kpis(state) {
            return {
                pending: this.pendingCount,
                approvedToday: this.approvedTodayCount,
                rejectedToday: this.rejectedTodayCount,
                avgHours: this.avgReviewHours,
            };
        },

        queryParams: (s) => {
            const p = { page: s.page };
            if (s.filters.status && s.filters.status !== 'all') p.status = s.filters.status;
            if (s.filters.search?.trim()) p.email = s.filters.search.trim();
            return p;
        },

        isActionInFlight: (s) => (id) => s.inFlightActions.has(id),
    },

    actions: {
        async fetchProofs({ silent = false } = {}) {
            const api = useApi();
            if (!silent && !this.proofs.length) {
                this.loading = true;
            }
            this.error = null;

            try {
                const { data } = await api.get('/api/v/admin/payment-proofs', {
                    params: this.queryParams,
                });

                const incoming = data.data ?? [];

                // Detectar nuevos pendientes (solo en silent polling, ignorar al filtrar)
                if (silent && this.filters.status === 'pendiente') {
                    const incomingIds = new Set(incoming.filter(p => p.status === 'pendiente').map(p => p.id));
                    const fresh = [...incomingIds].filter(id => !this.previousPendingIds.has(id));
                    if (fresh.length > 0 && this.previousPendingIds.size > 0) {
                        this.newSinceLastRefresh = fresh.length;
                    }
                    this.previousPendingIds = incomingIds;
                } else {
                    this.previousPendingIds = new Set(
                        incoming.filter(p => p.status === 'pendiente').map(p => p.id)
                    );
                    this.newSinceLastRefresh = 0;
                }

                this.proofs = incoming;
                this.pagination = data.meta ?? this.pagination;
                this.lastRefresh = new Date();
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al cargar comprobantes.';
                if (!silent) this.proofs = [];
            } finally {
                this.loading = false;
            }
        },

        setStatus(status) {
            if (!TABS.includes(status)) return;
            this.filters.status = status;
            this.page = 1;
            this.newSinceLastRefresh = 0;
            this.fetchProofs();
        },

        setSearch(value) {
            this.filters.search = value;
            this.page = 1;
            this.fetchProofs();
        },

        clearFilters() {
            this.filters = DEFAULT_FILTERS();
            this.page = 1;
            this.fetchProofs();
        },

        goToPage(p) {
            const last = this.pagination?.lastPage ?? 1;
            if (p < 1 || p > last) return;
            this.page = p;
            this.fetchProofs();
        },

        acknowledgeNew() {
            this.newSinceLastRefresh = 0;
        },

        // ── Drawer ──────────────────────────────────────────────────────────
        async openDrawer(proof) {
            this.selectedProof = { ...proof };
            this.fileUrl = null;
            await this.loadFileUrl(proof.id);
        },

        closeDrawer() {
            this.selectedProof = null;
            this.fileUrl = null;
            this.fileLoading = false;
        },

        async loadFileUrl(id) {
            const api = useApi();
            this.fileLoading = true;
            try {
                const { data } = await api.get(`/api/v/admin/payment-proofs/${id}/file`);
                this.fileUrl = data.url ?? null;
            } catch {
                this.fileUrl = null;
            } finally {
                this.fileLoading = false;
            }
        },

        // ── Approve / Reject (idempotentes) ────────────────────────────────
        async approveProof(id) {
            if (this.inFlightActions.has(id)) {
                return { ok: false, reason: 'in_flight' };
            }
            const api = useApi();
            this.inFlightActions.add(id);
            try {
                await api.post(`/api/v/admin/payment-proofs/${id}/approve`);
                // Actualizar el proof in-place para feedback inmediato
                const idx = this.proofs.findIndex(p => p.id === id);
                if (idx >= 0) {
                    this.proofs[idx] = {
                        ...this.proofs[idx],
                        status: 'aprobado',
                        reviewedAt: new Date().toISOString(),
                    };
                }
                if (this.selectedProof?.id === id) {
                    this.selectedProof = { ...this.selectedProof, status: 'aprobado', reviewedAt: new Date().toISOString() };
                }
                // Refrescar lista en background para sincronizar reviewer name + paginas
                this.fetchProofs({ silent: true });
                return { ok: true };
            } catch (err) {
                const status = err.response?.status;
                if (status === 404) {
                    // Otro admin ya actuo o el proof cambio de estado entre fetch y POST
                    await this.fetchProofs({ silent: true });
                    return { ok: false, reason: 'already_reviewed', message: 'Otro administrador ya revisó este comprobante.' };
                }
                return {
                    ok: false,
                    reason: 'error',
                    message: err.response?.data?.message || 'No se pudo aprobar el comprobante.',
                };
            } finally {
                this.inFlightActions.delete(id);
            }
        },

        async rejectProof(id, reviewNote) {
            if (this.inFlightActions.has(id)) {
                return { ok: false, reason: 'in_flight' };
            }
            const trimmed = (reviewNote || '').trim();
            if (trimmed.length < 10) {
                return { ok: false, reason: 'note_too_short', message: 'La razón debe tener al menos 10 caracteres.' };
            }
            const api = useApi();
            this.inFlightActions.add(id);
            try {
                await api.post(`/api/v/admin/payment-proofs/${id}/reject`, { review_note: trimmed });
                const idx = this.proofs.findIndex(p => p.id === id);
                if (idx >= 0) {
                    this.proofs[idx] = {
                        ...this.proofs[idx],
                        status: 'rechazado',
                        reviewedAt: new Date().toISOString(),
                        reviewNote: trimmed,
                    };
                }
                if (this.selectedProof?.id === id) {
                    this.selectedProof = {
                        ...this.selectedProof,
                        status: 'rechazado',
                        reviewedAt: new Date().toISOString(),
                        reviewNote: trimmed,
                    };
                }
                this.fetchProofs({ silent: true });
                return { ok: true };
            } catch (err) {
                const status = err.response?.status;
                if (status === 404) {
                    await this.fetchProofs({ silent: true });
                    return { ok: false, reason: 'already_reviewed', message: 'Otro administrador ya revisó este comprobante.' };
                }
                if (status === 422) {
                    return { ok: false, reason: 'validation', message: err.response?.data?.message || 'La razón no es válida.' };
                }
                return {
                    ok: false,
                    reason: 'error',
                    message: err.response?.data?.message || 'No se pudo rechazar el comprobante.',
                };
            } finally {
                this.inFlightActions.delete(id);
            }
        },

        // ── Polling ────────────────────────────────────────────────────────
        startPolling(intervalMs = 30000) {
            this.stopPolling();
            this.polling = setInterval(() => {
                if (document.visibilityState === 'visible') {
                    this.fetchProofs({ silent: true });
                }
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
            this.proofs = [];
            this.pagination = { currentPage: 1, perPage: 20, total: 0, lastPage: 1 };
            this.loading = false;
            this.error = null;
            this.lastRefresh = null;
            this.filters = DEFAULT_FILTERS();
            this.page = 1;
            this.selectedProof = null;
            this.fileUrl = null;
            this.fileLoading = false;
            this.inFlightActions = new Set();
            this.previousPendingIds = new Set();
            this.newSinceLastRefresh = 0;
        },
    },
});
