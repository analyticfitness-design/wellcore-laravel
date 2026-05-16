/**
 * useCardioAudio.js
 *
 * Manager mínimo de audio cues para cardio (beeps + vibración).
 * Usa Web Audio API directamente (sin assets). El AudioContext se crea lazy
 * en el primer call para respetar la política autoplay de mobile browsers.
 *
 * También soporta vibración como fallback cuando audio está bloqueado.
 *
 * Toggle persistente: localStorage `wc_cardio_audio_enabled` (default true).
 */
import { ref } from 'vue';

let audioCtx = null;
const enabled = ref(true);

// Init desde localStorage al cargar
try {
    const raw = localStorage.getItem('wc_cardio_audio_enabled');
    if (raw === '0') enabled.value = false;
} catch { /* ignore */ }

function ensureCtx() {
    if (!enabled.value) return null;
    if (audioCtx) return audioCtx;
    const AC = window.AudioContext || window.webkitAudioContext;
    if (!AC) return null;
    try {
        audioCtx = new AC();
        return audioCtx;
    } catch {
        return null;
    }
}

function beep(frequency, durationMs = 100, volume = 0.3) {
    const ctx = ensureCtx();
    if (!ctx) return;
    try {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.value = frequency;
        osc.type = 'sine';
        gain.gain.setValueAtTime(0, ctx.currentTime);
        gain.gain.linearRampToValueAtTime(volume, ctx.currentTime + 0.01);
        gain.gain.linearRampToValueAtTime(0, ctx.currentTime + durationMs / 1000);
        osc.start();
        osc.stop(ctx.currentTime + durationMs / 1000 + 0.05);
    } catch { /* ignore */ }
}

function vibrate(pattern) {
    if (navigator.vibrate && enabled.value) {
        try { navigator.vibrate(pattern); } catch { /* ignore */ }
    }
}

// Cues de uso común para intervalos
export function workStart()     { beep(880, 150, 0.4); vibrate([100]); }
export function workEnding()    { beep(660, 80, 0.25); }                    // Últimos 3 segundos
export function restStart()     { beep(440, 150, 0.4); vibrate([100, 50, 100]); }
export function restEnding()    { beep(660, 80, 0.25); }
export function roundComplete() { beep(880, 100, 0.4); setTimeout(() => beep(880, 100, 0.4), 150); vibrate([200, 100, 200]); }
export function allDone()       { beep(660, 120, 0.4); setTimeout(() => beep(880, 120, 0.4), 200); setTimeout(() => beep(1100, 200, 0.5), 400); vibrate([200, 50, 200, 50, 400]); }
export function lastRound()     { beep(550, 200, 0.4); }

export function useCardioAudio() {
    function toggle() {
        enabled.value = !enabled.value;
        try { localStorage.setItem('wc_cardio_audio_enabled', enabled.value ? '1' : '0'); } catch { /* ignore */ }
        // Si se activa, "prime" el audio context con un beep silente para desbloquear autoplay
        if (enabled.value) {
            const ctx = ensureCtx();
            if (ctx && ctx.state === 'suspended') ctx.resume().catch(() => {});
        }
    }

    function prime() {
        // Llamar en el primer click del cliente para desbloquear AudioContext en Safari iOS
        const ctx = ensureCtx();
        if (ctx && ctx.state === 'suspended') {
            ctx.resume().catch(() => {});
        }
    }

    return {
        enabled,
        toggle,
        prime,
        workStart,
        workEnding,
        restStart,
        restEnding,
        roundComplete,
        allDone,
        lastRound,
    };
}
