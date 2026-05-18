<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const METHODOLOGIES = computed(() => [
    { value: 'sobrecarga_progresiva', label: t('coach_growth.onboarding_form.s4_m_sobrecarga') },
    { value: 'deficit_calorico', label: t('coach_growth.onboarding_form.s4_m_deficit') },
    { value: 'periodizacion_lineal', label: t('coach_growth.onboarding_form.s4_m_per_lineal') },
    { value: 'periodizacion_ondulante', label: t('coach_growth.onboarding_form.s4_m_per_ondulante') },
    { value: 'full_body', label: t('coach_growth.onboarding_form.s4_m_fullbody') },
    { value: 'push_pull_legs', label: t('coach_growth.onboarding_form.s4_m_ppl') },
    { value: 'upper_lower', label: t('coach_growth.onboarding_form.s4_m_upper_lower') },
    { value: 'calistenia', label: t('coach_growth.onboarding_form.s4_m_calistenia') },
    { value: 'hiit', label: t('coach_growth.onboarding_form.s4_m_hiit') },
    { value: 'baja_carga_alta_volumen', label: t('coach_growth.onboarding_form.s4_m_baja_alta') },
]);

const TOPICS = computed(() => [
    { value: 'mitos_fitness', label: t('coach_growth.onboarding_form.s4_t_mitos') },
    { value: 'transformaciones', label: t('coach_growth.onboarding_form.s4_t_transformaciones') },
    { value: 'ciencia_del_entrenamiento', label: t('coach_growth.onboarding_form.s4_t_ciencia') },
    { value: 'recetas_macros', label: t('coach_growth.onboarding_form.s4_t_recetas') },
    { value: 'suplementacion', label: t('coach_growth.onboarding_form.s4_t_suplementacion') },
    { value: 'mentalidad', label: t('coach_growth.onboarding_form.s4_t_mentalidad') },
    { value: 'vida_real', label: t('coach_growth.onboarding_form.s4_t_vida_real') },
    { value: 'tutoriales_tecnica', label: t('coach_growth.onboarding_form.s4_t_tutoriales') },
]);

const newMethodology = ref('');
const newTopic = ref('');

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

function toggleArrayValue(field, value) {
    const current = Array.isArray(props.modelValue[field]) ? [...props.modelValue[field]] : [];
    const idx = current.indexOf(value);
    if (idx >= 0) current.splice(idx, 1);
    else current.push(value);
    update(field, current);
}

function isSelected(field, value) {
    const arr = props.modelValue[field];
    return Array.isArray(arr) && arr.includes(value);
}

function addOther(field, ref) {
    const v = (ref.value || '').trim();
    if (!v) return;
    const current = Array.isArray(props.modelValue[field]) ? [...props.modelValue[field]] : [];
    if (current.length >= 5) return;
    if (current.includes(v)) return;
    current.push(v);
    update(field, current);
    ref.value = '';
}

function removeOther(field, value) {
    const current = Array.isArray(props.modelValue[field]) ? [...props.modelValue[field]] : [];
    update(field, current.filter((v) => v !== value));
}
</script>

<template>
  <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-8 space-y-10">
    <header>
      <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-accent">{{ t('coach_growth.onboarding_form.s4_eyebrow') }}</p>
      <h2 class="mt-2 font-display text-3xl uppercase tracking-tight text-wc-text">{{ t('coach_growth.onboarding_form.s4_title') }}</h2>
      <p class="mt-2 font-editorial italic text-base text-wc-text-secondary">{{ t('coach_growth.onboarding_form.s4_subtitle') }}</p>
    </header>

    <div>
      <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
        {{ t('coach_growth.onboarding_form.s4_methods_label') }}
      </label>
      <div class="mt-3 flex flex-wrap gap-2">
        <button
          v-for="opt in METHODOLOGIES"
          :key="opt.value"
          type="button"
          :class="[
            'rounded-full border px-4 py-2 text-sm uppercase tracking-wide transition-colors',
            isSelected('preferred_methodologies', opt.value)
              ? 'border-wc-accent bg-wc-accent text-white'
              : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-wc-accent/50',
          ]"
          @click="toggleArrayValue('preferred_methodologies', opt.value)"
        >
          {{ opt.label }}
        </button>
      </div>

      <div v-if="(modelValue.preferred_methodologies_other || []).length" class="mt-3 flex flex-wrap gap-2">
        <span
          v-for="o in modelValue.preferred_methodologies_other"
          :key="o"
          class="inline-flex items-center gap-2 rounded-full border border-wc-accent/40 bg-wc-accent/10 px-3 py-1.5 text-xs uppercase tracking-wide text-wc-accent"
        >
          {{ o }}
          <button type="button" class="hover:text-white" @click="removeOther('preferred_methodologies_other', o)">
            &times;
          </button>
        </span>
      </div>

      <div class="mt-3 flex gap-2">
        <input
          v-model="newMethodology"
          type="text"
          maxlength="80"
          :placeholder="t('coach_growth.onboarding_form.s4_add_method_placeholder')"
          class="flex-1 rounded-lg border border-wc-border bg-wc-bg px-4 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
          @keydown.enter.prevent="addOther('preferred_methodologies_other', newMethodology)"
        />
        <button
          type="button"
          class="rounded-lg border border-wc-border bg-wc-bg px-4 py-2 font-mono text-xs uppercase tracking-[0.15em] text-wc-text-secondary hover:border-wc-accent hover:text-wc-text"
          @click="addOther('preferred_methodologies_other', newMethodology)"
        >
          {{ t('coach_growth.onboarding_form.add_btn') }}
        </button>
      </div>
    </div>

    <div>
      <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
        {{ t('coach_growth.onboarding_form.s4_topics_label') }}
      </label>
      <div class="mt-3 flex flex-wrap gap-2">
        <button
          v-for="opt in TOPICS"
          :key="opt.value"
          type="button"
          :class="[
            'rounded-full border px-4 py-2 text-sm uppercase tracking-wide transition-colors',
            isSelected('content_topics', opt.value)
              ? 'border-wc-accent bg-wc-accent text-white'
              : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-wc-accent/50',
          ]"
          @click="toggleArrayValue('content_topics', opt.value)"
        >
          {{ opt.label }}
        </button>
      </div>

      <div v-if="(modelValue.content_topics_other || []).length" class="mt-3 flex flex-wrap gap-2">
        <span
          v-for="o in modelValue.content_topics_other"
          :key="o"
          class="inline-flex items-center gap-2 rounded-full border border-wc-accent/40 bg-wc-accent/10 px-3 py-1.5 text-xs uppercase tracking-wide text-wc-accent"
        >
          {{ o }}
          <button type="button" class="hover:text-white" @click="removeOther('content_topics_other', o)">
            &times;
          </button>
        </span>
      </div>

      <div class="mt-3 flex gap-2">
        <input
          v-model="newTopic"
          type="text"
          maxlength="80"
          :placeholder="t('coach_growth.onboarding_form.s4_add_topic_placeholder')"
          class="flex-1 rounded-lg border border-wc-border bg-wc-bg px-4 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
          @keydown.enter.prevent="addOther('content_topics_other', newTopic)"
        />
        <button
          type="button"
          class="rounded-lg border border-wc-border bg-wc-bg px-4 py-2 font-mono text-xs uppercase tracking-[0.15em] text-wc-text-secondary hover:border-wc-accent hover:text-wc-text"
          @click="addOther('content_topics_other', newTopic)"
        >
          {{ t('coach_growth.onboarding_form.add_btn') }}
        </button>
      </div>
    </div>
  </section>
</template>
