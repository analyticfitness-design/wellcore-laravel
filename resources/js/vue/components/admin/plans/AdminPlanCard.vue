<script setup>
const props = defineProps({
    plan: { type: Object, required: true },
});

const emit = defineEmits(['edit', 'duplicate', 'delete', 'view']);

const TYPE_COLORS = {
    entrenamiento:  { bg: 'rgba(59,130,246,0.1)',  text: '#60A5FA' },
    nutricion:      { bg: 'rgba(16,185,129,0.1)',  text: '#34D399' },
    habitos:        { bg: 'rgba(139,92,246,0.1)',  text: '#A78BFA' },
    suplementacion: { bg: 'rgba(245,158,11,0.1)',  text: '#FCD34D' },
    ciclo:          { bg: 'rgba(236,72,153,0.1)',  text: '#F472B6' },
};

const TYPE_LABELS = {
    entrenamiento:  'Entrenamiento',
    nutricion:      'Nutricion',
    habitos:        'Habitos',
    suplementacion: 'Suplementacion',
    ciclo:          'Ciclo',
};

function typeStyle(t) {
    const c = TYPE_COLORS[t];
    if (!c) return { background: 'rgba(255,255,255,0.06)', color: 'var(--color-wc-text-tertiary)' };
    return { background: c.bg, color: c.text };
}

function typeLabel(t) {
    return TYPE_LABELS[t] ?? (t ? t.charAt(0).toUpperCase() + t.slice(1) : '—');
}
</script>

<template>
  <article class="plan-card">
    <!-- Top accent line -->
    <div class="plan-card-accent" aria-hidden="true"></div>

    <!-- Header -->
    <header class="plan-card-header">
      <span class="plan-eyebrow">TEMPLATE</span>
      <div class="plan-badges">
        <span v-if="plan.ai_generated" class="badge badge-ai">AI</span>
        <span v-if="plan.is_public" class="badge badge-public">PUBLICO</span>
        <span v-else class="badge badge-private">PRIVADO</span>
      </div>
    </header>

    <!-- Plan name -->
    <h2 class="plan-name">{{ plan.name.toUpperCase() }}</h2>

    <!-- Type badge -->
    <div class="plan-type-row">
      <span class="plan-type-badge" :style="typeStyle(plan.plan_type)">
        {{ typeLabel(plan.plan_type) }}
      </span>
      <span v-if="plan.methodology" class="plan-methodology">{{ plan.methodology }}</span>
    </div>

    <!-- Description -->
    <p v-if="plan.description" class="plan-desc">
      {{ plan.description.length > 100 ? plan.description.slice(0, 100) + '…' : plan.description }}
    </p>
    <p v-else class="plan-desc plan-desc--empty">Sin descripcion.</p>

    <!-- Divider -->
    <div class="plan-divider" aria-hidden="true"></div>

    <!-- Footer meta -->
    <footer class="plan-footer">
      <div class="plan-meta">
        <span v-if="plan.coach_name" class="plan-coach">{{ plan.coach_name }}</span>
        <span class="plan-date">{{ plan.created_at }}</span>
      </div>

      <!-- Actions -->
      <div class="plan-actions">
        <button
          type="button"
          class="plan-btn"
          aria-label="Ver contenido del template"
          @click="emit('view', plan)"
        >
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
          </svg>
        </button>
        <button
          type="button"
          class="plan-btn plan-btn--edit"
          aria-label="Editar template"
          @click="emit('edit', plan)"
        >
          EDITAR
        </button>
        <button
          type="button"
          class="plan-btn plan-btn--dup"
          aria-label="Duplicar template"
          @click="emit('duplicate', plan)"
        >
          DUPLICAR
        </button>
        <button
          type="button"
          class="plan-btn plan-btn--del"
          aria-label="Eliminar template"
          @click="emit('delete', plan)"
        >
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
          </svg>
        </button>
      </div>
    </footer>
  </article>
</template>

<style scoped>
.plan-card {
    position: relative;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.plan-card:hover {
    border-color: var(--color-wc-border-2);
}

.plan-card-accent {
    position: absolute;
    top: 0; left: 18px; right: 18px;
    height: 1px;
    background: linear-gradient(90deg, var(--color-wc-accent, #DC2626) 0%, transparent 100%);
    opacity: 0.3;
    border-radius: 1px;
}

/* Header */
.plan-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.plan-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.plan-badges {
    display: flex;
    align-items: center;
    gap: 4px;
}
.badge {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 2px 7px;
    border-radius: 20px;
}
.badge-ai      { background: rgba(139, 92, 246, 0.12); color: #A78BFA; }
.badge-public  { background: var(--color-wc-green-soft); color: var(--color-wc-green-text); }
.badge-private { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-tertiary); }

/* Name */
.plan-name {
    font-family: var(--font-display);
    font-size: 26px;
    letter-spacing: 0.04em;
    color: var(--color-wc-text);
    line-height: 1.1;
    margin: 0;
}

/* Type row */
.plan-type-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.plan-type-badge {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 3px 9px;
    border-radius: 20px;
}
.plan-methodology {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--color-wc-text-tertiary);
}

/* Description */
.plan-desc {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--color-wc-text-secondary);
    line-height: 1.55;
    margin: 0;
    flex: 1;
}
.plan-desc--empty {
    color: var(--color-wc-text-tertiary);
    font-style: italic;
}

/* Divider */
.plan-divider {
    height: 1px;
    background: var(--color-wc-border);
    margin: 2px 0;
}

/* Footer */
.plan-footer {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.plan-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.plan-coach {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-wc-text-secondary);
}
.plan-date {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 10px;
    color: var(--color-wc-text-tertiary);
}

/* Actions */
.plan-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}
.plan-btn {
    height: 28px;
    padding: 0 10px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease), background 0.15s var(--ease-out, ease);
}
.plan-btn:hover {
    border-color: var(--color-wc-border-2);
    color: var(--color-wc-text);
}
.plan-btn--edit:hover {
    border-color: var(--color-wc-accent);
    color: var(--color-wc-accent);
}
.plan-btn--dup:hover {
    border-color: var(--color-wc-blue-text);
    color: var(--color-wc-blue-text);
}
.plan-btn--del:hover {
    border-color: var(--color-wc-red-text);
    color: var(--color-wc-red-text);
}

@media (prefers-reduced-motion: reduce) {
    .plan-card,
    .plan-btn { transition: none !important; }
}
</style>
