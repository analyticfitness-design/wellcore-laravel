<script setup>
/**
 * PersonalDataForm.vue — sección "01 · DATOS PERSONALES".
 *
 * Layout: section header (num + título + sub) + grid 2 cols con:
 *   nombre, email, ciudad, fecha de nacimiento, whatsapp.
 *   Bio span-2 (con CommunityPreview a la derecha).
 *
 * Bind controlado vía v-model:state (objeto con todos los campos del formulario).
 *
 * Props:
 *   - state: ref del form (modificado en sitio).
 *   - errors: { campo: ['msg'] } o { campo: 'msg' }.
 *   - avatarUrl: para el CommunityPreview.
 *   - plan: opcional, para meta del CommunityPreview.
 *
 * Slots:
 *   - field-id-{key}: id del DOM scroll target (ej: 'profile-name', 'profile-bio').
 *
 * Emits:
 *   - none — todo via v-model bidireccional sobre state.
 */
import { computed, toRef } from 'vue';
import InputField from './InputField.vue';
import BioField from './BioField.vue';
import CommunityPreview from './CommunityPreview.vue';

const props = defineProps({
    state:     { type: Object, required: true },
    errors:    { type: Object, default: () => ({}) },
    avatarUrl: { type: String, default: '' },
    plan:      { type: String, default: '' },
});

// v-model two-way sobre la propiedad del state. El padre pasa el ref unwrapped,
// y al mutar state.field, Vue propaga (porque .value sigue siendo el mismo objeto reactivo).
function bind(key) {
    return computed({
        get: () => props.state[key] ?? '',
        set: (v) => { props.state[key] = v; },
    });
}

function err(key) {
    const e = props.errors?.[key];
    if (Array.isArray(e)) return e[0] || '';
    return e || '';
}

const nameModel       = bind('name');
const emailModel      = bind('email');
const cityModel       = bind('city');
const birthDateModel  = bind('birthDate');
const whatsappModel   = bind('whatsapp');
const bioModel        = bind('bio');
</script>

<template>
  <section class="section" aria-labelledby="section-personal-title">
    <header class="section-head">
      <div class="section-head__l">
        <span class="section-num font-display">01 · IDENTIDAD</span>
        <h2 id="section-personal-title" class="section-title font-display">DATOS PERSONALES</h2>
      </div>
      <p class="section-sub">Información básica de tu cuenta. Esto aparece en tu perfil de comunidad.</p>
    </header>

    <div class="field-grid field-grid--2">
      <InputField
        id="profile-name"
        v-model="nameModel.value"
        label="Nombre completo"
        type="text"
        autocomplete="name"
        placeholder="Tu nombre"
        :error="err('name')"
        required
      />

      <InputField
        id="profile-email"
        v-model="emailModel.value"
        label="Email"
        type="email"
        autocomplete="email"
        inputmode="email"
        placeholder="tu@email.com"
        :error="err('email')"
        required
      />

      <InputField
        id="profile-city"
        v-model="cityModel.value"
        label="Ciudad"
        type="text"
        autocomplete="address-level2"
        placeholder="Tu ciudad"
        :error="err('city')"
      />

      <InputField
        id="profile-birthDate"
        v-model="birthDateModel.value"
        label="Fecha de nacimiento"
        type="date"
        autocomplete="bday"
        :error="err('birth_date') || err('birthDate')"
      />

      <InputField
        id="profile-whatsapp"
        v-model="whatsappModel.value"
        label="WhatsApp"
        type="tel"
        inputmode="tel"
        autocomplete="tel"
        placeholder="300 123 4567"
        :error="err('whatsapp')"
        class="field-grid--span-2"
      >
        <template #prefix>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10" />
            <line x1="2" y1="12" x2="22" y2="12" />
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
          </svg>
          <span>+57</span>
        </template>
      </InputField>

      <div class="field-grid--span-2">
        <BioField
          id="profile-bio"
          v-model="bioModel.value"
          :error="err('bio')"
        >
          <template #preview>
            <CommunityPreview
              :name="state.name"
              :bio="state.bio"
              :city="state.city"
              :birth-date="state.birthDate"
              :avatar-url="avatarUrl"
              :plan="plan"
            />
          </template>
        </BioField>
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

.section-head__l {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

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
</style>
