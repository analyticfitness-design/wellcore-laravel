// Genérico Web Speech transcription — para dictado libre (descripciones de comida, notas, etc.)
// Diferente a useVoiceLogger (que parsea reps/weight con parser-fitness).
import { ref, computed } from 'vue';

export function useVoiceTranscription({ lang = 'es-CO' } = {}) {
    const SR = typeof window !== 'undefined'
        ? (window.SpeechRecognition || window.webkitSpeechRecognition)
        : null;

    const supported = computed(() => SR !== null && SR !== undefined);
    const listening = ref(false);
    const error = ref(null);
    const transcript = ref('');
    let _recognition = null;

    /**
     * Inicia escucha. Resuelve con el texto transcrito.
     * @param {object} opts - { continuous: bool, interim: bool }
     * @returns {Promise<string>} transcript
     */
    function start(opts = {}) {
        return new Promise((resolve, reject) => {
            if (!supported.value) {
                const msg = 'Tu navegador no soporta dictado por voz. Usa el teclado.';
                error.value = msg;
                reject(new Error(msg));
                return;
            }
            if (listening.value) {
                resolve(transcript.value);
                return;
            }

            error.value = null;
            transcript.value = '';

            try {
                _recognition = new SR();
                _recognition.lang = lang;
                _recognition.continuous = opts.continuous ?? false;
                _recognition.interimResults = opts.interim ?? true;
                _recognition.maxAlternatives = 1;

                let finalText = '';

                _recognition.onresult = (e) => {
                    let interim = '';
                    for (let i = e.resultIndex; i < e.results.length; i++) {
                        const t = e.results[i][0].transcript;
                        if (e.results[i].isFinal) finalText += t + ' ';
                        else interim += t;
                    }
                    transcript.value = (finalText + interim).trim();
                };

                _recognition.onerror = (e) => {
                    listening.value = false;
                    const msg = e.error === 'not-allowed'
                        ? 'Permiso de micrófono denegado. Actívalo en configuración del navegador.'
                        : e.error === 'no-speech'
                            ? 'No se detectó voz. Intenta de nuevo.'
                            : `Error: ${e.error}`;
                    error.value = msg;
                    reject(new Error(msg));
                };

                _recognition.onend = () => {
                    listening.value = false;
                    resolve(transcript.value);
                };

                _recognition.start();
                listening.value = true;
            } catch (e) {
                listening.value = false;
                error.value = e.message || 'Error iniciando dictado';
                reject(e);
            }
        });
    }

    function stop() {
        try { _recognition?.stop(); } catch (_) {}
    }

    function cancel() {
        try { _recognition?.abort(); } catch (_) {}
        _recognition = null;
        listening.value = false;
        transcript.value = '';
        error.value = null;
    }

    function clearError() { error.value = null; }

    return { supported, listening, error, transcript, start, stop, cancel, clearError };
}
