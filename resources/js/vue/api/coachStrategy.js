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
    /**
     * Descarga un asset individual.
     * Usa el endpoint auth-gated /strategy/drops/{dropId}/assets/{assetId} que devuelve
     * Content-Disposition: attachment, lo que en iOS Safari descarga directo en lugar
     * de abrir el preview nativo (que no tenía botón de cerrar y atrapaba al usuario).
     * Recibe (dropId, asset). El método legacy de un solo arg sigue funcionando como
     * fallback (link directo a /storage/...).
     */
    async downloadSingle(dropIdOrAsset, asset = null) {
        // Compat: viejo signature `downloadSingle(asset)` → no había dropId.
        if (asset === null && typeof dropIdOrAsset === 'object') {
            const a = dropIdOrAsset;
            const link = document.createElement('a');
            link.href = a.url;
            link.download = a.filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            return;
        }
        const dropId = dropIdOrAsset;
        try {
            const response = await api.get(
                `/strategy/drops/${dropId}/assets/${asset.id}`,
                { responseType: 'blob' },
            );
            const blob = response.data;
            const blobUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = blobUrl;
            link.download = asset.filename || 'asset';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            setTimeout(() => window.URL.revokeObjectURL(blobUrl), 1500);
        } catch (e) {
            // Fallback: si el endpoint API falla por algún motivo, intentar link directo
            // (seguirá teniendo el problema iOS pero al menos descarga).
            const link = document.createElement('a');
            link.href = asset.url;
            link.download = asset.filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
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
