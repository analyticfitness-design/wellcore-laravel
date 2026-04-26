<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useHaptics } from '../../composables/useHaptics';

const router = useRouter();
const route = useRoute();
const haptics = useHaptics();

const open = ref(false);
const fabEl = ref(null);

// Cuando el workout está activo aparece una barra en bottom-0; subir el FAB para no taparla
const isInActiveWorkout = computed(() => /^\/client\/workout(\/|$)/.test(route.path));
const bottomOffset = computed(() => isInActiveWorkout.value ? '11rem' : '5rem');

const actions = [
    {
        key: 'training',
        label: 'Registrar entreno',
        to: '/client/plan',
        color: 'bg-wc-accent',
        iconPath: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
    },
    {
        key: 'checkin',
        label: 'Hacer check-in',
        to: '/client/checkin',
        color: 'bg-emerald-500',
        iconPath: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    },
    {
        key: 'weight',
        label: 'Registrar peso',
        to: '/client/metrics',
        color: 'bg-sky-500',
        iconPath: 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726',
    },
];

function toggle() {
    open.value = !open.value;
    haptics.light();
}

function handleAction(action) {
    haptics.light();
    open.value = false;
    router.push(action.to);
}

function handleBackdropClick() {
    open.value = false;
}

function handleEsc(e) {
    if (e.key === 'Escape' && open.value) {
        open.value = false;
    }
}

// Cierra si se hace click fuera del FAB
function handleClickOutside(e) {
    if (!open.value) return;
    if (fabEl.value && !fabEl.value.contains(e.target)) {
        open.value = false;
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleEsc);
    document.addEventListener('click', handleClickOutside, true);
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleEsc);
    document.removeEventListener('click', handleClickOutside, true);
});
</script>

<template>
  <!-- Backdrop cuando el FAB está expandido (mobile only) -->
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="open"
      class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
      @click="handleBackdropClick"
      aria-hidden="true"
    ></div>
  </Transition>

  <!-- FAB + actions (solo mobile) -->
  <div
    ref="fabEl"
    class="fixed z-50 flex flex-col items-end gap-3 lg:hidden"
    :style="{
      right: 'calc(1rem + env(safe-area-inset-right))',
      bottom: `calc(${bottomOffset} + env(safe-area-inset-bottom))`
    }"
  >
    <!-- Action pills -->
    <TransitionGroup
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 translate-y-2 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 translate-y-2 scale-95"
      move-class="transition-transform duration-200"
    >
      <button
        v-for="(action, idx) in actions"
        v-show="open"
        :key="action.key"
        type="button"
        @click="handleAction(action)"
        :style="{ 'transition-delay': open ? `${idx * 50}ms` : '0ms' }"
        class="flex items-center gap-3 rounded-full bg-wc-bg-secondary px-4 py-2.5 text-sm font-medium text-wc-text shadow-xl border border-wc-border hover:bg-wc-bg-tertiary active:scale-95 transition-transform"
      >
        <span>{{ action.label }}</span>
        <span :class="['flex h-8 w-8 items-center justify-center rounded-full shrink-0', action.color]">
          <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" :d="action.iconPath" />
          </svg>
        </span>
      </button>
    </TransitionGroup>

    <!-- FAB principal -->
    <button
      type="button"
      @click="toggle"
      :aria-expanded="open"
      aria-label="Acciones rápidas"
      :class="[
        'relative flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent text-white shadow-lg shadow-wc-accent/40 transition-all active:scale-90',
        open ? 'rotate-45' : 'rotate-0'
      ]"
      style="box-shadow: 0 8px 24px rgba(220,38,38,.4), 0 2px 6px rgba(0,0,0,.2);"
    >
      <svg class="h-6 w-6 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
      </svg>
    </button>
  </div>
</template>
