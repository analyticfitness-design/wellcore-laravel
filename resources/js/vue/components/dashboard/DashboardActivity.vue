<script setup>
defineProps({
    activities: { type: Array, default: () => [] },
});

// Normaliza el "hace 2 días" → "hace 2d" / "hace 1 sem" estilo target.
function shortTimeAgo(timeAgo) {
    if (!timeAgo) return '';
    let s = String(timeAgo);
    s = s.replace(/^hace\s+/i, 'hace ');
    s = s.replace(/\s+d[ií]as?\b/g, 'd');
    s = s.replace(/\s+horas?\b/g, 'h');
    s = s.replace(/\s+minutos?\b/g, 'min');
    s = s.replace(/\s+semanas?\b/g, ' sem');
    s = s.replace(/\s+mes(es)?\b/g, ' mes');
    return s;
}

// Fallback descripción detallada (target muestra "Fuerza · 48 min · 12 ejercicios").
// Si backend no manda detalles, usar tipo genérico.
function buildMeta(activity) {
    const parts = [];
    if (activity.session_type) parts.push(activity.session_type);
    if (activity.duration_minutes) parts.push(`${activity.duration_minutes} min`);
    if (activity.exercises_count) parts.push(`${activity.exercises_count} ejercicios`);
    if (parts.length === 0) {
        // fallback genérico por type
        if (activity.type === 'training') parts.push('Entrenamiento');
        else if (activity.type === 'checkin') parts.push('Check-in semanal');
        else if (activity.type === 'payment') parts.push('Pago');
        else parts.push('Actividad');
    }
    return parts.join(' · ');
}
</script>

<template>
  <section class="card section wc-card-dashboard-activity" :style="{ animationDelay: '480ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">Actividad reciente</span>
      </div>
      <span class="card-meta">Últimos 5</span>
    </div>
    <div v-if="activities && activities.length > 0" class="activity">
      <div
        v-for="(activity, idx) in activities"
        :key="idx"
        class="act-row"
      >
        <div class="act-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.4 14.4 9.6 9.6"></path>
            <path d="M18.66 17.66 17.95 18.4a2.2 2.2 0 0 1-3.13.01l-9.13-9.13a2.2 2.2 0 0 1 0-3.13l.74-.74a2.2 2.2 0 0 1 3.13 0l9.13 9.13a2.2 2.2 0 0 1 0 3.13Z"></path>
          </svg>
        </div>
        <div class="act-body">
          <div class="act-title">{{ activity.description }}</div>
          <div class="act-meta">{{ buildMeta(activity) }}</div>
        </div>
        <div class="act-time">{{ shortTimeAgo(activity.timeAgo) }}</div>
      </div>
    </div>
    <div v-else style="padding: 24px 20px; text-align: center; color: var(--wc-text-3); font-size: 13px;">
      Sin actividad reciente
    </div>
  </section>
</template>
