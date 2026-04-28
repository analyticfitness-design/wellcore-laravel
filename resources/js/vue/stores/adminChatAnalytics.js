import { defineStore } from 'pinia';
import { useApi } from '../composables/useApi';

export const useAdminChatAnalyticsStore = defineStore('adminChatAnalytics', {
    state: () => ({
        data: null,
        loading: false,
        error: null,
        lastRefresh: null,
        period: 'month',
    }),

    getters: {
        kpis: (s) => s.data?.kpis ?? null,
        volumeChart: (s) => s.data?.volume_chart ?? [],
        responseTimeBuckets: (s) => s.data?.response_time_buckets ?? [],
        responseTimeStats: (s) => s.data?.response_time_stats ?? { mean: 0, median: 0, p90: 0 },
        heatmap: (s) => s.data?.heatmap ?? [],
        topCoaches: (s) => s.data?.top_coaches ?? [],
        hasData: (s) => s.data?.has_data ?? false,
        maxHeatmapCount: (s) => {
            const cells = s.data?.heatmap ?? [];
            return cells.reduce((max, c) => Math.max(max, c.count), 1);
        },
        maxBucketCount: (s) => {
            const buckets = s.data?.response_time_buckets ?? [];
            return buckets.reduce((max, b) => Math.max(max, b.count), 1);
        },
        secondsSinceRefresh: (s) =>
            s.lastRefresh ? Math.round((Date.now() - s.lastRefresh.getTime()) / 1000) : null,
    },

    actions: {
        async fetchAnalytics({ silent = false } = {}) {
            const api = useApi();
            if (!silent) this.loading = true;
            this.error = null;
            try {
                const { data } = await api.get('/api/v/admin/chat-analytics', {
                    params: { period: this.period },
                });
                this.data = data;
                this.lastRefresh = new Date();
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al cargar analytics de chat';
            } finally {
                this.loading = false;
            }
        },

        setPeriod(period) {
            this.period = period;
            this.fetchAnalytics();
        },
    },
});
