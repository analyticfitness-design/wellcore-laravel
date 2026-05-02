<script setup>
import { ref, watch } from 'vue';
import { useApi } from '../../../composables/useApi';

const props = defineProps({
    open: { type: Boolean, default: false },
    plan: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const api = useApi();

const loading     = ref(false);
const fullPlan    = ref(null);
const showJson    = ref(false);

const TYPE_LABELS = {
    entrenamiento: 'Entrenamiento', nutricion: 'Nutricion',
    habitos: 'Habitos', suplementacion: 'Suplementacion', ciclo: 'Ciclo',
};

watch(() => props.open, async (opened) => {
    if (!opened) { fullPlan.value = null; showJson.value = false; return; }
    if (!props.plan) return;
    loading.value = true;
    try {
        const res = await api.get(`/api/v/admin/plans/${props.plan.id}`);
        fullPlan.value = res.data.plan ?? null;
    } catch {
        fullPlan.value = null;
    } finally {
        loading.value = false;
    }
});

function close() { emit('close'); }

function jsonText() {
    if (!fullPlan.value?.content_json) return '';
    return JSON.stringify(fullPlan.value.content_json, null, 2);
}

function getWeeks(p)  { return p?.content_json?.weeks       ?? []; }
function getMeals(p)  { return p?.content_json?.meal_plan   ?? []; }
function getHabits(p) { return p?.content_json?.habits      ?? []; }
function getItems(p)  { return p?.content_json?.supplements ?? p?.content_json?.items ?? []; }
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="open" class="modal-backdrop" role="dialog" aria-modal="true" aria-label="Ver template">
        <div class="modal-overlay" @click="close"></div>

        <Transition name="modal-slide">
          <div v-if="open" class="modal-panel">
            <!-- Loading -->
            <template v-if="loading">
              <div class="modal-loading-state">
                <div class="loading-bar"></div>
                <div class="loading-bar" style="width: 70%"></div>
                <div class="loading-bar" style="width: 50%"></div>
              </div>
            </template>

            <!-- Content -->
            <template v-else-if="fullPlan">
              <div class="view-header">
                <div class="view-header-text">
                  <h2 class="view-name">{{ fullPlan.name.toUpperCase() }}</h2>
                  <div class="view-badges">
                    <span v-if="fullPlan.plan_type" class="view-type">{{ TYPE_LABELS[fullPlan.plan_type] ?? fullPlan.plan_type }}</span>
                    <span v-if="fullPlan.ai_generated" class="view-badge view-badge--ai">AI</span>
                    <span v-if="fullPlan.is_public" class="view-badge view-badge--pub">PUBLICO</span>
                    <span v-if="fullPlan.coach_name" class="view-coach">por {{ fullPlan.coach_name }}</span>
                  </div>
                </div>
                <button type="button" class="modal-close" aria-label="Cerrar" @click="close">
                  <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>

              <!-- Metadata grid -->
              <div class="meta-grid">
                <div class="meta-cell">
                  <span class="meta-label">Metodologia</span>
                  <span class="meta-value">{{ fullPlan.methodology || '—' }}</span>
                </div>
                <div class="meta-cell">
                  <span class="meta-label">Coach</span>
                  <span class="meta-value">{{ fullPlan.coach_name || '—' }}</span>
                </div>
                <div class="meta-cell">
                  <span class="meta-label">Creado</span>
                  <span class="meta-value">{{ fullPlan.created_at }}</span>
                </div>
                <div class="meta-cell">
                  <span class="meta-label">Actualizado</span>
                  <span class="meta-value">{{ fullPlan.updated_at }}</span>
                </div>
              </div>

              <!-- Description -->
              <div v-if="fullPlan.description" class="view-desc-card">
                <span class="meta-label">Descripcion</span>
                <p class="view-desc-text">{{ fullPlan.description }}</p>
              </div>

              <!-- Content block -->
              <div class="content-block">
                <div class="content-block-header">
                  <span class="meta-label">Contenido del plan</span>
                  <button type="button" class="json-toggle" @click="showJson = !showJson">
                    {{ showJson ? 'Vista estructurada' : 'Ver JSON' }}
                  </button>
                </div>

                <!-- Structured view -->
                <div v-show="!showJson">
                  <!-- Entrenamiento -->
                  <template v-if="fullPlan.plan_type === 'entrenamiento' && getWeeks(fullPlan).length">
                    <div v-for="(week, wi) in getWeeks(fullPlan)" :key="wi" class="content-week">
                      <p class="week-label">Semana {{ week.week ?? wi + 1 }}</p>
                      <div v-for="(session, si) in (week.sessions ?? [])" :key="si" class="session-card">
                        <p class="session-name">{{ session.name ?? ('Sesion ' + (si + 1)) }}</p>
                        <div class="exercise-list">
                          <div v-for="(ex, ei) in (session.exercises ?? [])" :key="ei" class="exercise-row">
                            <span class="exercise-name">{{ ex.name ?? ex.exercise ?? '—' }}</span>
                            <span class="exercise-sets">{{ ex.sets ? ex.sets + ' x ' : '' }}{{ ex.reps ?? ex.duration ?? '' }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- Nutricion -->
                  <template v-else-if="fullPlan.plan_type === 'nutricion' && getMeals(fullPlan).length">
                    <div v-for="(meal, mi) in getMeals(fullPlan)" :key="mi" class="session-card">
                      <p class="session-name">{{ meal.name ?? ('Comida ' + (mi + 1)) }}</p>
                      <div class="exercise-list">
                        <div v-for="(food, fi) in (meal.foods ?? [])" :key="fi" class="exercise-row">
                          <span class="exercise-name">{{ food.name ?? food.food ?? '—' }}</span>
                          <span class="exercise-sets">{{ food.amount ?? food.quantity ?? '' }}</span>
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- Habitos -->
                  <template v-else-if="fullPlan.plan_type === 'habitos' && getHabits(fullPlan).length">
                    <div class="habit-list">
                      <div v-for="(habit, hi) in getHabits(fullPlan)" :key="hi" class="habit-row">
                        <span class="habit-dot" aria-hidden="true"></span>
                        <span class="habit-name">{{ habit.name ?? habit.habit ?? habit }}</span>
                        <span v-if="habit.frequency" class="habit-freq">{{ habit.frequency }}</span>
                      </div>
                    </div>
                  </template>

                  <!-- Suplementacion / Ciclo -->
                  <template v-else-if="getItems(fullPlan).length">
                    <div class="habit-list">
                      <div v-for="(item, ii) in getItems(fullPlan)" :key="ii" class="exercise-row">
                        <span class="exercise-name">{{ item.name ?? item.supplement ?? item }}</span>
                        <span v-if="item.dose ?? item.amount" class="exercise-sets">{{ item.dose ?? item.amount }}</span>
                      </div>
                    </div>
                  </template>

                  <p v-else class="no-content">Sin contenido estructurado reconocible.</p>
                </div>

                <!-- JSON view -->
                <div v-show="showJson">
                  <pre class="json-pre">{{ jsonText() }}</pre>
                </div>
              </div>
            </template>

            <!-- Error / empty -->
            <template v-else>
              <div class="empty-state-view">
                <p class="empty-view-msg">No se pudo cargar el template.</p>
                <button type="button" class="btn-cancel" @click="close">Cerrar</button>
              </div>
            </template>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed; inset: 0; z-index: 200;
    display: flex; align-items: flex-end; justify-content: center; padding: 16px;
}
@media (min-width: 640px) { .modal-backdrop { align-items: center; } }

.modal-overlay {
    position: absolute; inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(4px);
}

.modal-panel {
    position: relative; z-index: 1;
    width: 100%; max-width: 720px; max-height: 90vh;
    overflow-y: auto;
    border-radius: var(--r-md, 16px);
    border: 1px solid rgba(255,255,255,0.12);
    background: var(--c-surface);
    padding: 24px;
    box-shadow: 0 32px 80px rgba(0, 0, 0, 0.6);
    display: flex; flex-direction: column; gap: 16px;
}

.modal-loading-state {
    display: flex; flex-direction: column; gap: 10px;
    padding: 40px 0;
}
.loading-bar {
    height: 14px; border-radius: 6px;
    background: var(--c-surface-2);
    animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse { 0%, 100% { opacity: 0.5; } 50% { opacity: 0.85; } }

/* Header */
.view-header {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
}
.view-header-text { display: flex; flex-direction: column; gap: 6px; }
.view-name {
    font-family: var(--font-display);
    font-size: 24px; letter-spacing: 0.04em;
    color: var(--c-text); margin: 0;
}
.view-badges { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.view-type {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase;
    padding: 3px 9px; border-radius: var(--r-pill, 999px);
    background: var(--c-accent-dim); color: #F87171;
}
.view-badge {
    font-family: var(--font-display);
    font-size: 8px; letter-spacing: 1.6px; text-transform: uppercase;
    padding: 2px 7px; border-radius: var(--r-pill, 999px);
}
.view-badge--ai  { background: rgba(139,92,246,0.12); color: #A78BFA; }
.view-badge--pub { background: rgba(16,185,129,0.10); color: #34D399; }
.view-coach { font-family: var(--font-sans); font-size: 11px; color: var(--c-text-3); }
.modal-close {
    flex-shrink: 0; width: 32px; height: 32px; border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border); background: transparent;
    color: var(--c-text-2);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: color 0.15s ease, border-color 0.15s ease;
}
.modal-close:hover { color: var(--c-text); border-color: rgba(255,255,255,0.12); }

/* Meta grid */
.meta-grid {
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;
}
@media (min-width: 480px) { .meta-grid { grid-template-columns: repeat(4, 1fr); } }
.meta-cell {
    border-radius: 10px; border: 1px solid var(--c-border);
    background: var(--c-surface-2); padding: 10px 12px;
    display: flex; flex-direction: column; gap: 4px;
}
.meta-label {
    font-family: var(--font-display);
    font-size: 8px; letter-spacing: 1.6px; text-transform: uppercase;
    color: var(--c-text-3);
}
.meta-value { font-family: var(--font-sans); font-size: 12px; color: var(--c-text); }

/* Description */
.view-desc-card {
    border-radius: 10px; border: 1px solid var(--c-border);
    background: var(--c-surface-2); padding: 12px;
    display: flex; flex-direction: column; gap: 6px;
}
.view-desc-text { font-family: var(--font-sans); font-size: 13px; color: var(--c-text); margin: 0; line-height: 1.55; }

/* Content block */
.content-block {
    border-radius: 10px; border: 1px solid var(--c-border);
    background: var(--c-surface-2); padding: 14px;
    display: flex; flex-direction: column; gap: 12px;
}
.content-block-header { display: flex; align-items: center; justify-content: space-between; }
.json-toggle {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 1.2px; text-transform: uppercase;
    color: var(--c-text-3); background: transparent; border: none; cursor: pointer;
    transition: color 0.15s ease;
}
.json-toggle:hover { color: var(--c-text); }
.json-pre {
    border-radius: var(--r-sm, 12px); background: var(--c-surface);
    padding: 12px; max-height: 320px; overflow-y: auto;
    font-family: var(--font-display); font-size: 11px;
    line-height: 1.6; color: var(--c-text);
}

.content-week { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
.week-label {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase;
    color: var(--c-text-2);
}
.session-card {
    border-radius: var(--r-sm, 12px); border: 1px solid var(--c-border);
    background: var(--c-surface); padding: 10px; margin-bottom: 6px;
    display: flex; flex-direction: column; gap: 6px;
}
.session-name { font-family: var(--font-sans); font-size: 12px; font-weight: 500; color: var(--c-text); margin: 0; }
.exercise-list { display: flex; flex-direction: column; gap: 3px; }
.exercise-row {
    display: flex; align-items: center; justify-content: space-between;
    border-radius: 6px; background: rgba(255,255,255,0.03);
    padding: 4px 8px;
}
.exercise-name { font-family: var(--font-sans); font-size: 11px; color: var(--c-text); }
.exercise-sets { font-family: var(--font-display); font-size: 11px; color: var(--c-text-3); font-feature-settings: 'tnum' 1; }

.habit-list { display: flex; flex-direction: column; gap: 4px; }
.habit-row {
    display: flex; align-items: center; gap: 10px;
    border-radius: 6px; border: 1px solid var(--c-border);
    background: var(--c-surface); padding: 6px 10px;
}
.habit-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--c-accent); flex-shrink: 0; }
.habit-name { flex: 1; font-family: var(--font-sans); font-size: 12px; color: var(--c-text); }
.habit-freq { font-family: var(--font-display); font-size: 10px; color: var(--c-text-3); }

.no-content { font-family: var(--font-sans); font-size: 12px; color: var(--c-text-3); font-style: italic; }

/* Empty / Error */
.empty-state-view {
    display: flex; flex-direction: column; align-items: center; gap: 16px;
    padding: 40px 0;
}
.empty-view-msg { font-family: var(--font-sans); font-size: 13px; color: var(--c-text-3); }
.btn-cancel {
    height: 36px; padding: 0 20px;
    border-radius: var(--r-sm, 12px); border: 1px solid var(--c-border); background: transparent;
    color: var(--c-text-2); font-family: var(--font-sans); font-size: 13px; cursor: pointer;
    transition: color 0.15s ease, border-color 0.15s ease;
}
.btn-cancel:hover { color: var(--c-text); border-color: rgba(255,255,255,0.12); }

/* Transitions */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-slide-enter-active, .modal-slide-leave-active { transition: transform 0.25s var(--ease-out, ease), opacity 0.25s ease; }
.modal-slide-enter-from, .modal-slide-leave-to { transform: translateY(32px); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .modal-fade-enter-active, .modal-fade-leave-active,
    .modal-slide-enter-active, .modal-slide-leave-active { transition: none !important; }
    .loading-bar { animation: none !important; }
}
</style>
