<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const AGE_RANGES = [
    { value: '18-25', label: '18-25' },
    { value: '25-35', label: '25-35' },
    { value: '35-45', label: '35-45' },
    { value: '45+', label: '45+' },
];

const GENDERS = computed(() => [
    { value: 'mujeres', label: t('coach_growth.onboarding_form.s3_gender_mujeres') },
    { value: 'hombres', label: t('coach_growth.onboarding_form.s3_gender_hombres') },
    { value: 'mixto', label: t('coach_growth.onboarding_form.s3_gender_mixto') },
]);

const OFFER_MAINS = computed(() => [
    { value: 'esencial', label: t('coach_growth.onboarding_form.s3_offer_esencial') },
    { value: 'metodo', label: t('coach_growth.onboarding_form.s3_offer_metodo') },
    { value: 'elite', label: t('coach_growth.onboarding_form.s3_offer_elite') },
    { value: 'presencial', label: t('coach_growth.onboarding_form.s3_offer_presencial') },
    { value: 'otro', label: t('coach_growth.onboarding_form.s3_offer_otro') },
]);

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

function isSelected(field, value) {
    return props.modelValue[field] === value;
}

const painCount = computed(() => (props.modelValue.audience_pain_main || '').length);
</script>

<template>
  <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-8 space-y-8">
    <header>
      <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-accent">{{ t('coach_growth.onboarding_form.s3_eyebrow') }}</p>
      <h2 class="mt-2 font-display text-3xl uppercase tracking-tight text-wc-text">{{ t('coach_growth.onboarding_form.s3_title') }}</h2>
      <p class="mt-2 font-editorial italic text-base text-wc-text-secondary">{{ t('coach_growth.onboarding_form.s3_subtitle') }}</p>
    </header>

    <div>
      <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ t('coach_growth.onboarding_form.s3_age_label') }}</label>
      <div class="mt-3 flex flex-wrap gap-2">
        <button
          v-for="opt in AGE_RANGES"
          :key="opt.value"
          type="button"
          :class="[
            'rounded-full border px-4 py-2 text-sm uppercase tracking-wide transition-colors',
            isSelected('audience_age_range', opt.value)
              ? 'border-wc-accent bg-wc-accent text-white'
              : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-wc-accent/50',
          ]"
          @click="update('audience_age_range', opt.value)"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <div>
      <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ t('coach_growth.onboarding_form.s3_gender_label') }}</label>
      <div class="mt-3 flex flex-wrap gap-2">
        <button
          v-for="opt in GENDERS"
          :key="opt.value"
          type="button"
          :class="[
            'rounded-full border px-4 py-2 text-sm uppercase tracking-wide transition-colors',
            isSelected('audience_gender', opt.value)
              ? 'border-wc-accent bg-wc-accent text-white'
              : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-wc-accent/50',
          ]"
          @click="update('audience_gender', opt.value)"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <div>
      <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
        {{ t('coach_growth.onboarding_form.s3_pain_label') }}
      </label>
      <textarea
        :value="modelValue.audience_pain_main"
        @input="update('audience_pain_main', $event.target.value)"
        rows="3"
        maxlength="200"
        :placeholder="t('coach_growth.onboarding_form.s3_pain_placeholder')"
        class="mt-2 w-full resize-none rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
      ></textarea>
      <div class="mt-1 flex justify-end font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">
        {{ painCount }} / 200
      </div>
    </div>

    <div>
      <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ t('coach_growth.onboarding_form.s3_offer_label') }}</label>
      <div class="mt-3 flex flex-wrap gap-2">
        <button
          v-for="opt in OFFER_MAINS"
          :key="opt.value"
          type="button"
          :class="[
            'rounded-full border px-4 py-2 text-sm uppercase tracking-wide transition-colors',
            isSelected('audience_offer_main', opt.value)
              ? 'border-wc-accent bg-wc-accent text-white'
              : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-wc-accent/50',
          ]"
          @click="update('audience_offer_main', opt.value)"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>
  </section>
</template>
