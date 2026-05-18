<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
  planName: { type: String, default: '' },
  currentWeek: { type: Number, default: 1 },
  totalWeeks: { type: Number, default: 12 },
  adherence: { type: Number, default: 0 },
});
</script>

<template>
  <section class="hero grain section" :style="{ animationDelay: '40ms' }">
    <div class="hero-content">
      <div class="hero-greeting tight">
        <span class="name">{{ planName || t('client_plan.plan_hero_default_name') }}</span>
      </div>
      <div class="hero-row">
        <span class="chip chip-accent">{{ t('client_plan.plan_hero_week_chip', { current: currentWeek, total: totalWeeks }) }}</span>
        <span v-if="adherence > 0" class="chip" style="background:rgba(16,185,129,.12); color:var(--wc-green); box-shadow:inset 0 0 0 1px rgba(16,185,129,.25);">
          {{ t('client_plan.plan_hero_adherence_chip', { percent: adherence }) }}
        </span>
      </div>
      <p class="hero-sub">{{ t('client_plan.plan_hero_subtitle') }}</p>
    </div>
  </section>
</template>
