<script setup>
import { computed } from 'vue';
import { useViewportAnimate } from '../../composables/dashboard/useViewportAnimate';

const props = defineProps({
    data: { type: Object, required: true },
    xpProgress: { type: Number, default: 0 },
    trainedRingOffset: { type: Number, default: 251 },
});

// Fase 8: ring + XP bar animan desde valor inicial (vacío) al entrar al viewport.
// Esto da "feel" nativo de app donde los indicadores se "rellenan" ante tus ojos.
const { targetRef: ringRef, visible: ringVisible } = useViewportAnimate({ threshold: 0.4 });
const { targetRef: xpRef, visible: xpVisible } = useViewportAnimate({ threshold: 0.4 });

const ringOffsetAnimated = computed(() => (ringVisible.value ? props.trainedRingOffset : 251));
const xpProgressAnimated = computed(() => (xpVisible.value ? props.xpProgress : 0));
</script>

<template>
  <div class="relative wc-grain grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    <!-- Streak with Flame -->
    <div class="sc-r relative overflow-hidden wc-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
      <div class="flex items-center justify-between">
        <span class="wc-caption">Racha</span>
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
          <svg :class="['h-4 w-4 text-orange-500', (data.streakDays || 0) >= 3 ? 'flame' : '']" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 0 0-1.071-.136 9.742 9.742 0 0 0-3.539 6.176A7.547 7.547 0 0 1 6.648 6.61a.75.75 0 0 0-1.152.082A9 9 0 1 0 15.68 4.534a7.46 7.46 0 0 1-2.717-2.248ZM15.75 14.25a3.75 3.75 0 1 1-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 0 1 1.925-3.546 3.75 3.75 0 0 1 3.255 3.718Z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
      <p class="mt-3 font-display text-3xl text-wc-accent" style="line-height:1">{{ data.streakDays || 0 }}</p>
      <p class="mt-1 text-sm font-medium text-wc-text-secondary">dias consecutivos</p>
    </div>

    <!-- Check-ins this month -->
    <div class="sc-g relative overflow-hidden wc-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
      <div class="flex items-center justify-between">
        <span class="wc-caption">Check-ins</span>
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
          <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>
      </div>
      <p class="mt-3 font-display text-3xl text-wc-accent" style="line-height:1">{{ data.checkinsThisMonth || 0 }}</p>
      <p class="mt-1 text-sm font-medium text-wc-text-secondary">este mes</p>
    </div>

    <!-- XP + Level -->
    <div class="sc-p relative overflow-hidden wc-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
      <div class="flex items-center justify-between">
        <span class="wc-caption">Nivel {{ data.level || 1 }}</span>
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
          <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
          </svg>
        </div>
      </div>
      <p class="mt-3 font-display text-3xl text-wc-accent" style="line-height:1">{{ (data.xpTotal || 0).toLocaleString() }}</p>
      <p class="mt-1 text-sm font-medium text-wc-text-secondary">XP total</p>
      <!-- XP Progress bar -->
      <div ref="xpRef" class="mt-3">
        <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
          <div
            class="h-full rounded-full bg-violet-500 transition-all duration-[900ms] ease-[cubic-bezier(.22,1,.36,1)]"
            :style="{ width: xpProgressAnimated + '%' }"
          ></div>
        </div>
        <p class="mt-1 text-[10px] text-wc-text-tertiary">
          {{ ((data.xpTotal || 0) - (data.xpCurrentLevelFloor || 0)).toLocaleString() }} / 200 XP
        </p>
      </div>
    </div>

    <!-- Days trained this week — Progress Ring -->
    <div class="sc-a relative overflow-hidden wc-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
      <div class="flex items-center justify-between">
        <span class="wc-caption">Esta semana</span>
      </div>
      <div class="mt-3 flex items-center gap-3">
        <svg ref="ringRef" width="60" height="60" viewBox="0 0 86 86" class="shrink-0">
          <circle cx="43" cy="43" r="40" fill="none" stroke="var(--color-wc-border)" stroke-width="6" />
          <circle
            cx="43" cy="43" r="40" fill="none" stroke="#DC2626" stroke-width="6"
            stroke-linecap="round"
            :stroke-dasharray="251"
            :stroke-dashoffset="ringOffsetAnimated"
            class="transition-all duration-[900ms] ease-[cubic-bezier(.22,1,.36,1)]"
            style="transform: rotate(-90deg); transform-origin: center;"
          />
          <text x="43" y="43" text-anchor="middle" dominant-baseline="central"
                fill="var(--color-wc-text)" font-family="var(--font-data)" font-size="18" font-weight="700">
            {{ data.trainedThisWeek || 0 }}/7
          </text>
        </svg>
        <div>
          <p class="text-sm font-medium text-wc-text-secondary">dias entrenados</p>
        </div>
      </div>
    </div>
  </div>
</template>
