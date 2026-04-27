import { defineStore } from 'pinia';
import { ref } from 'vue';
import { adminMarketingApi } from '../api/adminMarketing';

export const useAdminMarketingStore = defineStore('adminMarketing', () => {
    const queue = ref([]);
    const meta = ref({ current_page: 1, total: 0, pending_review_count: 0, coaches_without_drop_this_week: 0 });
    const isLoadingQueue = ref(false);

    const selectedDrop = ref(null);
    const isLoadingDrop = ref(false);

    const filters = ref({ status: '', coach_id: '', iso_year: '', iso_week: '', page: 1 });

    async function fetchQueue(overrideFilters = null) {
        if (overrideFilters) filters.value = { ...filters.value, ...overrideFilters };
        isLoadingQueue.value = true;
        try {
            const res = await adminMarketingApi.getQueue(filters.value);
            queue.value = res.data ?? [];
            meta.value = res.meta ?? meta.value;
        } finally {
            isLoadingQueue.value = false;
        }
    }

    async function fetchDrop(id) {
        isLoadingDrop.value = true;
        try {
            selectedDrop.value = await adminMarketingApi.getDrop(id);
        } finally {
            isLoadingDrop.value = false;
        }
    }

    async function updateDropContent(id, content) {
        const updated = await adminMarketingApi.updateDropContent(id, content);
        if (selectedDrop.value?.id === id) selectedDrop.value = updated;
        return updated;
    }

    async function approveDrop(id) {
        const updated = await adminMarketingApi.approveDrop(id);
        if (selectedDrop.value?.id === id) selectedDrop.value = updated;
        const idx = queue.value.findIndex((r) => r.id === id);
        if (idx >= 0) queue.value[idx].status = updated.status;
        return updated;
    }

    async function requestRegenerate(id, reason) {
        const updated = await adminMarketingApi.requestRegenerate(id, reason);
        if (selectedDrop.value?.id === id) selectedDrop.value = updated;
        return updated;
    }

    function reset() {
        queue.value = [];
        selectedDrop.value = null;
    }

    return {
        queue, meta, isLoadingQueue,
        selectedDrop, isLoadingDrop,
        filters,
        fetchQueue, fetchDrop, updateDropContent, approveDrop, requestRegenerate, reset,
    };
});
