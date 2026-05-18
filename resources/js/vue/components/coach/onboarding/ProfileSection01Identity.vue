<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const COUNTRIES = computed(() => [
    { code: '', label: t('coach_growth.onboarding_form.s1_country_select') },
    { code: 'CO', label: t('coach_growth.onboarding_form.s1_country_co') },
    { code: 'MX', label: t('coach_growth.onboarding_form.s1_country_mx') },
    { code: 'AR', label: t('coach_growth.onboarding_form.s1_country_ar') },
    { code: 'CL', label: t('coach_growth.onboarding_form.s1_country_cl') },
    { code: 'PE', label: t('coach_growth.onboarding_form.s1_country_pe') },
    { code: 'EC', label: t('coach_growth.onboarding_form.s1_country_ec') },
    { code: 'VE', label: t('coach_growth.onboarding_form.s1_country_ve') },
    { code: 'US', label: t('coach_growth.onboarding_form.s1_country_us') },
    { code: 'ES', label: t('coach_growth.onboarding_form.s1_country_es') },
    { code: 'XX', label: t('coach_growth.onboarding_form.s1_country_xx') },
]);

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}
</script>

<template>
  <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-8">
    <header class="mb-6">
      <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-accent">{{ t('coach_growth.onboarding_form.s1_eyebrow') }}</p>
      <h2 class="mt-2 font-display text-3xl uppercase tracking-tight text-wc-text">{{ t('coach_growth.onboarding_form.s1_title') }}</h2>
      <p class="mt-2 font-editorial italic text-base text-wc-text-secondary">{{ t('coach_growth.onboarding_form.s1_subtitle') }}</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          {{ t('coach_growth.onboarding_form.s1_brand_name_label') }}
        </label>
        <input
          type="text"
          :value="modelValue.brand_name"
          @input="update('brand_name', $event.target.value)"
          maxlength="120"
          :placeholder="t('coach_growth.onboarding_form.s1_brand_name_placeholder')"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        />
      </div>

      <div>
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          {{ t('coach_growth.onboarding_form.s1_city_label') }}
        </label>
        <input
          type="text"
          :value="modelValue.city ?? ''"
          @input="update('city', $event.target.value || null)"
          maxlength="80"
          :placeholder="t('coach_growth.onboarding_form.s1_city_placeholder')"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        />
      </div>

      <div>
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          {{ t('coach_growth.onboarding_form.s1_country_label') }}
        </label>
        <select
          :value="modelValue.country_code ?? ''"
          @change="update('country_code', $event.target.value || null)"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        >
          <option v-for="c in COUNTRIES" :key="c.code" :value="c.code">{{ c.label }}</option>
        </select>
      </div>
    </div>
  </section>
</template>
