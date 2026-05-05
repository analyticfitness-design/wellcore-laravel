import { ref } from 'vue';
import { useApi } from './useApi';

/**
 * useMentions — autocomplete + extract para Cross-Role Communication Layer.
 * Singleton search cache 10min. Skip API si query <3 chars.
 */

const searchCache = new Map();
const SEARCH_TTL_MS = 600_000;

const MENTION_REGEX = /@(cliente_(\d+)|coach|admin|wellcore)\b/giu;

export function useMentions() {
    const api = useApi();
    const loading = ref(false);

    async function search(query, { scope = null } = {}) {
        const trimmed = (query || '').trim().toLowerCase();
        if (trimmed.length < 3) return [];

        const key = `${scope || 'all'}:${trimmed}`;
        if (searchCache.has(key)) {
            const c = searchCache.get(key);
            if (Date.now() - c.timestamp < SEARCH_TTL_MS) return c.results;
        }

        loading.value = true;
        try {
            const res = await api.get('/api/v/community/mention-search', {
                params: { q: trimmed, scope: scope || undefined },
            });
            const results = res.data?.results || [];
            searchCache.set(key, { results, timestamp: Date.now() });
            return results;
        } catch (err) {
            // eslint-disable-next-line no-console
            console.error('[useMentions] search failed', err);
            return [];
        } finally {
            loading.value = false;
        }
    }

    function extract(content) {
        if (!content) return [];
        const tokens = [];
        const matches = content.matchAll(MENTION_REGEX);
        for (const m of matches) {
            if (m[2]) {
                tokens.push({ type: 'client', id: parseInt(m[2], 10), raw: m[0] });
            } else {
                const t = m[1].toLowerCase();
                tokens.push({
                    type: t === 'wellcore' ? 'admin' : t,
                    id: null,
                    raw: m[0],
                });
            }
        }
        return tokens;
    }

    return { loading, search, extract };
}

export function resetMentions() {
    searchCache.clear();
}
