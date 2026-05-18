<script setup>
import { useI18n } from 'vue-i18n';

defineProps({
    data: { type: Object, required: true },
});

const { t } = useI18n();
</script>

<template>
  <!-- Plan indicator (cuando hay plan activo) -->
  <div v-if="data.hasActivePlan" class="plan-indicator section" :style="{ animationDelay: '140ms' }">
    <div class="plan-icon">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
    </div>
    <div class="plan-text">
      <strong>{{ t('client_home.plan_alert_plan_prefix') }} <span class="capitalize">{{ data.planPhase || t('client_home.plan_alert_default_phase') }}</span></strong>
      <span>{{ t('client_home.plan_alert_active') }}</span>
    </div>
    <div class="plan-day tnum">{{ t('client_home.plan_alert_day') }} {{ data.planDaysActive || 1 }}</div>
  </div>

  <!-- Sin plan: banner alerta tipo target -->
  <div v-else class="banner section grain" :style="{ animationDelay: '140ms' }" role="button" tabindex="0">
    <div class="banner-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path></svg>
    </div>
    <div style="flex:1; min-width:0">
      <div class="banner-title">{{ t('client_home.plan_alert_none_title') }}</div>
      <div class="banner-sub">{{ t('client_home.plan_alert_none_desc') }}</div>
    </div>
    <svg class="banner-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
  </div>
</template>
