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
    async uploadAsset(dropId, file, meta = {}, onProgress = null) {
        const form = new FormData();
        form.append('file', file);
        if (meta.role) form.append('role', meta.role);
        if (meta.caption) form.append('caption', meta.caption);
        if (meta.notes) form.append('notes', meta.notes);
        if (Number.isInteger(meta.order)) form.append('order', String(meta.order));
        if (meta.linked_to) {
            const lt = meta.linked_to;
            if (lt.type) form.append('linked_to[type]', lt.type);
            if (lt.day) form.append('linked_to[day]', lt.day);
            if (lt.reel_key) form.append('linked_to[reel_key]', lt.reel_key);
            if (Number.isInteger(lt.slide_index)) form.append('linked_to[slide_index]', String(lt.slide_index));
        }
        const { data } = await api.post(`/marketing/drops/${dropId}/assets`, form, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (e) => {
                if (onProgress && e.total) onProgress(Math.round((e.loaded / e.total) * 100));
            },
        });
        return data.data;
    },
    async deleteAsset(dropId, assetId) {
        const { data } = await api.delete(`/marketing/drops/${dropId}/assets/${assetId}`);
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
