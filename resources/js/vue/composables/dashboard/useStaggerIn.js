import { onBeforeUnmount, ref, watch, nextTick } from 'vue';

/**
 * useStaggerIn — añade entry-animations escalonadas a los hijos directos de un elemento.
 *
 * Uso:
 *   const staggerRoot = useStaggerIn();
 *   <div ref="staggerRoot"> ...secciones... </div>
 *
 * El composable observa el ref con `watch` (no onMounted) para manejar el caso
 * donde el contenedor está detrás de un v-if/v-else-if (ej: <div v-else-if="data">).
 *
 * Asigna `data-stagger-index="N"` incremental y la clase `in` cuando el
 * elemento entra al viewport (IntersectionObserver). El CSS se encarga de la
 * animación visual (opacity + translateY). Respeta `prefers-reduced-motion`.
 *
 * Solo corre una vez por mount — un segundo cambio del ref no reactiva el effect
 * para evitar flicker en re-renders.
 */
export function useStaggerIn(options = {}) {
    const {
        rootMargin = '0px 0px -40px 0px',
        threshold = 0.05,
        maxDelay = 480,
        selector = ':scope > *',
    } = options;

    const rootEl = ref(null);
    let observer = null;
    let applied = false;

    const stop = watch(rootEl, async (el) => {
        if (!el || applied) return;
        applied = true;
        await nextTick();

        // Respeta OS/browser setting: si usuario quiere menos animación, apply instant.
        const reduced = typeof window !== 'undefined'
            && window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;

        const children = Array.from(el.querySelectorAll(selector));
        children.forEach((node, idx) => {
            const delay = reduced ? 0 : Math.min(idx * 60, maxDelay);
            node.style.setProperty('--stagger-delay', `${delay}ms`);
            if (!node.hasAttribute('data-stagger-index')) {
                node.setAttribute('data-stagger-index', String(idx));
            }
            if (reduced) node.classList.add('in');
        });

        if (reduced) return;

        observer = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in');
                    observer.unobserve(entry.target);
                }
            }
        }, { rootMargin, threshold });

        children.forEach(node => observer.observe(node));
    }, { immediate: true, flush: 'post' });

    onBeforeUnmount(() => {
        stop();
        if (observer) {
            observer.disconnect();
            observer = null;
        }
    });

    return rootEl;
}
