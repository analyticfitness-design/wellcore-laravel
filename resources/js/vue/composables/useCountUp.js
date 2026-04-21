import { watch } from 'vue';
import { useReducedMotion } from './useReducedMotion';

/**
 * useCountUp — anima un elemento de 0 al valor numérico con easing cubic-out.
 *
 * @param {Ref<HTMLElement|null>} elRef
 * @param {Function} valueFn  - getter reactivo: () => props.value
 * @param {Object}   opts     - { duration: ms, decimals: null|number }
 */
export function useCountUp(elRef, valueFn, opts = {}) {
    const duration = opts.duration || 1200;
    const decimals = opts.decimals ?? null;
    const reducedMotion = useReducedMotion();

    watch(
        [elRef, valueFn],
        ([el, target]) => {
            if (!el || target == null) return;

            const num = Number(String(target).replace(/[^0-9.-]/g, ''));
            if (Number.isNaN(num)) { el.textContent = String(target); return; }

            if (reducedMotion.value) { el.textContent = String(target); return; }

            const decimals_ = decimals != null ? decimals : (String(target).split('.')[1]?.length || 0);
            const startTime = performance.now();

            function step(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3); // cubic-out
                const current = num * eased;
                el.textContent = decimals_ > 0 ? current.toFixed(decimals_) : Math.round(current).toLocaleString();
                if (progress < 1) requestAnimationFrame(step);
                else el.textContent = String(target);
            }
            requestAnimationFrame(step);
        },
        { immediate: true, flush: 'post' },
    );
}
