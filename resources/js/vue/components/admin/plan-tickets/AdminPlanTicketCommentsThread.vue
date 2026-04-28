<script setup>
import { ref, computed, nextTick } from 'vue';

const props = defineProps({
    comments: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    error: { type: String, default: '' },
    submitting: { type: Boolean, default: false },
});

const emit = defineEmits(['post']);

const draft = ref('');
const inputRef = ref(null);
const submitError = ref('');

const sortedComments = computed(() => {
    return [...props.comments].sort((a, b) => {
        const ta = Date.parse(a.created_at) || 0;
        const tb = Date.parse(b.created_at) || 0;
        return ta - tb;
    });
});

function authorVariant(comment) {
    return comment.author_type === 'admin' ? 'admin' : 'coach';
}

function relativeTime(iso) {
    if (!iso) return '';
    try {
        const d = new Date(iso);
        const diff = Date.now() - d.getTime();
        const min = Math.round(diff / 60000);
        if (min < 1) return 'hace instantes';
        if (min < 60) return `hace ${min} min`;
        const h = Math.round(min / 60);
        if (h < 24) return `hace ${h} h`;
        const dd = Math.round(h / 24);
        if (dd < 7) return `hace ${dd} d`;
        return d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
    } catch {
        return '';
    }
}

async function onSubmit() {
    submitError.value = '';
    const body = draft.value.trim();
    if (!body) return;
    if (body.length > 5000) {
        submitError.value = 'El comentario es demasiado largo (max 5000 caracteres).';
        return;
    }
    try {
        emit('post', body);
        // El parent maneja la accion; cuando submitting cambia a false sin error,
        // limpiamos el draft. Eso lo gestionamos via watch en el parent:
        draft.value = '';
        await nextTick();
        inputRef.value?.focus();
    } catch (err) {
        submitError.value = err?.message ?? 'No se pudo enviar el comentario.';
    }
}
</script>

<template>
    <div class="thread">
        <header class="thread-head">
            <span class="thread-eyebrow">CONVERSACION ADMIN ↔ COACH</span>
            <span class="thread-count">{{ sortedComments.length }}</span>
        </header>

        <div v-if="loading && comments.length === 0" class="thread-loading" aria-live="polite">
            <div class="thread-skeleton thread-skeleton--admin"></div>
            <div class="thread-skeleton thread-skeleton--coach"></div>
            <div class="thread-skeleton thread-skeleton--admin"></div>
        </div>

        <p v-if="error" class="thread-error" role="alert">{{ error }}</p>

        <div v-if="sortedComments.length === 0 && !loading" class="thread-empty">
            <div class="thread-empty-num" aria-hidden="true">—</div>
            <p class="thread-empty-msg">
                "Aun no hay conversacion sobre este ticket. La primera nota la escribes vos."
            </p>
        </div>

        <ol v-else class="thread-list">
            <li
                v-for="c in sortedComments"
                :key="c.id"
                class="thread-msg"
                :class="`thread-msg--${authorVariant(c)}`"
            >
                <header class="thread-msg-head">
                    <span class="thread-msg-author">{{ c.author_name }}</span>
                    <span class="thread-msg-role">{{ c.author_type === 'admin' ? 'ADMIN' : 'COACH' }}</span>
                    <span class="thread-msg-time">{{ relativeTime(c.created_at) }}</span>
                </header>
                <p class="thread-msg-body">{{ c.body }}</p>
            </li>
        </ol>

        <form class="thread-form" @submit.prevent="onSubmit">
            <label class="thread-label" for="thread-input">
                Tu nota como admin
            </label>
            <textarea
                id="thread-input"
                ref="inputRef"
                v-model="draft"
                class="thread-input"
                rows="3"
                placeholder="Escribe una nota al coach. Lo va a ver en su bell de notificaciones."
                maxlength="5000"
                :disabled="submitting"
            ></textarea>
            <div class="thread-form-foot">
                <span class="thread-counter" :class="{ 'thread-counter--warn': draft.length > 4500 }">
                    {{ draft.length }} / 5000
                </span>
                <button
                    type="submit"
                    class="thread-submit"
                    :disabled="submitting || draft.trim().length === 0"
                >
                    {{ submitting ? 'Enviando...' : 'Enviar comentario' }}
                </button>
            </div>
            <p v-if="submitError" class="thread-error" role="alert">{{ submitError }}</p>
        </form>
    </div>
</template>

<style scoped>
.thread {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 16px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.55);
    min-width: 0;
}

.thread-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--color-wc-border);
}
.thread-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.thread-count {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-feature-settings: 'tnum' 1;
    font-size: 12px;
    font-weight: 600;
    color: var(--color-wc-text);
    background: rgba(255, 255, 255, 0.04);
    padding: 1px 8px;
    border-radius: 999px;
    min-width: 22px;
    text-align: center;
}

.thread-empty {
    padding: 22px 8px 12px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.thread-empty-num {
    font-family: var(--font-display);
    font-size: 48px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    user-select: none;
}
.thread-empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12px;
    line-height: 1.55;
    color: var(--color-wc-text-tertiary);
    margin: 0;
    text-wrap: balance;
    max-width: 36ch;
}

.thread-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 480px;
    overflow-y: auto;
}

.thread-msg {
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: rgba(10, 10, 10, 0.55);
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.thread-msg--admin {
    background: rgba(220, 38, 38, 0.05);
    border-color: rgba(220, 38, 38, 0.18);
}
.thread-msg--coach {
    background: rgba(59, 130, 246, 0.05);
    border-color: rgba(59, 130, 246, 0.18);
}

.thread-msg-head {
    display: flex;
    align-items: baseline;
    gap: 8px;
    flex-wrap: wrap;
}
.thread-msg-author {
    font-family: var(--font-sans);
    font-size: 12.5px;
    font-weight: 600;
    color: var(--color-wc-text);
}
.thread-msg-role {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    padding: 1px 6px;
    border-radius: 4px;
}
.thread-msg--admin .thread-msg-role {
    background: rgba(220, 38, 38, 0.12);
    color: var(--color-wc-red-text, #F87171);
}
.thread-msg--coach .thread-msg-role {
    background: rgba(59, 130, 246, 0.12);
    color: var(--color-wc-blue-text, #60A5FA);
}
.thread-msg-time {
    margin-left: auto;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.10em;
    color: var(--color-wc-text-tertiary);
    text-transform: lowercase;
}
.thread-msg-body {
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.6;
    color: var(--color-wc-text);
    margin: 0;
    white-space: pre-wrap;
}

.thread-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-top: 6px;
    border-top: 1px solid var(--color-wc-border);
}
.thread-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.thread-input {
    border-radius: 10px;
    background: rgba(0, 0, 0, 0.30);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.55;
    padding: 10px 12px;
    resize: vertical;
    min-height: 80px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.thread-input::placeholder {
    color: var(--color-wc-text-tertiary);
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
}
.thread-input:focus { outline: none; border-color: var(--color-wc-accent, #DC2626); }
.thread-input:disabled { opacity: 0.6; cursor: not-allowed; }

.thread-form-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.thread-counter {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
}
.thread-counter--warn { color: var(--color-wc-amber-text, #FCD34D); }

.thread-submit {
    height: 36px;
    padding: 0 16px;
    border-radius: 10px;
    background: var(--color-wc-accent, #DC2626);
    color: #fff;
    border: 1px solid transparent;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease);
}
.thread-submit:hover:not(:disabled) { background: #B91C1C; }
.thread-submit:disabled { opacity: 0.5; cursor: not-allowed; }

.thread-error {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--color-wc-red-text, #F87171);
    background: rgba(220, 38, 38, 0.07);
    border: 1px solid rgba(220, 38, 38, 0.20);
    border-radius: 8px;
    padding: 8px 10px;
    margin: 0;
}

.thread-loading { display: flex; flex-direction: column; gap: 8px; }
.thread-skeleton {
    height: 64px;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    animation: thread-pulse 1.5s ease-in-out infinite;
}
.thread-skeleton--admin { width: 75%; }
.thread-skeleton--coach { width: 60%; align-self: flex-end; }

@keyframes thread-pulse {
    0%, 100% { opacity: 0.55; }
    50%      { opacity: 0.85; }
}

@media (prefers-reduced-motion: reduce) {
    .thread-skeleton { animation: none !important; }
}
</style>
