<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: { type: Object, required: true },
    calendarDays: { type: Array, default: () => [] },
});

// Convertir flat [day...] en columnas de 7 filas (Lun-Dom).
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
    if (day.isFuture || day.isBeforeRange) {
        // empty / hidden
        return classes.join(' ');
    }
    const count = getCount(day);
    if (count >= 5) classes.push('l4');
    else if (count >= 3) classes.push('l3');
    else if (count === 2) classes.push('l2');
    else if (count === 1) classes.push('l1');
    return classes.join(' ');
}

// Today color usa l1 al menos para el ring; si hay sesión hoy, usar el nivel real.
function getCellStyle(day) {
    if (!day) return { visibility: 'hidden' };
    if (day.isFuture && !day.isToday) return { visibility: 'hidden' };
    return null;
}

// Sesiones totales en los 90 días
const totalSessions = computed(() => {
    const cal = props.data?.streakCalendar || {};
    return Object.values(cal).reduce((sum, c) => sum + (Number(c) || 0), 0);
});

// Mejor racha: usar data.calendarStreak si existe
const bestStreak = computed(() => props.data?.calendarStreak || 0);

// Etiquetas de meses: distribuir Feb/Mar/Abr/May según rango ~3 meses pasados
const monthLabels = computed(() => {
    const today = new Date();
    const labels = [];
    // 4 etiquetas: hace 3 meses, hace 2, hace 1, mes actual
    const fmt = new Intl.DateTimeFormat('es', { month: 'short' });
    for (let m = 3; m >= 0; m--) {
        const d = new Date(today.getFullYear(), today.getMonth() - m, 1);
        let label = fmt.format(d).replace('.', '');
        // Capitalizar primera letra
        label = label.charAt(0).toUpperCase() + label.slice(1);
        labels.push(label);
    }
    return labels;
});
</script>

<template>
  <section class="card section dash-card-heatmap" :style="{ animationDelay: '380ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">Racha de entrenamiento</span>
      </div>
      <span class="card-meta">90 días</span>
    </div>
    <div class="heatmap">
      <div class="heat-months">
        <span v-for="(m, idx) in monthLabels" :key="idx">{{ m }}</span>
      </div>
      <div class="heat-grid">
        <div class="heat-days">
          <span>L</span><span></span><span>M</span><span></span><span>V</span><span></span><span>D</span>
        </div>
        <div class="heat-cols">
          <div v-for="(col, ci) in heatColumns" :key="ci" class="heat-col">
            <div
              v-for="(day, ri) in col"
              :key="ri"
              class="heat-cell"
              :class="getCellClass(day)"
              :style="getCellStyle(day)"
              :title="day ? day.displayDate + (getCount(day) ? ' · ' + getCount(day) + ' sesión(es)' : '') : ''"
            ></div>
          </div>
        </div>
      </div>
      <div class="heat-legend">
        <span>Menos</span>
        <div class="heat-legend-cells">
          <div class="heat-cell"></div>
          <div class="heat-cell l1"></div>
          <div class="heat-cell l2"></div>
          <div class="heat-cell l3"></div>
          <div class="heat-cell l4"></div>
        </div>
        <span>Más</span>
      </div>
    </div>
    <hr class="divider" />
    <div class="heat-summary">
      <div class="heat-count tnum">
        <span class="accent">{{ totalSessions }}</span><small>sesiones · 90 días</small>
      </div>
      <div class="heat-count tnum">
        Mejor racha <span class="accent">{{ bestStreak }}</span><small>{{ bestStreak === 1 ? 'día' : 'días' }}</small>
      </div>
    </div>
  </section>
</template>
