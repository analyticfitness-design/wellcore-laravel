<script setup>
/**
 * ExerciseCardHead.vue — Header de cada exercise card.
 * Counter + label estado + nombre + meta-row + action buttons.
 *
 * IMPORTANTE: clases prefijadas con wcv2- para evitar conflicto con wc-shell.css
 * (que define .card-head, .meta-row globalmente y rompe el layout en mobile).
 */
import { computed } from 'vue';

const props = defineProps({
    exerciseIndex:     { type: Number, required: true },
    exercise:          { type: Object, required: true },
    isVariationActive: { type: Boolean, default: false },
    state:             { type: String, default: 'upcoming' },
    hasNotes:          { type: Boolean, default: false },
    hasMedia:          { type: Boolean, default: false },
    hasVariation:      { type: Boolean, default: false },
    notesExpanded:     { type: Boolean, default: false },
});

defineEmits(['notes-toggle', 'media-open', 'variation-toggle']);

const counterText = computed(() =>
    String(props.exerciseIndex + 1).padStart(2, '0')
);

const stateLabel = computed(() => {
    if (props.state === 'done') return 'Completado';
    if (props.state === 'active') return 'Ejercicio actual';
    return 'Próximo ejercicio';
});

const displayName = computed(() => {
    const ex = props.exercise || {};
    if (props.isVariationActive && ex.variacion?.nombre) return ex.variacion.nombre;
    return ex.nombre || ex.name || ex.ejercicio || 'Ejercicio';
});

const muscle = computed(() => props.exercise?.musculo || props.exercise?.muscle_group || null);
const series = computed(() => props.exercise?.series || props.exercise?.sets || null);
const reps   = computed(() => props.exercise?.repeticiones || props.exercise?.reps || null);
const descanso = computed(() => props.exercise?.descanso || props.exercise?.rest || '90s');
const rir = computed(() => {
    const r = props.exercise?.rir;
    return (r === null || r === undefined || r === '') ? null : Number(r);
});

const rirClass = computed(() => {
    if (rir.value === null) return '';
    if (rir.value <= 1) return 'wcv2-rir wcv2-rir--low';
    if (rir.value === 2) return 'wcv2-rir wcv2-rir--mid';
    return 'wcv2-rir wcv2-rir--high';
});
</script>

<template>
  <div class="wcv2-card-head">
    <!-- TOP LINE: counter + label + action buttons -->
    <div class="wcv2-top-line">
      <div class="wcv2-counter" :class="{ 'wcv2-counter--muted': state !== 'active' }">
        <span class="wcv2-counter-num">{{ counterText }}</span>
        <span class="wcv2-counter-label">{{ stateLabel }}</span>
      </div>
      <div class="wcv2-head-actions">
        <button
          v-if="hasVariation"
          type="button"
          class="wcv2-icon-btn"
          :class="{ 'wcv2-icon-btn--active': isVariationActive }"
          aria-label="Cambiar variación"
          @click="$emit('variation-toggle')"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/>
            <polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>
          </svg>
        </button>
        <button
          v-if="hasNotes"
          type="button"
          class="wcv2-icon-btn"
          :class="{ 'wcv2-icon-btn--active': notesExpanded }"
          :aria-label="notesExpanded ? 'Ocultar notas del coach' : 'Ver notas del coach'"
          @click="$emit('notes-toggle')"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            <line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="14" y2="13"/>
          </svg>
        </button>
        <button
          v-if="hasMedia"
          type="button"
          class="wcv2-icon-btn"
          aria-label="Ver demostración en pantalla completa"
          @click="$emit('media-open')"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- NAME (full width, block) -->
    <h2 class="wcv2-name">{{ displayName }}</h2>

    <!-- META ROW (chips horizontales, wrap) -->
    <div class="wcv2-meta-row">
      <span v-if="muscle" class="wcv2-meta-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="wcv2-meta-ico">
          <path d="M20.5 14.5L9.5 3.5l-4 4 11 11 4-4z"/><path d="M3 21l3-3"/>
        </svg>
        <span>{{ muscle }}</span>
      </span>

      <span v-if="series && reps" class="wcv2-meta-item wcv2-meta-item--accent">
        <strong class="wcv2-meta-v">{{ series }}×{{ reps }}</strong>
        <span>series</span>
      </span>

      <div v-if="rir !== null" :class="rirClass">
        <span class="wcv2-rir-label">RIR</span>
        <span class="wcv2-rir-val">{{ rir }}</span>
      </div>

      <span class="wcv2-meta-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="wcv2-meta-ico">
          <circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/>
        </svg>
        <span>{{ descanso }}</span>
      </span>
    </div>
  </div>
</template>

<style scoped>
.wcv2-card-head {
  display: block;
  padding: 16px 16px 12px;
  position: relative;
}
@media (min-width: 1024px) { .wcv2-card-head { padding: 18px 22px 14px; } }

/* TOP LINE — counter + actions */
.wcv2-top-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
  flex-wrap: nowrap;
}

.wcv2-counter {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--color-wc-text-secondary);
  min-width: 0;
}
.wcv2-counter-num {
  font-family: var(--font-display);
  font-weight: 700;
  background: rgba(239,68,68,0.15);
  color: var(--color-wc-accent-glow, #EF4444);
  width: 26px;
  height: 26px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  font-size: 13px;
  letter-spacing: 0;
  flex-shrink: 0;
}
.wcv2-counter--muted .wcv2-counter-num {
  background: rgba(255,255,255,0.06);
  color: var(--color-wc-text-secondary);
}
.wcv2-counter-label {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  min-width: 0;
}

.wcv2-head-actions {
  display: flex;
  gap: 6px;
  flex-shrink: 0;
}
.wcv2-icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 999px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--color-wc-border);
  display: grid;
  place-items: center;
  color: var(--color-wc-text-secondary);
  cursor: pointer;
  transition: all 0.15s var(--ease-out);
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
  padding: 0;
}
.wcv2-icon-btn:hover {
  color: var(--color-wc-text);
  background: rgba(255,255,255,0.08);
}
.wcv2-icon-btn--active {
  background: rgba(220,38,38,0.15);
  border-color: rgba(220,38,38,0.3);
  color: var(--color-wc-accent-glow, #EF4444);
}
.wcv2-icon-btn svg { width: 16px; height: 16px; }

/* NAME — block-level, full width */
.wcv2-name {
  display: block;
  width: 100%;
  margin: 0;
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 22px;
  line-height: 1.1;
  text-transform: uppercase;
  letter-spacing: 0.005em;
  color: var(--color-wc-text);
  text-wrap: balance;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: none;
}
@media (min-width: 768px)  { .wcv2-name { font-size: 24px; } }
@media (min-width: 1024px) { .wcv2-name { font-size: 26px; line-height: 1.1; } }

/* META ROW — chips wrap */
.wcv2-meta-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 10px;
  align-items: center;
  margin-top: 12px;
  width: 100%;
}
.wcv2-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  color: var(--color-wc-text-secondary);
  padding: 4px 10px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--color-wc-border);
  border-radius: 999px;
  white-space: nowrap;
}
.wcv2-meta-item--accent {
  background: rgba(220,38,38,0.08);
  border-color: rgba(220,38,38,0.20);
  color: var(--color-wc-accent-glow, #EF4444);
}
.wcv2-meta-ico { width: 12px; height: 12px; opacity: 0.7; flex-shrink: 0; }
.wcv2-meta-v {
  font-family: var(--font-display);
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  font-size: 13px;
}

/* RIR chip */
.wcv2-rir {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 999px;
  white-space: nowrap;
}
.wcv2-rir-label {
  font-family: var(--font-display);
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  opacity: 0.85;
}
.wcv2-rir-val {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 13px;
  font-variant-numeric: tabular-nums;
}
.wcv2-rir--low  { background: rgba(220,38,38,0.16);  color: var(--color-wc-accent-glow, #EF4444); }
.wcv2-rir--mid  { background: rgba(245,158,11,0.16); color: #F59E0B; }
.wcv2-rir--high { background: rgba(16,185,129,0.16); color: #10B981; }

/* Mobile small */
@media (max-width: 380px) {
  .wcv2-card-head { padding: 14px 14px 12px; }
  .wcv2-top-line { margin-bottom: 10px; gap: 8px; }
  .wcv2-counter { font-size: 10px; gap: 6px; }
  .wcv2-counter-num { width: 22px; height: 22px; font-size: 11px; }
  .wcv2-icon-btn { width: 36px; height: 36px; }
  .wcv2-icon-btn svg { width: 14px; height: 14px; }
  .wcv2-name { font-size: 21px; }
  .wcv2-meta-row { gap: 5px 8px; }
  .wcv2-meta-item, .wcv2-rir { font-size: 11px; padding: 3px 8px; }
  .wcv2-meta-v, .wcv2-rir-val { font-size: 12px; }
}
</style>
