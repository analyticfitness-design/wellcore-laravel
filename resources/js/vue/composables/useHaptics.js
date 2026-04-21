/**
 * useHaptics — Vibration API wrapper.
 * Stub funcional: ya usa navigator.vibrate en los patrones conocidos.
 * FASE 3 ampliará con patrones más ricos.
 */

const PATTERNS = {
    light: [10],
    success: [15, 50, 15],
    achievement: [20, 40, 20, 40, 60],
    levelUp: [30, 60, 30, 60, 100],
    error: [50, 30, 50],
};

function vibrate(pattern) {
    if (typeof navigator !== 'undefined' && navigator.vibrate) {
        navigator.vibrate(pattern);
    }
}

export function useHaptics() {
    function light() {
        vibrate(PATTERNS.light);
    }

    function pattern(name) {
        vibrate(PATTERNS[name] || PATTERNS.light);
    }

    return { light, pattern };
}
