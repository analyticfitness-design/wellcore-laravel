<script setup>
import { useI18n } from 'vue-i18n';

defineProps({
    activities: { type: Array, default: () => [] },
});

const { t, locale } = useI18n();

// Normaliza el "hace 2 días" → "hace 2d" / "hace 1 sem" estilo target.
// En inglés: "2d ago", "1w ago", etc. Backend devuelve string es; mapeamos.
function shortTimeAgo(timeAgo) {
    if (!timeAgo) return '';
    let s = String(timeAgo);
    if (locale.value === 'en') {
        s = s
            .replace(/^hace\s+/i, '')
            .replace(/\s+d[ií]as?\b/g, 'd')
            .replace(/\s+horas?\b/g, 'h')
            .replace(/\s+minutos?\b/g, 'min')
            .replace(/\s+semanas?\b/g, 'w')
            .replace(/\s+mes(es)?\b/g, 'mo')
            .trim();
        return s + ' ago';
    }
    s = s.replace(/^hace\s+/i, 'hace ');
    s = s.replace(/\s+d[ií]as?\b/g, 'd');
    s = s.replace(/\s+horas?\b/g, 'h');
    s = s.replace(/\s+minutos?\b/g, 'min');
    s = s.replace(/\s+semanas?\b/g, ' sem');
    s = s.replace(/\s+mes(es)?\b/g, ' mes');
    return s;
}

// Fallback descripción detallada (target muestra "Fuerza · 48 min · 12 ejercicios").
function buildMeta(activity) {
    const parts = [];
    if (activity.session_type) parts.push(activity.session_type);
    if (activity.duration_minutes) parts.push(`${activity.duration_minutes} min`);
    if (activity.exercises_count) parts.push(`${activity.exercises_count} ${t('client_home.activity_exercises')}`);
    if (parts.length === 0) {
        if (activity.type === 'training') parts.push(t('client_home.activity_type_training'));
        else if (activity.type === 'checkin') parts.push(t('client_home.activity_type_checkin'));
        else if (activity.type === 'payment') parts.push(t('client_home.activity_type_payment'));
        else parts.push(t('client_home.activity_type_other'));
    }
    return parts.join(' · ');
}
</script>

<template>
  <section class="card section wc-card-dashboard-activity" :style="{ animationDelay: '480ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">{{ t('client_home.activity_title') }}</span>
      </div>
      <span class="card-meta">{{ t('client_home.activity_last_n') }}</span>
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
      {{ t('client_home.activity_empty') }}
    </div>
  </section>
</template>
