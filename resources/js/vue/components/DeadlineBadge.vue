<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  deadline: { type: String, default: null }, // ISO datetime
  status: { type: String, default: '' },
});

// reactive "now" so countdown updates (we re-tick every minute)
const now = ref(Date.now());
let tickHandle = null;

const shouldShow = computed(() => {
  if (!props.deadline) return false;
  return ['pendiente', 'en_revision'].includes(props.status);
});

const diffMs = computed(() => {
  if (!props.deadline) return 0;
  const target = new Date(props.deadline).getTime();
  if (Number.isNaN(target)) return 0;
  return target - now.value;
});

const isOverdue = computed(() => diffMs.value < 0);
const hoursRemaining = computed(() => Math.abs(diffMs.value) / 3_600_000);

const tone = computed(() => {
  if (isOverdue.value) return 'red';
  if (hoursRemaining.value < 6) return 'red';
  if (hoursRemaining.value < 24) return 'yellow';
  return 'emerald';
});

const label = computed(() => {
  const h = hoursRemaining.value;
  if (isOverdue.value) {
    if (h < 1) return 'Vencido hace <1h';
    if (h < 24) return `Vencido hace ${Math.floor(h)}h`;
    const days = Math.floor(h / 24);
    return `Vencido hace ${days}d`;
  }
  if (h < 1) {
    const mins = Math.max(1, Math.floor((diffMs.value / 1000) / 60));
    return `SLA: ${mins}m restantes`;
  }
  if (h < 48) return `SLA: ${Math.floor(h)}h restantes`;
  const days = Math.floor(h / 24);
  return `SLA: ${days}d restantes`;
});

const classes = computed(() => {
  switch (tone.value) {
    case 'red':
      return 'border-red-500/30 bg-red-500/10 text-red-400';
    case 'yellow':
      return 'border-yellow-500/30 bg-yellow-500/10 text-yellow-500';
    case 'emerald':
    default:
      return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-500';
  }
});

onMounted(() => {
  tickHandle = setInterval(() => { now.value = Date.now(); }, 60_000);
});
onBeforeUnmount(() => {
  clearInterval(tickHandle);
});
</script>

<template>
  <span
    v-if="shouldShow"
    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold"
    :class="classes"
  >
    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
    </svg>
    {{ label }}
  </span>
</template>
