<script setup>
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    weightChartData: { type: Array, default: () => [] },
});

const router = useRouter();
const { t } = useI18n();

function goToMetrics() {
    router.push('/client/metrics');
}

function getWeightBarHeight(weight, min, range) {
    if (range === 0) return 50;
    return ((weight - min) / range) * 70 + 30;
}
</script>

<template>
  <section v-if="weightChartData && weightChartData.length > 0" class="card section wc-card-dashboard-weight" :style="{ animationDelay: '400ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">{{ t('client_home.weight_title') }}</span>
      </div>
      <span class="card-meta">{{ t('client_home.weight_meta_period') }}</span>
    </div>
    <div style="padding: 6px 20px 20px;">
      <div class="flex items-end justify-center gap-1 sm:gap-2 overflow-x-auto" style="height: 140px;">
        <div
          v-for="(entry, idx) in weightChartData"
          :key="idx"
          class="group relative flex w-8 sm:w-10 shrink-0 flex-col items-center justify-end"
          style="height: 100%;"
        >
          <div class="pointer-events-none absolute -top-8 z-10 hidden rounded bg-wc-bg-secondary px-2 py-1 text-xs font-medium text-wc-text shadow-lg group-hover:block">
            {{ Number(entry.weight).toFixed(1) }} kg
          </div>
          <div
            class="w-full rounded-t bg-wc-accent/80 transition-all group-hover:bg-wc-accent"
            :style="{
              height: getWeightBarHeight(
                entry.weight,
                Math.min(...weightChartData.map(e => e.weight)),
                Math.max(...weightChartData.map(e => e.weight)) - Math.min(...weightChartData.map(e => e.weight)) || 1
              ) + '%'
            }"
          ></div>
          <span class="mt-1 w-full truncate text-center text-xs" style="color: var(--wc-text-3)">
            {{ entry.date ? String(entry.date).slice(0, 5) : '' }}
          </span>
        </div>
      </div>
    </div>
  </section>

  <section v-else class="card section wc-card-dashboard-weight" :style="{ animationDelay: '400ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">{{ t('client_home.weight_title') }}</span>
      </div>
      <span class="card-meta">{{ t('client_home.weight_meta_empty') }}</span>
    </div>
    <div class="empty">
      <div class="empty-art">
        <svg width="64" height="64" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
          <rect x="8" y="14" width="48" height="40" rx="6"></rect>
          <path d="M14 22h36"></path>
          <circle cx="32" cy="38" r="9"></circle>
          <path d="M32 31v7l4-3"></path>
          <path d="M22 50h2M28 50h2M34 50h2M40 50h2"></path>
        </svg>
      </div>
      <div>
        <div class="empty-title">{{ t('client_home.weight_empty_title') }}</div>
        <p class="empty-sub">{{ t('client_home.weight_empty_sub') }}</p>
      </div>
      <button class="empty-cta" @click="goToMetrics">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"></path></svg>
        {{ t('client_home.weight_log') }}
      </button>
    </div>
  </section>
</template>
