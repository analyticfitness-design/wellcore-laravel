<template>
  <div class="cooldown" data-testid="cooldown-row">
    <span class="cooldown-lab">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Zm0 3h.008v3.75H12v-3.75Z" />
      </svg>
      {{ t('client_plan.v2_cooldown_label') }}
    </span>
    <p class="cooldown-tx">{{ texto }}</p>
  </div>
</template>

<script setup>
// CooldownRow — gemelo del warmup pero en azul/sky.
// CSS lines 643-658 del HTML V2.1.
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
  texto: { type: String, required: true },
});
</script>

<style scoped>
.cooldown {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 16px;
  border-top: 1px solid rgba(56, 189, 248, 0.20);
  background: linear-gradient(90deg, rgba(56, 189, 248, 0.08), transparent);
}
.cooldown-lab {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #38BDF8;
  padding-top: 2px;
}
.cooldown-lab svg {
  width: 12px;
  height: 12px;
}
.cooldown-tx {
  font-size: 13px;
  line-height: 1.55;
  color: var(--wc-text-secondary);
  margin: 0;
}
</style>
