<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  stats: { type: Object, required: true },
});

const xpProgress = computed(() => {
  const current = props.stats.xpCurrentLevel || 0;
  const max = props.stats.xpNextLevel || 200;
  return {
    current,
    max,
    percent: Math.min(100, Math.round((current / Math.max(1, max)) * 100)),
  };
});
</script>

<template>
  <section class="hero grain section" :style="{ animationDelay: '40ms' }">
    <div class="hero-content">
      <div class="hero-greeting tight">
        <span class="name">{{ t('client_account.achievements_level') }}</span> {{ stats.level || 1 }}
      </div>
      <div class="hero-row">
        <span class="chip chip-accent">{{ t('client_account.achievements_streak_days', { n: stats.streak || 0 }) }}</span>
      </div>
      <p class="hero-sub">
        {{ t('client_account.achievements_xp_to_next', {
          current: (xpProgress.current).toLocaleString(),
          max: (xpProgress.max).toLocaleString(),
          level: (stats.level || 1) + 1,
        }) }}
      </p>
      <div class="xp-bar" style="margin-top: 14px; max-width: 320px;">
        <div class="xp-fill" :style="{ '--pct': xpProgress.percent + '%' }"></div>
      </div>
    </div>
  </section>
</template>
