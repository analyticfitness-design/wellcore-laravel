<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    data: { type: Array, default: () => [] },
    period: { type: String, default: 'month' },
});

const containerRef = ref(null);
const containerWidth = ref(600);
const HEIGHT = 180;
const PAD_L = 42;
const PAD_R = 16;
const PAD_T = 14;
const PAD_B = 28;

const chartW = computed(() => Math.max(200, containerWidth.value - PAD_L - PAD_R));
const chartH = computed(() => HEIGHT - PAD_T - PAD_B);

let ro;
onMounted(() => {
    if (containerRef.value) {
        ro = new ResizeObserver(([e]) => {
            containerWidth.value = e.contentRect.width || 600;
        });
        ro.observe(containerRef.value);
    }
});
onBeforeUnmount(() => ro?.disconnect());

const maxVal = computed(() => {
    if (!props.data.length) return 1;
    return Math.max(1, ...props.data.flatMap(d => [d.coach_to_client ?? 0, d.client_to_coach ?? 0]));
});

const hasData = computed(() => props.data.some(d => (d.coach_to_client ?? 0) + (d.client_to_coach ?? 0) > 0));

function xPos(idx) {
    if (props.data.length < 2) return PAD_L + chartW.value / 2;
    return PAD_L + (idx / (props.data.length - 1)) * chartW.value;
}
function yPos(val) {
    return PAD_T + chartH.value - (val / maxVal.value) * chartH.value;
}

function buildPath(key) {
    if (!props.data.length) return '';
    return props.data.map((d, i) => {
        const x = xPos(i);
        const y = yPos(d[key] ?? 0);
        return i === 0 ? `M ${x} ${y}` : `L ${x} ${y}`;
    }).join(' ');
}

const pathCoach  = computed(() => buildPath('coach_to_client'));
const pathClient = computed(() => buildPath('client_to_coach'));

const yLabels = computed(() => {
    const m = maxVal.value;
    return [0, Math.round(m / 2), m].map(v => ({
        v,
        y: yPos(v),
    }));
});

const xLabels = computed(() => {
    const n = props.data.length;
    if (!n) return [];
    const step = n <= 10 ? 1 : n <= 20 ? 2 : n <= 60 ? Math.ceil(n / 10) : Math.ceil(n / 6);
    return props.data
        .map((d, i) => ({ label: d.date, x: xPos(i), show: i % step === 0 || i === n - 1 }))
        .filter(d => d.show);
});

const tooltip = ref(null);

function onMouseMove(e) {
    if (!props.data.length) return;
    const rect = e.currentTarget.getBoundingClientRect();
    const relX = e.clientX - rect.left - PAD_L;
    const pct = relX / chartW.value;
    const idx = Math.round(pct * (props.data.length - 1));
    const clamped = Math.max(0, Math.min(props.data.length - 1, idx));
    const d = props.data[clamped];
    if (!d) return;
    tooltip.value = {
        x: xPos(clamped),
        y: Math.min(yPos(d.coach_to_client ?? 0), yPos(d.client_to_coach ?? 0)) - 8,
        label: d.date,
        coach: d.coach_to_client ?? 0,
        client: d.client_to_coach ?? 0,
    };
}
function onMouseLeave() { tooltip.value = null; }
</script>

<template>
  <div class="volume-card">
    <header class="chart-head">
      <div>
        <h2 class="chart-title">VOLUMEN DE MENSAJES</h2>
        <span class="chart-eyebrow">MENSAJES POR PERÍODO</span>
      </div>
      <div class="chart-legend">
        <span class="legend-item legend-coach">
          <span class="legend-dot legend-dot--coach"></span>
          Coach → Cliente
        </span>
        <span class="legend-item legend-client">
          <span class="legend-dot legend-dot--client"></span>
          Cliente → Coach
        </span>
      </div>
    </header>

    <div v-if="!hasData" class="chart-empty">
      <div class="empty-num">—</div>
      <p class="empty-msg">"Sin mensajes en el período seleccionado."</p>
    </div>

    <div v-else ref="containerRef" class="chart-wrap">
      <svg
        :width="containerWidth"
        :height="HEIGHT"
        class="chart-svg"
        role="img"
        aria-label="Volumen de mensajes por período"
        @mousemove="onMouseMove"
        @mouseleave="onMouseLeave"
      >
        <!-- grid lines -->
        <line
          v-for="yl in yLabels" :key="`yl-${yl.v}`"
          :x1="PAD_L" :x2="PAD_L + chartW"
          :y1="yl.y" :y2="yl.y"
          class="grid-line"
        />
        <!-- y labels -->
        <text
          v-for="yl in yLabels" :key="`ytxt-${yl.v}`"
          :x="PAD_L - 6" :y="yl.y + 4"
          class="axis-label axis-label--y"
          text-anchor="end"
        >{{ yl.v }}</text>

        <!-- coach line -->
        <path :d="pathCoach"  class="chart-line chart-line--coach"  fill="none" />
        <!-- client line -->
        <path :d="pathClient" class="chart-line chart-line--client" fill="none" />

        <!-- dots on hover index -->
        <template v-if="tooltip">
          <circle
            :cx="tooltip.x" :cy="yPos(tooltip.coach)"
            r="4" class="dot dot--coach"
          />
          <circle
            :cx="tooltip.x" :cy="yPos(tooltip.client)"
            r="4" class="dot dot--client"
          />
          <!-- vertical crosshair -->
          <line
            :x1="tooltip.x" :x2="tooltip.x"
            :y1="PAD_T" :y2="PAD_T + chartH"
            class="crosshair"
          />
          <!-- tooltip box -->
          <g>
            <rect
              :x="Math.min(tooltip.x + 8, PAD_L + chartW - 120)"
              :y="Math.max(PAD_T, tooltip.y - 28)"
              width="118" height="48"
              class="tooltip-bg" rx="6"
            />
            <text
              :x="Math.min(tooltip.x + 16, PAD_L + chartW - 112)"
              :y="Math.max(PAD_T + 14, tooltip.y - 14)"
              class="tooltip-text tooltip-label"
            >{{ tooltip.label }}</text>
            <text
              :x="Math.min(tooltip.x + 16, PAD_L + chartW - 112)"
              :y="Math.max(PAD_T + 26, tooltip.y - 2)"
              class="tooltip-text tooltip-coach"
            >Coach: {{ tooltip.coach }}</text>
            <text
              :x="Math.min(tooltip.x + 16, PAD_L + chartW - 112)"
              :y="Math.max(PAD_T + 38, tooltip.y + 10)"
              class="tooltip-text tooltip-client"
            >Cliente: {{ tooltip.client }}</text>
          </g>
        </template>

        <!-- x labels -->
        <text
          v-for="xl in xLabels" :key="`xtxt-${xl.label}`"
          :x="xl.x" :y="HEIGHT - 4"
          class="axis-label axis-label--x"
          text-anchor="middle"
        >{{ xl.label }}</text>
      </svg>
    </div>
  </div>
</template>

<style scoped>
.volume-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17,17,17,0.7);
    padding: 18px;
}
.chart-head {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 10px; flex-wrap: wrap; margin-bottom: 16px;
}
.chart-title {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-text); margin: 0 0 2px;
}
.chart-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 7px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.chart-legend { display: flex; gap: 12px; flex-wrap: wrap; }
.legend-item {
    display: flex; align-items: center; gap: 5px;
    font-family: var(--font-mono, monospace);
    font-size: 8px; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--color-wc-text-secondary);
}
.legend-dot {
    width: 8px; height: 2px; border-radius: 1px; flex-shrink: 0;
}
.legend-dot--coach  { background: var(--color-wc-accent, #DC2626); }
.legend-dot--client { background: var(--color-wc-blue-text, #60A5FA); }

.chart-empty { padding: 24px 8px; text-align: center; }
.empty-num {
    font-family: var(--font-display); font-size: 56px;
    color: var(--color-wc-bg-tertiary); letter-spacing: 0.1em;
    line-height: 1; margin-bottom: 12px; user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic; font-size: 12px;
    color: var(--color-wc-text-tertiary); margin: 0;
}
.chart-wrap { width: 100%; overflow: hidden; }
.chart-svg { display: block; width: 100%; }

.grid-line { stroke: rgba(255,255,255,0.04); stroke-width: 1; }
.axis-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px; fill: rgba(250,250,250,0.4);
}
.axis-label--y { font-size: 8px; }
.axis-label--x { font-size: 7px; }

.chart-line { stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
.chart-line--coach  { stroke: var(--color-wc-accent, #DC2626); }
.chart-line--client { stroke: var(--color-wc-blue-text, #60A5FA); }

.dot { r: 4; }
.dot--coach  { fill: var(--color-wc-accent, #DC2626); }
.dot--client { fill: var(--color-wc-blue-text, #60A5FA); }

.crosshair { stroke: rgba(255,255,255,0.12); stroke-width: 1; stroke-dasharray: 3 3; }

.tooltip-bg { fill: rgba(24,24,24,0.95); stroke: rgba(255,255,255,0.08); stroke-width: 1; }
.tooltip-text { font-family: var(--font-mono, monospace); font-size: 9px; letter-spacing: 0.06em; }
.tooltip-label { fill: var(--color-wc-text-secondary); }
.tooltip-coach  { fill: var(--color-wc-red-text, #F87171); }
.tooltip-client { fill: var(--color-wc-blue-text, #60A5FA); }

@media (prefers-reduced-motion: reduce) {
    .chart-line { transition: none !important; }
}
</style>
