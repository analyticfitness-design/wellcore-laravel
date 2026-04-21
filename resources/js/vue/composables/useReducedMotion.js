import { ref, onMounted, onBeforeUnmount } from 'vue';

/**
 * useReducedMotion — reactivo a prefers-reduced-motion.
 * Retorna ref boolean; true significa que el usuario prefiere menos animación.
 */
export function useReducedMotion() {
    const reducedMotion = ref(false);
    let mq = null;

    function update() {
        reducedMotion.value = mq ? mq.matches : false;
    }

    onMounted(() => {
        if (typeof window === 'undefined') return;
        mq = window.matchMedia('(prefers-reduced-motion: reduce)');
        update();
        mq.addEventListener('change', update);
    });

    onBeforeUnmount(() => {
        if (mq) mq.removeEventListener('change', update);
    });

    return reducedMotion;
}
