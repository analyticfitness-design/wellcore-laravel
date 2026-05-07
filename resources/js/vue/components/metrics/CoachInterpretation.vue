<script setup>
defineProps({
  interpretation: {
    type: Object,
    default: null,
    // { message, date, coach_name, coach_initials }
  },
});
</script>

<template>
  <div v-if="interpretation" class="coach-card">
    <!-- Avatar -->
    <div class="coach-avatar" aria-hidden="true">
      {{ interpretation.coach_initials || 'WC' }}
    </div>

    <div class="coach-body">
      <!-- Header -->
      <div class="coach-hd">
        <span class="coach-who">{{ interpretation.coach_name }}</span>
        <span class="coach-role">Coach</span>
        <span class="coach-time">{{ interpretation.date }}</span>
      </div>

      <!-- Message -->
      <p class="coach-msg">{{ interpretation.message }}</p>

      <!-- CTAs -->
      <div class="coach-cta">
        <a href="/client/checkin" class="cta-btn cta-btn--ghost">Ver check-in</a>
      </div>
    </div>
  </div>
</template>

<style scoped>
.coach-card {
  display: flex;
  gap: 18px;
  padding: 22px;
  border-radius: 16px;
  border: 1px solid rgba(59,130,246,.22);
  background: linear-gradient(180deg, rgba(59,130,246,.07), rgba(59,130,246,.02));
  margin-bottom: 24px;
}
.coach-avatar {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  border-radius: 999px;
  background: linear-gradient(135deg, #3B82F6, #1E40AF);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-display);
  font-size: 18px;
  font-weight: 600;
  color: #fff;
}
.coach-body { flex: 1; min-width: 0; }
.coach-hd {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}
.coach-who { font-size: 13px; font-weight: 600; color: var(--color-wc-text); }
.coach-role {
  font-family: var(--font-mono);
  font-size: 11px;
  color: #93C5FD;
  letter-spacing: .06em;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(59,130,246,.12);
  border: 1px solid rgba(59,130,246,.22);
}
.coach-time {
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--color-wc-text-tertiary);
  letter-spacing: .04em;
  margin-left: auto;
}
.coach-msg {
  font-size: 14.5px;
  line-height: 1.6;
  color: var(--color-wc-text);
  margin: 0;
}
.coach-cta { margin-top: 14px; display: flex; gap: 8px; flex-wrap: wrap; }
.cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  min-height: 36px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  text-decoration: none;
  transition: background .12s;
}
.cta-btn--ghost {
  background: var(--color-wc-bg-secondary);
  color: var(--color-wc-text);
  border: 1px solid var(--color-wc-border);
}
.cta-btn--ghost:hover { background: var(--color-wc-bg-tertiary); }
</style>
