import { defineStore } from 'pinia';
import { useApi } from '@/composables/useApi';

export const useAdminFormsStore = defineStore('adminForms', {
    state: () => ({
        forms: [],
        loading: false,
        error: null,

        selectedForm: null,

        responses: {
            data: [],
            loading: false,
            search: '',
            dateFrom: '',
            dateTo: '',
            page: 1,
            meta: { total: 0, page: 1, per_page: 20, last_page: 1 },
        },

        previewModal: null,
    }),

    getters: {
        filteredForms: (state) => (tag, search) => {
            const q = search.trim().toLowerCase();
            return state.forms.filter((f) => {
                if (tag !== 'all' && f.tag !== tag) return false;
                if (!q) return true;
                return (
                    f.name.toLowerCase().includes(q) ||
                    f.description.toLowerCase().includes(q) ||
                    f.slug.toLowerCase().includes(q)
                );
            });
        },
    },

    actions: {
        async fetchCatalog() {
            this.loading = true;
            this.error = null;
            try {
                const api = useApi();
                const { data } = await api.get('/api/v/admin/forms');
                this.forms = data.forms;
            } catch (e) {
                this.error = e?.response?.data?.message ?? 'Error al cargar los formularios.';
            } finally {
                this.loading = false;
            }
        },

        selectForm(form) {
            this.selectedForm = form;
            this.responses = {
                data: [],
                loading: false,
                search: '',
                dateFrom: '',
                dateTo: '',
                page: 1,
                meta: { total: 0, page: 1, per_page: 20, last_page: 1 },
            };
            if (form?.has_submissions) {
                this.fetchResponses(1);
            }
        },

        async fetchResponses(page = 1) {
            const form = this.selectedForm;
            if (!form?.has_submissions) return;

            this.responses.loading = true;
            this.responses.page = page;

            try {
                const api = useApi();
                const params = new URLSearchParams({
                    page,
                    per_page: 20,
                    search: this.responses.search,
                    date_from: this.responses.dateFrom,
                    date_to: this.responses.dateTo,
                });
                const { data } = await api.get(
                    `/api/v/admin/forms/${form.area}/${form.slug}/responses?${params}`
                );
                this.responses.data = data.data;
                this.responses.meta = data.meta;
            } catch {
                // keep previous data on soft failure
            } finally {
                this.responses.loading = false;
            }
        },

        openPreview(form) {
            this.previewModal = form;
        },

        closePreview() {
            this.previewModal = null;
        },

        exportCsvUrl() {
            const form = this.selectedForm;
            if (!form) return null;
            const params = new URLSearchParams({
                search: this.responses.search,
                date_from: this.responses.dateFrom,
                date_to: this.responses.dateTo,
            });
            return `/api/v/admin/forms/${form.area}/${form.slug}/export?${params}`;
        },
    },
});
