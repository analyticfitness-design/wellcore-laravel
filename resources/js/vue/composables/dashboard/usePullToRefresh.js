import { ref, onMounted, onBeforeUnmount } from 'vue';

/**
 * usePullToRefresh — gesture nativo de apps mobile para refresh manual.
 *
 * Uso:
 *   const { containerRef, pullDistance, isRefreshing } = usePullToRefresh(async () => {
 *     await fetchData();
 *   });
 *   <div ref="containerRef"> ... </div>
 *   <PullIndicator :distance="pullDistance" :refreshing="isRefreshing" />
 *
 * Implementación:
 * - Listener touchstart/move/end en el container.
 * - Solo dispara si scrollTop === 0 al iniciar el touch.
 * - Resistencia: delta visual = touch_delta / 2 (feel nativo, no lineal).
 * - Umbral: 70px → al soltar, dispara onRefresh().
 * - Durante refresh: pullDistance se mantiene en umbral hasta resolver.
 * - Desktop/sin touch: no-op (el listener nunca se activa).
 */
export function usePullToRefresh(onRefresh, options = {}) {
    const {
        threshold = 70,
        maxPull = 120,
        resistance = 2,
    } = options;

    const containerRef = ref(null);
    const pullDistance = ref(0);
    const isRefreshing = ref(false);

    let startY = 0;
    let active = false;

    function onTouchStart(e) {
        if (isRefreshing.value) return;
        // Solo iniciar si estamos en el top del scroll
        const scrollTop = containerRef.value?.scrollTop
            ?? document.documentElement.scrollTop
            ?? window.scrollY;
        if (scrollTop > 0) return;
        startY = e.touches[0].clientY;
        active = true;
    }

    function onTouchMove(e) {
        if (!active || isRefreshing.value) return;
        const deltaY = e.touches[0].clientY - startY;
        if (deltaY <= 0) {
            pullDistance.value = 0;
            return;
        }
        // Aplicar resistencia (feel nativo) y cap en maxPull
        pullDistance.value = Math.min(deltaY / resistance, maxPull);
    }

    async function onTouchEnd() {
        if (!active || isRefreshing.value) return;
        active = false;

        if (pullDistance.value >= threshold) {
            isRefreshing.value = true;
            pullDistance.value = threshold;
            try {
                await onRefresh?.();
            } catch (err) {
                console.error('[pull-to-refresh] onRefresh error', err);
            } finally {
                isRefreshing.value = false;
                pullDistance.value = 0;
            }
        } else {
            pullDistance.value = 0;
        }
    }

    function onTouchCancel() {
        active = false;
        if (!isRefreshing.value) pullDistance.value = 0;
    }

    onMounted(() => {
        // Si no hay touch support, no registrar listeners (save bytes).
        if (typeof window === 'undefined') return;
        if (!('ontouchstart' in window) && !(navigator.maxTouchPoints > 0)) return;

        const target = containerRef.value || document.documentElement;
        target.addEventListener('touchstart', onTouchStart, { passive: true });
        target.addEventListener('touchmove', onTouchMove, { passive: true });
        target.addEventListener('touchend', onTouchEnd, { passive: true });
        target.addEventListener('touchcancel', onTouchCancel, { passive: true });
    });

    onBeforeUnmount(() => {
        const target = containerRef.value || document.documentElement;
        target.removeEventListener('touchstart', onTouchStart);
        target.removeEventListener('touchmove', onTouchMove);
        target.removeEventListener('touchend', onTouchEnd);
        target.removeEventListener('touchcancel', onTouchCancel);
    });

    return { containerRef, pullDistance, isRefreshing };
}
