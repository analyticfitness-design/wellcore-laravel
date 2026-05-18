import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

export const REJECTION_CODES = [
    { value: 'info_incompleta',        label: 'Informacion incompleta' },
    { value: 'contexto_insuficiente',  label: 'Contexto insuficiente' },
    { value: 'conflicto_datos',        label: 'Conflicto en los datos' },
    { value: 'fuera_de_scope',         label: 'Fuera de scope' },
    { value: 'necesita_validacion_medica', label: 'Necesita validacion medica' },
    { value: 'otro',                   label: 'Otro' },
];

export const useAdminPlanTicketDetailStore = defineStore('adminPlanTicketDetail', {
    state: () => ({
        ticket: null,
        comments: [],
        attachments: [],

        loading: false,
        loadingComments: false,
        loadingAttachments: false,

        error: null,
        commentsError: null,
        attachmentsError: null,

        // Una unica accion en vuelo a la vez (approve/reject/auto-revision/comment).
        // string p.ej. "approve", "reject", "auto_revision", "comment", o null.
        actionInFlight: null,
        actionError: null,

        commentsPolling: null,
    }),

    getters: {
        isPendiente:    (s) => s.ticket?.status === 'pendiente',
        isEnRevision:   (s) => s.ticket?.status === 'en_revision',
        isCompletado:   (s) => s.ticket?.status === 'completado',
        isRechazado:    (s) => s.ticket?.status === 'rechazado',
        isDecidable:    (s) => s.ticket?.status === 'pendiente' || s.ticket?.status === 'en_revision',
        canTakeAction:  (s) => s.ticket && (s.ticket.status === 'pendiente' || s.ticket.status === 'en_revision'),
    },

    actions: {
        async fetchTicket(id, { silent = false } = {}) {
            const api = useApi();
            if (!silent && this.ticket?.id !== Number(id)) {
                this.ticket = null;
                this.comments = [];
                this.attachments = [];
                this.loading = true;
            } else if (!silent) {
                this.loading = true;
            }
            this.error = null;

            try {
                const { data } = await api.get(`/api/v/admin/plan-tickets/${id}`);
                this.ticket = data?.ticket ?? null;
            } catch (err) {
                this.error = err.response?.data?.error || err.response?.data?.message || 'No se pudo cargar el ticket.';
            } finally {
                this.loading = false;
            }
        },

        async fetchComments(id) {
            const api = useApi();
            this.loadingComments = true;
            this.commentsError = null;
            try {
                const { data } = await api.get(`/api/v/admin/plan-tickets/${id}/comments`);
                this.comments = Array.isArray(data?.comments) ? data.comments : [];
            } catch (err) {
                this.commentsError = err.response?.data?.error || err.response?.data?.message || 'No se pudieron cargar los comentarios.';
            } finally {
                this.loadingComments = false;
            }
        },

        async fetchAttachments(id) {
            const api = useApi();
            this.loadingAttachments = true;
            this.attachmentsError = null;
            try {
                const { data } = await api.get(`/api/v/admin/plan-tickets/${id}/attachments`);
                this.attachments = Array.isArray(data?.attachments) ? data.attachments : [];
            } catch (err) {
                this.attachmentsError = err.response?.data?.error || err.response?.data?.message || 'No se pudieron cargar los adjuntos.';
            } finally {
                this.loadingAttachments = false;
            }
        },

        async loadAll(id) {
            const tasks = [this.fetchTicket(id), this.fetchComments(id), this.fetchAttachments(id)];
            await Promise.allSettled(tasks);
            // Auto-promote pendiente → en_revision una vez tenemos el ticket cargado.
            // Solo intenta una vez por carga; si falla, no rompe el render del detail.
            if (this.ticket?.status === 'pendiente') {
                await this.autoMoveToEnRevision();
            }
        },

        async autoMoveToEnRevision() {
            if (this.actionInFlight) return;
            const id = this.ticket?.id;
            if (!id) return;
            const api = useApi();
            this.actionInFlight = 'auto_revision';
            try {
                const { data } = await api.post(`/api/v/admin/plan-tickets/${id}/status`, {
                    status: 'en_revision',
                });
                if (data?.ticket) this.ticket = data.ticket;
            } catch {
                // silencioso: si falla, el admin sigue viendo el detail en pendiente.
            } finally {
                this.actionInFlight = null;
            }
        },

        async approve({ adminNotes = null, generatedPlanIds = [], forceCompleteWithoutPlans = false } = {}) {
            if (this.actionInFlight) return;
            const id = this.ticket?.id;
            if (!id) throw new Error('No hay ticket cargado.');
            const api = useApi();
            this.actionInFlight = 'approve';
            this.actionError = null;
            try {
                const body = { status: 'completado' };
                if (adminNotes && adminNotes.trim()) body.admin_notas = adminNotes.trim();
                if (Array.isArray(generatedPlanIds) && generatedPlanIds.length) {
                    body.generated_plan_ids = generatedPlanIds.map((n) => Number(n)).filter(Number.isFinite);
                }
                if (forceCompleteWithoutPlans) {
                    body.force_complete_without_plans = true;
                }
                const { data } = await api.post(`/api/v/admin/plan-tickets/${id}/status`, body);
                if (data?.ticket) this.ticket = data.ticket;
                return data?.ticket ?? null;
            } catch (err) {
                this.actionError = err.response?.data?.error || err.response?.data?.message || 'No se pudo aprobar el ticket.';
                throw err;
            } finally {
                this.actionInFlight = null;
            }
        },

        async reject({ rejectionCode, adminNotes = null }) {
            if (this.actionInFlight) return;
            const id = this.ticket?.id;
            if (!id) throw new Error('No hay ticket cargado.');
            if (!rejectionCode) throw new Error('Selecciona un codigo de rechazo.');
            const api = useApi();
            this.actionInFlight = 'reject';
            this.actionError = null;
            try {
                const body = { status: 'rechazado', rejection_code: rejectionCode };
                if (adminNotes && adminNotes.trim()) body.admin_notas = adminNotes.trim();
                const { data } = await api.post(`/api/v/admin/plan-tickets/${id}/status`, body);
                if (data?.ticket) this.ticket = data.ticket;
                return data?.ticket ?? null;
            } catch (err) {
                this.actionError = err.response?.data?.error || err.response?.data?.message || 'No se pudo rechazar el ticket.';
                throw err;
            } finally {
                this.actionInFlight = null;
            }
        },

        async fetchExport(section = 'full') {
            const id = this.ticket?.id;
            if (!id) throw new Error('No hay ticket cargado.');
            const api = useApi();
            const url = section === 'full'
                ? `/api/v/admin/plan-tickets/${id}/export`
                : `/api/v/admin/plan-tickets/${id}/export/${section}`;
            const { data } = await api.get(url);
            return data;
        },

        async addComment(body) {
            if (this.actionInFlight) return;
            const id = this.ticket?.id;
            if (!id) throw new Error('No hay ticket cargado.');
            if (!body || !body.trim()) throw new Error('El comentario esta vacio.');
            const api = useApi();
            this.actionInFlight = 'comment';
            try {
                const { data } = await api.post(`/api/v/admin/plan-tickets/${id}/comments`, {
                    body: body.trim(),
                });
                if (data?.comment) this.comments = [...this.comments, data.comment];
                return data?.comment ?? null;
            } finally {
                this.actionInFlight = null;
            }
        },

        startCommentsPolling(intervalMs = 60000) {
            this.stopCommentsPolling();
            this.commentsPolling = setInterval(() => {
                if (this.ticket?.id) this.fetchComments(this.ticket.id);
            }, intervalMs);
        },

        stopCommentsPolling() {
            if (this.commentsPolling) {
                clearInterval(this.commentsPolling);
                this.commentsPolling = null;
            }
        },

        $resetState() {
            this.stopCommentsPolling();
            this.ticket = null;
            this.comments = [];
            this.attachments = [];
            this.loading = false;
            this.loadingComments = false;
            this.loadingAttachments = false;
            this.error = null;
            this.commentsError = null;
            this.attachmentsError = null;
            this.actionInFlight = null;
            this.actionError = null;
        },
    },
});
