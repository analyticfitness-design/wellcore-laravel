<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const PRIMARY_OPTIONS = computed(() => [
    { value: '', label: t('coach_growth.onboarding_form.s2_primary_select') },
    { value: 'fuerza', label: t('coach_growth.onboarding_form.s2_opt_fuerza') },
    { value: 'hipertrofia', label: t('coach_growth.onboarding_form.s2_opt_hipertrofia') },
    { value: 'recomposicion', label: t('coach_growth.onboarding_form.s2_opt_recomposicion') },
    { value: 'perdida_grasa', label: t('coach_growth.onboarding_form.s2_opt_perdida_grasa') },
    { value: 'mujeres_postparto', label: t('coach_growth.onboarding_form.s2_opt_mujeres_postparto') },
    { value: 'funcional', label: t('coach_growth.onboarding_form.s2_opt_funcional') },
    { value: 'otro', label: t('coach_growth.onboarding_form.s2_opt_otro') },
]);

const SECONDARY_OPTIONS = computed(() => [
    { value: '', label: t('coach_growth.onboarding_form.s2_secondary_select') },
    { value: 'fuerza', label: t('coach_growth.onboarding_form.s2_opt_fuerza') },
    { value: 'hipertrofia', label: t('coach_growth.onboarding_form.s2_opt_hipertrofia') },
    { value: 'recomposicion', label: t('coach_growth.onboarding_form.s2_opt_recomposicion') },
    { value: 'perdida_grasa', label: t('coach_growth.onboarding_form.s2_opt_perdida_grasa') },
    { value: 'mujeres_postparto', label: t('coach_growth.onboarding_form.s2_opt_mujeres_postparto') },
    { value: 'funcional', label: t('coach_growth.onboarding_form.s2_opt_funcional') },
    { value: 'otro', label: t('coach_growth.onboarding_form.s2_opt_otro') },
]);

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

const differentiatorCount = computed(() => (props.modelValue.differentiator || '').length);
</script>

<template>
  <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-8">
    <header class="mb-6">
      <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-accent">{{ t('coach_growth.onboarding_form.s2_eyebrow') }}</p>
      <h2 class="mt-2 font-display text-3xl uppercase tracking-tight text-wc-text">{{ t('coach_growth.onboarding_form.s2_title') }}</h2>
      <p class="mt-2 font-editorial italic text-base text-wc-text-secondary">{{ t('coach_growth.onboarding_form.s2_subtitle') }}</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ t('coach_growth.onboarding_form.s2_primary_label') }}</label>
        <select
          :value="modelValue.specialty_primary ?? ''"
          @change="update('specialty_primary', $event.target.value || null)"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        >
          <option v-for="o in PRIMARY_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
        </select>
      </div>

      <div v-if="modelValue.specialty_primary === 'otro'">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ t('coach_growth.onboarding_form.s2_primary_other_label') }}</label>
        <input
          type="text"
          :value="modelValue.specialty_primary_other ?? ''"
          @input="update('specialty_primary_other', $event.target.value || null)"
          maxlength="80"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        />
      </div>

      <div>
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ t('coach_growth.onboarding_form.s2_secondary_label') }}</label>
        <select
          :value="modelValue.specialty_secondary ?? ''"
          @change="update('specialty_secondary', $event.target.value || null)"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        >
          <option v-for="o in SECONDARY_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
        </select>
      </div>

      <div v-if="modelValue.specialty_secondary === 'otro'">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ t('coach_growth.onboarding_form.s2_secondary_other_label') }}</label>
        <input
          type="text"
          :value="modelValue.specialty_secondary_other ?? ''"
          @input="update('specialty_secondary_other', $event.target.value || null)"
          maxlength="80"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        />
      </div>

      <div class="md:col-span-2">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          {{ t('coach_growth.onboarding_form.s2_differentiator_label') }}
        </label>
        <textarea
          :value="modelValue.differentiator"
          @input="update('differentiator', $event.target.value)"
          rows="4"
          maxlength="1000"
          :placeholder="t('coach_growth.onboarding_form.s2_differentiator_placeholder')"
          class="mt-2 w-full resize-none rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        ></textarea>
        <div class="mt-1 flex justify-end font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          {{ differentiatorCount }} / 1000
        </div>
      </div>
    </div>
  </section>
</template>
