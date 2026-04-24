import { ref, onMounted, onBeforeUnmount, watch } from 'vue';

/**
 * useViewportAnimate — activa una flag reactiva cuando el elemento entra al viewport.
 *
 * Uso para disparar animaciones "al entrar al fold" tipo ring-fill o bar-fill:
 *   const { targetRef, visible } = useViewportAnimate();
 *   <svg ref="targetRef" :stroke-dashoffset="visible ? computedOffset : 251">...</svg>
 *
 * - `visible` pasa a true una sola vez (cuando cruza el threshold).
 * - Después la animación puede seguir reactivamente con cambios de props.
 * - Respeta prefers-reduced-motion: dispara inmediatamente sin animación.
 */
export function useViewportAnimate(options = {}) {
    const { threshold = 0.3, rootMargin = '0px' } = options;

    const targetRef = ref(null);
    const visible = ref(false);
    let observer = null;

    const stop = watch(targetRef, (el) => {
        if (!el || visible.value) return;

        // Reduced motion: flag inmediato, la animación se reduce a none vía CSS.
        const reduced = typeof window !== 'undefined'
            && window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;
        if (reduced) {
            visible.value = true;
            return;
        }

        observer = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    visible.value = true;
                    observer.disconnect();
                    observer = null;
                    break;
                }
            }
        }, { threshold, rootMargin });

        observer.observe(el);
    }, { immediate: true, flush: 'post' });

    onBeforeUnmount(() => {
        stop();
        if (observer) {
            observer.disconnect();
            observer = null;
        }
    });

    return { targetRef, visible };
}
