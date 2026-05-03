<script setup>
import { computed } from 'vue';

const props = defineProps({
    weekDays: { type: Array, default: () => [] },
});

// ── ISO week + rango Lun-Dom calculado en frontend ──
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
    const day = (d.getDay() + 6) % 7; // 0 = Lun
    d.setDate(d.getDate() - day);
    d.setHours(0, 0, 0, 0);
    return d;
}

const today = new Date();
const isoWeek = getIsoWeek(today);
const monday = startOfIsoWeek(today);
const sunday = new Date(monday);
sunday.setDate(monday.getDate() + 6);

const monthFmt = new Intl.DateTimeFormat('es', { month: 'short' });

function fmtDayMonth(d) {
    let m = monthFmt.format(d).replace('.', '');
    return `${d.getDate()} ${m}`;
}

const weekRangeLabel = computed(() => {
    return `Sem. ${isoWeek} · ${fmtDayMonth(monday)} — ${fmtDayMonth(sunday)}`;
});

// Construir array Lun..Dom con número de día y estado.
// `weekDays` (del backend) trae .label, .completed, .isToday en orden Lun..Dom.
const weekCells = computed(() => {
    const labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    const cells = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(monday);
        d.setDate(monday.getDate() + i);
        const backendDay = (props.weekDays && props.weekDays[i]) || {};
        const isToday = d.toDateString() === today.toDateString();
        const completed = !!backendDay.completed;
        // Domingo y miércoles tipicamente son rest si no está completed y no es today
        // pero solo marcar "rest" si sabemos que es no-entrenamiento y no completado
        // Heurística simple: si i === 6 (Dom) o si backend dice rest. Como backend
        // no lo manda, dejamos solo: done | today | (default).
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
  <section class="card section" :style="{ animationDelay: '420ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">Semana de entrenamiento</span>
      </div>
      <span class="card-meta tnum">{{ weekRangeLabel }}</span>
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
      <span><i style="background:#10B981"></i>Completado</span>
      <span><i style="background:#DC2626"></i>Hoy</span>
      <span><i style="background:rgba(255,255,255,.2)"></i>Pendiente</span>
    </div>
  </section>
</template>
