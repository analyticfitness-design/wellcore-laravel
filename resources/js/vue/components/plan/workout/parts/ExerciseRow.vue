<template>
  <div
    class="ex-row"
    :class="{ 'block-row': isInBlock, 'is-cardio': isCardio }"
    :data-testid="`ex-row-${ejercicio?.id ?? 'noid'}`"
  >
    <div class="ex-thumb">
      <img
        v-if="effectiveGifUrl && !gifFailed"
        :src="effectiveGifUrl"
        :alt="effectiveName"
        loading="lazy"
        decoding="async"
        @error="onGifError"
      />
      <div v-else class="ex-thumb__fallback" :style="fallbackStyle">
        <span>{{ fallbackInitials }}</span>
      </div>
      <span class="num-badge">{{ formattedNumero }}</span>
      <span class="play-tag" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M5.25 5.65c0-.86.92-1.4 1.67-.99l11.54 6.35a1.13 1.13 0 0 1 0 1.97L6.92 19.34a1.13 1.13 0 0 1-1.67-.99V5.65Z"/></svg>
      </span>
      <span class="grp-strip" :style="{ background: groupColor }"></span>
    </div>

    <div class="ex-body">
      <div class="ex-name">
        {{ effectiveName }}
        <span v-if="isUsingVariant && hasVariation" class="variation-active-mark" data-testid="variation-active-mark">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
          </svg>
          Usando variación
        </span>
      </div>

      <div v-if="!isCardio" class="chips">
        <span v-if="series" class="metric"><span class="k">Series</span><span class="v">{{ series }}</span></span>
        <span v-if="reps" class="metric"><span class="k">Reps</span><span class="v">{{ reps }}</span></span>
        <span v-if="rest" class="metric rest"><span class="k">Rest</span><span class="v">{{ rest }}</span></span>
        <span v-if="rir" class="metric" :class="rirClass"><span class="k">RIR</span><span class="v">{{ rir }}</span></span>
      </div>

      <ExerciseCardioChips
        v-if="isCardio"
        :min="ejercicio?.cardio_min ?? null"
        :velocidad="ejercicio?.cardio_velocidad ?? null"
        :inclinacion="ejercicio?.cardio_inclinacion ?? null"
      />

      <div v-if="hasCoachNote && coachNoteExpanded" class="ex-coach-note">
        <span class="av">M</span>
        <p class="body" v-html="formattedCoachNote"></p>
      </div>
      <button
        v-else-if="hasCoachNote"
        type="button"
        class="ex-collapsed-note"
        @click="coachNoteExpanded = true"
        :aria-label="`Ver nota del coach para ${effectiveName}`"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379"/>
        </svg>
        Nota del coach
      </button>

      <!-- Variation toggle/selector — SIEMPRE visible bajo cada ejercicio (paridad V1).
           Si no hay variación populada en el JSON: botón disabled "Sin variación". -->
      <div class="variation-controls">
        <!-- Selector múltiple A/B/C cuando hay opciones (>1 variación) -->
        <div v-if="hasMultipleOpciones" class="variation-selector" data-testid="variation-selector">
          <span class="variation-selector__label">Opciones</span>
          <div class="variation-selector__options">
            <button
              v-for="(opt, i) in opcionesList"
              :key="i"
              type="button"
              class="variation-selector__opt"
              :class="{ 'is-active': i === selectedOpcionIdx }"
              :disabled="isToggling"
              :title="opt"
              @click="onSelectOpcion(i)"
            >
              {{ String.fromCharCode(65 + i) }}
            </button>
          </div>
        </div>
        <!-- Toggle simple (1 variación) o botón disabled (sin variación) -->
        <ExerciseVariationToggle
          v-else
          :has-variation="hasVariation"
          :is-using-variant="isUsingVariant"
          :variant-name="variantName"
          :is-toggling="isToggling"
          @toggle="onToggleVariation"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
// ExerciseRow — fila de ejercicio (refactor extraído del PlanViewer V1).
// CSS lines 1011-1097 del HTML V2.1.
// Reglas de preservación (rules 11-20 del prompt):
//  - gif_url SOLO se lee. NUNCA se reescribe en DB ni se postea on-error.
//  - sort_order/numero NO se reordena: respetar tal cual viene del backend.
//  - series/reps/rest/RIR NO se recalculan: render literal.
//  - coach_note NO se modifica: solo se muestra.
import { computed, ref } from 'vue';
import ExerciseVariationToggle from './ExerciseVariationToggle.vue';
import ExerciseCardioChips from './ExerciseCardioChips.vue';
import { usePlanViewer } from '@/composables/usePlanViewer';

const props = defineProps({
  ejercicio: { type: Object, required: true },
  numero: { type: Number, default: null },
  isInBlock: { type: Boolean, default: false },
  isToggling: { type: Boolean, default: false },
});

const emit = defineEmits(['variation-toggle']);

const coachNoteExpanded = ref(false);
const gifFailed = ref(false);

const { variationStateFor } = usePlanViewer();
const localVariantOverride = computed(() => variationStateFor(props.ejercicio?.id).value);

const isUsingVariant = computed(() => {
  if (typeof localVariantOverride.value === 'boolean') return localVariantOverride.value;
  return !!(props.ejercicio?.is_using_variant);
});

const hasVariation = computed(() => {
  const v = props.ejercicio?.variacion;
  return !!(v && (v.gif_url || v.nombre));
});

const variantName = computed(() => {
  return props.ejercicio?.variacion?.nombre ?? '';
});

// Opciones múltiples (a/b/c) — backend normalizeVariacion las extrae cuando
// el JSON original tiene array indexado de strings.
const opcionesList = computed(() => {
  const opts = props.ejercicio?.variacion?.opciones;
  return Array.isArray(opts) ? opts.filter((o) => typeof o === 'string' && o.trim().length > 0) : [];
});
const hasMultipleOpciones = computed(() => opcionesList.value.length >= 2);
const selectedOpcionIdx = ref(0);

function onSelectOpcion(idx) {
  if (idx < 0 || idx >= opcionesList.value.length) return;
  selectedOpcionIdx.value = idx;
  // Idx 0 = original (volver al original), Idx >0 = usar variación N
  // Emitimos el toggle con use_variant=true cuando idx>0, false cuando idx=0.
  const exId = props.ejercicio?.id;
  if (!exId) return;
  emit('variation-toggle', exId, idx > 0);
}

// effectiveGifUrl: cuando is_using_variant=true preferir variacion.gif_url, sino el original.
// IMPORTANTE: nunca sobreescribir el campo original — solo elegir cual mostrar.
const effectiveGifUrl = computed(() => {
  if (isUsingVariant.value && props.ejercicio?.variacion?.gif_url) {
    return props.ejercicio.variacion.gif_url;
  }
  return props.ejercicio?.gif_url || '';
});

const effectiveName = computed(() => {
  if (isUsingVariant.value && variantName.value) return variantName.value;
  return props.ejercicio?.nombre || 'Ejercicio sin nombre';
});

const formattedNumero = computed(() => {
  const n = props.numero ?? props.ejercicio?.numero ?? props.ejercicio?.sort_order ?? props.ejercicio?.posicion;
  if (n == null) return '·';
  return String(n).padStart(2, '0');
});

const series = computed(() => props.ejercicio?.series ?? null);
const reps = computed(() => props.ejercicio?.reps ?? null);
const rest = computed(() => props.ejercicio?.rest ?? null);
const rir = computed(() => props.ejercicio?.rir ?? props.ejercicio?.RIR ?? null);

const rirClass = computed(() => {
  const r = String(rir.value ?? '').trim();
  if (!r) return '';
  // Heurística: RIR 0-1 = high intensity (red), 2 = mid (amber), 3+ = low (green)
  const firstNum = parseInt(r, 10);
  if (Number.isNaN(firstNum)) return '';
  if (firstNum <= 1) return 'rir-low';
  if (firstNum === 2) return 'rir-mid';
  return 'rir-high';
});

// Mapa color por grupo muscular (mismo del HTML V2.1).
const GROUP_COLORS = {
  pecho: '#F87171',
  piernas: '#34D399',
  cuadriceps: '#34D399',
  espalda: '#FBBF24',
  hombros: '#60A5FA',
  femoral: '#C4B5FD',
  isquios: '#C4B5FD',
  biceps: '#F472B6',
  triceps: '#FB923C',
  core: '#9CA3AF',
  abs: '#9CA3AF',
  cardio: '#38BDF8',
  gluteos: '#34D399',
};

const groupColor = computed(() => {
  const g = String(props.ejercicio?.grupo || '').toLowerCase().trim();
  return GROUP_COLORS[g] || 'rgba(255,255,255,0.10)';
});

const fallbackStyle = computed(() => ({
  background: `linear-gradient(135deg, ${groupColor.value}40, transparent)`,
  borderColor: `${groupColor.value}66`,
}));

const fallbackInitials = computed(() => {
  return effectiveName.value
    .split(/\s+/)
    .slice(0, 2)
    .map((w) => w.charAt(0).toUpperCase())
    .join('');
});

const isCardio = computed(() => {
  const t = String(props.ejercicio?.tipo || '').toLowerCase();
  if (t === 'cardio') return true;
  const grupo = String(props.ejercicio?.grupo || '').toLowerCase();
  if (grupo === 'cardio') return true;
  // Heurística defensiva: si tiene cardio_min populated tratarlo como cardio
  return props.ejercicio?.cardio_min != null && props.ejercicio?.cardio_min !== '';
});

const hasCoachNote = computed(() => {
  const n = props.ejercicio?.coach_note;
  return typeof n === 'string' && n.trim().length > 0;
});

// Permitir <strong> dentro del coach_note (es un patron del HTML V2.1) pero escapar el resto.
function escapeHtml(s) {
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
const formattedCoachNote = computed(() => {
  const raw = props.ejercicio?.coach_note ?? '';
  // Convertir **xxxx** o *xxxxx* en <strong>xxxxx</strong> (texto del coach a veces tiene markdown ligero)
  const escaped = escapeHtml(raw);
  return escaped
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/(^|[^*])\*([^*]+)\*(?!\*)/g, '$1<strong>$2</strong>');
});

function onToggleVariation(useVariant) {
  const exId = props.ejercicio?.id;
  if (!exId) return;
  emit('variation-toggle', exId, useVariant);
}

// onGifError: NUNCA toca DB ni hace POST. Solo flagea local para mostrar fallback.
function onGifError() {
  gifFailed.value = true;
}
</script>

<style scoped>
.ex-row {
  display: flex;
  gap: 14px;
  padding: 14px 16px;
  border-bottom: 1px solid var(--wc-border);
  position: relative;
  align-items: flex-start;
}
.ex-row:last-child { border-bottom: 0; }
.block-row {
  border-left: 2px solid var(--wc-accent);
}

.ex-thumb {
  width: 88px;
  height: 88px;
  border-radius: 12px;
  overflow: hidden;
  flex-shrink: 0;
  background: var(--wc-bg-tertiary);
  position: relative;
  border: 1px solid var(--wc-border);
}
.ex-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.ex-thumb__fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid;
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 24px;
  font-weight: 700;
  letter-spacing: 0.04em;
  color: var(--wc-text-secondary);
}
.ex-thumb .num-badge {
  position: absolute;
  top: 6px;
  left: 6px;
  min-width: 24px;
  height: 24px;
  padding: 0 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.78);
  backdrop-filter: blur(8px);
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 13px;
  font-weight: 700;
  color: #fff;
}
.ex-thumb .play-tag {
  position: absolute;
  bottom: 6px;
  right: 6px;
  width: 22px;
  height: 22px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
}
.ex-thumb .play-tag svg {
  width: 10px;
  height: 10px;
  fill: #fff;
  margin-left: 1px;
}
.ex-thumb .grp-strip {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  width: 4px;
}

.ex-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.ex-name {
  font-size: 15px;
  font-weight: 600;
  color: var(--wc-text);
  line-height: 1.30;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.variation-active-mark {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 9.5px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: rgba(239, 68, 68, 0.85);
  font-weight: 500;
}
.variation-active-mark svg { width: 9px; height: 9px; }

/* Variation controls — paridad V1: debajo del coach note, pill prominente */
.variation-controls {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 8px;
}
.variation-selector {
  display: flex;
  align-items: center;
  gap: 8px;
}
.variation-selector__label {
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 10px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--wc-text-tertiary);
  font-weight: 600;
}
.variation-selector__options {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px;
  border-radius: 999px;
  border: 1px solid var(--wc-border);
  background: var(--wc-bg-tertiary, rgba(255,255,255,0.04));
}
.variation-selector__opt {
  width: 26px;
  height: 26px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  border: none;
  background: transparent;
  font-family: 'Oswald', Impact, sans-serif;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.04em;
  color: var(--wc-text-secondary);
  cursor: pointer;
  transition: all 0.15s ease;
}
.variation-selector__opt:hover:not(:disabled) {
  background: rgba(220, 38, 38, 0.08);
  color: #EF4444;
}
.variation-selector__opt.is-active {
  background: linear-gradient(135deg, #DC2626, #7F1D1D);
  color: #fff;
  box-shadow: 0 2px 8px -2px rgba(220, 38, 38, 0.5);
}
.variation-selector__opt:disabled {
  opacity: 0.5;
  cursor: progress;
}
.chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  align-items: center;
  margin-top: 2px;
}
.metric {
  display: inline-flex;
  align-items: baseline;
  gap: 4px;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 12px;
  padding: 3px 9px;
  border-radius: 6px;
  background: var(--wc-bg-tertiary);
  border: 1px solid var(--wc-border);
}
.metric .k {
  font-size: 9px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--wc-text-tertiary);
  font-weight: 600;
}
.metric .v {
  color: var(--wc-text);
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}
.metric.rest { color: var(--wc-text-secondary); }
.metric.rest .v { color: var(--wc-text-secondary); }
.metric.rir-low { background: rgba(220, 38, 38, 0.10); border-color: rgba(220, 38, 38, 0.30); }
.metric.rir-low .v { color: #EF4444; }
.metric.rir-mid { background: rgba(251, 191, 36, 0.10); border-color: rgba(251, 191, 36, 0.30); }
.metric.rir-mid .v { color: #FBBF24; }
.metric.rir-high { background: rgba(52, 211, 153, 0.10); border-color: rgba(52, 211, 153, 0.30); }
.metric.rir-high .v { color: #34D399; }

.ex-coach-note {
  margin-top: 6px;
  display: flex;
  gap: 8px;
  align-items: flex-start;
  padding: 8px 10px;
  border-left: 2px solid #7F1D1D;
  background: rgba(220, 38, 38, 0.04);
  border-radius: 0 6px 6px 0;
}
.ex-coach-note .av {
  width: 18px;
  height: 18px;
  border-radius: 999px;
  flex-shrink: 0;
  background: linear-gradient(135deg, #DC2626, #7F1D1D);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 9px;
  font-weight: 700;
  color: #fff;
}
.ex-coach-note .body {
  font-size: 13px;
  line-height: 1.55;
  color: var(--wc-text-secondary);
  font-style: italic;
  margin: 0;
}
.ex-coach-note .body :deep(strong) {
  color: var(--wc-text);
  font-weight: 600;
  font-style: normal;
}

.ex-collapsed-note {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 8px;
  border-radius: 999px;
  background: var(--wc-bg-tertiary);
  border: 1px solid var(--wc-border);
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  color: var(--wc-text-tertiary);
  letter-spacing: 0.10em;
  text-transform: uppercase;
  cursor: pointer;
  align-self: flex-start;
}
.ex-collapsed-note:hover {
  color: var(--wc-text-secondary);
  border-color: var(--wc-border);
}
.ex-collapsed-note svg { width: 10px; height: 10px; }

@media (max-width: 480px) {
  .ex-thumb { width: 76px; height: 76px; }
}
</style>
