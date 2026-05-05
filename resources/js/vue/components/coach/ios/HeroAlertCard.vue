<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'urgent',
    validator: v => ['urgent', 'warning', 'success', 'info'].includes(v),
  },
  eyebrow: { type: String, default: '' },
  title: { type: String, required: true },
  chips: { type: Array, default: () => [] },
  ctaLabel: { type: String, default: '' },
  layout: {
    type: String,
    default: 'mobile',
    validator: v => ['mobile', 'desktop'].includes(v),
  },
});

const emit = defineEmits(['click', 'cta-click']);

const isDesktop = computed(() => props.layout === 'desktop');

const eyebrowColor = computed(() => {
  switch (props.variant) {
    case 'warning': return 'text-amber-400';
    case 'success': return 'text-emerald-400';
    case 'info':    return 'text-blue-400';
    default:        return 'text-wc-accent';
  }
});
</script>

<template>
  <article
    class="hero-alert-base liquid-glass anim-entry anim-entry-1"
    :class="isDesktop
      ? 'p-5 px-7 flex items-center gap-5'
      : 'p-[18px_20px_20px]'"
    role="alert"
    @click="emit('click')"
  >
    <!-- Desktop: Icon left -->
    <div
      v-if="isDesktop"
      class="w-11 h-11 rounded-xl bg-wc-accent/10 border border-wc-accent/20 flex items-center justify-center flex-shrink-0"
      style="box-shadow: 0 0 0 4px rgba(220,38,38,0.06);"
    >
      <slot name="icon">
        <svg class="h-5 w-5" style="stroke: var(--color-wc-accent);" fill="none" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </slot>
    </div>

    <div :class="isDesktop ? 'flex-1 relative z-[1]' : 'relative z-[1]'">
      <div v-if="!isDesktop && eyebrow" class="flex items-center gap-2 mb-2">
        <slot name="icon">
          <svg class="h-3.5 w-3.5 stroke-wc-accent flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
        </slot>
        <span :class="['text-[10px] font-bold tracking-[0.1em] uppercase', eyebrowColor]">
          {{ eyebrow }}
        </span>
      </div>

      <div v-else-if="isDesktop && eyebrow" :class="['text-[10px] font-bold tracking-[0.1em] uppercase mb-1', eyebrowColor]">
        {{ eyebrow }}
      </div>

      <h2
        :class="[
          'font-display font-bold uppercase text-wc-text leading-[1.05] tracking-wide',
          isDesktop ? 'text-[22px] mb-1.5' : 'text-[26px] mb-2.5',
        ]"
      >
        {{ title }}
      </h2>

      <div v-if="chips.length" class="flex items-center gap-2 flex-wrap">
        <span
          v-for="(chip, i) in chips"
          :key="i"
          :class="[
            'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold',
            chip.urgent
              ? 'bg-wc-accent/10 border border-wc-accent/25 text-red-400'
              : 'bg-white/[0.07] border border-white/10 text-wc-text-2',
          ]"
        >
          {{ chip.label }}
        </span>
      </div>
    </div>

    <button
      v-if="isDesktop && ctaLabel"
      class="action-pill h-[38px] px-5 text-[12px] flex-shrink-0 whitespace-nowrap"
      :aria-label="ctaLabel"
      @click.stop="emit('cta-click')"
    >
      {{ ctaLabel }}
      <svg class="h-3 w-3 stroke-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
      </svg>
    </button>
  </article>
</template>
