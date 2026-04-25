import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

function getAuthHeaders() {
    const token = localStorage.getItem('wc_token');
    return token ? { Authorization: `Bearer ${token}` } : {};
}

export const useInvitationsStore = defineStore('invitations', () => {
    const invitations = ref([]);
    const stats = ref({
        sent: 0,
        opened: 0,
        linkClicked: 0,
        paid: 0,
        expired: 0,
        cancelled: 0,
        failed: 0,
    });
    const pagination = ref(null);
    const loading = ref(false);
    const filters = ref({
        status: '',
        plan: '',
        page: 1,
        per_page: 20,
    });

    async function fetchInvitations() {
        loading.value = true;
        try {
            const params = {};
            if (filters.value.status) params.status = filters.value.status;
            if (filters.value.plan) params.plan = filters.value.plan;
            params.page = filters.value.page;
            params.per_page = filters.value.per_page;

            const response = await axios.get('/api/v/coach/invitations', {
                headers: getAuthHeaders(),
                params,
            });

            invitations.value = response.data.data ?? response.data.invitations ?? [];
            pagination.value = response.data.meta ?? response.data.pagination ?? null;

            if (response.data.stats) {
                const s = response.data.stats;
                stats.value = {
                    sent: s.sent ?? 0,
                    opened: s.opened ?? 0,
                    linkClicked: s.link_clicked ?? s.linkClicked ?? 0,
                    paid: s.paid ?? 0,
                    expired: s.expired ?? 0,
                    cancelled: s.cancelled ?? 0,
                    failed: s.failed ?? 0,
                };
            }
        } catch (e) {
            invitations.value = [];
        } finally {
            loading.value = false;
        }
    }

    async function createInvitation(data) {
        const response = await axios.post('/api/v/coach/invitations', data, {
            headers: getAuthHeaders(),
        });
        const created = response.data.invitation ?? response.data;
        invitations.value = [created, ...invitations.value];
        return created;
    }

    async function previewInvitation(data) {
        const response = await axios.post('/api/v/coach/invitations/preview', data, {
            headers: getAuthHeaders(),
        });
        return response.data.html ?? '';
    }

    async function resendInvitation(id) {
        const response = await axios.post(`/api/v/coach/invitations/${id}/resend`, {}, {
            headers: getAuthHeaders(),
        });
        const updated = response.data.invitation ?? response.data;
        const idx = invitations.value.findIndex((inv) => inv.id === id);
        if (idx !== -1) {
            invitations.value[idx] = { ...invitations.value[idx], ...updated };
        }
        return updated;
    }

    async function cancelInvitation(id) {
        const response = await axios.delete(`/api/v/coach/invitations/${id}`, {
            headers: getAuthHeaders(),
        });
        const updated = response.data.invitation ?? null;
        const idx = invitations.value.findIndex((inv) => inv.id === id);
        if (idx !== -1) {
            if (updated) {
                invitations.value[idx] = { ...invitations.value[idx], ...updated };
            } else {
                invitations.value[idx] = { ...invitations.value[idx], status: 'cancelled' };
            }
        }
        return updated;
    }

    return {
        invitations,
        stats,
        pagination,
        loading,
        filters,
        fetchInvitations,
        createInvitation,
        previewInvitation,
        resendInvitation,
        cancelInvitation,
    };
});
