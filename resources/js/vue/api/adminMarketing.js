import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v/admin',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
});

api.interceptors.request.use((cfg) => {
    const token = localStorage.getItem('wc_token');
    if (token) cfg.headers.Authorization = `Bearer ${token}`;
    return cfg;
});

export const adminMarketingApi = {
    async getQueue(filters = {}) {
        const params = {};
        if (filters.status) params.status = filters.status;
        if (filters.coach_id) params.coach_id = filters.coach_id;
        if (filters.iso_year) params.iso_year = filters.iso_year;
        if (filters.iso_week) params.iso_week = filters.iso_week;
        if (filters.page) params.page = filters.page;
        const { data } = await api.get('/marketing/drops', { params });
        return data;
    },
    async getDrop(id) {
        const { data } = await api.get(`/marketing/drops/${id}`);
        return data.data;
    },
    async updateDropContent(id, content) {
        const { data } = await api.put(`/marketing/drops/${id}/content`, { content });
        return data.data;
    },
    async approveDrop(id) {
        const { data } = await api.post(`/marketing/drops/${id}/approve`);
        return data.data;
    },
    async requestRegenerate(id, reason) {
        const body = reason ? { reason } : {};
        const { data } = await api.post(`/marketing/drops/${id}/request-regenerate`, body);
        return data.data;
    },
    async getCoachProfile(coachId) {
        const { data } = await api.get(`/coaches/${coachId}/marketing-profile`);
        return data.data ?? null;
    },
    async updateCoachProfile(coachId, payload) {
        const { data } = await api.put(`/coaches/${coachId}/marketing-profile`, payload);
        return data.data;
    },
};

export default adminMarketingApi;
