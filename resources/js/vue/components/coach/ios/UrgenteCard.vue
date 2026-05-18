<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AvatarConic from './AvatarConic.vue';

const props = defineProps({
  clientName: { type: String, required: true },
  clientInitial: { type: String, default: '' },
  subText: { type: String, default: '' },
  etaLabel: { type: String, default: '' },
  ctaLabel: { type: String, default: '' },
  imageUrl: { type: String, default: '' },
});

const emit = defineEmits(['click', 'cta-click']);
const { t } = useI18n();

const resolvedCtaLabel = computed(() => props.ctaLabel || t('coach_home.urgent_card_cta'));
</script>

<template>
  <article
    class="urgente-card relative overflow-hidden rounded-[14px] border border-wc-accent/20 p-4 cursor-pointer flex items-start gap-3 transition active:scale-[0.985] hover:border-wc-accent/35"
    style="background: var(--color-wc-bg-secondary, #111113); box-shadow: var(--shadow-urgent-ios); transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
    @click="emit('click')"
  >
    <span
      class="absolute left-0 top-0 bottom-0 w-[3px]"
      style="background: linear-gradient(180deg, var(--color-wc-accent), rgba(220,38,38,0.4));"
      aria-hidden="true"
    />

    <AvatarConic
      :initial="clientInitial || clientName.charAt(0).toUpperCase()"
      tone="accent"
      size="md"
      :image-url="imageUrl"
    />

    <div class="flex-1 min-w-0">
      <p class="font-display text-[15px] font-semibold tracking-wide text-wc-text truncate">
        {{ clientName }}
      </p>
      <p v-if="subText" class="text-[12px] text-[var(--color-wc-text-3)] mb-2">
        {{ subText }}
      </p>
      <div class="flex items-center gap-2">
        <span
          v-if="etaLabel"
          class="px-2 py-0.5 rounded-full bg-wc-accent/[0.12] border border-wc-accent/20 font-display text-[10px] font-bold text-red-400 tracking-wide"
        >
          {{ etaLabel }}
        </span>
        <button
          class="action-pill h-7 text-[11px] px-3"
          :aria-label="resolvedCtaLabel"
          @click.stop="emit('cta-click')"
        >
          {{ resolvedCtaLabel }}
          <svg class="h-2.5 w-2.5 stroke-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
          </svg>
        </button>
      </div>
    </div>
  </article>
</template>
