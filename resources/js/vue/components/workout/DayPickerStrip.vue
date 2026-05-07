<script setup>
/**
 * DayPickerStrip.vue — Selector de día (mobile pill / desktop tabs) + week selector opcional.
 *
 * Mobile: pill compacto con dropdown (sheet)
 * Desktop: tabs horizontales con todos los días visibles
 * Si workoutStarted: días lockeados (no se puede cambiar día durante sesión activa)
 */
import { computed, ref } from 'vue';

const props = defineProps({
  days:             { type: Array, default: () => [] },
  currentDayIndex:  { type: Number, default: 0 },
  workoutStarted:   { type: Boolean, default: false },
  hasProgressions:  { type: Boolean, default: false },
  currentWeek:      { type: Number, default: 1 },
  totalWeeks:       { type: Number, default: 1 },
});

const emit = defineEmits(['change-day', 'change-week', 'back']);

const isOpen = ref(false);

const currentDay = computed(() => props.days[props.currentDayIndex] || {});
const currentLabel = computed(() => {
  const d = currentDay.value;
  return d.grupo_muscular || d.muscle_group || d.nombre || d.name || d.dia || `Día ${props.currentDayIndex + 1}`;
});

function dayLabel(d, idx) {
  return d.grupo_muscular || d.muscle_group || d.nombre || d.name || d.dia || `Día ${idx + 1}`;
}

function selectDay(idx) {
  if (props.workoutStarted && idx !== props.currentDayIndex) return;
  emit('change-day', idx);
  isOpen.value = false;
}
</script>

<template>
  <div class="day-picker">
    <!-- Week selector (Elite plans con progresiones) -->
    <div v-if="hasProgressions && totalWeeks > 1" class="week-strip">
      <span class="week-label">Semana</span>
      <button
        v-for="w in totalWeeks"
        :key="`w-${w}`"
        type="button"
        class="week-pill"
        :class="{ 'week-pill--active': currentWeek === w, 'week-pill--locked': workoutStarted && currentWeek !== w }"
        @click="currentWeek !== w && !workoutStarted && emit('change-week', w)"
      >{{ w }}</button>
    </div>

    <div class="top-row">
      <button type="button" class="back-btn" @click="emit('back')" aria-label="Volver">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 18l-6-6 6-6"/>
        </svg>
      </button>

      <!-- Mobile: pill collapsed -->
      <div class="day-pill" @click="!workoutStarted && (isOpen = !isOpen)" :class="{ 'day-pill--locked': workoutStarted }">
        <span class="day-num">D{{ currentDayIndex + 1 }}</span>
        <span class="day-label">{{ currentLabel }}</span>
        <svg v-if="!workoutStarted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 9l6 6 6-6"/>
        </svg>
      </div>

      <!-- Desktop: full strip -->
      <div class="day-strip">
        <button
          v-for="(day, i) in days"
          :key="`day-${i}`"
          type="button"
          class="day-tab"
          :class="{ 'day-tab--active': i === currentDayIndex, 'day-tab--locked': workoutStarted && i !== currentDayIndex }"
          @click="selectDay(i)"
          :disabled="workoutStarted && i !== currentDayIndex"
        >
          <span class="num">DÍA {{ i + 1 }}</span>
          <span class="label">{{ dayLabel(day, i) }}</span>
        </button>
      </div>

      <!-- Más opciones (3-dots) — fiel al target HTML -->
      <button type="button" class="back-btn" @click="emit('more')" aria-label="Más opciones">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <circle cx="5" cy="12" r="1.6"/>
          <circle cx="12" cy="12" r="1.6"/>
          <circle cx="19" cy="12" r="1.6"/>
        </svg>
      </button>
    </div>

    <!-- Mobile dropdown sheet -->
    <Transition>
      <div v-if="isOpen" class="day-sheet" @click.self="isOpen = false">
        <div class="day-sheet-inner">
          <div class="sheet-handle"></div>
          <div class="sheet-title">Elegir día</div>
          <button
            v-for="(day, i) in days"
            :key="`m-${i}`"
            type="button"
            class="sheet-row"
            :class="{ 'sheet-row--active': i === currentDayIndex }"
            @click="selectDay(i)"
          >
            <span class="sheet-num">D{{ i + 1 }}</span>
            <span class="sheet-label">{{ dayLabel(day, i) }}</span>
            <svg v-if="i === currentDayIndex" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.day-picker {
  position: sticky;
  top: 0;
  z-index: 20;
  padding: 8px 0 12px;
  background: linear-gradient(to bottom, var(--color-wc-bg) 75%, rgba(9,9,11,0));
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.week-strip {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 8px;
  overflow-x: auto;
  scrollbar-width: none;
}
.week-strip::-webkit-scrollbar { display: none; }
.week-label {
  font-family: var(--font-display);
  font-size: 10px;
  font-weight: 500;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  margin-right: 4px;
}
.week-pill {
  flex-shrink: 0;
  min-width: 36px;
  height: 32px;
  padding: 0 12px;
  border-radius: 999px;
  background: var(--color-wc-bg-tertiary);
  border: 1px solid var(--color-wc-border);
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 12px;
  color: var(--color-wc-text-secondary);
  cursor: pointer;
}
.week-pill--active {
  background: var(--color-wc-accent, #DC2626);
  color: white;
  border-color: transparent;
}
.week-pill--locked {
  opacity: 0.5;
  cursor: not-allowed;
}

.top-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.back-btn {
  width: 44px; height: 44px;
  border-radius: 999px;
  background: var(--color-wc-bg-tertiary);
  border: 1px solid var(--color-wc-border);
  display: grid;
  place-items: center;
  flex-shrink: 0;
  color: var(--color-wc-text);
  cursor: pointer;
}
.back-btn svg { width: 20px; height: 20px; }
.back-btn:hover { background: rgba(255,255,255,0.06); }

.day-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px 10px 12px;
  background: var(--color-wc-bg-tertiary);
  border: 1px solid var(--color-wc-border);
  border-radius: 999px;
  height: 44px;
  min-width: 0;
  flex: 1;
  cursor: pointer;
}
.day-pill--locked { opacity: 0.7; cursor: not-allowed; }
.day-pill .day-num {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 14px;
  letter-spacing: 0.04em;
  background: var(--color-wc-accent, #DC2626);
  color: white;
  padding: 4px 10px;
  border-radius: 999px;
  flex-shrink: 0;
}
.day-pill .day-label {
  font-family: var(--font-display);
  font-weight: 500;
  letter-spacing: 0.06em;
  font-size: 13px;
  text-transform: uppercase;
  color: var(--color-wc-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.day-pill svg { width: 14px; height: 14px; opacity: 0.5; flex-shrink: 0; }

.day-strip { display: none; }
@media (min-width: 1024px) {
  .day-pill { display: none; }
  .day-strip {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none;
    flex: 1;
    min-width: 0;
    padding: 2px 0;
  }
  .day-strip::-webkit-scrollbar { display: none; }
}
.day-tab {
  display: inline-flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  padding: 8px 12px;
  border-radius: 12px;
  background: var(--color-wc-bg-tertiary);
  border: 1px solid var(--color-wc-border);
  width: 148px;
  flex-shrink: 0;
  flex-grow: 0;
  gap: 3px;
  cursor: pointer;
  height: 48px;
  overflow: hidden;
  transition: all 0.15s var(--ease-out);
}
.day-tab:hover {
  border-color: var(--color-wc-border-strong);
  background: rgba(255,255,255,0.03);
}
.day-tab .num {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 10px;
  letter-spacing: 0.16em;
  color: var(--color-wc-text-tertiary);
  text-transform: uppercase;
  line-height: 1;
}
.day-tab .label {
  font-family: var(--font-display);
  font-weight: 500;
  font-size: 11px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--color-wc-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
  line-height: 1.1;
}
.day-tab--active {
  background: linear-gradient(180deg, rgba(220,38,38,0.14), rgba(220,38,38,0.04));
  border-color: rgba(239,68,68,0.40);
}
.day-tab--active .num { color: var(--color-wc-accent-glow, #EF4444); }
.day-tab--locked, .day-tab[disabled] { opacity: 0.5; cursor: not-allowed; }

/* Mobile dropdown sheet */
.day-sheet {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  z-index: 100;
  display: flex;
  align-items: flex-end;
}
.day-sheet-inner {
  width: 100%;
  background: var(--color-wc-bg-secondary);
  border-top: 1px solid var(--color-wc-border-strong);
  border-radius: 24px 24px 0 0;
  padding: 12px 16px max(20px, env(safe-area-inset-bottom));
  max-height: 70vh;
  overflow-y: auto;
}
.sheet-handle {
  width: 40px; height: 4px;
  background: rgba(255,255,255,0.2);
  border-radius: 999px;
  margin: 4px auto 12px;
}
.sheet-title {
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  padding: 0 4px 8px;
}
.sheet-row {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 14px 12px;
  border-radius: 14px;
  background: transparent;
  border: 1px solid transparent;
  cursor: pointer;
  text-align: left;
}
.sheet-row + .sheet-row { margin-top: 4px; }
.sheet-row--active {
  background: rgba(220,38,38,0.10);
  border-color: rgba(220,38,38,0.25);
}
.sheet-num {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 14px;
  background: rgba(255,255,255,0.06);
  color: var(--color-wc-text);
  padding: 6px 12px;
  border-radius: 999px;
  flex-shrink: 0;
}
.sheet-row--active .sheet-num { background: var(--color-wc-accent, #DC2626); color: white; }
.sheet-label {
  flex: 1;
  font-family: var(--font-display);
  font-weight: 500;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-wc-text);
}
.sheet-row--active svg { color: var(--color-wc-accent-glow, #EF4444); width: 18px; height: 18px; }

.v-enter-active, .v-leave-active { transition: opacity 0.2s, transform 0.25s var(--ease-out); }
.v-enter-from, .v-leave-to { opacity: 0; }
.v-enter-from .day-sheet-inner, .v-leave-to .day-sheet-inner { transform: translateY(100%); }

@media (prefers-reduced-motion: reduce) {
  .v-enter-active, .v-leave-active { transition: none; }
}
</style>
