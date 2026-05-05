<script setup>
import AvatarConic from './AvatarConic.vue';

defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  meta: { type: String, default: '' },
  avatarInitial: { type: String, default: '' },
  avatarTone: {
    type: String,
    default: 'accent',
    validator: v => ['accent', 'gold', 'purple'].includes(v),
  },
  imageUrl: { type: String, default: '' },
  badge: { type: String, default: '' },
  badgeColor: { type: String, default: '' },
  trailingIcon: { type: Boolean, default: true },
});

const emit = defineEmits(['click']);

function rgbaFromHex(hex, alpha) {
  if (!hex || !hex.startsWith('#')) return null;
  const h = hex.replace('#', '');
  const bigint = parseInt(h.length === 3 ? h.split('').map(c => c + c).join('') : h, 16);
  const r = (bigint >> 16) & 255;
  const g = (bigint >> 8) & 255;
  const b = bigint & 255;
  return `rgba(${r},${g},${b},${alpha})`;
}

function badgeStyle(color) {
  if (!color) return '';
  const bg = rgbaFromHex(color, 0.15);
  return bg ? `background: ${bg}; color: ${color};` : '';
}
</script>

<template>
  <button
    class="panel-row w-full flex items-center gap-3 text-left transition cursor-pointer hover:bg-[var(--s2)] active:bg-[var(--s2)]"
    style="transition-duration: var(--t-tap);"
    @click="emit('click')"
  >
    <AvatarConic
      v-if="avatarInitial || imageUrl"
      :initial="avatarInitial"
      :tone="avatarTone"
      :image-url="imageUrl"
      size="md"
    />
    <div class="flex-1 min-w-0">
      <p class="text-sm font-semibold text-wc-text truncate">{{ title }}</p>
      <p v-if="subtitle" class="text-[12px] text-[var(--color-wc-text-2)] truncate">{{ subtitle }}</p>
      <p v-if="meta" class="text-[11px] text-[var(--color-wc-text-3)]">{{ meta }}</p>
    </div>
    <span
      v-if="badge"
      class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
      :style="badgeStyle(badgeColor)"
      :class="!badgeColor && 'bg-wc-accent/15 text-wc-accent'"
    >
      {{ badge }}
    </span>
    <svg
      v-if="trailingIcon"
      class="h-3.5 w-3.5 shrink-0 ml-1"
      style="stroke: var(--color-wc-text-3);"
      fill="none" viewBox="0 0 24 24" stroke-width="2"
      aria-hidden="true"
    >
      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
    </svg>
  </button>
</template>
