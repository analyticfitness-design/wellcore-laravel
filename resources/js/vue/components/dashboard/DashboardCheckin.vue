<script setup>
import { useRouter } from 'vue-router';
import { useHaptics } from '../../composables/useHaptics';

const props = defineProps({
    data: { type: Object, required: true },
    showCheckinTimer: { type: Boolean, default: false },
    checkinHours: { type: String, default: '00' },
    checkinMinutes: { type: String, default: '00' },
    checkinSeconds: { type: String, default: '00' },
});

const router = useRouter();
const haptics = useHaptics();

function handleCheckinTap() {
    if ((props.data.daysUntilCheckin ?? 99) <= 0) {
        haptics.pattern('success');
    } else {
        haptics.light();
    }
    router.push('/client/checkin');
}
</script>

<template>
  <div
    v-if="data.daysUntilCheckin !== undefined && data.daysUntilCheckin <= 0"
    class="banner section grain"
    :style="{ animationDelay: '180ms' }"
    role="button"
    tabindex="0"
    @click="handleCheckinTap"
    @keydown.enter="handleCheckinTap"
  >
    <div class="banner-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path></svg>
    </div>
    <div style="flex:1; min-width:0">
      <div class="banner-title">Check-in pendiente</div>
      <div class="banner-sub">Tu check-in semanal está listo. Envíalo ahora.</div>
    </div>
    <svg class="banner-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
  </div>
</template>
