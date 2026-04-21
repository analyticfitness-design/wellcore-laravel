<script setup>
/**
 * LevelUpCelebration — modal fullscreen disparado cuando useMedals detecta
 * que stats.level subió entre fetches.
 *
 * Disenado siguiendo el patron Bento 05 (violeta) del gallery de modals.
 *
 * Uso:
 *   <LevelUpCelebration :event="levelUp" @close="clearLevelUp" />
 *
 * Prop event shape:
 *   { from: 3, to: 4, totalXP: 2040, xpGained: 540 }
 */

import { ref, computed, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    event: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const visible = ref(false);
let autoCloseTimer = null;

function close() {
    visible.value = false;
    // Give the leave transition a moment before emitting
    setTimeout(() => emit('close'), 300);
}

function startAutoClose() {
    clearTimeout(autoCloseTimer);
    autoCloseTimer = setTimeout(close, 5200);
}

watch(
    () => props.event,
    (val) => {
        if (val) {
            visible.value = true;
            startAutoClose();
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => clearTimeout(autoCloseTimer));

// Ring progress: empezamos en 0% y corremos a 100% para celebrar que acaba
// de completar el nivel anterior (el ciclo que lo llevó hasta aqui).
const ringDashoffset = computed(() => (visible.value ? 0 : 301));

// Mensaje motivacional — indexado por nivel alcanzado, con fallback ciclico.
// El usuario ve frases distintas en niveles distintos, no "canned" repetido.
const LEVEL_MESSAGES = [
    'Esto apenas empieza. La constancia forja resultados.',
    'Vas mas lejos que la mayoria. Sigue subiendo.',
    'Dedicacion real. Tu progreso habla por ti.',
    'Nivel elite. Pocos llegan hasta aqui.',
    'Disciplina legendaria. Ya eres referencia.',
    'Estas reescribiendo lo que creias posible.',
    'Cada nivel te vuelve mas fuerte. Sin atajos.',
    'Respeto. Esto es trabajo de verdad.',
];

const motivationalMessage = computed(() => {
    const to = props.event?.to ?? 1;
    // Cycle through messages, starting with level 1 = index 0
    return LEVEL_MESSAGES[(to - 1) % LEVEL_MESSAGES.length];
});
</script>

<template>
  <Transition name="levelup">
    <div
      v-if="visible && event"
      class="fixed inset-0 z-[80] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
      role="dialog"
      aria-modal="true"
      aria-label="Subiste de nivel"
      @click.self="close"
    >
      <div
        class="relative w-full max-w-[340px] rounded-[28px] overflow-hidden bg-[#0b0b10] border border-wc-border p-5 shadow-[0_30px_80px_-20px_rgba(139,92,246,.4)]"
      >
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-violet-500">
              <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path d="M5 12.5 10 17.5 19 7.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
            <span class="font-display text-sm tracking-widest">WELLCORE</span>
          </div>
          <span class="text-[10px] text-wc-text-tertiary">
            Lvl {{ event.from }} → Lvl {{ event.to }}
          </span>
        </div>

        <!-- Title -->
        <h2 class="mt-4 font-display text-4xl leading-none tracking-wide">
          Subiste<br />
          <span class="text-violet-400">de nivel</span>
        </h2>
        <p class="mt-1 text-xs tracking-[0.22em] uppercase text-violet-400 flex items-center gap-1.5">
          <span class="h-1.5 w-1.5 rounded-full bg-violet-400" style="box-shadow: 0 0 0 4px rgba(139,92,246,.15)"></span>
          Ahora eres nivel {{ event.to }}
        </p>

        <!-- Bento grid: hero ring + stats -->
        <div class="mt-4 grid grid-cols-6 auto-rows-[70px] gap-2 text-sm">
          <!-- HERO: ring circular -->
          <div
            class="col-span-4 row-span-2 rounded-2xl border border-violet-500/30 p-3 flex flex-col items-center justify-center relative overflow-hidden"
            style="background: radial-gradient(circle at 20% 20%, rgba(139,92,246,.35), transparent 55%), linear-gradient(180deg, rgba(139,92,246,.16), rgba(139,92,246,.04))"
          >
            <div class="relative h-[120px] w-[120px]">
              <svg viewBox="0 0 110 110" class="h-full w-full">
                <defs>
                  <linearGradient id="gradLvl" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#a78bfa" />
                    <stop offset="100%" stop-color="#7c3aed" />
                  </linearGradient>
                </defs>
                <circle cx="55" cy="55" r="48" stroke-width="6" fill="none" stroke="rgba(255,255,255,.06)" />
                <circle
                  cx="55"
                  cy="55"
                  r="48"
                  stroke-width="6"
                  fill="none"
                  stroke="url(#gradLvl)"
                  stroke-linecap="round"
                  stroke-dasharray="301"
                  :stroke-dashoffset="ringDashoffset"
                  style="transform: rotate(-90deg); transform-origin: center; transition: stroke-dashoffset 1.2s ease-out"
                />
              </svg>
              <div class="absolute inset-0 flex flex-col items-center justify-center">
                <p class="text-[9px] tracking-wider uppercase text-violet-300">Nivel</p>
                <p class="font-display text-4xl leading-none">{{ event.to }}</p>
              </div>
            </div>
          </div>

          <div class="col-span-2 rounded-2xl bg-wc-bg-tertiary border border-violet-500/20 p-3">
            <p class="text-[9px] tracking-wider uppercase text-violet-400">XP Total</p>
            <p class="font-data text-xl font-bold mt-1">
              {{ event.totalXP.toLocaleString() }}
            </p>
          </div>
          <div class="col-span-2 rounded-2xl bg-wc-bg-tertiary border border-wc-border p-3">
            <p class="text-[9px] tracking-wider uppercase text-wc-text-tertiary">Ganados</p>
            <p class="font-data text-xl font-bold mt-1">
              +{{ event.xpGained.toLocaleString() }}
            </p>
          </div>

          <!-- Mensaje motivacional — rotamos por nivel para que no se sienta canned -->
          <div class="col-span-6 rounded-2xl bg-wc-bg-tertiary border border-violet-500/20 p-3">
            <p class="text-[9px] tracking-wider uppercase text-violet-400">Tu coach</p>
            <p class="mt-1 text-sm italic text-wc-text-secondary leading-snug">
              {{ motivationalMessage }}
            </p>
          </div>
        </div>

        <button
          @click="close"
          class="mt-4 w-full rounded-xl bg-wc-accent py-3 text-sm font-semibold shadow-[0_10px_30px_-10px_rgba(220,38,38,.5)] hover:brightness-110 transition"
        >
          Continuar
        </button>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.levelup-enter-active,
.levelup-leave-active {
    transition: opacity 0.3s ease;
}
.levelup-enter-active > div,
.levelup-leave-active > div {
    transition: transform 0.35s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.levelup-enter-from,
.levelup-leave-to {
    opacity: 0;
}
.levelup-enter-from > div {
    transform: scale(0.85) translateY(20px);
}
.levelup-leave-to > div {
    transform: scale(0.95) translateY(10px);
}
</style>
