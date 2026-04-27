// resources/js/vue/composables/useVoiceLogger.js
import { ref } from 'vue';
import { VoiceCaps }            from '../../voice/voice-capabilities.js';
import { WebSpeechEngine }      from '../../voice/web-speech-engine.js';
import { extractWorkoutIntent } from '../../voice/parser-fitness.js';

export function useVoiceLogger() {
    const engine       = VoiceCaps.pickEngine();
    const listening    = ref(false);
    const isProcessing = ref(false);
    const confirmation = ref(null);   // { weight, reps, unit, confidence }
    const error        = ref(null);
    let _sr = null;

    async function startListening() {
        if (listening.value || isProcessing.value) return;
        error.value        = null;
        confirmation.value = null;

        if (!engine) {
            error.value = 'Tu navegador no soporta voz. Usa el teclado.';
            return;
        }

        try {
            listening.value = true;
            _sr = new WebSpeechEngine({ lang: 'es-CO' });
            const alts = await _sr.listen();
            _sr = null;

            for (const t of alts) {
                const intent = extractWorkoutIntent(t);
                if (intent.intent === 'log_set' && intent.reps && intent.weight) {
                    confirmation.value = intent;
                    return;
                }
                if (intent.intent === 'complete_only') {
                    // caller checks this via onCompleteOnly callback
                    confirmation.value = { intent: 'complete_only' };
                    return;
                }
            }

            error.value = 'No te entendí. Prueba: "quince reps con cincuenta kilos"';
        } catch (e) {
            const msg = e?.message ?? '';
            error.value = msg === 'not-allowed'
                ? 'Permiso de micrófono denegado. Actívalo en configuración del navegador.'
                : 'Error con el reconocimiento. Vuelve a intentarlo.';
        } finally {
            listening.value = false;
        }
    }

    function stopListening() {
        _sr?.stop();
        _sr             = null;
        listening.value = false;
    }

    function cancel() {
        confirmation.value = null;
        error.value        = null;
    }

    return { engine, listening, isProcessing, confirmation, error, startListening, stopListening, cancel };
}
