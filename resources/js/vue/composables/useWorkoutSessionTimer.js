import { ref, computed, onBeforeUnmount } from 'vue';

/**
 * useWorkoutSessionTimer
 *
 * Timer global de la sesión de entrenamiento. Usa timestamps absolutos
 * para resistir background suspends y reload de página.
 *
 * - start(initialElapsed = 0) — arranca/continúa la sesión
 * - stop()                    — detiene el conteo
 * - reset()                   — resetea a 0
 * - resync()                  — re-calcula desde startTimestamp tras visibilitychange
 *
 * Display reactivo: elapsedDisplay ('MM:SS' o 'H:MM:SS' si > 1h)
 */
export function useWorkoutSessionTimer() {
    const elapsed = ref(0); // segundos transcurridos
    let intervalId = null;
    let startTimestamp = null;
    let initialOffset = 0;

    function tick() {
        if (startTimestamp === null) return;
        elapsed.value = initialOffset + Math.floor((Date.now() - startTimestamp) / 1000);
    }

    function startInterval() {
        stopInterval();
        intervalId = setInterval(tick, 1000);
    }
    function stopInterval() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }

    function start(initialElapsed = 0) {
        initialOffset = Math.max(0, initialElapsed);
        startTimestamp = Date.now();
        elapsed.value = initialOffset;
        startInterval();
    }

    function stop() {
        stopInterval();
        startTimestamp = null;
    }

    function reset() {
        stop();
        elapsed.value = 0;
        initialOffset = 0;
    }

    function resync() {
        if (startTimestamp !== null) tick();
    }

    const elapsedDisplay = computed(() => {
        const total = Math.max(0, elapsed.value);
        const h = Math.floor(total / 3600);
        const m = Math.floor((total % 3600) / 60);
        const s = total % 60;
        const mStr = String(m).padStart(2, '0');
        const sStr = String(s).padStart(2, '0');
        return (h > 0 ? `${h}:` : '') + `${mStr}:${sStr}`;
    });

    onBeforeUnmount(stop);

    return {
        elapsed,
        elapsedDisplay,
        start,
        stop,
        reset,
        resync,
    };
}
