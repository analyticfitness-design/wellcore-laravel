<script setup>
import { RouterLink } from 'vue-router';
import { useI18n } from 'vue-i18n';

defineProps({
    data: { type: Object, required: true },
    greeting: { type: String, required: true },
    motivationalQuote: { type: String, default: '' },
});

const { t } = useI18n();
</script>

<template>
  <section class="hero grain section" :style="{ animationDelay: '40ms' }">
    <div class="hero-content">
      <div class="hero-greeting tight">
        <span class="name">{{ greeting }},</span> {{ data.clientName }}
      </div>
      <div v-if="data.planLabel" class="hero-row">
        <span class="chip chip-accent">{{ t('client_home.hero_plan_prefix') }} {{ data.planLabel }}<span v-if="data.planDaysActive"> · {{ t('client_home.hero_day') }} {{ data.planDaysActive }}</span></span>
      </div>
      <p v-if="motivationalQuote" class="hero-sub">{{ motivationalQuote }}</p>
    </div>
    <div class="hero-actions">
      <RouterLink to="/client/training" class="btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"></path></svg>
        {{ t('client_home.hero_log_workout') }}
      </RouterLink>
      <RouterLink to="/client/checkin" class="btn-secondary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        {{ t('client_home.hero_do_checkin') }}
      </RouterLink>
    </div>
  </section>
</template>
