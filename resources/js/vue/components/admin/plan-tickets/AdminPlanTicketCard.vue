<script setup>
import { computed } from 'vue';

const props = defineProps({
    ticket: { type: Object, required: true },
    flash: { type: Boolean, default: false },
});

const emit = defineEmits(['open']);

const PLAN_LABEL = {
    esencial: 'Esencial',
    metodo: 'Metodo',
    elite: 'Elite',
    rise: 'Rise',
    presencial: 'Presencial',
    trial: 'Trial',
};

const STATUS_VARIANT = {
    pendiente: 'amber',
    en_revision: 'blue',
    completado: 'green',
    rechazado: 'red',
    borrador: 'muted',
};

const planLabel = computed(() => PLAN_LABEL[props.ticket?.plan_type] ?? '—');
const statusVariant = computed(() => STATUS_VARIANT[props.ticket?.status] ?? 'muted');

const submittedRel = computed(() => {
    const iso = props.ticket?.submitted_at ?? props.ticket?.created_at;
    if (!iso) return '—';
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
        return '—';
    }
});

const deadlineState = computed(() => {
    const iso = props.ticket?.deadline_at;
    if (!iso) return null;
    try {
        const d = new Date(iso);
        const diff = d.getTime() - Date.now();
        const h = Math.round(diff / 3600000);
        if (diff < 0) return { state: 'overdue', text: `vencido ${Math.abs(h)} h` };
        if (h < 12)   return { state: 'urgent',  text: `${h} h restantes` };
        if (h < 48)   return { state: 'soon',    text: `${h} h restantes` };
        const dd = Math.round(h / 24);
        return { state: 'ok', text: `${dd} d restantes` };
    } catch {
        return null;
    }
});

const clientInitial = computed(() => {
    const name = props.ticket?.client_name ?? '';
    return name.trim().charAt(0).toUpperCase() || '·';
});

function onClick() {
    emit('open', props.ticket.id);
}

function onKey(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        onClick();
    }
}
</script>

<template>
    <article
        class="pt-card"
        :class="{ 'pt-card--flash': flash }"
        tabindex="0"
        role="button"
        :aria-label="`Ticket de ${ticket.client_name}, plan ${planLabel}, estado ${ticket.status}`"
        @click="onClick"
        @keydown="onKey"
    >
        <header class="pt-card-head">
            <div class="pt-card-avatar" aria-hidden="true">{{ clientInitial }}</div>
            <div class="pt-card-head-text">
                <span class="pt-card-client">{{ ticket.client_name || 'Cliente sin nombre' }}</span>
                <span class="pt-card-coach">{{ ticket.coach_name || `coach #${ticket.coach_id}` }}</span>
            </div>
            <span class="pt-card-plan" :class="`pt-card-plan--${ticket.plan_type}`">{{ planLabel }}</span>
        </header>

        <div class="pt-card-meta">
            <span class="pt-card-time">{{ submittedRel }}</span>
            <span
                v-if="deadlineState"
                class="pt-card-deadline"
                :class="`pt-card-deadline--${deadlineState.state}`"
            >
                {{ deadlineState.text }}
            </span>
        </div>

        <footer class="pt-card-foot">
            <span class="pt-card-status" :class="`pt-card-status--${statusVariant}`">
                {{ ticket.status?.replace('_', ' ') }}
            </span>
            <span class="pt-card-cta">REVISAR <span aria-hidden="true">→</span></span>
        </footer>
    </article>
</template>

<style scoped>
.pt-card {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    cursor: pointer;
    text-align: left;
    color: inherit;
    transition: border-color 0.15s var(--ease-out, ease), background 0.15s var(--ease-out, ease);
    position: relative;
}
.pt-card:hover {
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.16));
    background: rgba(24, 24, 24, 0.85);
}
.pt-card:focus-visible {
    outline: none;
    border-color: var(--color-wc-accent, #DC2626);
    box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.18);
}

.pt-card--flash {
    animation: pt-card-flash 1.4s var(--ease-out, ease);
}
@keyframes pt-card-flash {
    0% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); border-color: var(--color-wc-border); }
    20% { box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.25); border-color: var(--color-wc-green-text, #34D399); }
    100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); border-color: var(--color-wc-border); }
}

.pt-card-head {
    display: grid;
    grid-template-columns: 28px 1fr auto;
    gap: 8px;
    align-items: center;
    min-width: 0;
}
.pt-card-avatar {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(220, 38, 38, 0.10);
    color: var(--color-wc-red-text, #F87171);
    font-family: var(--font-display);
    font-size: 14px;
    letter-spacing: 0.04em;
}
.pt-card-head-text { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.pt-card-client {
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    color: var(--color-wc-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.pt-card-coach {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pt-card-plan {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: 4px;
    border: 1px solid transparent;
    white-space: nowrap;
    background: rgba(255, 255, 255, 0.04);
    color: var(--color-wc-text-secondary);
    border-color: var(--color-wc-border);
}
.pt-card-plan--esencial   { background: rgba(59, 130, 246, 0.10);  color: var(--color-wc-blue-text, #60A5FA);   border-color: rgba(59, 130, 246, 0.20); }
.pt-card-plan--metodo     { background: rgba(245, 158, 11, 0.10);  color: var(--color-wc-amber-text, #FCD34D);  border-color: rgba(245, 158, 11, 0.20); }
.pt-card-plan--elite      { background: rgba(220, 38, 38, 0.10);   color: var(--color-wc-red-text, #F87171);    border-color: rgba(220, 38, 38, 0.20); }
.pt-card-plan--rise       { background: rgba(200, 167, 105, 0.10); color: var(--color-wc-gold, #C8A769);        border-color: rgba(200, 167, 105, 0.22); }
.pt-card-plan--presencial { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-secondary);       border-color: var(--color-wc-border); }
.pt-card-plan--trial      { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-tertiary);        border-color: var(--color-wc-border); }

.pt-card-meta {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
}
.pt-card-time {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.10em;
    color: var(--color-wc-text-tertiary);
    text-transform: lowercase;
}
.pt-card-deadline {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 4px;
}
.pt-card-deadline--ok       { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-tertiary); }
.pt-card-deadline--soon     { background: rgba(245, 158, 11, 0.10);  color: var(--color-wc-amber-text, #FCD34D); }
.pt-card-deadline--urgent   { background: rgba(220, 38, 38, 0.10);   color: var(--color-wc-red-text, #F87171); }
.pt-card-deadline--overdue  { background: rgba(220, 38, 38, 0.18);   color: var(--color-wc-red-text, #F87171); }

.pt-card-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    padding-top: 6px;
    border-top: 1px solid rgba(255, 255, 255, 0.04);
}
.pt-card-status {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: 4px;
    border: 1px solid transparent;
}
.pt-card-status--amber  { background: var(--color-wc-amber-soft, rgba(245,158,11,0.10)); color: var(--color-wc-amber-text, #FCD34D); border-color: rgba(245,158,11,0.20); }
.pt-card-status--green  { background: var(--color-wc-green-soft, rgba(16,185,129,0.10)); color: var(--color-wc-green-text, #34D399); border-color: rgba(16,185,129,0.20); }
.pt-card-status--blue   { background: var(--color-wc-blue-soft, rgba(59,130,246,0.10));  color: var(--color-wc-blue-text, #60A5FA);  border-color: rgba(59,130,246,0.20); }
.pt-card-status--red    { background: rgba(220, 38, 38, 0.10);                            color: var(--color-wc-red-text, #F87171);   border-color: rgba(220, 38, 38, 0.20); }
.pt-card-status--muted  { background: rgba(255,255,255,0.03); color: var(--color-wc-text-tertiary); border-color: var(--color-wc-border); }

.pt-card-cta {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-secondary);
    text-transform: uppercase;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 2px;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.pt-card:hover .pt-card-cta { color: var(--color-wc-text); border-bottom-color: var(--color-wc-accent, #DC2626); }

@media (prefers-reduced-motion: reduce) {
    .pt-card { transition: none !important; }
    .pt-card--flash { animation: none !important; }
}
</style>
