import { onMounted, onBeforeUnmount, ref } from 'vue';

/**
 * useStaggerIn — añade entry-animations escalonadas a los hijos directos de un elemento.
 *
 * Uso:
 *   const staggerRoot = useStaggerIn();
 *   <div :ref="staggerRoot"> ...secciones... </div>
 *
 * El composable asigna `data-stagger-index="N"` incremental y la clase `in` cuando
 * el elemento entra al viewport (IntersectionObserver). El CSS se encarga de la
 * animación visual (opacity + translateY). Respeta `prefers-reduced-motion`.
 *
 * Solo corre una vez al mount — no se reactiva con re-renders, evitando flicker.
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

    onMounted(() => {
        if (!rootEl.value) return;

        // Respeta OS/browser setting: si usuario quiere menos animación, apply instant.
        const reduced = typeof window !== 'undefined'
            && window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;

        const children = Array.from(rootEl.value.querySelectorAll(selector));
        children.forEach((el, idx) => {
            const delay = reduced ? 0 : Math.min(idx * 60, maxDelay);
            el.style.setProperty('--stagger-delay', `${delay}ms`);
            if (!el.hasAttribute('data-stagger-index')) {
                el.setAttribute('data-stagger-index', String(idx));
            }
            if (reduced) el.classList.add('in');
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

        children.forEach(el => observer.observe(el));
    });

    onBeforeUnmount(() => {
        if (observer) {
            observer.disconnect();
            observer = null;
        }
    });

    return rootEl;
}
