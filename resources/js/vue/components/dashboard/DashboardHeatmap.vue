<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    data: { type: Object, required: true },
    calendarDays: { type: Array, default: () => [] },
    userVsGroup: { type: Object, default: null },
});

const { t, tc, locale } = useI18n();

const heatColumns = computed(() => {
    const cols = [];
    const days = props.calendarDays || [];
    for (let i = 0; i < days.length; i += 7) {
        cols.push(days.slice(i, i + 7));
    }
    return cols;
});

function getCount(day) {
    if (!day || day.isFuture || day.isBeforeRange) return 0;
    if (!props.data?.streakCalendar) return 0;
    return props.data.streakCalendar[day.date] || 0;
}

function getCellClass(day) {
    if (!day) return '';
    const classes = [];
    if (day.isToday) classes.push('today');
    if (day.isFuture || day.isBeforeRange) return classes.join(' ');
    const count = getCount(day);
    if (count >= 5) classes.push('l4');
    else if (count >= 3) classes.push('l3');
    else if (count === 2) classes.push('l2');
    else if (count === 1) classes.push('l1');
    return classes.join(' ');
}

function getCellStyle(day) {
    if (!day) return { visibility: 'hidden' };
    if (day.isFuture && !day.isToday) return { visibility: 'hidden' };
    return null;
}

function cellTitle(day) {
    if (!day) return '';
    const c = getCount(day);
    if (!c) return day.displayDate;
    // Pluralize via array selection
    const unit = c === 1
        ? t('client_home.heatmap_session_count', { n: c }).split('|')[0]
        : t('client_home.heatmap_session_count', { n: c }).split('|')[1] || t('client_home.heatmap_session_count', { n: c });
    return `${day.displayDate} · ${unit}`;
}

const totalSessions = computed(() => {
    const cal = props.data?.streakCalendar || {};
    return Object.values(cal).reduce((sum, c) => sum + (Number(c) || 0), 0);
});

const bestStreak = computed(() => props.data?.calendarStreak || 0);

const dayLabels = computed(() => t('client_home.heatmap_days'));

const monthLabels = computed(() => {
    const today = new Date();
    const labels = [];
    const intlLocale = t('client_home.heatmap_intl_month'); // 'es' | 'en'
    const fmt = new Intl.DateTimeFormat(intlLocale, { month: 'short' });
    for (let m = 3; m >= 0; m--) {
        const d = new Date(today.getFullYear(), today.getMonth() - m, 1);
        let label = fmt.format(d).replace('.', '');
        label = label.charAt(0).toUpperCase() + label.slice(1);
        labels.push(label);
    }
    return labels;
});

const bestStreakUnit = computed(() => bestStreak.value === 1
    ? t('client_home.stat_streak_day_singular')
    : t('client_home.stat_streak_day_plural'));
</script>

<template>
  <section class="card section wc-card-dashboard-heatmap" :style="{ animationDelay: '380ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">{{ t('client_home.heatmap_title') }}</span>
      </div>
      <span class="card-meta">{{ t('client_home.heatmap_meta') }}</span>
    </div>
    <div class="heatmap">
      <div class="heat-months">
        <span v-for="(m, idx) in monthLabels" :key="idx">{{ m }}</span>
      </div>
      <div class="heat-grid">
        <div class="heat-days">
          <span v-for="(d, idx) in dayLabels" :key="idx">{{ d }}</span>
        </div>
        <div class="heat-cols">
          <div v-for="(col, ci) in heatColumns" :key="ci" class="heat-col">
            <div
              v-for="(day, ri) in col"
              :key="ri"
              class="heat-cell"
              :class="getCellClass(day)"
              :style="getCellStyle(day)"
              :title="cellTitle(day)"
            ></div>
          </div>
        </div>
      </div>
      <div class="heat-legend">
        <span>{{ t('client_home.heatmap_less') }}</span>
        <div class="heat-legend-cells">
          <div class="heat-cell"></div>
          <div class="heat-cell l1"></div>
          <div class="heat-cell l2"></div>
          <div class="heat-cell l3"></div>
          <div class="heat-cell l4"></div>
        </div>
        <span>{{ t('client_home.heatmap_more') }}</span>
      </div>
    </div>
    <hr class="divider" />
    <div class="heat-summary">
      <div class="heat-count tnum">
        <span class="accent">{{ totalSessions }}</span><small>{{ t('client_home.heatmap_sessions_total') }}</small>
      </div>
      <div class="heat-count tnum">
        {{ t('client_home.heatmap_best_streak') }} <span class="accent">{{ bestStreak }}</span><small>{{ bestStreakUnit }}</small>
      </div>
    </div>
    <div v-if="userVsGroup" class="hm-vs-group">
      <span class="hm-vs-item">
        <span class="hm-vs-label">{{ t('client_home.heatmap_vs_user') }}</span>
        <span class="hm-vs-num tnum">{{ userVsGroup.user }}<span class="hm-vs-unit">{{ t('client_home.heatmap_vs_unit') }}</span></span>
      </span>
      <span class="hm-vs-divider" aria-hidden="true"></span>
      <span class="hm-vs-item">
        <span class="hm-vs-label">{{ t('client_home.heatmap_vs_group') }}</span>
        <span class="hm-vs-num tnum">{{ userVsGroup.group_avg }}<span class="hm-vs-unit">{{ t('client_home.heatmap_vs_unit') }}</span></span>
      </span>
      <span class="hm-vs-rank">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
          <polyline points="16 7 22 7 22 13"></polyline>
        </svg>
        {{ t('client_home.heatmap_vs_top', { pct: userVsGroup.rank_pct }) }}
      </span>
    </div>
  </section>
</template>

<style scoped>
.hm-vs-group { display: flex; align-items: center; gap: var(--s12); margin-top: var(--s12); padding-top: var(--s12); border-top: 1px solid var(--wc-border); font-family: var(--fs); }
.hm-vs-item { display: flex; flex-direction: column; gap: 2px; }
.hm-vs-label { font-size: 10px; color: var(--wc-text-3); text-transform: uppercase; letter-spacing: 0.06em; }
.hm-vs-num { font-family: var(--fd); font-weight: 600; font-size: 16px; color: var(--wc-text); letter-spacing: -0.01em; }
.hm-vs-unit { font-size: 11px; color: var(--wc-text-3); font-weight: 400; margin-left: 2px; }
.hm-vs-divider { width: 1px; align-self: stretch; background: var(--wc-border); }
.hm-vs-rank { display: inline-flex; align-items: center; gap: 4px; margin-left: auto; padding: 4px 10px; border-radius: var(--r-pill); background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.30); color: var(--wc-amber); font: 600 11px/1 var(--fs); letter-spacing: 0.02em; }
</style>
