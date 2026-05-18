<script setup>
/**
 * DaysPicker.vue — selector de días disponibles para entrenar.
 *
 * v-model: array de strings lowercase (sin tildes):
 *   'lunes' | 'martes' | 'miercoles' | 'jueves' | 'viernes' | 'sabado' | 'domingo'
 *
 * Pills de 56px alto · checkbox oculto · grid 7 columnas (4 columnas <640px).
 * Counter "X días/semana" abajo, en Oswald.
 *
 * Visual: cuando checked → bg=text (blanco en dark), color invertido
 *         (réplica del HTML v2 — `.day-pill:has(input:checked)`).
 */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    legend: { type: String, default: '' },
    hint:   { type: String, default: '' },
});

const model = defineModel({ default: () => [] });

const legendText = computed(() => props.legend || t('client_account.profile_field_days'));

const DAYS = computed(() => [
    { value: 'lunes',     short: t('client_account.profile_day_monday_short'),    num: 1 },
    { value: 'martes',    short: t('client_account.profile_day_tuesday_short'),   num: 2 },
    { value: 'miercoles', short: t('client_account.profile_day_wednesday_short'), num: 3 },
    { value: 'jueves',    short: t('client_account.profile_day_thursday_short'),  num: 4 },
    { value: 'viernes',   short: t('client_account.profile_day_friday_short'),    num: 5 },
    { value: 'sabado',    short: t('client_account.profile_day_saturday_short'),  num: 6 },
    { value: 'domingo',   short: t('client_account.profile_day_sunday_short'),    num: 7 },
]);

const selected = computed(() => Array.isArray(model.value) ? model.value : []);
const count = computed(() => selected.value.length);

function isSelected(value) {
    return selected.value.includes(value);
}

function toggle(value) {
    const arr = Array.isArray(model.value) ? [...model.value] : [];
    const idx = arr.indexOf(value);
    if (idx >= 0) arr.splice(idx, 1);
    else arr.push(value);
    model.value = arr;
}

function onKey(e, value) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggle(value);
    }
}
</script>

<template>
  <fieldset class="days-fieldset" role="group" :aria-label="legendText">
    <div class="days-head">
      <legend class="days-legend">{{ legendText }}</legend>
      <span class="days-counter font-display tabular-nums" aria-live="polite">
        {{ count }} {{ count === 1 ? t('client_account.profile_field_days_singular') : t('client_account.profile_field_days_plural') }}{{ t('client_account.profile_field_days_per_week') }}
      </span>
    </div>

    <div class="days" role="presentation">
      <label
        v-for="d in DAYS"
        :key="d.value"
        class="day-pill"
        :class="{ 'is-checked': isSelected(d.value) }"
      >
        <input
          type="checkbox"
          :value="d.value"
          :checked="isSelected(d.value)"
          @change="toggle(d.value)"
          @keydown="onKey($event, d.value)"
        />
        <span class="day-pill__short font-display">{{ d.short }}</span>
        <span class="day-pill__num tabular-nums">{{ d.num }}</span>
      </label>
    </div>

    <p v-if="hint" class="days-hint">{{ hint }}</p>
  </fieldset>
</template>

<style scoped>
.days-fieldset {
  border: 0;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 0;
}

.days-head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
}

.days-legend {
  padding: 0;
  font-size: 14px;
  font-weight: 500;
  color: var(--color-wc-text);
  letter-spacing: 0.005em;
}

.days-counter {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.1em;
  color: var(--color-wc-text-tertiary);
  text-transform: uppercase;
}

.days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}
@media (max-width: 640px) {
  .days { grid-template-columns: repeat(4, 1fr); }
}

.day-pill {
  position: relative;
  height: 56px;
  border-radius: 12px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  cursor: pointer;
  transition: all 0.15s ease;
  user-select: none;
}
.day-pill:hover {
  border-color: var(--color-wc-border-strong, var(--color-wc-border));
  background: var(--color-wc-bg-tertiary);
}
.day-pill:focus-within {
  outline: 2px solid var(--color-wc-accent-glow, #EF4444);
  outline-offset: 2px;
}

.day-pill input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
  width: 0;
  height: 0;
}

.day-pill__short {
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.1em;
  color: var(--color-wc-text-secondary);
  text-transform: uppercase;
  transition: color 0.15s ease;
}

.day-pill__num {
  font-size: 10px;
  font-weight: 500;
  color: var(--color-wc-text-quaternary, var(--color-wc-text-tertiary));
  letter-spacing: 0.04em;
  transition: color 0.15s ease;
}

.day-pill.is-checked {
  background: var(--color-wc-text);
  border-color: var(--color-wc-text);
}
.day-pill.is-checked .day-pill__short { color: var(--color-wc-bg); }
.day-pill.is-checked .day-pill__num { color: rgba(0, 0, 0, 0.6); }

.days-hint {
  margin: 0;
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  line-height: 1.4;
}

@media (prefers-reduced-motion: reduce) {
  .day-pill,
  .day-pill__short,
  .day-pill__num { transition-duration: 0.01ms; }
}
</style>
