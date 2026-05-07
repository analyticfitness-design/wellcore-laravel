<script setup>
/**
 * FitnessDataForm.vue — sección "02 · ENTRENAMIENTO".
 *
 * Layout grid 2 cols con:
 *   - peso (UnitInput KG) + altura (UnitInput CM)
 *   - objetivo (InputField) [span-2]
 *   - nivel (VisualSelect) + lugarEntreno (VisualSelect)
 *   - diasDisponibles (DaysPicker) [span-2]
 *   - restricciones (textarea) [span-2]
 *
 * Bind two-way sobre props.state (mismo patrón que PersonalDataForm).
 */
import { computed } from 'vue';
import InputField from './InputField.vue';
import UnitInput from './UnitInput.vue';
import VisualSelect from './VisualSelect.vue';
import DaysPicker from './DaysPicker.vue';

const props = defineProps({
    state:  { type: Object, required: true },
    errors: { type: Object, default: () => ({}) },
});

function bind(key) {
    return computed({
        get: () => props.state[key] ?? (key === 'diasDisponibles' ? [] : ''),
        set: (v) => { props.state[key] = v; },
    });
}

function err(key) {
    const e = props.errors?.[key];
    if (Array.isArray(e)) return e[0] || '';
    return e || '';
}

const pesoModel        = bind('peso');
const alturaModel      = bind('altura');
const objetivoModel    = bind('objetivo');
const nivelModel       = bind('nivel');
const lugarModel       = bind('lugarEntreno');
const diasModel        = bind('diasDisponibles');
const restriccionesModel = bind('restricciones');

// Iconos inline en strings para que VisualSelect los inyecte (SVGs minimal).
const ICON_BEGINNER = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/></svg>`;
const ICON_INTER = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>`;
const ICON_ADVANCED = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15 8.5 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 8.5 12 2"/></svg>`;

const ICON_GYM = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v16M18 4v16M2 8v8M22 8v8M6 12h12"/></svg>`;
const ICON_HOME = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`;
const ICON_BOTH = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`;

const NIVEL_OPTIONS = [
    { value: 'principiante', label: 'Principiante', desc: '0–6 meses entrenando', icon: ICON_BEGINNER },
    { value: 'intermedio',   label: 'Intermedio',   desc: '6 meses–2 años con técnica sólida', icon: ICON_INTER },
    { value: 'avanzado',     label: 'Avanzado',     desc: '2+ años entrenando regularmente', icon: ICON_ADVANCED },
];

const LUGAR_OPTIONS = [
    { value: 'gym',   label: 'Gimnasio', desc: 'Acceso a equipo completo', icon: ICON_GYM },
    { value: 'casa',  label: 'Casa',     desc: 'Equipo limitado o peso corporal', icon: ICON_HOME },
    { value: 'ambos', label: 'Ambos',    desc: 'Combinas gym y entrenamiento en casa', icon: ICON_BOTH },
];
</script>

<template>
  <section class="section" aria-labelledby="section-fitness-title">
    <header class="section-head">
      <div class="section-head__l">
        <span class="section-num font-display">02 · ENTRENAMIENTO</span>
        <h2 id="section-fitness-title" class="section-title font-display">DATOS DE ENTRENAMIENTO</h2>
      </div>
      <p class="section-sub">Información para que tu coach personalice tu plan según tu nivel y disponibilidad.</p>
    </header>

    <div class="field-grid field-grid--2">
      <UnitInput
        id="profile-peso"
        v-model="pesoModel.value"
        label="Peso"
        unit="KG"
        :step="0.1"
        :min="0"
        placeholder="75.0"
        :error="err('peso')"
      />

      <UnitInput
        id="profile-altura"
        v-model="alturaModel.value"
        label="Altura"
        unit="CM"
        :step="0.1"
        :min="0"
        placeholder="175"
        :error="err('altura')"
      />

      <div class="field-grid--span-2">
        <InputField
          id="profile-objetivo"
          v-model="objetivoModel.value"
          label="Objetivo"
          hint="Descríbelo en una frase: lo que quieres lograr en los próximos 3-6 meses."
          placeholder="Ej: bajar grasa y mantener fuerza, ganar 3 kg de masa magra…"
          :error="err('objetivo')"
        />
      </div>

      <VisualSelect
        id="profile-nivel"
        v-model="nivelModel.value"
        label="Nivel"
        placeholder="Selecciona tu nivel"
        :options="NIVEL_OPTIONS"
        :error="err('nivel')"
      />

      <VisualSelect
        id="profile-lugarEntreno"
        v-model="lugarModel.value"
        label="Lugar de entrenamiento"
        placeholder="¿Dónde entrenas?"
        :options="LUGAR_OPTIONS"
        :error="err('lugar_entreno') || err('lugarEntreno')"
      />

      <div class="field-grid--span-2">
        <DaysPicker
          v-model="diasModel.value"
          legend="Días disponibles"
          hint="Marca los días en los que puedes entrenar regularmente."
        />
        <p
          v-if="err('dias_disponibles')"
          class="field-error"
          role="alert"
        >{{ err('dias_disponibles') }}</p>
      </div>

      <div class="field-grid--span-2">
        <label for="profile-restricciones" class="field-label">
          Restricciones o lesiones
          <span class="field-label__hint">opcional</span>
        </label>
        <textarea
          id="profile-restricciones"
          v-model="restriccionesModel.value"
          class="textarea"
          :class="{ 'is-invalid': !!err('restricciones') }"
          rows="3"
          placeholder="Ej: lesión en rodilla derecha, alergia al gluten, hombro con poca movilidad…"
          :aria-invalid="err('restricciones') ? 'true' : 'false'"
          :aria-describedby="err('restricciones') ? 'profile-restricciones-error' : undefined"
        />
        <p
          v-if="err('restricciones')"
          id="profile-restricciones-error"
          class="field-error"
          role="alert"
        >{{ err('restricciones') }}</p>
      </div>
    </div>
  </section>
</template>

<style scoped>
.section {
  margin-top: 56px;
  width: 100%;
  min-width: 0;
}

.section-head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 16px;
  padding-bottom: 16px;
  margin-bottom: 24px;
  border-bottom: 1px solid var(--color-wc-border);
}
.section-head__l { display: flex; flex-direction: column; gap: 2px; }

.section-num {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.section-title {
  font-size: 18px;
  font-weight: 600;
  letter-spacing: 0.04em;
  color: var(--color-wc-text);
  text-transform: uppercase;
  margin: 0;
}
.section-sub {
  font-size: 13px;
  color: var(--color-wc-text-tertiary);
  text-align: right;
  max-width: 32ch;
  margin: 0;
}
@media (max-width: 640px) {
  .section-head { flex-direction: column; align-items: flex-start; }
  .section-sub { text-align: left; max-width: 100%; }
}

.field-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
}
@media (min-width: 720px) {
  .field-grid--2 {
    grid-template-columns: 1fr 1fr;
    gap: 24px 28px;
  }
  .field-grid--span-2 { grid-column: span 2; }
}

.field-label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 500;
  color: var(--color-wc-text);
}
.field-label__hint {
  margin-left: 6px;
  font-size: 12px;
  font-weight: 400;
  color: var(--color-wc-text-tertiary);
}

.textarea {
  width: 100%;
  min-height: 96px;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  color: var(--color-wc-text);
  font-size: 16px;
  font-family: inherit;
  line-height: 1.5;
  resize: vertical;
  transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}
.textarea::placeholder {
  color: var(--color-wc-text-tertiary);
}
.textarea:hover { border-color: var(--color-wc-border-strong, var(--color-wc-border)); }
.textarea:focus,
.textarea:focus-visible {
  outline: none;
  border-color: var(--color-wc-accent-glow, #EF4444);
  background: var(--color-wc-bg-tertiary);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
}
.textarea.is-invalid {
  border-color: var(--color-wc-accent, #DC2626);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.10);
}

.field-error {
  margin: 4px 0 0;
  font-size: 12px;
  color: var(--color-wc-accent, #DC2626);
  line-height: 1.4;
}

@media (prefers-reduced-motion: reduce) {
  .textarea { transition-duration: 0.01ms; }
}
</style>
