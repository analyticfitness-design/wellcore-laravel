<template>
  <div class="lock-demo" data-testid="plan-lock-overlay">
    <div class="lock-overlay">
      <div class="lock-overlay__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
        </svg>
      </div>
      <h3>{{ t('client_plan.v2_lock_title') }}</h3>
      <p>{{ resolvedReason }}</p>
      <p v-if="formattedExpiry" class="lock-overlay__expiry">{{ t('client_plan.v2_lock_expiry_prefix', { date: formattedExpiry }) }}</p>
      <button type="button" class="lock-overlay__cta" @click="$emit('renew')">
        {{ t('client_plan.v2_lock_cta') }}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
// PlanLockOverlay — plan vencido, renueva.
// CSS lines 805-843 del HTML V2.1.
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps({
  expiresAt: { type: [String, Number, Date], default: null },
  reason: { type: String, default: '' },
});

defineEmits(['renew']);

const resolvedReason = computed(() => {
  const r = (props.reason || '').trim();
  return r.length ? r : t('client_plan.v2_lock_default_reason');
});

const formattedExpiry = computed(() => {
  if (!props.expiresAt) return '';
  try {
    const d = new Date(props.expiresAt);
    if (Number.isNaN(d.getTime())) return '';
    const intlLocale = locale.value === 'en' ? 'en-US' : 'es-CO';
    return new Intl.DateTimeFormat(intlLocale, {
      day: '2-digit', month: 'short', year: 'numeric',
    }).format(d);
  } catch {
    return '';
  }
});
</script>

<style scoped>
.lock-demo {
  position: relative;
  min-height: 320px;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid var(--wc-border);
  background:
    repeating-linear-gradient(45deg, rgba(220, 38, 38, 0.04) 0 12px, rgba(220, 38, 38, 0.02) 12px 24px),
    var(--wc-bg-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 24px;
}
.lock-overlay {
  text-align: center;
  max-width: 360px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
}
.lock-overlay__icon {
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  background: rgba(220, 38, 38, 0.12);
  border: 1px solid rgba(220, 38, 38, 0.30);
}
.lock-overlay__icon svg {
  width: 28px;
  height: 28px;
  color: #EF4444;
}
.lock-overlay h3 {
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 22px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--wc-text);
  margin: 0;
}
.lock-overlay p {
  font-size: 14px;
  line-height: 1.55;
  color: var(--wc-text-secondary);
  margin: 0;
}
.lock-overlay__expiry {
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 11px;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--wc-text-tertiary);
  margin-top: -6px;
}
.lock-overlay__cta {
  margin-top: 6px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: none;
  border-radius: 16px;
  background: var(--wc-accent);
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #fff;
  box-shadow: 0 8px 24px -8px rgba(220, 38, 38, 0.50);
  cursor: pointer;
  transition: transform .12s ease, box-shadow .12s ease;
}
.lock-overlay__cta:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 28px -8px rgba(220, 38, 38, 0.65);
}
.lock-overlay__cta svg { width: 14px; height: 14px; }
</style>
