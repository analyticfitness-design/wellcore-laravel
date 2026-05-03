<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: { type: Object, required: true },
    weeklySummaryMessage: { type: Object, default: () => ({ label: '', desc: '', colorClass: '' }) },
});

// ISO week previa (resumen es de la semana pasada)
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

const lastWeekIso = computed(() => {
    const d = new Date();
    d.setDate(d.getDate() - 7);
    return getIsoWeek(d);
});
</script>

<template>
  <section class="card section" :style="{ animationDelay: '500ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">Resumen semanal</span>
      </div>
      <span class="card-meta">Sem. {{ lastWeekIso }}</span>
    </div>

    <div v-if="data.hasLastWeekData" class="summary">
      <div class="summary-numbers">
        <div class="summary-num">
          <div class="k">Entrenamientos</div>
          <div class="v tnum">{{ data.lastWeekWorkouts || 0 }}</div>
        </div>
        <div class="summary-num">
          <div class="k">Check-ins</div>
          <div class="v tnum">{{ data.lastWeekCheckins || 0 }}</div>
        </div>
      </div>
      <div class="summary-status">
        <span class="badge">{{ weeklySummaryMessage.label || 'En camino' }}</span>
        <span class="msg">{{ weeklySummaryMessage.desc || 'Cada sesión cuenta. Mantén el ritmo.' }}</span>
      </div>
    </div>

    <div v-else class="summary">
      <div class="summary-status">
        <span class="badge">Nueva semana</span>
        <span class="msg">Completa tu primer entrenamiento y check-in para ver tu resumen aquí.</span>
      </div>
    </div>
  </section>
</template>
