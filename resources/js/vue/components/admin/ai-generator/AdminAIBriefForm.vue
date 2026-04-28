<script setup>
import { ref, computed, watch } from 'vue';
import { useAdminAIGeneratorStore } from '../../../stores/adminAIGenerator';

const props = defineProps({
    isStreaming: { type: Boolean, default: false },
});
const emit = defineEmits(['generate']);

const store = useAdminAIGeneratorStore();

const PLAN_TYPES = [
    { key: 'entrenamiento', label: 'Entrenamiento' },
    { key: 'nutricion',     label: 'Nutrición' },
    { key: 'habitos',       label: 'Hábitos' },
    { key: 'combinado',     label: 'Plan combinado' },
];

const TRAINING_METHODS = [
    'Progressive Overload', 'DUP', 'Periodización por bloques', 'Periodización lineal',
    '5/3/1 Wendler', 'PPL (Push/Pull/Legs)', 'Upper/Lower split', 'Full Body',
    'GVT (German Volume Training)', 'HIIT', 'Calistenia', 'Powerlifting', 'Hipertrofia enfocada',
];
const NUTRITION_METHODS = [
    'Flexible Dieting (IIFYM)', 'Keto', 'Reverse Diet', 'Mediterránea',
];
const HABIT_AREAS = ['Sueño', 'Hidratación', 'Estrés', 'Movilidad', 'Hábitos alimenticios', 'Recuperación'];

const methodOptions = computed(() => {
    if (store.brief.plan_type === 'entrenamiento') return TRAINING_METHODS;
    if (store.brief.plan_type === 'nutricion')     return NUTRITION_METHODS;
    if (store.brief.plan_type === 'combinado')     return [...TRAINING_METHODS, ...NUTRITION_METHODS];
    return [];
});

// Client autocomplete
const clientQuery = ref(store.brief.target_client_name || '');
const showClientResults = ref(false);
let searchTimer = null;

watch(clientQuery, (q) => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        store.searchClients(q);
        showClientResults.value = true;
    }, 220);
});

function pickClient(c) {
    store.setBrief({ target_client_id: c.id, target_client_name: c.name });
    clientQuery.value = c.name;
    showClientResults.value = false;
}
function clearClient() {
    store.setBrief({ target_client_id: null, target_client_name: '' });
    clientQuery.value = '';
}

function toggleHabit(h) {
    const set = new Set(store.brief.habit_focus_areas || []);
    if (set.has(h)) set.delete(h); else set.add(h);
    store.setBrief({ habit_focus_areas: Array.from(set) });
}

function onGenerate() {
    if (props.isStreaming) return;
    if (!store.canGenerate) return;
    emit('generate');
}
</script>

<template>
  <section class="brief-card">
    <header class="brief-head">
      <p class="brief-eyebrow">BRIEF DEL PLAN</p>
      <h2 class="brief-title">Configurar generación</h2>
      <p class="brief-tagline">"La precisión del brief define la precisión del plan."</p>
    </header>

    <!-- Plan type -->
    <div class="brief-row">
      <label class="brief-label">Tipo de plan</label>
      <div class="brief-pill-group">
        <button
          v-for="pt in PLAN_TYPES"
          :key="pt.key"
          type="button"
          class="brief-pill"
          :class="{ 'brief-pill--active': store.brief.plan_type === pt.key }"
          :disabled="isStreaming"
          @click="store.setBrief({ plan_type: pt.key, methodology: '' })"
        >{{ pt.label }}</button>
      </div>
    </div>

    <!-- Methodology -->
    <div v-if="methodOptions.length" class="brief-row">
      <label class="brief-label" for="ai-methodology">Metodología</label>
      <select
        id="ai-methodology"
        class="brief-select"
        :value="store.brief.methodology"
        :disabled="isStreaming"
        @change="store.setBrief({ methodology: $event.target.value })"
      >
        <option value="">Sin metodología específica</option>
        <option v-for="m in methodOptions" :key="m" :value="m">{{ m }}</option>
      </select>
    </div>

    <!-- Duration + frequency -->
    <div class="brief-row brief-row--two">
      <div>
        <label class="brief-label" for="ai-weeks">Semanas</label>
        <input
          id="ai-weeks"
          type="number"
          min="1"
          max="52"
          class="brief-input"
          :value="store.brief.duration_weeks"
          :disabled="isStreaming"
          @input="store.setBrief({ duration_weeks: Number($event.target.value) })"
        >
      </div>
      <div v-if="store.brief.plan_type === 'entrenamiento' || store.brief.plan_type === 'combinado'">
        <label class="brief-label" for="ai-freq">Días/semana</label>
        <input
          id="ai-freq"
          type="number"
          min="1"
          max="7"
          class="brief-input"
          :value="store.brief.frequency"
          :disabled="isStreaming"
          @input="store.setBrief({ frequency: Number($event.target.value) })"
        >
      </div>
    </div>

    <!-- Client target -->
    <div class="brief-row brief-row--client">
      <label class="brief-label" for="ai-client">Cliente target (opcional)</label>
      <div class="brief-client-wrap">
        <input
          id="ai-client"
          type="text"
          class="brief-input"
          placeholder="Buscar por nombre o email"
          v-model="clientQuery"
          :disabled="isStreaming"
          @focus="showClientResults = true"
          @blur="setTimeout(() => showClientResults = false, 180)"
        >
        <button
          v-if="store.brief.target_client_id"
          type="button"
          class="brief-client-clear"
          :disabled="isStreaming"
          @click="clearClient"
          aria-label="Limpiar cliente"
        >×</button>
        <ul
          v-if="showClientResults && store.clientResults.length"
          class="brief-client-results"
        >
          <li
            v-for="c in store.clientResults"
            :key="c.id"
            class="brief-client-result"
            @mousedown.prevent="pickClient(c)"
          >
            <span class="brief-client-name">{{ c.name }}</span>
            <span class="brief-client-email">{{ c.email }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Training-specific fields -->
    <template v-if="store.brief.plan_type === 'entrenamiento' || store.brief.plan_type === 'combinado'">
      <div class="brief-row brief-row--two">
        <div>
          <label class="brief-label" for="ai-level">Nivel</label>
          <select
            id="ai-level"
            class="brief-select"
            :value="store.brief.experience_level"
            :disabled="isStreaming"
            @change="store.setBrief({ experience_level: $event.target.value })"
          >
            <option value="principiante">Principiante</option>
            <option value="intermedio">Intermedio</option>
            <option value="avanzado">Avanzado</option>
          </select>
        </div>
        <div>
          <label class="brief-label" for="ai-goal">Objetivo</label>
          <select
            id="ai-goal"
            class="brief-select"
            :value="store.brief.training_goal"
            :disabled="isStreaming"
            @change="store.setBrief({ training_goal: $event.target.value })"
          >
            <option value="hipertrofia">Hipertrofia</option>
            <option value="fuerza">Fuerza máxima</option>
            <option value="resistencia">Resistencia</option>
            <option value="perdida_grasa">Pérdida de grasa</option>
            <option value="rendimiento">Rendimiento deportivo</option>
          </select>
        </div>
      </div>

      <div class="brief-row">
        <label class="brief-label" for="ai-injuries">Lesiones / restricciones</label>
        <textarea
          id="ai-injuries"
          rows="2"
          class="brief-textarea"
          placeholder="Hombro derecho con tendinitis, evitar press militar..."
          :value="store.brief.injuries"
          :disabled="isStreaming"
          @input="store.setBrief({ injuries: $event.target.value })"
        ></textarea>
      </div>
    </template>

    <!-- Nutrition-specific fields -->
    <template v-if="store.brief.plan_type === 'nutricion' || store.brief.plan_type === 'combinado'">
      <div class="brief-row brief-row--two">
        <div>
          <label class="brief-label" for="ai-cal">Calorías objetivo</label>
          <input
            id="ai-cal"
            type="number"
            min="800"
            max="10000"
            placeholder="Ej. 2200"
            class="brief-input"
            :value="store.brief.calorie_target ?? ''"
            :disabled="isStreaming"
            @input="store.setBrief({ calorie_target: $event.target.value ? Number($event.target.value) : null })"
          >
        </div>
        <div>
          <label class="brief-label" for="ai-meals">Comidas/día</label>
          <input
            id="ai-meals"
            type="number"
            min="1"
            max="10"
            class="brief-input"
            :value="store.brief.meals_per_day"
            :disabled="isStreaming"
            @input="store.setBrief({ meals_per_day: Number($event.target.value) })"
          >
        </div>
      </div>

      <div class="brief-row">
        <label class="brief-label" for="ai-diet">Restricciones dietéticas</label>
        <textarea
          id="ai-diet"
          rows="2"
          class="brief-textarea"
          placeholder="Vegetariano, alergia a frutos secos, presupuesto bajo..."
          :value="store.brief.dietary_restrictions"
          :disabled="isStreaming"
          @input="store.setBrief({ dietary_restrictions: $event.target.value })"
        ></textarea>
      </div>
    </template>

    <!-- Habits-specific fields -->
    <template v-if="store.brief.plan_type === 'habitos' || store.brief.plan_type === 'combinado'">
      <div class="brief-row">
        <label class="brief-label">Áreas de hábito</label>
        <div class="brief-pill-group">
          <button
            v-for="h in HABIT_AREAS"
            :key="h"
            type="button"
            class="brief-pill brief-pill--small"
            :class="{ 'brief-pill--active': (store.brief.habit_focus_areas || []).includes(h) }"
            :disabled="isStreaming"
            @click="toggleHabit(h)"
          >{{ h }}</button>
        </div>
      </div>
    </template>

    <!-- Preferences (always available) -->
    <div class="brief-row">
      <label class="brief-label" for="ai-prefs">Preferencias adicionales</label>
      <textarea
        id="ai-prefs"
        rows="2"
        class="brief-textarea"
        placeholder="Equipos disponibles, estilo de entreno favorito, alimentos que rechaza..."
        :value="store.brief.preferences"
        :disabled="isStreaming"
        @input="store.setBrief({ preferences: $event.target.value })"
      ></textarea>
    </div>

    <!-- CTA -->
    <button
      type="button"
      class="brief-cta"
      :class="{ 'brief-cta--streaming': isStreaming }"
      :disabled="!store.canGenerate || isStreaming"
      @click="onGenerate"
    >
      <span class="brief-cta-dot" v-if="isStreaming"></span>
      <span>{{ isStreaming ? 'Generando...' : 'Generar plan' }}</span>
    </button>

    <p v-if="!store.canGenerate" class="brief-hint">
      Selecciona el tipo de plan y la duración para empezar.
    </p>
  </section>
</template>

<style scoped>
.brief-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    position: relative;
    z-index: 1;
}
.brief-head {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--color-wc-border);
}
.brief-eyebrow {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-tertiary);
    margin: 0;
    text-transform: uppercase;
}
.brief-title {
    font-family: var(--font-display);
    font-size: 24px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0;
    line-height: 1.05;
}
.brief-tagline {
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 12px;
    color: var(--color-wc-gold);
    margin: 0;
    line-height: 1.45;
}
.brief-row {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.brief-row--two {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.brief-row--client {
    position: relative;
}
.brief-label {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
}
.brief-input,
.brief-select,
.brief-textarea {
    width: 100%;
    height: 36px;
    padding: 0 12px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 13px;
    transition: border-color 0.15s var(--ease-out), background 0.15s var(--ease-out);
}
.brief-textarea {
    height: auto;
    padding: 8px 12px;
    line-height: 1.5;
    resize: vertical;
    min-height: 60px;
}
.brief-input:focus,
.brief-select:focus,
.brief-textarea:focus {
    outline: none;
    border-color: var(--color-wc-border-2);
    background: rgba(255, 255, 255, 0.05);
}
.brief-input:disabled,
.brief-select:disabled,
.brief-textarea:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.brief-pill-group {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.brief-pill {
    height: 32px;
    padding: 0 14px;
    border-radius: 999px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.02);
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out), border-color 0.15s var(--ease-out), color 0.15s var(--ease-out);
}
.brief-pill--small { height: 28px; padding: 0 11px; font-size: 9px; }
.brief-pill:hover:not(:disabled) {
    border-color: var(--color-wc-border-2);
    color: var(--color-wc-text);
}
.brief-pill--active {
    background: var(--color-wc-red-soft);
    border-color: var(--color-wc-accent);
    color: var(--color-wc-text);
}
.brief-pill:disabled { opacity: 0.5; cursor: not-allowed; }

.brief-client-wrap {
    position: relative;
}
.brief-client-clear {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 1px solid var(--color-wc-border);
    background: rgba(0, 0, 0, 0.4);
    color: var(--color-wc-text-tertiary);
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
}
.brief-client-clear:hover { color: var(--color-wc-text); }
.brief-client-results {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 30;
    background: var(--color-wc-bg-tertiary);
    border: 1px solid var(--color-wc-border);
    border-radius: 10px;
    list-style: none;
    margin: 0;
    padding: 4px;
    max-height: 220px;
    overflow-y: auto;
}
.brief-client-result {
    padding: 8px 10px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 1px;
    transition: background 0.12s var(--ease-out);
}
.brief-client-result:hover { background: rgba(255, 255, 255, 0.04); }
.brief-client-name {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text);
}
.brief-client-email {
    font-family: var(--font-mono);
    font-size: 10px;
    color: var(--color-wc-text-tertiary);
    letter-spacing: 0.04em;
}

.brief-cta {
    margin-top: 4px;
    height: 44px;
    border-radius: 10px;
    border: 1px solid var(--color-wc-accent);
    background: var(--color-wc-accent);
    color: #fff;
    font-family: var(--font-mono);
    font-size: 11px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: opacity 0.15s var(--ease-out), background 0.15s var(--ease-out), transform 0.15s var(--ease-out);
}
.brief-cta:hover:not(:disabled) {
    background: #B91C1C;
    transform: translateY(-1px);
}
.brief-cta:disabled { opacity: 0.5; cursor: not-allowed; }
.brief-cta--streaming {
    background: rgba(220, 38, 38, 0.2);
    border-color: var(--color-wc-accent);
    color: var(--color-wc-text);
}
.brief-cta-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--color-wc-accent);
    animation: brief-pulse 1.2s var(--ease-out) infinite;
}
@keyframes brief-pulse {
    0%, 100% { opacity: 0.4; transform: scale(0.85); }
    50%      { opacity: 1;   transform: scale(1.15); }
}
.brief-hint {
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 11px;
    color: var(--color-wc-text-tertiary);
    margin: 0;
    text-align: center;
    line-height: 1.5;
}

@media (prefers-reduced-motion: reduce) {
    .brief-cta-dot { animation: none !important; }
}
</style>
