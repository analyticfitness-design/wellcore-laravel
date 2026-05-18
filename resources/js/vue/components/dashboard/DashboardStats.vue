<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    data: { type: Object, required: true },
    xpProgress: { type: Number, default: 0 },
    trainedRingOffset: { type: Number, default: 251 },
});

const { t } = useI18n();

// Texto auxiliar XP: "X / 200 al nivel N+1"
const xpDeltaText = computed(() => {
    const total = props.data.xpTotal || 0;
    const floor = props.data.xpCurrentLevelFloor || 0;
    const inLevel = total - floor;
    const nextLevel = (props.data.level || 1) + 1;
    return t('client_home.stat_xp_progress', { in: inLevel, next: nextLevel });
});

// Streak label "1 day in a row" / "X days in a row"
const streakUnitText = computed(() => {
    const d = props.data.streakDays || 0;
    return d === 1
        ? t('client_home.stat_streak_day_consec_singular')
        : t('client_home.stat_streak_day_consec_plural');
});

const streakUnitInline = computed(() => {
    const d = props.data.streakDays || 0;
    return d === 1
        ? t('client_home.stat_streak_day_singular')
        : t('client_home.stat_streak_day_plural');
});
</script>

<template>
  <div class="stats-grid section" :style="{ animationDelay: '220ms' }">
    <!-- RACHA (red) -->
    <div class="stat-card red">
      <svg class="stat-ghost" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5Z"></path>
      </svg>
      <div class="stat-label">{{ t('client_home.stat_streak') }}</div>
      <div class="stat-value tight">{{ data.streakDays || 0 }}<span class="unit">{{ streakUnitInline }}</span></div>
      <div class="stat-sub">{{ streakUnitText }}</div>
    </div>

    <!-- CHECK-INS (green) -->
    <div class="stat-card green">
      <svg class="stat-ghost" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 11l3 3L22 4"></path>
        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
      </svg>
      <div class="stat-label">{{ t('client_home.stat_checkins') }}</div>
      <div class="stat-value tight">{{ data.checkinsThisMonth || 0 }}</div>
      <div class="stat-sub">{{ t('client_home.stat_this_month') }}</div>
    </div>

    <!-- NIVEL + XP (purple) -->
    <div class="stat-card purple">
      <svg class="stat-ghost" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
      </svg>
      <div class="stat-label">{{ t('client_home.stat_level') }} {{ data.level || 1 }}</div>
      <div class="stat-value tight tnum">{{ (data.xpTotal || 0).toLocaleString() }}<span class="unit">{{ t('client_home.stat_xp') }}</span></div>
      <div class="stat-sub">{{ xpDeltaText }}</div>
      <div class="xp-bar">
        <div class="xp-fill" :style="{ '--pct': xpProgress + '%' }"></div>
      </div>
    </div>

    <!-- ESTA SEMANA (amber) -->
    <div class="stat-card amber">
      <svg class="stat-ghost" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
        <path d="M16 2v4M8 2v4M3 10h18"></path>
      </svg>
      <div class="stat-label">{{ t('client_home.stat_this_week') }}</div>
      <div class="stat-value tight tnum">{{ data.trainedThisWeek || 0 }}<span class="unit">/7</span></div>
      <div class="stat-sub">{{ t('client_home.stat_days_trained') }}</div>
    </div>
  </div>
</template>
