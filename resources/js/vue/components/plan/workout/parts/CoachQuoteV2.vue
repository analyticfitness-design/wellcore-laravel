<template>
  <div class="coach-quote" data-testid="coach-quote-v2">
    <div class="cq-row">
      <div class="cq-avatar">{{ avatarLetter }}</div>
      <div class="cq-meta">
        <div class="cq-name">{{ coachName || t('client_plan.v2_coach_default_name') }}</div>
        <div class="cq-role">{{ t('client_plan.v2_coach_role') }}</div>
      </div>
      <div v-if="timeAgo" class="cq-time">{{ timeAgo }}</div>
    </div>
    <p class="cq-body">{{ message }}</p>
    <div class="cq-foot">
      <span class="cq-sig">{{ t('client_plan.v2_coach_signature', { weeks: totalWeeks }) }}</span>
      <button v-if="canReply" type="button" class="cq-reply" @click="$emit('reply')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
        </svg>
        {{ t('client_plan.v2_coach_reply') }}
      </button>
    </div>
  </div>
</template>

<script setup>
// CoachQuoteV2 — bloque del mensaje del coach con avatar + body + foot.
// CSS lines 348-403 del HTML V2.1.
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  coachName: { type: String, default: '' },
  message: { type: String, default: '' },
  totalWeeks: { type: Number, default: 4 },
  timeAgo: { type: String, default: '' },
  canReply: { type: Boolean, default: true },
});

defineEmits(['reply']);

const avatarLetter = computed(() => {
  const n = (props.coachName || '').trim();
  if (!n) return 'C';
  return n.charAt(0).toUpperCase();
});
</script>

<style scoped>
.coach-quote {
  position: relative;
  border-radius: 16px;
  border: 1px solid var(--wc-border, rgba(255,255,255,0.08));
  background: linear-gradient(180deg, var(--wc-bg-tertiary, #18181B), var(--wc-bg-secondary, #111113));
  overflow: hidden;
}
.coach-quote::before {
  content: '"';
  position: absolute;
  top: -28px;
  left: 14px;
  font-family: 'Oswald', Impact, sans-serif;
  font-weight: 700;
  font-size: 180px;
  line-height: 1;
  color: rgba(220,38,38,0.10);
  pointer-events: none;
}
.cq-row {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 18px 18px 0;
  position: relative;
}
.cq-avatar {
  width: 44px;
  height: 44px;
  border-radius: 999px;
  background: linear-gradient(135deg, #DC2626, #7F1D1D);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Oswald', Impact, sans-serif;
  font-weight: 600;
  color: #fff;
  font-size: 18px;
  flex-shrink: 0;
  box-shadow: 0 0 0 2px var(--wc-bg-secondary, #111113), 0 0 0 3px rgba(220,38,38,0.30);
}
.cq-meta {
  flex: 1;
  min-width: 0;
}
.cq-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--wc-text, #FAFAFA);
}
.cq-role {
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 10px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #EF4444;
  margin-top: 2px;
}
.cq-time {
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 10px;
  color: var(--wc-text-tertiary, rgba(250,250,250,0.40));
  margin-left: auto;
  flex-shrink: 0;
}
.cq-body {
  padding: 12px 18px 18px;
  font-size: 15px;
  line-height: 1.65;
  color: var(--wc-text-secondary, rgba(250,250,250,0.72));
  font-style: italic;
  position: relative;
  margin: 0;
}
.cq-body :deep(strong) {
  color: var(--wc-text, #FAFAFA);
  font-weight: 600;
  font-style: normal;
}
.cq-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 18px;
  border-top: 1px solid var(--wc-border, rgba(255,255,255,0.08));
  background: rgba(0,0,0,0.30);
  flex-wrap: wrap;
  gap: 8px;
}
.cq-sig {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 10px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--wc-text-tertiary, rgba(250,250,250,0.40));
}
.cq-reply {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  border: 1px solid var(--wc-border, rgba(255,255,255,0.14));
  background: rgba(255,255,255,0.04);
  font-size: 12px;
  font-weight: 500;
  color: var(--wc-text-secondary, rgba(250,250,250,0.72));
  cursor: pointer;
  font-family: inherit;
  transition: background 0.15s, border-color 0.15s;
}
.cq-reply:hover {
  background: rgba(255,255,255,0.08);
  border-color: rgba(255,255,255,0.20);
}
.cq-reply svg { width: 12px; height: 12px; }
</style>
