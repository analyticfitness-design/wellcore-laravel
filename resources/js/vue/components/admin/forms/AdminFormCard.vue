<script setup>
import { computed } from 'vue';
import { formatNumber } from '@/composables/useFormat';

const props = defineProps({
    form: { type: Object, required: true },
    isActive: { type: Boolean, default: false },
});

const emit = defineEmits(['select', 'preview']);

const tagClass = computed(() => ({
    'Inscripcion': 'form-tag--amber',
    'Cliente':     'form-tag--sky',
    'RISE':        'form-tag--violet',
}[props.form.tag] ?? 'form-tag--default'));

const delta = computed(() => {
    const m = props.form.metrics;
    if (!m) return null;
    if (m.last_week === 0) return null;
    return Math.round(((m.this_week - m.last_week) / m.last_week) * 100);
});

const deltaClass = computed(() => {
    if (delta.value === null) return 'text-[var(--color-wc-text-tertiary)]';
    return delta.value >= 0 ? 'text-[var(--color-wc-green-text)]' : 'text-[var(--color-wc-red-text)]';
});

const deltaLabel = computed(() => {
    if (delta.value === null) return '';
    return delta.value >= 0 ? `+${delta.value}%` : `${delta.value}%`;
});
</script>

<template>
  <div
    class="form-card"
    :class="{ 'form-card--active': isActive }"
    role="button"
    tabindex="0"
    :aria-pressed="isActive"
    :aria-labelledby="`form-card-name-${form.area}-${form.slug}`"
    @click="$emit('select')"
    @keydown.enter="$emit('select')"
    @keydown.space.prevent="$emit('select')"
  >
    <!-- Top row: tag + preview button -->
    <div class="form-card__top">
      <span class="form-tag" :class="tagClass">{{ form.tag }}</span>
      <button
        class="form-card__preview-btn"
        :aria-label="`Ver preview de ${form.name}`"
        title="Ver preview del formulario"
        @click.stop="$emit('preview')"
      >
        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        <span>PREVIEW</span>
      </button>
    </div>

    <!-- Name -->
    <p :id="`form-card-name-${form.area}-${form.slug}`" class="form-card__name">{{ form.name.toUpperCase() }}</p>

    <!-- Description -->
    <p class="form-card__desc">{{ form.description }}</p>

    <!-- Metrics -->
    <div v-if="form.has_submissions && form.metrics" class="form-card__metrics">
      <span class="form-card__total">{{ formatNumber(form.metrics.total) }}</span>
      <span class="form-card__total-label">submissions</span>
      <span v-if="deltaLabel" class="form-card__delta" :class="deltaClass">
        {{ deltaLabel }} semana
      </span>
    </div>
    <div v-else class="form-card__no-sub">
      <span>Solo edición</span>
    </div>

    <!-- Route -->
    <div class="form-card__route">
      <svg aria-hidden="true" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
      </svg>
      <code>/{{ form.area }}/{{ form.slug }}</code>
    </div>
  </div>
</template>

<style scoped>
.form-card {
    display: flex;
    flex-direction: column;
    gap: 10px;
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 16px;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out), background 0.15s var(--ease-out);
    outline: none;
}
.form-card:hover {
    border-color: rgba(255,255,255,0.12);
    background: rgba(24, 24, 24, 0.9);
}
.form-card--active {
    border-color: var(--c-accent);
    background: rgba(220, 38, 38, 0.04);
}
.form-card:focus-visible {
    outline: 2px solid var(--c-accent);
    outline-offset: 2px;
}

.form-card__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.form-tag {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: var(--r-pill, 999px);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    font-weight: 500;
    border: 1px solid transparent;
}
.form-tag--amber { background: rgba(245,158,11,0.1); color: #FCD34D; border-color: rgba(245,158,11,0.2); }
.form-tag--sky   { background: rgba(59,130,246,0.1);  color: #60A5FA;  border-color: rgba(59,130,246,0.2); }
.form-tag--violet{ background: rgba(139,92,246,0.1);  color: #a78bfa;  border-color: rgba(139,92,246,0.2); }
.form-tag--default{ background: rgba(255,255,255,0.05); color: var(--c-text-2); }

.form-card__preview-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    min-height: 24px;
    border-radius: 6px;
    border: 1px solid var(--c-border);
    background: transparent;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    cursor: pointer;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.form-card__preview-btn:hover {
    color: var(--c-text);
    border-color: rgba(255,255,255,0.12);
}

.form-card__name {
    font-family: var(--font-display);
    font-size: 18px;
    letter-spacing: 0.04em;
    color: var(--c-text);
    line-height: 1.1;
    margin: 0;
}

.form-card__desc {
    font-family: var(--font-sans);
    font-size: 12px;
    line-height: 1.5;
    color: var(--c-text-2);
    margin: 0;
    flex: 1;
}

.form-card__metrics {
    display: flex;
    align-items: baseline;
    gap: 6px;
    margin-top: 2px;
}
.form-card__total {
    font-family: var(--font-display);
    font-size: 20px;
    font-feature-settings: 'tnum' 1;
    color: var(--c-text);
    line-height: 1;
}
.form-card__total-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.form-card__delta {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.0px;
    margin-left: auto;
}

.form-card__no-sub {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

.form-card__route {
    display: flex;
    align-items: center;
    gap: 5px;
    color: var(--c-text-3);
    margin-top: auto;
}
.form-card__route code {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 0.08em;
}

@media (prefers-reduced-motion: reduce) {
    .form-card { transition: none; }
    .form-card__preview-btn { transition: none; }
}
</style>
