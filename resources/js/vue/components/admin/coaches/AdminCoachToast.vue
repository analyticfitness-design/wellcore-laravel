<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    type: { type: String, default: 'success' }, // success | error | info
    message: { type: String, default: '' },
});
const emit = defineEmits(['close']);

let timer = null;

watch(() => props.show, (val) => {
    clearTimeout(timer);
    if (val) {
        timer = setTimeout(() => emit('close'), 4200);
    }
});

onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
  <Teleport to="body">
    <Transition name="toast-up">
      <div
        v-if="show"
        class="toast"
        :class="`toast--${type}`"
        role="status"
      >{{ message }}</div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.toast {
    position: fixed;
    bottom: 78px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 100;
    padding: 11px 18px;
    border-radius: 10px;
    border: 1px solid;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 500;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
    max-width: 90vw;
    text-wrap: balance;
}
@media (min-width: 1024px) {
    .toast { bottom: 24px; }
}
.toast--success {
    border-color: rgba(16, 185, 129, 0.45);
    background: rgba(16, 185, 129, 0.12);
    color: var(--color-wc-green-text, #34D399);
}
.toast--error {
    border-color: rgba(220, 38, 38, 0.45);
    background: rgba(220, 38, 38, 0.12);
    color: var(--color-wc-red-text, #F87171);
}
.toast--info {
    border-color: var(--color-wc-border);
    background: rgba(17, 17, 17, 0.92);
    color: var(--color-wc-text-secondary);
}

.toast-up-enter-active,
.toast-up-leave-active { transition: opacity 0.2s var(--ease-out, ease), transform 0.2s var(--ease-out, ease); }
.toast-up-enter-from,
.toast-up-leave-to { opacity: 0; transform: translate(-50%, 10px); }

@media (prefers-reduced-motion: reduce) {
    .toast-up-enter-active,
    .toast-up-leave-active { transition: none !important; }
    .toast-up-enter-from,
    .toast-up-leave-to { transform: translate(-50%, 0); }
}
</style>
