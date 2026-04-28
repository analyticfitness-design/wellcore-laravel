import { ref, onBeforeUnmount } from 'vue';

/**
 * useToolStream — Fetch ReadableStream SSE consumer for /api/v/admin/tools/:id/run.
 *
 * Chunk format: { type: 'output', text: '...' } | { type: 'done', status: 'success|failed', duration_ms: N }
 *
 * Uses Fetch (not EventSource) to inject Bearer token — EventSource does not
 * support custom headers and we auth via Authorization header in the SPA.
 */
export function useToolStream() {
    const isStreaming = ref(false);
    const lines       = ref([]);
    const status      = ref(null);      // null | 'success' | 'failed'
    const durationMs  = ref(null);
    const error       = ref(null);
    let   controller  = null;

    function abort() {
        if (controller) {
            try { controller.abort(); } catch { /* noop */ }
            controller = null;
        }
        isStreaming.value = false;
    }

    function reset() {
        lines.value      = [];
        status.value     = null;
        durationMs.value = null;
        error.value      = null;
    }

    onBeforeUnmount(() => abort());

    /**
     * Start streaming a tool run.
     * @param {string} toolId - tool catalog id
     * @param {object} params - optional params for the tool
     * @param {{ onLine, onDone, onError }} callbacks
     */
    async function start(toolId, params = {}, { onLine, onDone, onError } = {}) {
        if (isStreaming.value) return;

        reset();
        controller        = new AbortController();
        isStreaming.value = true;

        const token = localStorage.getItem('wc_token') || '';

        let response;
        try {
            response = await fetch(`/api/v/admin/tools/${toolId}/run`, {
                method:  'POST',
                headers: {
                    'Accept':        'text/event-stream',
                    'Content-Type':  'application/json',
                    'Authorization': token ? `Bearer ${token}` : '',
                },
                body:   JSON.stringify(params),
                signal: controller.signal,
            });
        } catch (e) {
            if (e?.name === 'AbortError') {
                isStreaming.value = false;
                return;
            }
            error.value = 'Error de red al iniciar la herramienta';
            isStreaming.value = false;
            onError?.(error.value);
            return;
        }

        if (! response.ok || ! response.body) {
            let msg = `HTTP ${response.status}`;
            try {
                const j = await response.json();
                msg = j.message || msg;
            } catch { /* noop */ }
            error.value = msg;
            isStreaming.value = false;
            onError?.(error.value);
            return;
        }

        const reader  = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let   buffer  = '';

        try {
            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                buffer += decoder.decode(value, { stream: true });

                let nlnl;
                while ((nlnl = buffer.indexOf('\n\n')) !== -1) {
                    const rawEvent = buffer.slice(0, nlnl);
                    buffer         = buffer.slice(nlnl + 2);

                    const dataLines = rawEvent.split('\n')
                        .filter(l => l.startsWith('data:'))
                        .map(l => l.slice(5).trimStart());
                    if (! dataLines.length) continue;

                    let payload;
                    try { payload = JSON.parse(dataLines.join('\n')); } catch { continue; }

                    if (payload.type === 'output') {
                        lines.value.push(payload.text);
                        onLine?.(payload.text);
                    } else if (payload.type === 'done') {
                        status.value    = payload.status;
                        durationMs.value = payload.duration_ms;
                        onDone?.({ status: payload.status, durationMs: payload.duration_ms });
                    } else if (payload.error) {
                        error.value = payload.error;
                        onError?.(error.value);
                    }
                }
            }
        } catch (e) {
            if (e?.name !== 'AbortError') {
                error.value = 'Conexion interrumpida';
                onError?.(error.value);
            }
        } finally {
            controller        = null;
            isStreaming.value = false;
        }
    }

    return { isStreaming, lines, status, durationMs, error, start, abort, reset };
}
