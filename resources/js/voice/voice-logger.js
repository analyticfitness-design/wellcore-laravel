// resources/js/voice/voice-logger.js
import { VoiceCaps }           from './voice-capabilities.js';
import { WebSpeechEngine }     from './web-speech-engine.js';
import { extractWorkoutIntent } from './parser-fitness.js';

window.voiceLogger = function ({ exIndex, setNum }) {
    return {
        exIndex,
        setNum,
        engine:       VoiceCaps.pickEngine(),
        listening:    false,
        isProcessing: false,   // guard: evita doble-dispatch a Livewire
        confirmation: null,    // { weight, reps, unit }
        error:        null,
        _sr:          null,    // instancia activa del engine

        async listen() {
            if (this.listening || this.isProcessing) return;
            this.error        = null;
            this.confirmation = null;

            if (!this.engine) {
                this.error = 'Tu navegador no soporta voz. Usa el teclado.';
                return;
            }

            try {
                this.listening = true;
                this._sr = new WebSpeechEngine({ lang: 'es-CO' });
                const alts = await this._sr.listen();
                this._sr = null;

                // Probar las 3 alternativas, quedarse con el primer match sólido
                for (const t of alts) {
                    const intent = extractWorkoutIntent(t);

                    if (intent.intent === 'log_set' && intent.reps && intent.weight) {
                        this.confirmation = intent;
                        return;
                    }

                    if (intent.intent === 'complete_only') {
                        this.$dispatch('voice-complete-only', { exIndex: this.exIndex, setNum: this.setNum });
                        return;
                    }
                }

                this.error = 'No te entendi. Prueba: "quince reps con cincuenta kilos"';
            } catch (e) {
                const msg = e?.message ?? '';
                this.error = msg === 'not-allowed'
                    ? 'Permiso de microfono denegado. Activaló en configuración del navegador.'
                    : 'Error con el reconocimiento. Vuelve a intentarlo.';
            } finally {
                this.listening = false;
            }
        },

        stopListening() {
            this._sr?.stop();
            this._sr      = null;
            this.listening = false;
        },

        async confirm() {
            if (!this.confirmation || this.isProcessing) return;
            const { weight, reps, unit } = this.confirmation;
            const saveWeight = unit === 'lbs' ? +(weight / 2.205).toFixed(2) : weight;

            this.isProcessing = true;
            try {
                await this.$wire.completeSet(this.exIndex, this.setNum, saveWeight, reps);
                this.$dispatch('voice-confirmed', { weight: saveWeight, reps, exIndex: this.exIndex, setNum: this.setNum });
                this.confirmation = null;
            } catch (_) {
                this.error = 'Error al guardar. Intenta de nuevo.';
            } finally {
                this.isProcessing = false;
            }
        },

        cancel() {
            this.confirmation = null;
            this.error        = null;
        },
    };
};
