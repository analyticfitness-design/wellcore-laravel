<script setup>
import QuickLogInput from './QuickLogInput.vue';
import MeasurementsForm from './MeasurementsForm.vue';

defineProps({
  mode: { type: String, default: 'quick' }, // 'quick' | 'full'
  quickValue: { type: [String, Number], default: '' },
  quickError: { type: String, default: '' },
  form: { type: Object, required: true },
  formErrors: { type: Object, default: () => ({}) },
  saving: { type: Boolean, default: false },
});

const emit = defineEmits([
  'update:quickValue',
  'update:form',
  'quick-submit',
  'full-submit',
  'expand',
  'collapse',
  'save-draft',
]);
</script>

<template>
  <section class="mform" id="log">
    <div class="mform-hd">
      <div class="mform-hd-left">
        <p class="mform-title">Nuevo registro</p>
        <p class="mform-sub">Idealmente en ayunas, mismo día y hora cada semana.</p>
      </div>
      <!-- Mode toggle pills -->
      <div class="mform-mode" role="tablist">
        <button
          type="button"
          role="tab"
          class="mform-mode-btn"
          :class="{ 'mform-mode-btn--active': mode === 'quick' }"
          :aria-selected="mode === 'quick'"
          @click="emit('collapse')"
        >Rápido</button>
        <button
          type="button"
          role="tab"
          class="mform-mode-btn"
          :class="{ 'mform-mode-btn--active': mode === 'full' }"
          :aria-selected="mode === 'full'"
          @click="emit('expand')"
        >Completo</button>
      </div>
    </div>

    <form @submit.prevent="mode === 'quick' ? emit('quick-submit') : emit('full-submit')">
      <!-- Quick mode -->
      <Transition name="mform-slide" mode="out-in">
        <QuickLogInput
          v-if="mode === 'quick'"
          :modelValue="quickValue"
          :error="quickError"
          :saving="saving"
          @update:modelValue="emit('update:quickValue', $event)"
          @expand="emit('expand')"
          @submit="emit('quick-submit')"
        />

        <!-- Full mode -->
        <MeasurementsForm
          v-else
          :form="form"
          :errors="formErrors"
          :saving="saving"
          @update:form="emit('update:form', $event)"
          @submit="emit('full-submit')"
          @collapse="emit('collapse')"
          @save-draft="emit('save-draft')"
        />
      </Transition>
    </form>
  </section>
</template>

<style scoped>
.mform {
  border-radius: 16px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  padding: 24px;
  margin-bottom: 40px;
}
.mform-hd {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}
.mform-hd-left { flex: 1; min-width: 0; }
.mform-title {
  font-family: var(--font-display);
  font-size: 14px;
  font-weight: 500;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--color-wc-text);
  margin: 0;
}
.mform-sub {
  font-size: 13px;
  color: var(--color-wc-text-tertiary);
  margin: 4px 0 0;
}

/* Mode toggle */
.mform-mode {
  display: inline-flex;
  padding: 4px;
  background: var(--color-wc-bg);
  border: 1px solid var(--color-wc-border);
  border-radius: 999px;
  flex-shrink: 0;
}
.mform-mode-btn {
  font-family: var(--font-mono);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: .06em;
  padding: 7px 14px;
  border-radius: 999px;
  color: var(--color-wc-text-tertiary);
  background: none;
  border: none;
  cursor: pointer;
  transition: background .12s, color .12s;
}
.mform-mode-btn--active {
  background: var(--color-wc-bg-secondary);
  color: var(--color-wc-text);
  box-shadow: 0 0 0 1px rgba(255,255,255,.14);
}

/* Transition: slide + fade between modes */
.mform-slide-enter-active,
.mform-slide-leave-active { transition: opacity .18s ease, transform .18s ease; }
.mform-slide-enter-from { opacity: 0; transform: translateY(6px); }
.mform-slide-leave-to  { opacity: 0; transform: translateY(-6px); }
</style>
