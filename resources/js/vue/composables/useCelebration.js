import { ref, shallowReactive, readonly } from 'vue';

/**
 * useCelebration — singleton global para disparar celebraciones fullscreen.
 *
 * Uso desde cualquier composable:
 *   const { celebrate } = useCelebration();
 *   celebrate('workout', { hero: { value: '10.0', unit: 'kg', ... }, stats: [...] });
 *
 * El <BentoCelebration :global="true"> montado en ClientLayout/RiseLayout escucha y renderiza.
 */

const currentCelebration = ref(null);
const queue = shallowReactive([]);

function celebrate(preset, data = {}) {
    const entry = {
        preset,
        data,
        id: Date.now() + Math.random(),
        createdAt: Date.now(),
    };

    if (currentCelebration.value) {
        queue.push(entry);
    } else {
        currentCelebration.value = entry;
    }
    return entry.id;
}

function dismiss(id) {
    if (currentCelebration.value?.id === id || id === undefined) {
        currentCelebration.value = null;
        // Dequeue siguiente con pequeño delay para que la salida se complete
        setTimeout(() => {
            const next = queue.shift();
            if (next) currentCelebration.value = next;
        }, 400);
    } else {
        const idx = queue.findIndex((e) => e.id === id);
        if (idx !== -1) queue.splice(idx, 1);
    }
}

function clear() {
    currentCelebration.value = null;
    queue.splice(0, queue.length);
}

export function useCelebration() {
    return {
        current: readonly(currentCelebration),
        queueLength: readonly(ref(queue.length)),
        celebrate,
        dismiss,
        clear,
    };
}
