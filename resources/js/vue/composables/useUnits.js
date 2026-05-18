import { computed } from 'vue';
import { useLocaleStore } from '../stores/locale';

/**
 * useUnits — formatters para peso/distancia/temperatura/altura según `unit_system`
 * del usuario. Toda data en backend se guarda en MÉTRICO; este composable
 * formatea para presentación.
 *
 * - kg ↔ lbs       (1 kg = 2.20462 lbs)
 * - cm ↔ in        (1 in = 2.54 cm)
 * - km ↔ mi        (1 mi = 1.609344 km)
 * - °C ↔ °F        (°F = °C * 9/5 + 32)
 *
 * Inputs del usuario (formularios) deben pasar por `parseWeightInput`,
 * `parseDistanceInput`, etc. para almacenar siempre en métrico.
 */

const KG_TO_LBS = 2.2046226218;
const CM_TO_IN = 0.3937007874;
const KM_TO_MI = 0.6213711922;

export function useUnits() {
    const store = useLocaleStore();

    const unitSystem = computed(() => store.unitSystem ?? 'metric');
    const isImperial = computed(() => unitSystem.value === 'imperial');

    function formatWeight(kg, { precision = 1, withUnit = true } = {}) {
        if (kg == null || Number.isNaN(Number(kg))) return '';
        const value = Number(kg);
        if (isImperial.value) {
            const lbs = value * KG_TO_LBS;
            return withUnit ? `${lbs.toFixed(precision)} lbs` : lbs.toFixed(precision);
        }
        return withUnit ? `${value.toFixed(precision)} kg` : value.toFixed(precision);
    }

    function formatHeight(cm, { withUnit = true } = {}) {
        if (cm == null || Number.isNaN(Number(cm))) return '';
        const value = Number(cm);
        if (isImperial.value) {
            const totalInches = value * CM_TO_IN;
            const feet = Math.floor(totalInches / 12);
            const inches = Math.round(totalInches - feet * 12);
            return withUnit ? `${feet}'${inches}"` : `${feet}-${inches}`;
        }
        return withUnit ? `${Math.round(value)} cm` : `${Math.round(value)}`;
    }

    function formatLength(cm, { precision = 1, withUnit = true } = {}) {
        if (cm == null || Number.isNaN(Number(cm))) return '';
        const value = Number(cm);
        if (isImperial.value) {
            const inches = value * CM_TO_IN;
            return withUnit ? `${inches.toFixed(precision)} in` : inches.toFixed(precision);
        }
        return withUnit ? `${value.toFixed(precision)} cm` : value.toFixed(precision);
    }

    function formatDistance(km, { precision = 2, withUnit = true } = {}) {
        if (km == null || Number.isNaN(Number(km))) return '';
        const value = Number(km);
        if (isImperial.value) {
            const mi = value * KM_TO_MI;
            return withUnit ? `${mi.toFixed(precision)} mi` : mi.toFixed(precision);
        }
        return withUnit ? `${value.toFixed(precision)} km` : value.toFixed(precision);
    }

    function formatTemperature(c, { precision = 0, withUnit = true } = {}) {
        if (c == null || Number.isNaN(Number(c))) return '';
        const value = Number(c);
        if (isImperial.value) {
            const f = (value * 9) / 5 + 32;
            return withUnit ? `${f.toFixed(precision)}°F` : f.toFixed(precision);
        }
        return withUnit ? `${value.toFixed(precision)}°C` : value.toFixed(precision);
    }

    /** Convierte input del usuario (en su sistema actual) a kg para guardar. */
    function parseWeightToKg(value) {
        const n = Number(value);
        if (Number.isNaN(n)) return null;
        return isImperial.value ? n / KG_TO_LBS : n;
    }

    function parseLengthToCm(value) {
        const n = Number(value);
        if (Number.isNaN(n)) return null;
        return isImperial.value ? n / CM_TO_IN : n;
    }

    function parseDistanceToKm(value) {
        const n = Number(value);
        if (Number.isNaN(n)) return null;
        return isImperial.value ? n / KM_TO_MI : n;
    }

    function parseTemperatureToC(value) {
        const n = Number(value);
        if (Number.isNaN(n)) return null;
        return isImperial.value ? ((n - 32) * 5) / 9 : n;
    }

    return {
        unitSystem,
        isImperial,
        formatWeight,
        formatHeight,
        formatLength,
        formatDistance,
        formatTemperature,
        parseWeightToKg,
        parseLengthToCm,
        parseDistanceToKm,
        parseTemperatureToC,
    };
}
