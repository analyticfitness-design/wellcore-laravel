<script setup>
/**
 * ExerciseCardHead.vue — Header de cada exercise card.
 * Counter + label estado + nombre + meta-row + action buttons.
 */
import { computed } from 'vue';

const props = defineProps({
  exerciseIndex:     { type: Number, required: true },
  exercise:          { type: Object, required: true },
  isVariationActive: { type: Boolean, default: false },
  state:             { type: String, default: 'upcoming' }, // 'active' | 'upcoming' | 'done'
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
const rir    = computed(() => {
  const r = props.exercise?.rir;
  return (r === null || r === undefined || r === '') ? null : Number(r);
});

const rirClass = computed(() => {
  if (rir.value === null) return '';
  if (rir.value <= 1) return 'rir rir-low';
  if (rir.value === 2) return 'rir rir-mid';
  return 'rir rir-high';
});
</script>

<template>
  <div class="card-head">
    <div class="top-line">
      <div class="ex-counter" :class="{ muted: state !== 'active' }">
        <span class="num">{{ counterText }}</span>
        <span>{{ stateLabel }}</span>
      </div>
      <div class="head-actions">
        <button
          v-if="hasVariation"
          type="button"
          class="icon-btn"
          :class="{ 'icon-btn--active': isVariationActive }"
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
          class="icon-btn"
          :class="{ 'icon-btn--active': notesExpanded }"
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
          class="icon-btn"
          aria-label="Ver demostración"
          @click="$emit('media-open')"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/>
          </svg>
        </button>
      </div>
    </div>

    <h2 class="ex-name">{{ displayName }}</h2>

    <div class="meta-row">
      <span v-if="muscle" class="meta-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20.5 14.5L9.5 3.5l-4 4 11 11 4-4z"/><path d="M3 21l3-3"/>
        </svg>
        <span>{{ muscle }}</span>
      </span>
      <span v-if="series && reps" class="meta-item">
        <span class="v">{{ series }}×{{ reps }}</span>
        <span>series</span>
      </span>
      <div v-if="rir !== null" :class="rirClass">
        <span class="rir-label">RIR</span>
        <span class="rir-val">{{ rir }}</span>
      </div>
      <span class="meta-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/>
        </svg>
        <span>Descanso <span class="v">{{ descanso }}</span></span>
      </span>
    </div>
  </div>
</template>

<style scoped>
.card-head { padding: 20px 20px 14px; position: relative; }
@media (min-width: 1024px) { .card-head { padding: 26px 28px 18px; } }

.top-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.ex-counter {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-display);
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--color-wc-text-secondary);
}
.ex-counter .num {
  font-family: var(--font-display);
  font-weight: 700;
  background: rgba(239,68,68,0.15);
  color: var(--color-wc-accent-glow, #EF4444);
  width: 26px; height: 26px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  font-size: 13px;
  letter-spacing: 0;
}
.ex-counter.muted .num {
  background: rgba(255,255,255,0.06);
  color: var(--color-wc-text-secondary);
}

.head-actions { display: flex; gap: 8px; }
.icon-btn {
  width: 44px; height: 44px;
  border-radius: 999px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--color-wc-border);
  display: grid;
  place-items: center;
  color: var(--color-wc-text-secondary);
  cursor: pointer;
  transition: all 0.15s var(--ease-out);
}
.icon-btn:hover { color: var(--color-wc-text); background: rgba(255,255,255,0.08); }
.icon-btn--active {
  background: rgba(220,38,38,0.15);
  border-color: rgba(220,38,38,0.3);
  color: var(--color-wc-accent-glow, #EF4444);
}
.icon-btn svg { width: 18px; height: 18px; }

.ex-name {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 28px;
  line-height: 1.05;
  text-transform: uppercase;
  letter-spacing: 0.005em;
  color: var(--color-wc-text);
  text-wrap: balance;
}
@media (min-width: 1024px) { .ex-name { font-size: 38px; } }

.meta-row {
  margin-top: 14px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px 14px;
  align-items: center;
}
.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--color-wc-text-secondary);
}
.meta-item svg { width: 14px; height: 14px; opacity: 0.7; }
.meta-item .v {
  color: var(--color-wc-text);
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}

.rir { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; }
.rir .rir-label { color: var(--color-wc-text-secondary); }
.rir .rir-val {
  font-family: var(--font-display);
  font-weight: 600;
  font-variant-numeric: tabular-nums;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 12px;
  letter-spacing: 0.04em;
}
.rir.rir-low .rir-val  { background: rgba(220,38,38,0.16);  color: var(--color-wc-accent-glow, #EF4444); }
.rir.rir-mid .rir-val  { background: rgba(245,158,11,0.16); color: #F59E0B; }
.rir.rir-high .rir-val { background: rgba(16,185,129,0.16); color: #10B981; }
</style>
