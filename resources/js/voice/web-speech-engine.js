// resources/js/voice/web-speech-engine.js
export class WebSpeechEngine {
    constructor({ lang = 'es-CO' } = {}) {
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) throw new Error('SpeechRecognition not available');
        this.recognition = new SR();
        this.recognition.lang = lang;
        this.recognition.continuous = false;
        this.recognition.interimResults = false;
        this.recognition.maxAlternatives = 3;
    }

    listen() {
        return new Promise((resolve, reject) => {
            let settled = false;

            this.recognition.onresult = (e) => {
                settled = true;
                const alts = Array.from(e.results[0]).map(a => a.transcript);
                resolve(alts);
            };

            this.recognition.onerror = (e) => {
                if (!settled) {
                    settled = true;
                    reject(new Error(e.error));
                }
            };

            this.recognition.start();
        });
    }

    stop() {
        try { this.recognition.stop(); } catch (_) {}
    }
}
