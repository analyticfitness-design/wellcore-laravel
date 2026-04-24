import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

/**
 * useFeatureFlags — feature toggle store.
 *
 * Flags persistidos en localStorage bajo la clave `wc_flags_v1`.
 * Permite rollout gradual de experimentos sin tocar el servidor.
 *
 * Activación manual desde la consola:
 *   localStorage.setItem('wc_flags_v1', JSON.stringify({ dashboard_v2: true }))
 *
 * O vía URL query string (útil para QA):
 *   ?ff=dashboard_v2        → activa dashboard_v2
 *   ?ff=!dashboard_v2       → desactiva dashboard_v2
 *
 * Los flags son aditivos: nunca rompen la experiencia existente.
 */

const STORAGE_KEY = 'wc_flags_v1';

function readFlags() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return {};
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch {
        return {};
    }
}

function writeFlags(flags) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(flags));
    } catch {
        // ignore quota/private-mode errors
    }
}

function applyUrlOverrides(flags) {
    if (typeof window === 'undefined') return flags;
    const params = new URLSearchParams(window.location.search);
    const ff = params.getAll('ff');
    if (ff.length === 0) return flags;
    const next = { ...flags };
    for (const entry of ff) {
        if (!entry) continue;
        if (entry.startsWith('!')) {
            next[entry.slice(1)] = false;
        } else {
            next[entry] = true;
        }
    }
    writeFlags(next);
    return next;
}

export const useFeatureFlags = defineStore('featureFlags', () => {
    const flags = ref(applyUrlOverrides(readFlags()));

    const dashboardV2 = computed(() => flags.value.dashboard_v2 === true);

    function set(name, value) {
        flags.value = { ...flags.value, [name]: !!value };
        writeFlags(flags.value);
    }

    function toggle(name) {
        set(name, !flags.value[name]);
    }

    function isEnabled(name) {
        return flags.value[name] === true;
    }

    return { flags, dashboardV2, set, toggle, isEnabled };
});
