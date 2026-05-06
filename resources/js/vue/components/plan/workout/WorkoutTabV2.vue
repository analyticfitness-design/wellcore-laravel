<template>
  <div class="workout-v2" data-testid="workout-tab-v2">
    <!-- LOADING -->
    <PlanSkeleton v-if="loading" />

    <!-- ERROR -->
    <PlanErrorState
      v-else-if="error"
      :message="errorMessage"
      @retry="$emit('retry')"
    />

    <!-- LOCKED / EXPIRED -->
    <PlanLockOverlay
      v-else-if="isPlanLocked"
      :expires-at="planExpiresAt"
      :reason="lockReason"
      @renew="onRenew"
    />

    <!-- EMPTY (no plan, no semanas) -->
    <PlanEmptyState
      v-else-if="isEmpty"
      :coach-name="coachShortName"
    />

    <!-- OK: content completo -->
    <template v-else>
      <PlanHeroV2
        :plan-type="clientPlanType"
        :current-week="currentWeek"
        :total-weeks="effectiveTotalWeeks"
        :dias-semana="diasSemanaMeta"
        :volumen-label="trainingPlan?.volumen_label || ''"
        :total-series-semana="trainingPlan?.total_series_semana || null"
        :rir-objetivo="trainingPlan?.rir_objetivo || ''"
        :semanas="semanas"
      />

      <CoachQuoteV2
        v-if="coachMessage"
        :coach-name="coachDisplayName"
        :message="coachMessage"
        :total-weeks="effectiveTotalWeeks"
        :time-ago="''"
        :can-reply="false"
      />

      <PlanObjetivoBanner
        v-if="objetivoBloque"
        :objetivo="objetivoBloque"
      />

      <WeeklyScheduleOverview
        v-if="weeklySchedule.length"
        :days="weeklySchedule"
      />

      <!-- Section label "N semanas" -->
      <div v-if="totalWeeksLabel" class="wp-section-label">
        <span class="lab">{{ totalWeeksLabel }}</span>
        <span class="ln"></span>
        <span class="cnt">{{ currentWeek }} / {{ effectiveTotalWeeks }}</span>
      </div>

      <!-- Week list -->
      <div class="week-list">
        <article
          v-for="(semana, sIdx) in semanas"
          :key="sIdx"
          class="week"
          :class="weekStateClass(sIdx)"
        >
          <button
            type="button"
            class="week-header"
            :aria-expanded="isWeekOpen(sIdx)"
            @click="toggleWeek(sIdx)"
          >
            <div class="week-num">
              <div class="lab">SEM</div>
              <div class="n">{{ String(semana?.numero ?? sIdx + 1).padStart(2, '0') }}</div>
            </div>
            <div class="week-info">
              <div class="week-title-row">
                <span class="week-title">{{ semana?.titulo || `Semana ${sIdx + 1}` }}</span>
                <span v-if="weekPhaseLabel(semana)" class="week-phase" :class="weekPhaseClass(semana)">
                  {{ weekPhaseLabel(semana) }}
                </span>
              </div>
              <div v-if="weekMeta(semana)" class="week-meta">{{ weekMeta(semana) }}</div>
            </div>
            <span v-if="isCurrentWeek(sIdx)" class="week-pill-now">Ahora</span>
            <span v-else-if="semana?.completada" class="week-pill-done">Listo</span>
            <svg
              class="week-chev"
              :class="{ open: isWeekOpen(sIdx) }"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              aria-hidden="true"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            </svg>
          </button>

          <div v-if="isWeekOpen(sIdx)" class="week-body">
            <article
              v-for="(dia, dIdx) in (semana?.dias || [])"
              :key="`${sIdx}-${dIdx}`"
              class="day"
              :class="dayStateClass(semana, dia, sIdx, dIdx)"
            >
              <div class="day-head" @click="toggleDay(sIdx, dIdx)">
                <div class="day-num">
                  <div class="lab">DÍA</div>
                  <div class="n">{{ String(dia?.numero ?? dIdx + 1).padStart(2, '0') }}</div>
                </div>
                <div class="day-info">
                  <div v-if="dayGroups(dia).length" class="day-tags">
                    <span
                      v-for="(g, gi) in dayGroups(dia)"
                      :key="gi"
                      class="tag-grp"
                      :class="`tg-${g.key}`"
                    >
                      <span class="sw" :style="{ background: g.color }"></span>{{ g.label }}
                    </span>
                  </div>
                  <h3 class="day-title">{{ dia?.titulo || dia?.nombre || `Día ${dIdx + 1}` }}</h3>
                  <div v-if="daySubline(dia)" class="day-subline">{{ daySubline(dia) }}</div>
                </div>
                <div v-if="dia?.completado" class="day-status">
                  <span class="status-chip done">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Listo
                  </span>
                </div>
              </div>

              <template v-if="isDayOpen(sIdx, dIdx)">
                <div v-if="dia?.calentamiento" class="warmup">
                  <span class="warmup-lab">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/>
                    </svg>
                    Calent.
                  </span>
                  <p class="warmup-tx">{{ dia.calentamiento }}</p>
                </div>

                <button
                  v-if="shouldShowCtaToday(semana, dia)"
                  type="button"
                  class="cta-today"
                  @click="onTrainNow(semana, dia)"
                >
                  <span class="icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.25 5.65c0-.86.92-1.4 1.67-.99l11.54 6.35a1.13 1.13 0 0 1 0 1.97L6.92 19.34a1.13 1.13 0 0 1-1.67-.99V5.65Z"/></svg>
                  </span>
                  <span class="tx">
                    <span class="lab">{{ dia?.es_hoy ? 'Sesión de hoy' : 'Próxima sesión' }}</span>
                    <span class="ttl">{{ dia?.es_hoy ? 'Entrenar ahora' : 'Empezar' }}</span>
                  </span>
                  <svg class="arrow" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </button>

                <div v-if="(dia?.ejercicios || []).length" class="ex-list">
                  <template v-for="(ej, eIdx) in (dia?.ejercicios || [])" :key="ejKey(ej, eIdx)">
                    <BlockDivider
                      v-if="shouldShowBlockDivider(dia.ejercicios, eIdx)"
                      :type="blockType(ej)"
                      :label="blockLabel(dia.ejercicios, eIdx)"
                      :meta="blockMeta(dia.ejercicios, eIdx)"
                    />
                    <ExerciseRow
                      :ejercicio="ej"
                      :numero="ej?.numero ?? eIdx + 1"
                      :is-in-block="!!(ej?.block_id || ej?.es_superset || ej?.es_circuito)"
                      :is-toggling="isToggling(ej?.id).value"
                      @variation-toggle="handleVariationToggle"
                    />
                  </template>
                </div>

                <CooldownRow
                  v-if="dia?.cooldown"
                  :texto="dia.cooldown"
                />
              </template>
            </article>
          </div>
        </article>
      </div>

      <!-- Sticky CTA mobile (solo cuando hay día de hoy) -->
      <div v-if="todayDayMeta" class="phone-sticky-wrap">
        <button type="button" class="phone-sticky" @click="onTrainNow(todayDayMeta.semana, todayDayMeta.dia)">
          <span class="icn"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.25 5.65c0-.86.92-1.4 1.67-.99l11.54 6.35a1.13 1.13 0 0 1 0 1.97L6.92 19.34a1.13 1.13 0 0 1-1.67-.99V5.65Z"/></svg></span>
          <div class="phone-sticky-tx">
            <span class="lab">Sesión de hoy</span>
            <span class="ttl">Entrenar ahora</span>
          </div>
          <svg class="arrow" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
// WorkoutTabV2 — orquestador del tab Entrenamiento V2.
// Sigue patrón del HTML V2.1 (docs/superpowers/plan-viewer-v2-source/v2.1.html).
// Wired al composable usePlanViewer para el toggle de variation con optimistic UI.
import { ref, computed, watchEffect } from 'vue';
import { useRouter } from 'vue-router';
import PlanSkeleton from './parts/PlanSkeleton.vue';
import PlanErrorState from './parts/PlanErrorState.vue';
import PlanEmptyState from './parts/PlanEmptyState.vue';
import PlanLockOverlay from './parts/PlanLockOverlay.vue';
import PlanHeroV2 from './parts/PlanHeroV2.vue';
import CoachQuoteV2 from './parts/CoachQuoteV2.vue';
import PlanObjetivoBanner from './parts/PlanObjetivoBanner.vue';
import WeeklyScheduleOverview from './parts/WeeklyScheduleOverview.vue';
import BlockDivider from './parts/BlockDivider.vue';
import CooldownRow from './parts/CooldownRow.vue';
import ExerciseRow from './parts/ExerciseRow.vue';
import { usePlanViewer } from '../../../composables/usePlanViewer';

const props = defineProps({
  trainingPlan: { type: Object, default: null },
  clientPlanType: { type: String, default: '' },
  coach: { type: Object, default: null },
  isLocked: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  error: { type: [Object, String, Boolean, null], default: null },
  currentWeek: { type: Number, default: 1 },
  totalWeeks: { type: Number, default: 4 },
});

const emit = defineEmits(['retry', 'open-week-detail']);

const router = useRouter();
const { toggleVariation, isToggling } = usePlanViewer();

const semanas = computed(() => Array.isArray(props.trainingPlan?.semanas) ? props.trainingPlan.semanas : []);
const objetivoBloque = computed(() => {
  return (props.trainingPlan?.objetivo_bloque || props.trainingPlan?.objetivo || '').toString().trim();
});
const weeklySchedule = computed(() => {
  if (Array.isArray(props.trainingPlan?.weekly_schedule)) return props.trainingPlan.weekly_schedule;
  // Defensive fallback: derive from first/current semana
  const sem = semanas.value.find((s) => s?.es_actual) || semanas.value[0];
  if (!sem || !Array.isArray(sem.dias)) return [];
  const LETTERS = ['L','M','X','J','V','S','D'];
  const LABELS = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
  return sem.dias.slice(0, 7).map((d, i) => ({
    day_letter: LETTERS[i] || '·',
    day_label: LABELS[i] || '',
    muscle_groups: d?.titulo || d?.nombre || (Array.isArray(d?.grupos) ? d.grupos.join(' · ') : ''),
  }));
});

const isPlanLocked = computed(() => {
  if (props.isLocked) return true;
  return !!(props.trainingPlan?.is_expired);
});
const planExpiresAt = computed(() => {
  return props.trainingPlan?.expires_at || props.trainingPlan?.plan_end || null;
});
const lockReason = computed(() => {
  return props.trainingPlan?.lock_reason || '';
});

const isEmpty = computed(() => {
  if (!props.trainingPlan) return true;
  if (!semanas.value.length) return true;
  const totalDias = semanas.value.reduce((acc, s) => acc + ((s?.dias?.length) || 0), 0);
  return totalDias === 0;
});

const coachShortName = computed(() => {
  const n = props.coach?.name || props.coach?.nombre || '';
  if (!n) return '';
  return n.split(/\s+/)[0];
});

const coachDisplayName = computed(() => {
  return props.coach?.name || props.coach?.nombre || 'Tu coach';
});

const coachMessage = computed(() => {
  // El mensaje del coach se toma de notas_coach del plan (si existe).
  // El backend lo expone en trainingPlan.notas_coach. Si no hay, no se renderiza.
  const m = props.trainingPlan?.notas_coach
    || props.trainingPlan?.coach_note
    || props.trainingPlan?.notas
    || '';
  return typeof m === 'string' ? m.trim() : '';
});

const diasSemanaMeta = computed(() => {
  const explicit = props.trainingPlan?.dias_semana;
  if (typeof explicit === 'number' && explicit > 0) return explicit;
  const sem = semanas.value.find((s) => s?.es_actual) || semanas.value[0];
  return sem?.dias?.length || null;
});

const errorMessage = computed(() => {
  if (!props.error) return '';
  if (typeof props.error === 'string') return props.error;
  return props.error?.message || '';
});

// Week phase mapping (cls determina el color de la pill)
const PHASE_MAP = {
  acumulacion: { cls: 'acumul' },
  acumul: { cls: 'acumul' },
  intensificacion: { cls: 'intens' },
  intens: { cls: 'intens' },
  pico: { cls: 'pico' },
  deload: { cls: 'deload' },
  descarga: { cls: 'deload' },
};
// La pill secundaria muestra estado de la semana (V2.1: "En curso" si es_actual,
// "Listo" si completada, sino vacía). El titulo de la semana (Acumulación/etc)
// viene server-side y se renderiza en .week-title — no se duplica acá.
function weekPhaseLabel(s) {
  if (!s) return '';
  if (s.es_actual) return 'En curso';
  if (s.completada) return 'Listo';
  return '';
}
function weekPhaseClass(s) {
  if (!s) return '';
  if (s.es_actual) return 'intens';
  if (s.completada) return 'deload';
  const f = String(s?.fase || s?.phase || '').toLowerCase();
  return PHASE_MAP[f]?.cls || '';
}
function weekMeta(s) {
  const dias = (s?.dias?.length) || 0;
  const series = s?.total_series ?? null;
  const minutos = s?.total_minutos ?? null;
  const parts = [];
  if (dias) parts.push(`${dias} días`);
  if (minutos) parts.push(`~${Math.round(minutos / 60)}h ${minutos % 60}m total`);
  if (series) parts.push(`${series} series`);
  return parts.join(' · ');
}

// Week / day open state — Map<sIdx, bool> y Map<`${s}-${d}`, bool>
const openWeeks = ref({});
const openDays = ref({});

watchEffect(() => {
  // Default: open la semana actual
  const list = semanas.value;
  if (!list.length) return;
  const currentIdx = list.findIndex((s) => s?.es_actual);
  const idx = currentIdx >= 0 ? currentIdx : 0;
  if (openWeeks.value[idx] === undefined) {
    openWeeks.value = { ...openWeeks.value, [idx]: true };
  }
  // Default: día 1 (HOY si aplica) abierto en la semana abierta
  const sem = list[idx];
  if (sem?.dias?.length) {
    const todayIdx = sem.dias.findIndex((d) => d?.es_hoy);
    const dayIdx = todayIdx >= 0 ? todayIdx : 0;
    const k = `${idx}-${dayIdx}`;
    if (openDays.value[k] === undefined) {
      openDays.value = { ...openDays.value, [k]: true };
    }
  }
});

function isWeekOpen(idx) {
  return !!openWeeks.value[idx];
}
function toggleWeek(idx) {
  openWeeks.value = { ...openWeeks.value, [idx]: !openWeeks.value[idx] };
}
function isCurrentWeek(idx) {
  const sem = semanas.value[idx];
  if (sem?.es_actual) return true;
  // Fallback: prop currentWeek (1-based)
  return idx === Math.max(0, (props.currentWeek || 1) - 1);
}
function weekStateClass(idx) {
  const sem = semanas.value[idx];
  if (sem?.completada) return 'completed';
  if (isCurrentWeek(idx)) return 'current';
  return '';
}

function isDayOpen(sIdx, dIdx) {
  return !!openDays.value[`${sIdx}-${dIdx}`];
}
function toggleDay(sIdx, dIdx) {
  const k = `${sIdx}-${dIdx}`;
  openDays.value = { ...openDays.value, [k]: !openDays.value[k] };
}
function isDayToday(semana, dia) {
  return !!(dia?.es_hoy);
}

// CTA Entrenar ahora se muestra en cualquier día EXPANDIDO (no solo es_hoy).
// Con label diferente: "Entrenar ahora" si es_hoy, "Empezar" si no.
function shouldShowCtaToday(semana, dia) {
  return !!(dia?.ejercicios?.length);
}
function dayStateClass(semana, dia) {
  if (dia?.es_hoy) return 'today';
  if (dia?.completado) return 'completed';
  return 'upcoming';
}

// Day groups -> color tags
const GROUP_TAG_COLORS = {
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
function dayGroups(dia) {
  const list = Array.isArray(dia?.grupos) ? dia.grupos
    : (typeof dia?.titulo === 'string' ? dia.titulo.split('·').map((s) => s.trim()) : []);
  return list
    .map((g) => {
      const key = String(g || '').toLowerCase().trim();
      if (!key) return null;
      return {
        key,
        label: capitalize(key),
        color: GROUP_TAG_COLORS[key] || 'rgba(255,255,255,0.30)',
      };
    })
    .filter(Boolean)
    .slice(0, 4);
}
function capitalize(s) {
  if (!s) return '';
  return s.charAt(0).toUpperCase() + s.slice(1);
}
function daySubline(dia) {
  const ej = (dia?.ejercicios?.length) || 0;
  const min = dia?.total_minutos ?? null;
  const rir = dia?.rir_promedio ?? null;
  const parts = [];
  if (ej) parts.push(`${ej} EJ.`);
  if (min) parts.push(`~${min} MIN`);
  if (rir) parts.push(`RIR ${rir}`);
  return parts.join(' · ');
}

// Block divider detection — entre ejercicios consecutivos con mismo block_id, mostrar divider antes del primero.
function shouldShowBlockDivider(ejercicios, idx) {
  const cur = ejercicios?.[idx];
  if (!cur) return false;
  const curBlock = cur.block_id ?? null;
  const isBlocky = !!(curBlock || cur.es_superset || cur.es_circuito);
  if (!isBlocky) return false;
  const prev = idx > 0 ? ejercicios[idx - 1] : null;
  const prevBlock = prev?.block_id ?? null;
  return curBlock !== prevBlock;
}
function blockType(ej) {
  if (ej?.es_circuito) return 'circuito';
  return 'superset';
}
function blockLabel(ejercicios, idx) {
  const cur = ejercicios?.[idx];
  return cur?.block_label || cur?.block_id || '';
}
function blockMeta(ejercicios, idx) {
  const cur = ejercicios?.[idx];
  const bid = cur?.block_id;
  if (!bid) return '';
  let count = 0;
  for (const ej of ejercicios) {
    if (ej?.block_id === bid) count++;
  }
  if (!count) return '';
  return `${count} ejercicios`;
}

function ejKey(ej, idx) {
  return ej?.id ?? `idx-${idx}`;
}

// Section labels
const effectiveTotalWeeks = computed(() => {
  return semanas.value.length || props.totalWeeks || 4;
});
const totalWeeksLabel = computed(() => {
  const n = effectiveTotalWeeks.value;
  return n > 0 ? `${n} ${n === 1 ? 'semana' : 'semanas'}` : '';
});

// Sticky CTA on mobile — solo si hay día de hoy en alguna semana
const todayDayMeta = computed(() => {
  for (const semana of semanas.value) {
    const idx = (semana?.dias || []).findIndex((d) => d?.es_hoy);
    if (idx >= 0) return { semana, dia: semana.dias[idx] };
  }
  return null;
});

function onTrainNow(semana, dia) {
  // Reusa la ruta existente del workout player. Si no existe, no falla.
  try {
    router.push({ name: 'client.workout-player', params: { semana: semana?.numero, dia: dia?.numero } });
  } catch {
    // fallback: navega via window.location
    if (semana?.numero && dia?.numero) {
      window.location.href = `/client/workout/play?week=${semana.numero}&day=${dia.numero}`;
    }
  }
}

function onRenew() {
  if (typeof window !== 'undefined') window.location.href = '/planes';
}

async function handleVariationToggle(exerciseId, useVariant) {
  await toggleVariation(exerciseId, useVariant);
  // El backend persiste; el padre se encargará de re-fetch en su próximo refresh.
  // Aquí no re-fetchamos para evitar layout shift.
}
</script>

<style scoped>
.workout-v2 {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.wp-section-label {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 6px;
  margin-bottom: 6px;
}
.wp-section-label .lab {
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 11px;
  letter-spacing: 0.20em;
  text-transform: uppercase;
  color: var(--wc-text-secondary);
}
.wp-section-label .ln {
  flex: 1;
  height: 1px;
  background: var(--wc-border);
}
.wp-section-label .cnt {
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 11px;
  color: var(--wc-text-tertiary);
}

.week-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.week {
  position: relative;
  border-radius: 16px;
  border: 1px solid var(--wc-border);
  background: var(--wc-bg-secondary);
  overflow: hidden;
  transition: border-color 0.15s;
}
.week.current {
  border-color: rgba(220, 38, 38, 0.32);
  background: linear-gradient(180deg, rgba(220, 38, 38, 0.06), var(--wc-bg-secondary) 30%);
}
.week.current::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: linear-gradient(180deg, #EF4444, #DC2626, transparent);
  box-shadow: 0 0 12px #EF4444;
}
.week.completed { opacity: 0.85; }

.week-header {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  cursor: pointer;
  width: 100%;
  text-align: left;
  background: transparent;
  border: none;
  color: inherit;
  font: inherit;
}
.week-num {
  width: 44px;
  height: 44px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  background: var(--wc-bg-tertiary);
  flex-shrink: 0;
}
.week-num .lab {
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 8px;
  letter-spacing: 0.18em;
  color: var(--wc-text-tertiary);
}
.week-num .n {
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 22px;
  font-weight: 700;
  line-height: 1;
  color: var(--wc-text);
}
.week.current .week-num {
  background: linear-gradient(180deg, #DC2626, #7F1D1D);
  box-shadow: 0 4px 16px -4px rgba(220, 38, 38, 0.6);
}
.week.current .week-num .lab { color: rgba(255, 255, 255, 0.7); }
.week.completed .week-num { background: var(--wc-bg-tertiary); }
.week.completed .week-num .n { color: var(--wc-text-tertiary); }

.week-info {
  flex: 1;
  min-width: 0;
}
.week-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.week-title {
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--wc-text);
}
.week-phase {
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  padding: 2px 8px;
  border-radius: 999px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}
.week-phase.acumul { background: rgba(96, 165, 250, 0.14); color: #60A5FA; }
.week-phase.intens { background: rgba(220, 38, 38, 0.14); color: #EF4444; }
.week-phase.pico { background: rgba(251, 191, 36, 0.14); color: #FBBF24; }
.week-phase.deload { background: rgba(255, 255, 255, 0.08); color: var(--wc-text-tertiary); }

.week-meta {
  font-size: 12px;
  color: var(--wc-text-tertiary);
  margin-top: 4px;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  letter-spacing: 0.06em;
}
.week-pill-now {
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 9px;
  padding: 3px 8px;
  border-radius: 999px;
  background: #DC2626;
  color: #fff;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  box-shadow: 0 0 12px rgba(239, 68, 68, 0.5);
}
.week-pill-done {
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 9px;
  padding: 3px 8px;
  border-radius: 999px;
  background: rgba(16, 185, 129, 0.14);
  color: #10B981;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}
.week-chev {
  width: 16px;
  height: 16px;
  color: var(--wc-text-tertiary);
  flex-shrink: 0;
  transition: transform 0.2s;
}
.week-chev.open { transform: rotate(180deg); }

.week-body {
  padding: 0 16px 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.day {
  position: relative;
  border-radius: 16px;
  border: 1px solid var(--wc-border);
  background: var(--wc-bg-tertiary);
  overflow: hidden;
}
.day.today {
  border-color: rgba(220, 38, 38, 0.40);
  background: linear-gradient(180deg, rgba(220, 38, 38, 0.08), var(--wc-bg-tertiary) 40%);
  box-shadow: 0 8px 32px -12px rgba(220, 38, 38, 0.30);
}
.day.upcoming { background: var(--wc-bg-tertiary); }

.day-head {
  display: flex;
  align-items: stretch;
  gap: 14px;
  padding: 14px 16px;
  cursor: pointer;
}
.day-num {
  width: 56px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  background: var(--wc-bg-tertiary);
  flex-shrink: 0;
  position: relative;
}
.day-num .lab {
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 8px;
  letter-spacing: 0.18em;
  color: var(--wc-text-tertiary);
  text-transform: uppercase;
  line-height: 1;
}
.day-num .n {
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 30px;
  font-weight: 700;
  line-height: 0.95;
  color: var(--wc-text);
}
.day.today .day-num {
  background: linear-gradient(180deg, #DC2626, #7F1D1D);
}
.day.today .day-num .lab { color: rgba(255, 255, 255, 0.78); }
.day.today .day-num::after {
  content: 'HOY';
  position: absolute;
  bottom: -6px;
  left: 50%;
  transform: translateX(-50%);
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 8px;
  letter-spacing: 0.18em;
  font-weight: 700;
  background: var(--wc-text);
  color: var(--wc-bg);
  padding: 2px 6px;
  border-radius: 4px;
  white-space: nowrap;
}

.day-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding-top: 2px;
}
.day-tags {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.tag-grp {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 8px;
  border-radius: 999px;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  font-weight: 500;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  border: 1px solid rgba(255, 255, 255, 0.10);
  background: rgba(255, 255, 255, 0.03);
  color: var(--wc-text-secondary);
}
.tag-grp .sw {
  width: 6px;
  height: 6px;
  border-radius: 999px;
}
.day-title {
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 18px;
  font-weight: 600;
  letter-spacing: 0.02em;
  color: var(--wc-text);
  line-height: 1.15;
  text-transform: uppercase;
  margin: 0;
}
.day-subline {
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 11px;
  color: var(--wc-text-tertiary);
  letter-spacing: 0.04em;
}

.day-status {
  flex-shrink: 0;
  display: flex;
  align-items: flex-start;
}
.status-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  border-radius: 999px;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  font-weight: 600;
}
.status-chip.done {
  background: rgba(16, 185, 129, 0.14);
  color: #10B981;
}
.status-chip.done svg {
  width: 10px;
  height: 10px;
}

.warmup {
  display: flex;
  gap: 12px;
  padding: 12px 16px;
  border-top: 1px solid var(--wc-border);
  background: linear-gradient(90deg, rgba(251, 191, 36, 0.06), transparent 60%);
}
.warmup-lab {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #FBBF24;
  font-weight: 600;
  margin-top: 2px;
}
.warmup-lab svg {
  width: 12px;
  height: 12px;
}
.warmup-tx {
  font-size: 13px;
  line-height: 1.55;
  color: var(--wc-text-secondary);
  margin: 0;
}

.cta-today {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px;
  margin: 12px 16px 0;
  border-radius: 16px;
  background: linear-gradient(135deg, #DC2626 0%, #7F1D1D 100%);
  position: relative;
  overflow: hidden;
  cursor: pointer;
  box-shadow: 0 8px 24px -8px rgba(220, 38, 38, 0.6);
  width: calc(100% - 32px);
  border: none;
  color: #fff;
  font: inherit;
  text-align: left;
}
.cta-today::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.06) 0 1px, transparent 1px 18px);
  pointer-events: none;
}
.cta-today .icon {
  width: 44px;
  height: 44px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.30);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid rgba(255, 255, 255, 0.16);
}
.cta-today .icon svg {
  width: 18px;
  height: 18px;
  fill: #fff;
  margin-left: 2px;
}
.cta-today .tx {
  flex: 1;
  min-width: 0;
}
.cta-today .lab {
  display: block;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  letter-spacing: 0.20em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.78);
}
.cta-today .ttl {
  display: block;
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 18px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #fff;
  margin-top: 2px;
}
.cta-today .arrow {
  color: #fff;
  flex-shrink: 0;
}

.ex-list {
  border-top: 1px solid var(--wc-border);
}

.phone-sticky-wrap {
  position: sticky;
  bottom: calc(env(safe-area-inset-bottom, 0) + 8px);
  z-index: 40;
  padding: 0 12px;
}
.phone-sticky {
  width: 100%;
  padding: 14px 16px;
  border-radius: 16px;
  background: linear-gradient(135deg, #DC2626 0%, #7F1D1D 100%);
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 12px 36px -8px rgba(220, 38, 38, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.10);
  cursor: pointer;
  font: inherit;
  color: #fff;
}
.phone-sticky .icn {
  width: 36px;
  height: 36px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.30);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.phone-sticky .icn svg {
  width: 14px;
  height: 14px;
  fill: #fff;
  margin-left: 2px;
}
.phone-sticky-tx {
  flex: 1;
  min-width: 0;
  text-align: left;
}
.phone-sticky-tx .lab {
  display: block;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 9px;
  letter-spacing: 0.18em;
  color: rgba(255, 255, 255, 0.78);
  text-transform: uppercase;
}
.phone-sticky-tx .ttl {
  display: block;
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #fff;
  line-height: 1.1;
  margin-top: 1px;
}
.phone-sticky .arrow {
  margin-left: auto;
  color: #fff;
}

@media (min-width: 768px) {
  .phone-sticky-wrap { display: none; }
}
</style>
