import { ref, onBeforeUnmount } from 'vue';

/**
 * useRestTimer({ onComplete, playBeep })
 *
 * Timer de descanso entre sets. Usa timestamps reales (no setInterval count)
 * para sobrevivir background suspends del browser móvil.
 *
 * - start(seconds, exIndex?, setIndex?) → arranca el timer
 * - pause() / resume()
 * - skip() → cierra el timer sin completar
 * - add(s) / subtract(s) → ajustes ±15s
 *
 * Triggers de audio:
 * - beep al iniciar (440Hz · 100ms)
 * - countdown últimos 3s (660Hz · 80ms)
 * - beep doble al terminar (880Hz · 150ms × 2)
 */
export function useRestTimer({ onComplete, playBeep } = {}) {
    const secondsRemaining = ref(0);
    const totalSeconds = ref(0);
    const isVisible = ref(false);
    const isPaused = ref(false);
    const associatedExerciseIndex = ref(null);
    const associatedSetIndex = ref(null);

    let intervalId = null;
    let startedAt = 0;
    let pausedAt = 0;
    let pausedDuration = 0;
    let lastBeepSecond = -1;

    function tick() {
        if (isPaused.value) return;
        const elapsed = Math.floor((Date.now() - startedAt - pausedDuration) / 1000);
        const remaining = Math.max(0, totalSeconds.value - elapsed);
        secondsRemaining.value = remaining;

        // Countdown beeps últimos 3 segundos
        if (remaining > 0 && remaining <= 3 && remaining !== lastBeepSecond) {
            lastBeepSecond = remaining;
            if (playBeep) playBeep(660, 0.08);
        }

        if (remaining <= 0) {
            stopInterval();
            isVisible.value = false;
            if (playBeep) {
                playBeep(880, 0.15);
                setTimeout(() => playBeep(880, 0.15), 200);
            }
            if (onComplete) {
                onComplete({
                    exIndex: associatedExerciseIndex.value,
                    setIndex: associatedSetIndex.value,
                });
            }
        }
    }

    function startInterval() {
        stopInterval();
        intervalId = setInterval(tick, 250);
    }
    function stopInterval() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }

    function start(seconds, exIndex = null, setIndex = null) {
        if (!seconds || seconds <= 0) return;
        totalSeconds.value = seconds;
        secondsRemaining.value = seconds;
        associatedExerciseIndex.value = exIndex;
        associatedSetIndex.value = setIndex;
        isPaused.value = false;
        isVisible.value = true;
        startedAt = Date.now();
        pausedDuration = 0;
        lastBeepSecond = -1;
        if (playBeep) playBeep(440, 0.10);
        startInterval();
    }

    function pause() {
        if (!isVisible.value || isPaused.value) return;
        isPaused.value = true;
        pausedAt = Date.now();
    }

    function resume() {
        if (!isVisible.value || !isPaused.value) return;
        pausedDuration += Date.now() - pausedAt;
        isPaused.value = false;
    }

    function skip() {
        stopInterval();
        isVisible.value = false;
        isPaused.value = false;
        secondsRemaining.value = 0;
    }

    function add(s) {
        if (!isVisible.value) return;
        totalSeconds.value += s;
        secondsRemaining.value += s;
    }

    function subtract(s) {
        if (!isVisible.value) return;
        totalSeconds.value = Math.max(1, totalSeconds.value - s);
        secondsRemaining.value = Math.max(1, secondsRemaining.value - s);
    }

    /** Re-sincroniza tras visibility change (browser background suspend). */
    function resync() {
        if (!isVisible.value || isPaused.value) return;
        tick();
    }

    onBeforeUnmount(stopInterval);

    return {
        secondsRemaining,
        totalSeconds,
        isVisible,
        isPaused,
        associatedExerciseIndex,
        associatedSetIndex,
        start,
        pause,
        resume,
        skip,
        add,
        subtract,
        resync,
    };
}
