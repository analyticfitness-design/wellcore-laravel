import { ref, onBeforeUnmount } from 'vue';

/**
 * useAIStream — Fetch ReadableStream-based SSE consumer for the
 * Admin AI Generator endpoint.
 *
 * Why Fetch and not EventSource?
 *   EventSource cannot send custom Authorization headers (it does not
 *   accept arbitrary headers and uses cookies only). The wellcore SPA
 *   authenticates with a Bearer token from localStorage, so we MUST use
 *   Fetch + ReadableStream to inject the header on every request.
 *
 * Cleanup contract:
 *   The composable registers an onBeforeUnmount hook that aborts the
 *   in-flight stream automatically when the parent component is destroyed.
 *   Without this hook, navigating away mid-stream would keep the LLM call
 *   running on the server until its hard timeout — wasting tokens.
 */
export function useAIStream() {
    const isStreaming = ref(false);
    const accumulated = ref('');
    const error = ref(null);
    const lastHistoryId = ref(null);
    const lastDurationMs = ref(null);
    let controller = null;

    function abort() {
        if (controller) {
            try { controller.abort(); } catch { /* noop */ }
            controller = null;
        }
        isStreaming.value = false;
    }

    onBeforeUnmount(() => abort());

    /**
     * Start a new generation. Resolves when the stream finishes (success,
     * error, or abort). Does NOT throw on backend stream errors — those
     * surface via `error.value`.
     */
    async function start(brief, { onChunk, onDone, onError } = {}) {
        if (isStreaming.value) {
            throw new Error('A generation is already in progress');
        }

        accumulated.value = '';
        error.value = null;
        lastHistoryId.value = null;
        lastDurationMs.value = null;

        controller = new AbortController();
        isStreaming.value = true;

        const token = localStorage.getItem('wc_token') || '';

        let response;
        try {
            response = await fetch('/api/v/admin/ai-generator/stream', {
                method: 'POST',
                headers: {
                    'Accept': 'text/event-stream',
                    'Content-Type': 'application/json',
                    'Authorization': token ? `Bearer ${token}` : '',
                },
                body: JSON.stringify(brief),
                signal: controller.signal,
            });
        } catch (e) {
            if (e?.name === 'AbortError') {
                isStreaming.value = false;
                return;
            }
            error.value = 'Error de red al iniciar la generación';
            isStreaming.value = false;
            onError?.(error.value);
            return;
        }

        if (!response.ok || !response.body) {
            error.value = `El sistema rechazó la solicitud (HTTP ${response.status})`;
            isStreaming.value = false;
            onError?.(error.value);
            return;
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let buffer = '';

        try {
            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                buffer += decoder.decode(value, { stream: true });

                let nlnl;
                while ((nlnl = buffer.indexOf('\n\n')) !== -1) {
                    const rawEvent = buffer.slice(0, nlnl);
                    buffer = buffer.slice(nlnl + 2);

                    const dataLines = rawEvent.split('\n')
                        .filter(line => line.startsWith('data:'))
                        .map(line => line.slice(5).trimStart());
                    if (dataLines.length === 0) continue;

                    let payload;
                    try {
                        payload = JSON.parse(dataLines.join('\n'));
                    } catch {
                        continue;
                    }

                    if (payload.error) {
                        error.value = payload.error === 'aborted'
                            ? null
                            : payload.error;
                        if (payload.error !== 'aborted') {
                            onError?.(error.value);
                        }
                        continue;
                    }
                    if (payload.chunk) {
                        accumulated.value += payload.chunk;
                        onChunk?.(payload.chunk, accumulated.value);
                        continue;
                    }
                    if (payload.done) {
                        lastHistoryId.value = payload.history_id ?? null;
                        lastDurationMs.value = payload.duration_ms ?? null;
                        onDone?.({
                            historyId: payload.history_id,
                            durationMs: payload.duration_ms,
                            chars: payload.chars,
                            text: accumulated.value,
                        });
                    }
                }
            }
        } catch (e) {
            if (e?.name !== 'AbortError') {
                error.value = 'Conexión interrumpida';
                onError?.(error.value);
            }
        } finally {
            controller = null;
            isStreaming.value = false;
        }
    }

    return {
        isStreaming,
        accumulated,
        error,
        lastHistoryId,
        lastDurationMs,
        start,
        abort,
    };
}
