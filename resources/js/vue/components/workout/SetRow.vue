<script setup>
/**
 * SetRow.vue — Fila de un set (peso × reps × completar).
 *
 * Átomo del WorkoutPlayer v2. Soporta 3 estados (pending/active/completed),
 * variante cardio con duración/velocidad/inclinación, y PR badge.
 *
 * Touch targets ≥44px en steppers + complete button.
 * Validación: si reps <= 0 al intentar complete → no emite, dispara shake visual.
 *
 * Visual fidelity: replica exactamente el target HTML — número grande del valor
 * con el rango/unidad pequeño debajo (ej: "9" arriba + "8 – 10" abajo).
 */
import { ref, computed } from 'vue';

const props = defineProps({
    setIndex:     { type: Number, required: true },
    setNumber:    { type: Number, required: true },
    state:        { type: String, default: 'pending' },
    weight:       { type: [Number, String], default: '' },
    reps:         { type: [Number, String], default: '' },
    targetWeight: { type: [Number, String, null], default: null },
    targetReps:   { type: String, default: '' },
    weightUnit:   { type: String, default: 'kg' },
    isPr:         { type: Boolean, default: false },
    isCardio:     { type: Boolean, default: false },
    isIsometric:  { type: Boolean, default: false },
    duration:     { type: [Number, String], default: '' },
    speed:        { type: [Number, String], default: '' },
    incline:      { type: [Number, String], default: '' },
    disabled:     { type: Boolean, default: false },
});

const emit = defineEmits([
    'update:weight', 'update:reps', 'update:duration', 'update:speed', 'update:incline',
    'complete', 'uncomplete',
]);

const shake = ref(false);
const editingWeight = ref(false);
const editingReps   = ref(false);

const weightStep = computed(() => (props.weightUnit === 'lbs' ? 5 : 2.5));

function clampWeight(v) { return Math.max(0, Number(v) || 0); }
function clampReps(v)   { return Math.max(0, parseInt(v) || 0); }

function bumpWeight(delta) {
    const next = clampWeight((parseFloat(props.weight) || 0) + delta);
    emit('update:weight', next);
}
function bumpReps(delta) {
    const next = clampReps((parseInt(props.reps) || 0) + delta);
    emit('update:reps', next);
}
function bumpDuration(delta) {
    const next = Math.max(0, (parseInt(props.duration) || 0) + delta);
    emit('update:duration', next);
}
function bumpSpeed(delta) {
    const next = Math.max(0, +((parseFloat(props.speed) || 0) + delta).toFixed(1));
    emit('update:speed', next);
}
function bumpIncline(delta) {
    const next = Math.max(0, +((parseFloat(props.incline) || 0) + delta).toFixed(1));
    emit('update:incline', next);
}

function onCompleteClick() {
    if (props.disabled) return;
    if (props.state === 'completed') {
        emit('uncomplete');
        return;
    }
    if (!props.isCardio) {
        const reps = clampReps(props.reps);
        if (reps <= 0) {
            shake.value = true;
            setTimeout(() => { shake.value = false; }, 400);
            return;
        }
    } else {
        const duration = parseInt(props.duration) || 0;
        if (duration <= 0) {
            shake.value = true;
            setTimeout(() => { shake.value = false; }, 400);
            return;
        }
    }
    emit('complete');
}

function onWeightInput(e)   { emit('update:weight',   clampWeight(e.target.value)); }
function onRepsInput(e)     { emit('update:reps',     clampReps(e.target.value)); }
function onDurationInput(e) { emit('update:duration', Math.max(0, parseInt(e.target.value) || 0)); }
function onSpeedInput(e)    { emit('update:speed',    Math.max(0, +parseFloat(e.target.value).toFixed(1) || 0)); }
function onInclineInput(e)  { emit('update:incline',  Math.max(0, +parseFloat(e.target.value).toFixed(1) || 0)); }

// Display values con formato target HTML
const weightDisplay = computed(() => {
    const v = parseFloat(props.weight);
    if (isNaN(v) || v === 0) return '—';
    return Number.isInteger(v) ? String(v) : v.toFixed(1).replace('.', ',');
});
const repsDisplay = computed(() => {
    const v = parseInt(props.reps);
    if (isNaN(v) || v === 0) return '—';
    return String(v);
});
const durationDisplay = computed(() => {
    const v = parseInt(props.duration);
    return (isNaN(v) || v === 0) ? '—' : String(v);
});
const speedDisplay = computed(() => {
    const v = parseFloat(props.speed);
    return (isNaN(v) || v === 0) ? '—' : (Number.isInteger(v) ? String(v) : v.toFixed(1).replace('.', ','));
});
const inclineDisplay = computed(() => {
    const v = parseFloat(props.incline);
    return (isNaN(v) || v === 0) ? '—' : (Number.isInteger(v) ? String(v) : v.toFixed(1).replace('.', ','));
});

const targetRepsLabel = computed(() => {
    const tr = String(props.targetReps || '').trim();
    if (!tr) return '';
    return tr.replace('-', ' – ');
});

function startEditWeight() { if (!props.disabled) editingWeight.value = true; }
function startEditReps()   { if (!props.disabled) editingReps.value = true; }
function endEditWeight()   { editingWeight.value = false; }
function endEditReps()     { editingReps.value = false; }
</script>

<template>
  <div
    class="wc-set-row group relative grid items-center transition-all duration-200"
    :class="[
      isCardio ? 'wc-set-row--cardio' : 'wc-set-row--strength',
      disabled ? 'opacity-60 pointer-events-none' : '',
      shake ? 'wc-shake' : '',
    ]"
    :data-state="state"
  >
    <!-- Active accent bar -->
    <span
      v-if="state === 'active'"
      aria-hidden="true"
      class="active-bar"
    ></span>

    <!-- PR badge -->
    <span
      v-if="isPr"
      class="pr-badge"
    >PR</span>

    <!-- Set number -->
    <div class="set-num">{{ setNumber }}</div>

    <!-- STRENGTH variant -->
    <template v-if="!isCardio">
      <!-- PESO stepper -->
      <div class="stepper">
        <span class="stepper-label">Peso</span>
        <button type="button" aria-label="Disminuir peso" class="stepper-btn" @click="bumpWeight(-weightStep)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <input
          v-if="editingWeight"
          ref="weightInput"
          type="number"
          inputmode="decimal"
          :step="weightStep"
          min="0"
          :value="weight"
          class="stepper-input"
          autofocus
          @input="onWeightInput"
          @blur="endEditWeight"
          @keydown.enter="endEditWeight"
        />
        <div v-else class="val" @click="startEditWeight" role="button" tabindex="0" @keydown.enter="startEditWeight">
          {{ weightDisplay }}<span class="target">{{ weightUnit }}</span>
        </div>
        <button type="button" aria-label="Aumentar peso" class="stepper-btn" @click="bumpWeight(+weightStep)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
      </div>

      <!-- REPS stepper -->
      <div class="stepper">
        <span class="stepper-label">Reps</span>
        <button type="button" aria-label="Disminuir reps" class="stepper-btn" @click="bumpReps(-1)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <input
          v-if="editingReps"
          type="number"
          inputmode="numeric"
          step="1"
          min="0"
          :value="reps"
          class="stepper-input"
          autofocus
          @input="onRepsInput"
          @blur="endEditReps"
          @keydown.enter="endEditReps"
        />
        <div v-else class="val" @click="startEditReps" role="button" tabindex="0" @keydown.enter="startEditReps">
          {{ repsDisplay }}<span class="target">{{ targetRepsLabel || '—' }}</span>
        </div>
        <button type="button" aria-label="Aumentar reps" class="stepper-btn" @click="bumpReps(+1)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
      </div>
    </template>

    <!-- CARDIO variant -->
    <template v-else>
      <div class="stepper">
        <span class="stepper-label">Tiempo</span>
        <button type="button" class="stepper-btn" @click="bumpDuration(-30)" aria-label="Menos tiempo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <input
          type="number"
          inputmode="numeric"
          step="30"
          min="0"
          :value="duration"
          class="stepper-input stepper-input--always"
          @input="onDurationInput"
        />
        <span class="cardio-unit">seg</span>
        <button type="button" class="stepper-btn" @click="bumpDuration(+30)" aria-label="Más tiempo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
      </div>
      <div class="stepper">
        <span class="stepper-label">Vel · Inc</span>
        <button type="button" class="stepper-btn" @click="bumpSpeed(-0.5)" aria-label="Menos velocidad">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <div class="cardio-pair">
          <input type="number" inputmode="decimal" step="0.5" min="0" :value="speed"   class="cardio-input" @input="onSpeedInput"   placeholder="0" aria-label="Velocidad km/h" />
          <span class="cardio-sep">·</span>
          <input type="number" inputmode="decimal" step="1"   min="0" :value="incline" class="cardio-input" @input="onInclineInput" placeholder="0" aria-label="Inclinación %" />
        </div>
        <button type="button" class="stepper-btn" @click="bumpSpeed(+0.5)" aria-label="Más velocidad">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
      </div>
    </template>

    <!-- Complete -->
    <button
      type="button"
      class="complete-btn"
      :class="{
        'complete-btn--active': state === 'active',
        'complete-btn--done':   state === 'completed',
      }"
      :aria-label="state === 'completed' ? 'Deshacer set' : 'Completar set'"
      @click="onCompleteClick"
    >
      <svg v-if="state === 'completed'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 6L9 17l-5-5"/>
      </svg>
      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 6L9 17l-5-5"/>
      </svg>
    </button>
  </div>
</template>

<style scoped>
.wc-set-row {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid transparent;
  padding: 10px 10px 10px 8px;
  gap: 10px;
  border-radius: 16px;
}
.wc-set-row--strength { grid-template-columns: 36px 1fr 1fr 48px; }
.wc-set-row--cardio   { grid-template-columns: 36px 1.2fr 1fr 48px; }

@media (min-width: 1024px) {
  .wc-set-row { grid-template-columns: 44px 1fr 1fr 56px; gap: 14px; padding: 12px 12px 12px 10px; }
  .wc-set-row--cardio { grid-template-columns: 44px 1.2fr 1fr 56px; }
}

.wc-set-row[data-state="active"] {
  background: linear-gradient(180deg, rgba(220,38,38,0.08), rgba(220,38,38,0.02));
  border-color: rgba(239,68,68,0.30);
}
.wc-set-row[data-state="completed"] {
  background: rgba(16,185,129,0.05);
  border-color: rgba(16,185,129,0.18);
}
.wc-set-row[data-state="completed"] .stepper { opacity: 0.7; }

.active-bar {
  position: absolute;
  left: -1px;
  top: 8px;
  bottom: 8px;
  width: 3px;
  border-radius: 999px;
  background: var(--color-wc-accent, #DC2626);
}

.pr-badge {
  position: absolute;
  top: -8px;
  right: 12px;
  z-index: 10;
  background: #F59E0B;
  color: #422006;
  padding: 2px 8px;
  border-radius: 999px;
  font-family: var(--font-display);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  box-shadow: 0 4px 12px -2px rgba(245,158,11,0.5);
}

.set-num {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 18px;
  text-align: center;
  color: var(--color-wc-text-secondary);
  font-variant-numeric: tabular-nums;
}
.wc-set-row[data-state="active"] .set-num    { color: var(--color-wc-accent-glow, #EF4444); }
.wc-set-row[data-state="completed"] .set-num { color: #10B981; }

/* Stepper container — replica del target HTML */
.stepper {
  display: grid;
  grid-template-columns: 44px 1fr 44px;
  align-items: center;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--color-wc-border);
  border-radius: 16px;
  height: 56px;
  overflow: hidden;
  position: relative;
}
.wc-set-row[data-state="active"] .stepper {
  background: rgba(255,255,255,0.05);
  border-color: rgba(239,68,68,0.25);
}
.wc-set-row[data-state="completed"] .stepper {
  background: rgba(16,185,129,0.04);
  border-color: rgba(16,185,129,0.14);
}

.stepper-label {
  position: absolute;
  top: 6px;
  left: 50%;
  transform: translateX(-50%);
  font-family: var(--font-display);
  font-size: 9px;
  font-weight: 500;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  pointer-events: none;
  z-index: 1;
}

.stepper-btn {
  height: 100%;
  min-height: 44px;
  display: grid;
  place-items: center;
  color: var(--color-wc-text-secondary);
  background: transparent;
  border: 0;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}
.stepper-btn:hover  { background: rgba(255,255,255,0.05); color: var(--color-wc-text); }
.stepper-btn:active { background: rgba(220,38,38,0.18); color: var(--color-wc-accent-glow, #EF4444); }
.stepper-btn svg    { width: 16px; height: 16px; }

/* Display value (no editando) — replica target HTML */
.val {
  width: 100%;
  height: 100%;
  background: transparent;
  border: 0;
  text-align: center;
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 22px;
  color: var(--color-wc-text);
  font-variant-numeric: tabular-nums;
  outline: none;
  padding-top: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  line-height: 1.05;
  cursor: text;
  -webkit-user-select: none;
  user-select: none;
}
.val:hover { background: rgba(255,255,255,0.02); }
.val .target {
  font-family: var(--font-mono);
  font-size: 10px;
  font-weight: 400;
  color: var(--color-wc-text-tertiary);
  margin-top: -2px;
  letter-spacing: 0.04em;
  display: block;
}

/* Input editable */
.stepper-input {
  width: 100%;
  height: 100%;
  background: transparent;
  border: 0;
  text-align: center;
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 22px;
  color: var(--color-wc-text);
  font-variant-numeric: tabular-nums;
  outline: none;
  padding-top: 12px;
}
.stepper-input::-webkit-outer-spin-button,
.stepper-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.stepper-input { -moz-appearance: textfield; }

.stepper-input--always { padding-top: 0; }

/* Cardio pair (vel + inc en mismo stepper) */
.cardio-pair {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding-top: 14px;
}
.cardio-input {
  width: 36px;
  background: transparent;
  border: 0;
  text-align: center;
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 18px;
  color: var(--color-wc-text);
  font-variant-numeric: tabular-nums;
  outline: none;
}
.cardio-input::-webkit-outer-spin-button,
.cardio-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.cardio-input { -moz-appearance: textfield; }
.cardio-sep {
  color: var(--color-wc-text-tertiary);
  font-family: var(--font-mono);
  font-size: 14px;
}
.cardio-unit {
  position: absolute;
  bottom: 8px;
  left: 50%;
  transform: translateX(-50%);
  font-family: var(--font-mono);
  font-size: 10px;
  color: var(--color-wc-text-tertiary);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  pointer-events: none;
}

/* Complete button */
.complete-btn {
  width: 48px;
  height: 56px;
  border-radius: 16px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--color-wc-border);
  display: grid;
  place-items: center;
  color: var(--color-wc-text-tertiary);
  transition: all 0.2s var(--ease-out);
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}
@media (min-width: 1024px) { .complete-btn { width: 56px; } }
.complete-btn svg { width: 22px; height: 22px; }

.complete-btn--active {
  background: var(--color-wc-accent, #DC2626);
  border-color: transparent;
  color: white;
  box-shadow:
    0 0 0 0 rgba(239,68,68,0.6),
    0 6px 20px -4px rgba(220,38,38,0.6);
  animation: wc-pulse-set-btn 2.4s ease-out infinite;
}
@keyframes wc-pulse-set-btn {
  0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4), 0 6px 20px -4px rgba(220,38,38,0.6); }
  50%      { box-shadow: 0 0 0 8px rgba(239,68,68,0), 0 6px 20px -4px rgba(220,38,38,0.5); }
}
.complete-btn--done {
  background: #10B981;
  border-color: transparent;
  color: #042f24;
  box-shadow: 0 4px 14px -4px rgba(16,185,129,0.45);
}

/* Shake on validation error */
@keyframes wc-shake {
  0%, 100%  { transform: translateX(0); }
  20%, 60%  { transform: translateX(-4px); }
  40%, 80%  { transform: translateX(4px); }
}
.wc-shake { animation: wc-shake 0.4s ease-out; border-color: rgba(239,68,68,0.5) !important; }

/* Mobile breakpoints — viewport pequeño */
@media (max-width: 380px) {
  .wc-set-row--strength { grid-template-columns: 30px 1fr 1fr 44px; gap: 6px; padding: 8px 8px 8px 6px; }
  .wc-set-row--cardio   { grid-template-columns: 30px 1.3fr 1fr 44px; gap: 6px; padding: 8px 8px 8px 6px; }
  .stepper { height: 52px; grid-template-columns: 40px 1fr 40px; }
  .stepper-btn { min-height: 40px; }
  .val, .stepper-input { font-size: 19px; padding-top: 11px; }
  .complete-btn { width: 44px; height: 52px; }
  .complete-btn svg { width: 18px; height: 18px; }
  .set-num { font-size: 15px; }
  .cardio-input { width: 28px; font-size: 15px; }
}

@media (prefers-reduced-motion: reduce) {
  .complete-btn--active { animation: none !important; }
  .wc-shake { animation: none !important; }
}
</style>
