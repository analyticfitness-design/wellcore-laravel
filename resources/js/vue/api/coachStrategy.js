import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v/coach',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
});

api.interceptors.request.use((cfg) => {
    const token = localStorage.getItem('wc_token');
    if (token) {
        cfg.headers.Authorization = `Bearer ${token}`;
    }
    return cfg;
});

export const coachStrategyApi = {
    async getProfile() {
        const { data } = await api.get('/marketing-profile');
        return data.data ?? null;
    },
    async submitProfile(payload) {
        const { data } = await api.put('/marketing-profile', payload);
        return data.data;
    },
    async saveDraft(patch) {
        const { data } = await api.patch('/marketing-profile/draft', patch);
        return data.data;
    },
    async getCurrentDrop() {
        const { data } = await api.get('/strategy/current');
        return data.data ?? null;
    },
    async getHistory(page = 1, perPage = 20) {
        const { data } = await api.get('/strategy/history', { params: { page, per_page: perPage } });
        return data;
    },
    async getDropById(id) {
        const { data } = await api.get(`/strategy/drops/${id}`);
        return data.data;
    },
    async publishPiece(dropId, pieceKey, body = {}) {
        const { data } = await api.post(`/strategy/drops/${dropId}/pieces/${pieceKey}/publish`, body);
        return data.data;
    },
    async skipPiece(dropId, pieceKey) {
        const { data } = await api.post(`/strategy/drops/${dropId}/pieces/${pieceKey}/skip`);
        return data.data;
    },
    async inProgressPiece(dropId, pieceKey) {
        const { data } = await api.post(`/strategy/drops/${dropId}/pieces/${pieceKey}/in-progress`);
        return data.data;
    },
};

export default coachStrategyApi;
