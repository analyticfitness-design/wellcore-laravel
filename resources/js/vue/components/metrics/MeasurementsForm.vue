<script setup>
import { ref } from 'vue';

defineProps({
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  saving: { type: Boolean, default: false },
});

const emit = defineEmits(['update:form', 'submit', 'collapse', 'save-draft']);
const showGuide = ref(false);

function updateField(key, value) {
  emit('update:form', { key, value });
}
</script>

<template>
  <div class="meas-form">
    <!-- Primary stats row -->
    <div class="meas-grid meas-grid--primary">
      <!-- Peso -->
      <div class="meas-field meas-field--required">
        <label for="meas-peso" class="meas-label">
          Peso (kg) <span class="req" aria-label="requerido">*</span>
        </label>
        <input
          id="meas-peso"
          type="number"
          inputmode="decimal"
          step="0.1" min="20" max="300"
          placeholder="75.0"
          :value="form.peso"
          @input="updateField('peso', $event.target.value)"
          class="meas-input"
          :class="{ 'meas-input--error': errors.peso }"
          autocomplete="off"
        />
        <p v-if="errors.peso" class="meas-error">{{ errors.peso[0] }}</p>
      </div>

      <!-- % Músculo -->
      <div class="meas-field">
        <label for="meas-musculo" class="meas-label">% Músculo</label>
        <input id="meas-musculo" type="number" inputmode="decimal" step="0.1" min="0" max="100" placeholder="40.0"
          :value="form.porcentajeMusculo" @input="updateField('porcentajeMusculo', $event.target.value)"
          class="meas-input" :class="{ 'meas-input--error': errors.porcentaje_musculo }" autocomplete="off"/>
        <p v-if="errors.porcentaje_musculo" class="meas-error">{{ errors.porcentaje_musculo[0] }}</p>
      </div>

      <!-- % Grasa -->
      <div class="meas-field">
        <label for="meas-grasa" class="meas-label">% Grasa</label>
        <input id="meas-grasa" type="number" inputmode="decimal" step="0.1" min="0" max="100" placeholder="18.0"
          :value="form.porcentajeGrasa" @input="updateField('porcentajeGrasa', $event.target.value)"
          class="meas-input" autocomplete="off"/>
      </div>

      <!-- Notas -->
      <div class="meas-field">
        <label for="meas-notas" class="meas-label">Notas</label>
        <input id="meas-notas" type="text" placeholder="En ayunas, post-entreno..."
          :value="form.notas" @input="updateField('notas', $event.target.value)"
          class="meas-input" maxlength="500" autocomplete="off"/>
      </div>
    </div>

    <!-- Body measurements section -->
    <div class="meas-section">
      <div class="meas-section-hd">
        <div class="meas-section-icon" aria-hidden="true">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
          </svg>
        </div>
        <div>
          <p class="meas-section-title">Mediciones corporales</p>
          <p class="meas-section-sub">Mide con cinta métrica, en la mañana</p>
        </div>
      </div>

      <div class="meas-grid meas-grid--body">
        <div class="meas-field">
          <label for="meas-chest" class="meas-label">Pecho (cm)</label>
          <input id="meas-chest" type="number" inputmode="decimal" step="0.1" min="30" max="200" placeholder="95.0"
            :value="form.chest" @input="updateField('chest', $event.target.value)" class="meas-input" autocomplete="off"/>
        </div>
        <div class="meas-field">
          <label for="meas-waist" class="meas-label">Cintura (cm)</label>
          <input id="meas-waist" type="number" inputmode="decimal" step="0.1" min="30" max="200" placeholder="80.0"
            :value="form.waist" @input="updateField('waist', $event.target.value)" class="meas-input" autocomplete="off"/>
        </div>
        <div class="meas-field">
          <label for="meas-hip" class="meas-label">Cadera (cm)</label>
          <input id="meas-hip" type="number" inputmode="decimal" step="0.1" min="30" max="200" placeholder="95.0"
            :value="form.hip" @input="updateField('hip', $event.target.value)" class="meas-input" autocomplete="off"/>
        </div>
        <div class="meas-field">
          <label for="meas-thigh" class="meas-label">Muslo (cm)</label>
          <input id="meas-thigh" type="number" inputmode="decimal" step="0.1" min="20" max="100" placeholder="55.0"
            :value="form.thigh" @input="updateField('thigh', $event.target.value)" class="meas-input" autocomplete="off"/>
        </div>
        <div class="meas-field">
          <label for="meas-arm" class="meas-label">Brazo (cm)</label>
          <input id="meas-arm" type="number" inputmode="decimal" step="0.1" min="15" max="60" placeholder="32.0"
            :value="form.arm" @input="updateField('arm', $event.target.value)" class="meas-input" autocomplete="off"/>
        </div>
      </div>

      <!-- Guide accordion -->
      <div class="guide-accordion">
        <button type="button" class="guide-toggle" @click="showGuide = !showGuide" :aria-expanded="showGuide">
          <span>Cómo tomar las mediciones correctamente</span>
          <svg :style="{ transform: showGuide ? 'rotate(180deg)' : 'none', transition: 'transform 200ms' }"
            width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
          </svg>
        </button>
        <Transition name="guide">
          <div v-show="showGuide" class="guide-content">
            <p><strong>Pecho:</strong> Cinta a la altura de los pezones. Brazos relajados. No inflar el pecho.</p>
            <p><strong>Cintura:</strong> En el punto más estrecho, 2-3 cm arriba del ombligo. Exhala normalmente.</p>
            <p><strong>Cadera:</strong> En el punto más ancho de los glúteos. Pies juntos, de pie recto.</p>
            <p><strong>Muslo:</strong> En el punto más grueso, justo debajo del glúteo. Pierna relajada.</p>
            <p><strong>Brazo:</strong> En el punto más grueso del bíceps. Brazo relajado sin flexionar.</p>
            <p class="guide-tip">Mide siempre en las mismas condiciones: por la mañana, antes de comer.</p>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Save row -->
    <div class="meas-save-row">
      <div class="meas-privacy">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
        </svg>
        <span>Solo tú y tu coach pueden ver estos datos</span>
      </div>
      <div class="meas-save-btns">
        <button type="button" class="btn-draft" @click="emit('save-draft')">Guardar borrador</button>
        <button type="submit" class="btn-save" :disabled="saving" :aria-busy="saving">
          <svg v-if="saving" class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:.25"/>
            <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity:.75"/>
          </svg>
          {{ saving ? 'Guardando...' : 'Guardar registro' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.meas-grid { display: grid; gap: 14px; }
.meas-grid--primary {
  grid-template-columns: 1fr;
}
@media (min-width: 640px) { .meas-grid--primary { grid-template-columns: 1fr 1fr; } }
@media (min-width: 1024px) { .meas-grid--primary { grid-template-columns: repeat(4, 1fr); } }
.meas-grid--body { grid-template-columns: 1fr 1fr; }
@media (min-width: 768px) { .meas-grid--body { grid-template-columns: repeat(3, 1fr); } }
.meas-field { display: flex; flex-direction: column; gap: 6px; }
.meas-label {
  font-family: var(--font-display);
  font-size: 10.5px;
  font-weight: 500;
  letter-spacing: .16em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.req { color: var(--color-wc-accent); margin-left: 3px; }
.meas-input {
  width: 100%;
  padding: 14px;
  min-height: 48px;
  border-radius: 10px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  color: var(--color-wc-text);
  font-family: var(--font-mono);
  font-size: 14px;
  font-variant-numeric: tabular-nums;
  outline: none;
  transition: border-color .15s;
  -moz-appearance: textfield;
}
.meas-input::-webkit-outer-spin-button,
.meas-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.meas-input:focus { border-color: rgba(220,38,38,.50); background: var(--color-wc-bg); }
.meas-input--error { border-color: rgba(220,38,38,.60) !important; }
.meas-error { font-size: 12px; color: #FCA5A5; margin: 0; }
.meas-section { margin-top: 24px; border-top: 1px solid var(--color-wc-border); padding-top: 20px; }
.meas-section-hd { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.meas-section-icon {
  width: 32px; height: 32px; flex-shrink: 0; border-radius: 10px;
  background: rgba(220,38,38,.10); display: flex; align-items: center; justify-content: center;
  color: var(--color-wc-accent);
}
.meas-section-title { font: 600 11px/1 var(--font-sans); letter-spacing: .06em; text-transform: uppercase; color: var(--color-wc-text-secondary); margin: 0; }
.meas-section-sub { font-size: 12px; color: var(--color-wc-text-tertiary); margin-top: 2px; }
/* Guide accordion */
.guide-accordion { margin-top: 16px; border-radius: 12px; border: 1px solid rgba(245,158,11,.20); background: rgba(245,158,11,.05); padding: 14px 16px; }
.guide-toggle { display: flex; width: 100%; align-items: center; justify-content: space-between; font-size: 13px; font-weight: 600; color: #F59E0B; background: none; border: none; cursor: pointer; padding: 0; text-align: left; min-height: 44px; }
.guide-content { margin-top: 12px; display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: var(--color-wc-text-secondary); line-height: 1.5; overflow: hidden; }
.guide-content strong { color: var(--color-wc-text); }
.guide-tip { margin-top: 4px; color: rgba(245,158,11,.70); }
/* Transitions */
.guide-enter-active, .guide-leave-active { transition: max-height .3s ease, opacity .2s ease; max-height: 400px; }
.guide-enter-from, .guide-leave-to { max-height: 0; opacity: 0; }
/* Save row */
.meas-save-row {
  margin-top: 22px;
  padding-top: 22px;
  border-top: 1px solid var(--color-wc-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}
.meas-privacy {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11.5px;
  color: var(--color-wc-text-tertiary);
}
.meas-save-btns { display: flex; gap: 10px; }
.btn-draft {
  padding: 12px 18px;
  min-height: 44px;
  border-radius: 10px;
  background: var(--color-wc-bg-secondary);
  border: 1px solid var(--color-wc-border);
  color: var(--color-wc-text);
  font-family: var(--font-sans);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background .12s;
  white-space: nowrap;
}
.btn-draft:hover { background: var(--color-wc-bg-tertiary); }
.btn-save {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  min-height: 44px; padding: 12px 20px; border-radius: 10px;
  background: var(--color-wc-accent); color: #fff;
  font-family: var(--font-display); font-size: 14px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
  border: none; cursor: pointer; transition: background .12s;
  white-space: nowrap;
}
.btn-save:hover:not(:disabled) { background: var(--color-wc-accent-hover); }
.btn-save:disabled { opacity: .55; cursor: not-allowed; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
