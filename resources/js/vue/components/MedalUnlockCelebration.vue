<script setup>
import { ref, watch, onBeforeUnmount, computed } from 'vue';
import MedalHex from './MedalHex.vue';

const props = defineProps({
    medal: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const visible = ref(false);
let autoCloseTimer = null;

function openFor(_medal) {
    visible.value = true;
    clearTimeout(autoCloseTimer);
    autoCloseTimer = setTimeout(() => close(), 4200);
}

function close() {
    if (!visible.value) return;
    visible.value = false;
    clearTimeout(autoCloseTimer);
    autoCloseTimer = null;
    emit('close');
}

watch(
    () => props.medal,
    (m) => {
        if (m) openFor(m);
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    clearTimeout(autoCloseTimer);
});

const particleCount = 14;
const particles = Array.from({ length: particleCount }, (_, i) => i);

const particleColor = computed(() => {
    if (!props.medal) return '#FFD700';
    const t = props.medal.tier;
    if (t === 'legendario') return '#DC2626';
    if (t === 'platino') return '#00FFFF';
    if (t === 'oro') return '#FFD700';
    if (t === 'plata') return '#C0C0C0';
    return '#F59E0B';
});
</script>

<template>
  <Transition name="celebrate">
    <div
      v-if="visible && medal"
      class="celebrate-root"
      role="dialog"
      aria-modal="true"
      aria-label="Medalla desbloqueada"
      @click.self="close"
    >
      <!-- White flash -->
      <div class="flash"></div>

      <!-- Radial backdrop -->
      <div class="backdrop"></div>

      <!-- Content -->
      <div class="content">
        <p class="kicker">MEDALLA DESBLOQUEADA</p>

        <div class="medal-stage">
          <!-- Big hex, non-interactive -->
          <MedalHex :medal="medal" :size="200" :interactive="false" />

          <!-- Particles -->
          <span
            v-for="i in particles"
            :key="i"
            class="particle"
            :style="{
                '--angle': (i * (360 / particleCount)) + 'deg',
                '--delay': (i * 0.04) + 's',
                '--color': particleColor,
            }"
          ></span>
        </div>

        <h2 class="title">{{ medal.name }}</h2>
        <p class="desc">{{ medal.description }}</p>

        <div class="xp-banner">
          <span>+{{ medal.xp }} XP</span>
        </div>

        <button type="button" class="dismiss" @click="close">
          CONTINUAR
        </button>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.celebrate-root {
    position: fixed;
    inset: 0;
    z-index: 100;
    display: grid;
    place-items: center;
    padding: 24px;
    font-family: 'Raleway', 'Inter', sans-serif;
    color: #FAFAFA;
}

.backdrop {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at center, rgba(220,38,38,0.25), rgba(9,9,11,0.92) 60%),
        rgba(9, 9, 11, 0.88);
    backdrop-filter: blur(8px);
}

/* White flash fade */
.flash {
    position: absolute;
    inset: 0;
    background: #FFFFFF;
    animation: flash-fade 0.45s ease-out forwards;
    pointer-events: none;
    z-index: 2;
}
@keyframes flash-fade {
    0%   { opacity: 1; }
    100% { opacity: 0; }
}

.content {
    position: relative;
    z-index: 3;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    text-align: center;
    max-width: 420px;
    width: 100%;
}

.kicker {
    font-family: 'Oswald', 'Bebas Neue', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.28em;
    color: #DC2626;
    margin: 0;
}

.medal-stage {
    position: relative;
    display: grid;
    place-items: center;
    width: 260px;
    height: 260px;
    animation: medal-bounce 0.85s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes medal-bounce {
    0%   { transform: scale(0.2) rotate(-18deg); opacity: 0; }
    55%  { transform: scale(1.18) rotate(6deg); opacity: 1; }
    80%  { transform: scale(0.96) rotate(-2deg); }
    100% { transform: scale(1) rotate(0); opacity: 1; }
}

/* Particles */
.particle {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--color, #FFD700);
    box-shadow: 0 0 12px var(--color, #FFD700);
    opacity: 0;
    transform: translate(-50%, -50%);
    animation: particle-burst 1.4s ease-out var(--delay, 0s) forwards;
    pointer-events: none;
}
@keyframes particle-burst {
    0% {
        opacity: 0;
        transform: translate(-50%, -50%) rotate(var(--angle)) translateX(0) scale(0.4);
    }
    30% {
        opacity: 1;
    }
    100% {
        opacity: 0;
        transform: translate(-50%, -50%) rotate(var(--angle)) translateX(180px) scale(0.8);
    }
}

.title {
    font-family: 'Oswald', 'Bebas Neue', sans-serif;
    font-weight: 700;
    font-size: 32px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin: 6px 0 0;
    color: #FAFAFA;
    text-shadow: 0 2px 12px rgba(220,38,38,0.55);
}
.desc {
    font-size: 13px;
    color: rgba(250,250,250,0.7);
    line-height: 1.45;
    margin: 0;
    max-width: 340px;
}

.xp-banner {
    margin-top: 8px;
    font-family: 'JetBrains Mono', ui-monospace, monospace;
    font-size: 18px;
    font-weight: 700;
    color: #000000;
    background: #C4D92E;
    padding: 8px 20px;
    border-radius: 100px;
    letter-spacing: 0.06em;
    box-shadow: 0 6px 20px rgba(196,217,46,0.45);
}

.dismiss {
    margin-top: 10px;
    font-family: 'Oswald', 'Bebas Neue', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.2em;
    color: #FAFAFA;
    background: transparent;
    border: 1px solid rgba(250,250,250,0.25);
    padding: 10px 28px;
    border-radius: 8px;
    cursor: pointer;
    transition: border-color 0.2s ease, background 0.2s ease;
}
.dismiss:hover {
    border-color: #DC2626;
    background: rgba(220,38,38,0.12);
}

/* Enter/leave */
.celebrate-enter-active,
.celebrate-leave-active {
    transition: opacity 0.35s ease;
}
.celebrate-enter-from,
.celebrate-leave-to {
    opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
    .flash { animation: none; opacity: 0; }
    .medal-stage { animation: none; }
    .particle { animation: none; opacity: 0; }
}
</style>
