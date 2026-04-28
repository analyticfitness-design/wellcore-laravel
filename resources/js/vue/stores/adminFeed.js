import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

export const useAdminFeedStore = defineStore('adminFeed', {
    state: () => ({
        events: [],
        cursor: null,
        loading: false,
        error: null,
        paused: false,
        filters: {
            types: ['signup', 'payment', 'checkin', 'message', 'training'],
            search: '',
        },
        polling: null,
        stats: { eventsToday: 0, actionsToday: 0, paymentsToday: 0, activeNow: 0 },
    }),

    getters: {
        filteredEvents(state) {
            let evs = state.events;
            evs = evs.filter(e => state.filters.types.includes(e.type));
            const q = state.filters.search.trim().toLowerCase();
            if (q) {
                evs = evs.filter(e =>
                    (e.description ?? '').toLowerCase().includes(q) ||
                    (e.title ?? '').toLowerCase().includes(q)
                );
            }
            return evs;
        },
    },

    actions: {
        async fetchInitial() {
            const api = useApi();
            this.loading = true;
            this.error = null;
            try {
                const resp = await api.get('/api/v/admin/feed', { params: { date: 'today' } });
                const incoming = resp.data.feed || [];
                this.events = incoming;
                if (resp.data.stats) this.stats = resp.data.stats;
                this.cursor = resp.data.next_cursor || (incoming[0]?.timestamp ?? null);
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al cargar el feed';
            } finally {
                this.loading = false;
            }
        },

        async fetchNewEvents() {
            if (!this.cursor) {
                return this.fetchInitial();
            }
            const api = useApi();
            try {
                const resp = await api.get('/api/v/admin/feed', { params: { since: this.cursor } });
                const incoming = resp.data.feed || [];
                if (incoming.length > 0) {
                    const seen = new Set(this.events.map(e => e.id));
                    const fresh = incoming.filter(e => e.id && !seen.has(e.id));
                    if (fresh.length > 0) {
                        this.events = [...fresh, ...this.events];
                    }
                    this.cursor = resp.data.next_cursor || incoming[0].timestamp;
                }
                if (resp.data.stats) this.stats = resp.data.stats;
            } catch {
                // poll silencioso — no interrumpir el feed
            }
        },

        togglePause() {
            this.paused = !this.paused;
        },

        startPolling(intervalMs = 10_000) {
            if (this.polling) return;
            this.polling = setInterval(() => {
                if (!this.paused) this.fetchNewEvents();
            }, intervalMs);
        },

        stopPolling() {
            if (this.polling) {
                clearInterval(this.polling);
                this.polling = null;
            }
        },
    },
});
