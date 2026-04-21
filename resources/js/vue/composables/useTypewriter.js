import { ref, watch } from 'vue';
import { useReducedMotion } from './useReducedMotion';

/**
 * useTypewriter — revela texto caracter a caracter.
 *
 * @param {Function} textFn  - getter reactivo: () => 'texto a revelar'
 * @param {Object}   opts    - { speed: ms por caracter }
 * @returns {Ref<string>} displayed
 */
export function useTypewriter(textFn, opts = {}) {
    const speed = opts.speed || 30;
    const displayed = ref('');
    const reducedMotion = useReducedMotion();

    watch(
        textFn,
        (text) => {
            if (!text) { displayed.value = ''; return; }
            if (reducedMotion.value) { displayed.value = text; return; }
            displayed.value = '';
            let i = 0;
            const interval = setInterval(() => {
                displayed.value = text.slice(0, ++i);
                if (i >= text.length) clearInterval(interval);
            }, speed);
        },
        { immediate: true },
    );

    return displayed;
}
