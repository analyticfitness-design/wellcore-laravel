<script setup>
/**
 * WcStatCard — Stat card con icon ghost decorativo + tabular nums.
 *
 * Layout: icon ghost (opacity 0.08) en esquina superior derecha + label en
 * caption arriba + valor grande con tabular nums en el centro + unit/trend
 * abajo. El icono opcional viene como prop o slot leading.
 *
 * Props:
 *   label     string  — caption arriba (uppercase)
 *   value     string|number — el numero principal
 *   unit      string  — unidad/sufijo (ej "dias consecutivos")
 *   trend     string  — texto de trend ("+12% vs prev", "585 XP / 200")
 *   iconGhost string  — nombre Phosphor (ej "ph-flame") o slug premium ("wc-flame")
 *
 * Slots:
 *   chart — sparkline o mini-grafico opcional (renderizado abajo del trend)
 *   icon  — override del icon ghost si necesitas SVG custom
 *
 * Uso:
 *   <WcStatCard label="RACHA" :value="1" unit="dias consecutivos" icon-ghost="ph-fill ph-flame"/>
 */
import { useSlots } from 'vue';
import WcIcon from '../WcIcon.vue';

defineProps({
    label:     { type: String, required: true },
    value:     { type: [String, Number], required: true },
    unit:      { type: String, default: '' },
    trend:     { type: String, default: '' },
    iconGhost: { type: String, default: '' },
});

const slots = useSlots();
</script>

<template>
  <div class="wc-card relative overflow-hidden" style="padding: var(--ds-s-5);">
    <!-- Icon ghost decorativo (opacity 0.08, esquina sup-derecha) -->
    <div
      v-if="iconGhost || slots.icon"
      class="absolute top-3 right-3 pointer-events-none"
      style="opacity: 0.08; color: var(--color-wc-text);"
      aria-hidden="true"
    >
      <slot name="icon">
        <WcIcon :name="iconGhost" :size="48" />
      </slot>
    </div>

    <span class="wc-caption block mb-2">{{ label }}</span>

    <div class="wc-num-large wc-tnum mb-1" style="color: var(--color-wc-text);">
      {{ value }}
    </div>

    <div v-if="unit" class="text-xs" style="color: var(--color-wc-text-secondary);">
      {{ unit }}
    </div>

    <div v-if="trend" class="text-xs mt-1 wc-tnum" style="color: var(--color-wc-text-tertiary);">
      {{ trend }}
    </div>

    <div v-if="slots.chart" class="mt-3">
      <slot name="chart" />
    </div>
  </div>
</template>
