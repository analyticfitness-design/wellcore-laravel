import { onMounted } from 'vue';
import { useReducedMotion } from './useReducedMotion';

/**
 * useStagger — anima hijos de un container en secuencia (fade + slide-up).
 *
 * @param {Ref<HTMLElement|null>} containerRef
 * @param {Object} opts - { delay: ms entre hijos, duration: ms de cada animación }
 */
export function useStagger(containerRef, opts = {}) {
    const delay = opts.delay || 80;
    const duration = opts.duration || 400;
    const reducedMotion = useReducedMotion();

    onMounted(() => {
        if (!containerRef.value || reducedMotion.value) return;
        const children = Array.from(containerRef.value.children);
        children.forEach((child, i) => {
            child.style.opacity = '0';
            child.style.transform = 'translateY(12px)';
            child.style.transition = `opacity ${duration}ms cubic-bezier(0.2,0.8,0.2,1) ${i * delay}ms, transform ${duration}ms cubic-bezier(0.2,0.8,0.2,1) ${i * delay}ms`;
            requestAnimationFrame(() => {
                child.style.opacity = '1';
                child.style.transform = 'translateY(0)';
            });
        });
    });
}
