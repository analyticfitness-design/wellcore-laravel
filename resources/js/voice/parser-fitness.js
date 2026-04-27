// resources/js/voice/parser-fitness.js
const NUMBER_WORDS = {
    'cero':0, 'uno':1, 'una':1, 'dos':2, 'tres':3, 'cuatro':4, 'cinco':5,
    'seis':6, 'siete':7, 'ocho':8, 'nueve':9, 'diez':10, 'once':11,
    'doce':12, 'trece':13, 'catorce':14, 'quince':15, 'dieciseis':16,
    'diecisiete':17, 'dieciocho':18, 'diecinueve':19, 'veinte':20,
    'veintiuno':21, 'veintidos':22, 'veintitres':23, 'veinticuatro':24,
    'veinticinco':25, 'veintiseis':26, 'veintisiete':27, 'veintiocho':28,
    'veintinueve':29, 'treinta':30, 'cuarenta':40, 'cincuenta':50,
    'sesenta':60, 'setenta':70, 'ochenta':80, 'noventa':90,
    'cien':100, 'ciento':100, 'doscientos':200, 'trescientos':300,
    'cuatrocientos':400, 'quinientos':500,
};

const COMPOUND_TENS = ['treinta','cuarenta','cincuenta','sesenta','setenta','ochenta','noventa'];

function parseSpanishNumber(words) {
    let total = 0;
    for (let i = 0; i < words.length; i++) {
        const w = words[i].toLowerCase();
        if (COMPOUND_TENS.includes(w) && words[i + 1] === 'y' && NUMBER_WORDS[words[i + 2]] !== undefined) {
            total += NUMBER_WORDS[w] + NUMBER_WORDS[words[i + 2]];
            i += 2;
        } else if (NUMBER_WORDS[w] !== undefined) {
            total += NUMBER_WORDS[w];
        }
    }
    return total || null;
}

function extractNumberNear(text, keywordPattern) {
    const re = new RegExp(
        `(\\d+(?:\\.\\d+)?|[a-záéíóúü]+(?:\\s+y\\s+[a-záéíóúü]+)?)\\s+(?:${keywordPattern})|(?:${keywordPattern})\\s+(\\d+(?:\\.\\d+)?|[a-záéíóúü]+(?:\\s+y\\s+[a-záéíóúü]+)?)`,
        'i'
    );
    const m = text.match(re);
    if (!m) return null;
    const raw = (m[1] || m[2] || '').trim().toLowerCase();
    if (!raw) return null;
    if (/^\d+(\.\d+)?$/.test(raw)) return parseFloat(raw);
    return parseSpanishNumber(raw.split(/\s+/));
}

export function extractWorkoutIntent(transcript) {
    const text = transcript
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/[,!?]/g, ' ')
        .replace(/(?<!\d)\.(?!\d)/g, ' ')  // reemplaza puntos que NO son decimales
        .replace(/\s+/g, ' ')
        .trim();

    if (/cancela|cancelar|sal\b|abandona/.test(text)) {
        return { intent: 'cancel' };
    }

    // termin* cubre "termine", "terminé", "terminar", "terminado"
    if (/termin[a-z]*|listo|completad[ao]|hecho|fin[aio]?$/.test(text) && !/\d/.test(text) && !/kilo|libra|rep/.test(text)) {
        return { intent: 'complete_only' };
    }

    const reps   = extractNumberNear(text, 'reps?|repeticiones?');
    const weight = extractNumberNear(text, 'kilos?|kg|libras?|lbs?');
    const unit   = /libras?|lbs?/.test(text) ? 'lbs' : 'kg';

    if (reps !== null && weight !== null) {
        return { intent: 'log_set', reps, weight, unit, confidence: 0.85 };
    }

    // Heurística: 2 números sin keyword → primero=reps, segundo=weight
    const numbers = [...text.matchAll(/(\d+(?:\.\d+)?)/g)].map(m => parseFloat(m[1]));
    if (numbers.length === 2) {
        return { intent: 'log_set', reps: numbers[0], weight: numbers[1], unit, confidence: 0.6 };
    }

    if (reps !== null || weight !== null) {
        return { intent: 'log_set', reps, weight, unit, confidence: 0.5 };
    }

    return { intent: 'unknown', transcript };
}
