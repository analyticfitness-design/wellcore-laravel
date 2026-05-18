<template>
  <div class="error-state" data-testid="plan-error-state">
    <div class="error-state__icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
      </svg>
    </div>
    <h3>{{ t('client_plan.v2_error_title') }}</h3>
    <p>{{ message || t('client_plan.v2_error_default_message') }}</p>
    <button type="button" class="error-state__retry" @click="$emit('retry')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
      </svg>
      {{ t('client_plan.v2_error_retry') }}
    </button>
  </div>
</template>

<script setup>
// PlanErrorState — error fallback con retry.
// CSS lines 896-925 del HTML V2.1.
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
  message: { type: String, default: '' },
});
defineEmits(['retry']);
</script>

<style scoped>
.error-state {
  text-align: center;
  padding: 40px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  border-radius: 16px;
  border: 1px solid var(--wc-border);
  background: var(--wc-bg-secondary);
}
.error-state__icon {
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  background: rgba(220, 38, 38, 0.12);
  border: 1px solid rgba(220, 38, 38, 0.30);
}
.error-state__icon svg {
  width: 26px;
  height: 26px;
  color: #EF4444;
}
.error-state h3 {
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 18px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--wc-text);
  margin: 0;
}
.error-state p {
  font-size: 13px;
  color: var(--wc-text-secondary);
  max-width: 280px;
  line-height: 1.5;
  margin: 0;
}
.error-state__retry {
  margin-top: 8px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 18px;
  border-radius: 12px;
  border: 1px solid rgba(220, 38, 38, 0.40);
  background: rgba(220, 38, 38, 0.08);
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #EF4444;
  cursor: pointer;
  transition: background-color .15s ease, border-color .15s ease;
}
.error-state__retry:hover {
  background: rgba(220, 38, 38, 0.14);
  border-color: rgba(220, 38, 38, 0.55);
}
.error-state__retry svg { width: 12px; height: 12px; }
</style>
