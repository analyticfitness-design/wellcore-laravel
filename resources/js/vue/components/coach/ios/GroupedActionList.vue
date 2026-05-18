<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  items: { type: Array, default: () => [] },
  layout: {
    type: String,
    default: 'mobile',
    validator: v => ['mobile', 'desktop'].includes(v),
  },
});

const emit = defineEmits(['select']);
const { t } = useI18n();

const gridCols = computed(() =>
  props.layout === 'desktop' ? 'grid-cols-4' : 'grid-cols-2'
);

const isDesktop = computed(() => props.layout === 'desktop');
</script>

<template>
  <div :class="['grid gap-2 lg:gap-3 anim-entry anim-entry-2', gridCols]">
    <component
      :is="item.to ? RouterLink : 'button'"
      v-for="item in items"
      :key="item.id"
      :to="item.to"
      :class="[
        'qa-card relative overflow-hidden rounded-[14px] border border-[var(--b1)] cursor-pointer transition active:scale-[0.95] hover:bg-[var(--s2)] flex',
        isDesktop
          ? 'items-center gap-3.5 p-4 lg:px-[18px] hover:border-[var(--b2)] lg:hover:-translate-y-px'
          : 'flex-col gap-2 p-4',
      ]"
      style="background: var(--s2); box-shadow: var(--shadow-card-ios); transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
      :aria-label="item.label + (item.badge ? ', ' + item.badge + ' ' + t('coach_nav.pending') : '')"
      @click="emit('select', item)"
    >
      <span
        class="absolute right-2.5 opacity-[0.07] pointer-events-none"
        :class="isDesktop ? 'top-1/2 -translate-y-1/2' : 'bottom-1.5'"
        aria-hidden="true"
      >
        <svg
          :class="isDesktop ? 'h-12 w-12' : 'h-11 w-11'"
          stroke="currentColor"
          stroke-width="1.2"
          fill="none"
          viewBox="0 0 24 24"
          v-html="item.iconSvgPath"
        />
      </span>

      <span
        v-if="!isDesktop && item.badge && item.badge > 0"
        class="absolute top-2 right-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-wc-accent text-white font-display text-[11px] font-bold glow-accent"
      >
        {{ item.badge > 99 ? '99+' : item.badge }}
      </span>

      <span
        :class="[
          'flex items-center justify-center flex-shrink-0 rounded-[10px] z-[1]',
          isDesktop ? 'w-[38px] h-[38px]' : 'w-8 h-8',
        ]"
        :style="{ background: item.iconColor ? rgbaFromHex(item.iconColor, 0.12) : 'rgba(220,38,38,0.12)' }"
      >
        <svg
          :class="isDesktop ? 'h-[18px] w-[18px]' : 'h-4 w-4'"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="2"
          :stroke="item.iconStrokeColor || '#DC2626'"
          aria-hidden="true"
          v-html="item.iconSvgPath"
        />
      </span>

      <div :class="isDesktop ? 'flex-1 min-w-0 z-[1]' : 'z-[1]'">
        <div class="text-[10px] font-bold tracking-[0.09em] uppercase text-[var(--color-wc-text-3)] leading-[1.2]">
          {{ item.label }}
        </div>
        <span
          v-if="isDesktop && (item.badge || item.badgeVariant === 'empty')"
          :class="[
            'qa-badge inline-flex items-center justify-center mt-1 px-1.5 min-w-[20px] h-5 rounded-full font-display font-bold',
            item.badgeVariant === 'empty'
              ? 'bg-[var(--s2)] text-[var(--color-wc-text-3)] border border-[var(--b1)] text-[10px]'
              : 'bg-wc-accent text-white text-xs glow-accent',
          ]"
        >
          {{ item.badge ?? '—' }}
        </span>
      </div>
    </component>
  </div>
</template>

<script>
function rgbaFromHex(hex, alpha) {
  if (!hex) return `rgba(220,38,38,${alpha})`;
  const h = hex.replace('#', '');
  const bigint = parseInt(h.length === 3 ? h.split('').map(c => c + c).join('') : h, 16);
  const r = (bigint >> 16) & 255;
  const g = (bigint >> 8) & 255;
  const b = bigint & 255;
  return `rgba(${r},${g},${b},${alpha})`;
}
</script>
