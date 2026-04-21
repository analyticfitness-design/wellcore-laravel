<script setup>
import { useToastState } from '../../composables/useToast';

const state = useToastState();

function dismiss(id) {
  const idx = state.toasts.findIndex((t) => t.id === id);
  if (idx !== -1) state.toasts.splice(idx, 1);
}

function roleFor(type) {
  return type === 'error' || type === 'warning' ? 'alert' : 'status';
}
</script>

<template>
  <Teleport to="body">
    <div
      class="pointer-events-none fixed inset-x-0 top-4 z-[200] flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:bottom-4 sm:right-4 sm:top-auto sm:items-end"
      aria-live="polite"
      aria-atomic="false"
    >
      <TransitionGroup name="toast">
        <div
          v-for="t in state.toasts"
          :key="t.id"
          :role="roleFor(t.type)"
          :class="[
            'pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border p-3 shadow-lg backdrop-blur-xl',
            t.type === 'success' && 'border-emerald-500/40 bg-emerald-500/10 text-emerald-50 dark:bg-emerald-500/15',
            t.type === 'error' && 'border-wc-accent/50 bg-wc-accent/10 text-wc-text',
            t.type === 'info' && 'border-blue-500/40 bg-blue-500/10 text-wc-text',
            t.type === 'warning' && 'border-yellow-500/40 bg-yellow-500/10 text-wc-text',
          ]"
        >
          <!-- Icono -->
          <div
            :class="[
              'mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full',
              t.type === 'success' && 'text-emerald-500',
              t.type === 'error' && 'text-wc-accent',
              t.type === 'info' && 'text-blue-500',
              t.type === 'warning' && 'text-yellow-500',
            ]"
          >
            <!-- success check -->
            <svg v-if="t.type === 'success'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <!-- error x -->
            <svg v-else-if="t.type === 'error'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <!-- info -->
            <svg v-else-if="t.type === 'info'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
            <!-- warning -->
            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.008v.008H12v-.008Z" />
            </svg>
          </div>

          <!-- Contenido -->
          <div class="flex-1 min-w-0">
            <p v-if="t.title" class="text-sm font-semibold text-wc-text">{{ t.title }}</p>
            <p class="text-sm text-wc-text break-words">{{ t.message }}</p>
          </div>

          <!-- Cerrar -->
          <button
            type="button"
            @click="dismiss(t.id)"
            class="shrink-0 rounded-md p-1 text-wc-text-tertiary transition-colors hover:bg-wc-bg-tertiary hover:text-wc-text focus:outline-none focus:ring-2 focus:ring-wc-accent/40"
            aria-label="Cerrar notificación"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

@media (min-width: 640px) {
  .toast-enter-from {
    transform: translateX(12px);
  }
  .toast-leave-to {
    transform: translateX(12px);
  }
}
</style>
