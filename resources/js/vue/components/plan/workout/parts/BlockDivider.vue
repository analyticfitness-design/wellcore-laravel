<template>
  <div class="block-divider" data-testid="block-divider">
    <span class="pill">{{ pillText }}</span>
    <span class="ln"></span>
    <span v-if="meta" class="meta">{{ meta }}</span>
  </div>
</template>

<script setup>
// BlockDivider — superset/circuito header divider.
// CSS lines 660-682 del HTML V2.1.
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'superset',
    validator: (v) => ['superset', 'circuito'].includes(v),
  },
  label: { type: String, default: '' },
  meta: { type: String, default: '' },
});

const pillText = computed(() => {
  const base = props.type === 'circuito' ? 'Circuito' : 'Superset';
  if (props.label) return `${base} · ${props.label}`;
  return base;
});
</script>

<style scoped>
.block-divider {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 16px 6px;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  letter-spacing: 0.20em;
  text-transform: uppercase;
}
.block-divider .pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  border-radius: 999px;
  border: 1px solid var(--wc-accent);
  background: rgba(220, 38, 38, 0.10);
  color: #EF4444;
  font-weight: 600;
}
.block-divider .pill::before {
  content: '';
  width: 5px;
  height: 5px;
  border-radius: 999px;
  background: #EF4444;
  box-shadow: 0 0 6px #EF4444;
}
.block-divider .ln {
  flex: 1;
  height: 1px;
  background: linear-gradient(90deg, var(--wc-accent), transparent);
  opacity: 0.50;
}
.block-divider .meta {
  color: var(--wc-text-tertiary);
  font-weight: 500;
}
</style>
