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
  <section class="mform">
    <div class="mform-hd">
      <p class="mform-title">Nuevo registro</p>
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
  padding: 0;
  margin-bottom: 24px;
}
.mform-hd { margin-bottom: 16px; }
.mform-title {
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 400;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  margin: 0;
}
/* Transition: slide + fade between modes */
.mform-slide-enter-active,
.mform-slide-leave-active { transition: opacity .18s ease, transform .18s ease; }
.mform-slide-enter-from { opacity: 0; transform: translateY(6px); }
.mform-slide-leave-to  { opacity: 0; transform: translateY(-6px); }
</style>
