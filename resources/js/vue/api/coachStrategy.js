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
    async downloadSingle(asset) {
        const link = document.createElement('a');
        link.href = asset.url;
        link.download = asset.filename;
        link.target = '_blank';
        link.rel = 'noopener';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    },
    async downloadZip(dropId, fallbackName = 'wellcore-drop.zip') {
        const response = await api.get(`/strategy/drops/${dropId}/assets.zip`, { responseType: 'blob' });
        const cd = response.headers?.['content-disposition'] ?? '';
        const match = /filename="?([^"]+)"?/.exec(cd);
        const name = match?.[1] ?? fallbackName;
        const blobUrl = window.URL.createObjectURL(response.data);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(() => window.URL.revokeObjectURL(blobUrl), 1000);
    },
};

export default coachStrategyApi;
