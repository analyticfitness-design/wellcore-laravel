<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useReducedMotion } from '../../composables/useReducedMotion';

const props = defineProps({
    score: { type: Number, default: 0 },
    size: { type: Number, default: 200 },
    label: { type: String, default: 'Latido del Equipo' },
});

const reduced = useReducedMotion();
const animatedScore = ref(0);

const radius = 80;
const circumference = 2 * Math.PI * radius;

const scoreColorClass = computed(() => {
    if (props.score >= 80) return 'text-emerald-500';
    if (props.score >= 60) return 'text-amber-500';
    return 'text-rose-500';
});

const ringStrokeOffset = computed(() => {
    const pct = Math.max(0, Math.min(100, animatedScore.value)) / 100;
    return circumference - (circumference * pct);
});

function animateTo(target, duration = 1100) {
    if (reduced.value || duration === 0) {
        animatedScore.value = target;
        return;
    }
    const start = animatedScore.value;
    const startTime = performance.now();
    function tick(now) {
        const elapsed = now - startTime;
        const progress = Math.min(1, elapsed / duration);
        const eased = 1 - Math.pow(1 - progress, 3);
        animatedScore.value = start + (target - start) * eased;
        if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

const flash = ref(false);
function flashHealthScore() {
    flash.value = true;
    setTimeout(() => (flash.value = false), 800);
}
defineExpose({ flashHealthScore });

onMounted(() => animateTo(props.score, reduced.value ? 0 : 1100));
watch(() => props.score, (newScore) => animateTo(newScore, reduced.value ? 0 : 600));
</script>

<template>
  <div class="relative inline-flex flex-col items-center justify-center" :style="{ width: size + 'px', height: size + 'px' }">
    <svg :width="size" :height="size" viewBox="0 0 200 200" class="block">
      <circle cx="100" cy="100" :r="radius" fill="none" stroke="currentColor" class="text-wc-bg-tertiary" stroke-width="14" />
      <circle
        cx="100" cy="100" :r="radius"
        fill="none" stroke-width="14"
        stroke-linecap="round"
        :class="scoreColorClass"
        stroke="currentColor"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="ringStrokeOffset"
        transform="rotate(-90 100 100)"
        :style="{ transition: reduced ? 'none' : 'stroke-dashoffset 0.6s ease-out' }"
      />
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center" :class="flash ? 'animate-pulse' : ''">
      <span class="font-display text-5xl tracking-tight" :class="scoreColorClass">{{ Math.round(animatedScore) }}</span>
      <span class="text-[10px] uppercase tracking-widest text-wc-text-tertiary mt-1">{{ label }}</span>
    </div>
  </div>
</template>
