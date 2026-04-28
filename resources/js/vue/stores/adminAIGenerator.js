import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

/**
 * Pinia store for the Admin AI Generator v2.
 *
 * Holds:
 *  - the brief form values (so the user can navigate away and come back),
 *  - the result of the latest stream (history_id + output text),
 *  - the history list (recent 20 generations),
 *  - the templates list (winning prompts).
 *
 * Stream lifecycle is owned by useAIStream() (Fetch + AbortController);
 * this store mirrors the *result* of a stream once it ends and exposes
 * server actions: approve / discard / load history detail / templates.
 */
export const useAdminAIGeneratorStore = defineStore('adminAIGenerator', {
    state: () => ({
        brief: defaultBrief(),
        currentHistoryId: null,
        currentText: '',
        currentBrief: null,
        history: [],
        templates: [],
        loadingHistory: false,
        loadingTemplates: false,
        clientResults: [],
        clientSearchLoading: false,
    }),
    getters: {
        canGenerate(state) {
            const b = state.brief;
            if (!b.plan_type) return false;
            if (!b.duration_weeks || b.duration_weeks < 1) return false;
            return true;
        },
    },
    actions: {
        resetBrief() {
            this.brief = defaultBrief();
        },
        setBrief(partial) {
            this.brief = { ...this.brief, ...partial };
        },
        setCurrent({ historyId, text, brief }) {
            this.currentHistoryId = historyId;
            this.currentText = text || '';
            if (brief) this.currentBrief = brief;
        },
        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const api = useApi();
                const { data } = await api.get('/api/v/admin/ai-generator/history');
                this.history = data.rows || [];
            } catch {
                this.history = [];
            } finally {
                this.loadingHistory = false;
            }
        },
        async loadHistoryDetail(id) {
            const api = useApi();
            const { data } = await api.get(`/api/v/admin/ai-generator/history/${id}`);
            this.setCurrent({
                historyId: data.id,
                text: data.output_text,
                brief: data.brief,
            });
            return data;
        },
        async fetchTemplates() {
            this.loadingTemplates = true;
            try {
                const api = useApi();
                const { data } = await api.get('/api/v/admin/ai-generator/templates');
                this.templates = data.rows || [];
            } catch {
                this.templates = [];
            } finally {
                this.loadingTemplates = false;
            }
        },
        async approve({ templateName, isPublic, saveMode, targetClientId, editedText }) {
            if (!this.currentHistoryId) throw new Error('No hay generación activa para aprobar');
            const api = useApi();
            const { data } = await api.post(
                `/api/v/admin/ai-generator/history/${this.currentHistoryId}/approve`,
                {
                    template_name: templateName,
                    is_public: !!isPublic,
                    save_mode: saveMode || 'template_only',
                    target_client_id: targetClientId || null,
                    edited_text: editedText || null,
                }
            );
            await this.fetchHistory();
            return data;
        },
        async discard() {
            if (!this.currentHistoryId) return;
            const api = useApi();
            await api.post(`/api/v/admin/ai-generator/history/${this.currentHistoryId}/discard`);
            this.currentHistoryId = null;
            this.currentText = '';
            this.currentBrief = null;
            await this.fetchHistory();
        },
        async searchClients(q) {
            this.clientSearchLoading = true;
            try {
                const api = useApi();
                const { data } = await api.get('/api/v/admin/ai-generator/clients/search', { params: { q } });
                this.clientResults = data.rows || [];
            } catch {
                this.clientResults = [];
            } finally {
                this.clientSearchLoading = false;
            }
        },
    },
});

function defaultBrief() {
    return {
        plan_type: '',
        methodology: '',
        duration_weeks: 8,
        frequency: 4,
        experience_level: 'intermedio',
        training_goal: 'hipertrofia',
        injuries: '',
        preferences: '',
        calorie_target: null,
        meals_per_day: 4,
        dietary_restrictions: '',
        habit_focus_areas: [],
        target_client_id: null,
        target_client_name: '',
    };
}
