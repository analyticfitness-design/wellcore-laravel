// resources/js/voice/voice-capabilities.js
export const VoiceCaps = {
    hasWebSpeech: typeof window.SpeechRecognition !== 'undefined'
                  || typeof window.webkitSpeechRecognition !== 'undefined',
    hasWebGPU: typeof navigator !== 'undefined' && typeof navigator.gpu !== 'undefined',
    isIOS: /iP(hone|od|ad)/.test(navigator.userAgent),
    isAndroid: /Android/.test(navigator.userAgent),

    // Fase 1: solo Web Speech. Whisper WebGPU se activa en Fase 4 cuando
    // haya data de telemetría que justifique la descarga de 80MB.
    pickEngine() {
        if (this.hasWebSpeech) return 'webspeech';
        return null;
    },
};
