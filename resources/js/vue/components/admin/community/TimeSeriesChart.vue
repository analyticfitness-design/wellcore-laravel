<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    data: { type: Array, required: true },
    label: { type: String, default: 'Posts' },
    color: { type: String, default: '#DC2626' },
    height: { type: Number, default: 220 },
});

const canvasRef = ref(null);
let chartInstance = null;

function buildChart() {
    if (!canvasRef.value) return;
    if (chartInstance) chartInstance.destroy();

    const ctx = canvasRef.value.getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.data.map(d => d.date),
            datasets: [{
                label: props.label,
                data: props.data.map(d => d.count),
                borderColor: props.color,
                backgroundColor: props.color + '20',
                fill: true,
                tension: 0.35,
                pointRadius: 0,
                pointHoverRadius: 5,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.4)' } },
            },
        },
    });
}

watch(() => props.data, () => buildChart(), { deep: true });
onMounted(() => buildChart());
onBeforeUnmount(() => { if (chartInstance) chartInstance.destroy(); });
</script>

<template>
  <div :style="{ height: height + 'px' }" class="relative">
    <canvas ref="canvasRef"></canvas>
  </div>
</template>
