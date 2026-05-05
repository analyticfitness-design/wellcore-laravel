<script setup>
const props = defineProps({
    data: { type: Array, default: () => [] },
    max: { type: Number, default: 0 },
});

const DAYS = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

function opacity(value) {
    if (!props.max) return 0.05;
    return 0.05 + (value / props.max) * 0.85;
}
</script>

<template>
  <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
    <h3 class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-3">Heatmap actividad × hora</h3>
    <div class="overflow-x-auto">
      <div class="min-w-[600px]">
        <div v-for="(row, dayIdx) in data" :key="dayIdx" class="flex items-center gap-1 py-0.5">
          <span class="w-8 text-[10px] text-wc-text-tertiary uppercase tracking-widest">{{ DAYS[dayIdx] }}</span>
          <div
            v-for="(cell, hour) in row" :key="hour"
            class="h-3 flex-1 rounded-sm"
            :style="{ background: `rgba(220, 38, 38, ${opacity(cell)})` }"
            :title="`${DAYS[dayIdx]} ${hour}:00 — ${cell}`"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>
