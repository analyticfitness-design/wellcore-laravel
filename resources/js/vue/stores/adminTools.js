import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

export const useAdminToolsStore = defineStore('adminTools', {
    state: () => ({
        tools:          [],
        history:        [],
        isSuperadmin:   false,
        loadingCatalog: false,
        loadingHistory: false,
        error:          null,
        activeCategory: null,
        searchQuery:    '',
    }),

    getters: {
        categories:    (s) => [...new Set(s.tools.map(t => t.category))],
        filteredTools: (s) => {
            let list = s.tools;
            if (s.activeCategory) list = list.filter(t => t.category === s.activeCategory);
            if (s.searchQuery) {
                const q = s.searchQuery.toLowerCase();
                list = list.filter(t =>
                    t.title.toLowerCase().includes(q) ||
                    t.description.toLowerCase().includes(q)
                );
            }
            return list;
        },
        hasFilters: (s) => !! (s.activeCategory || s.searchQuery),
    },

    actions: {
        async fetchCatalog() {
            if (this.tools.length) return;
            this.loadingCatalog = true;
            this.error = null;
            try {
                const api = useApi();
                const res = await api.get('/api/v/admin/tools');
                this.tools        = res.data.tools || [];
                this.isSuperadmin = !! res.data.isSuperadmin;
            } catch (e) {
                this.error = e.response?.data?.message || 'Error cargando herramientas';
            } finally {
                this.loadingCatalog = false;
            }
        },

        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const api = useApi();
                const res = await api.get('/api/v/admin/tools/history');
                this.history = res.data.history || [];
            } catch { /* non-critical */ } finally {
                this.loadingHistory = false;
            }
        },

        setCategory(cat) {
            this.activeCategory = this.activeCategory === cat ? null : cat;
        },

        clearFilters() {
            this.activeCategory = null;
            this.searchQuery    = '';
        },

        prependHistory(entry) {
            this.history.unshift(entry);
            if (this.history.length > 50) this.history.pop();
        },
    },
});
