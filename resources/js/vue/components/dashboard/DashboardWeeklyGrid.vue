<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    weekDays: { type: Array, default: () => [] },
});

const { t } = useI18n();

function getIsoWeek(date) {
    const target = new Date(date.valueOf());
    const dayNr = (date.getDay() + 6) % 7;
    target.setDate(target.getDate() - dayNr + 3);
    const firstThursday = target.valueOf();
    target.setMonth(0, 1);
    if (target.getDay() !== 4) {
        target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);
    }
    return 1 + Math.ceil((firstThursday - target) / 604800000);
}

function startOfIsoWeek(date) {
    const d = new Date(date);
    const day = (d.getDay() + 6) % 7;
    d.setDate(d.getDate() - day);
    d.setHours(0, 0, 0, 0);
    return d;
}

const today = new Date();
const isoWeek = getIsoWeek(today);
const monday = startOfIsoWeek(today);
const sunday = new Date(monday);
sunday.setDate(monday.getDate() + 6);

const monthFmt = computed(() => new Intl.DateTimeFormat(t('client_home.weekly_grid_intl_locale'), { month: 'short' }));

function fmtDayMonth(d) {
    let m = monthFmt.value.format(d).replace('.', '');
    return `${d.getDate()} ${m}`;
}

const weekRangeLabel = computed(() => {
    return t('client_home.weekly_grid_week_range', {
        week: isoWeek,
        start: fmtDayMonth(monday),
        end: fmtDayMonth(sunday),
    });
});

const weekCells = computed(() => {
    const labels = t('client_home.weekly_grid_short');
    const cells = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(monday);
        d.setDate(monday.getDate() + i);
        const backendDay = (props.weekDays && props.weekDays[i]) || {};
        const isToday = d.toDateString() === today.toDateString();
        const completed = !!backendDay.completed;
        let status = '';
        if (completed) status = 'done';
        else if (isToday) status = 'today';
        cells.push({
            label: labels[i],
            num: d.getDate(),
            status,
        });
    }
    return cells;
});
</script>

<template>
  <section class="card section wc-card-dashboard-week" :style="{ animationDelay: '440ms' }">
    <div class="card-head">
      <div class="card-head-left" style="min-width:0; overflow:hidden;">
        <span class="card-title" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ t('client_home.weekly_grid_title') }}</span>
      </div>
      <span class="card-meta tnum" style="white-space:nowrap; flex-shrink:0; padding-left:8px;">{{ weekRangeLabel }}</span>
    </div>
    <div class="week-grid">
      <div
        v-for="(cell, idx) in weekCells"
        :key="idx"
        class="weekday"
        :class="cell.status"
      >
        <div class="weekday-name">{{ cell.label }}</div>
        <div class="weekday-num tnum">{{ cell.num }}</div>
        <div class="weekday-dot"></div>
      </div>
    </div>
    <div class="week-legend">
      <span><i style="background:#10B981"></i>{{ t('client_home.weekly_grid_completed') }}</span>
      <span><i style="background:#DC2626"></i>{{ t('client_home.weekly_grid_today') }}</span>
      <span><i class="legend-pending"></i>{{ t('client_home.weekly_grid_pending') }}</span>
    </div>
  </section>
</template>
