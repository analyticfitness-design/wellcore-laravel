export const safeStorage = {
    get(key, fallback = null) {
        try { return localStorage.getItem(key); } catch { return fallback; }
    },
    set(key, value) {
        try { localStorage.setItem(key, value); return true; } catch { return false; }
    },
    remove(key) {
        try { localStorage.removeItem(key); } catch {}
    },
    getJSON(key, fallback = null) {
        try {
            const raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : fallback;
        } catch { return fallback; }
    },
    setJSON(key, value) {
        try { localStorage.setItem(key, JSON.stringify(value)); return true; } catch { return false; }
    },
};
