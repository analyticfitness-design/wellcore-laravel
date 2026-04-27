import { describe, test, expect } from 'vitest';
import { extractWorkoutIntent } from '../../resources/js/voice/parser-fitness.js';

describe('extractWorkoutIntent — números arábigos', () => {
    test('reps antes, peso después', () => {
        expect(extractWorkoutIntent('15 reps con 50 kilos')).toMatchObject({
            intent: 'log_set', reps: 15, weight: 50, unit: 'kg',
        });
    });

    test('peso antes, reps después', () => {
        expect(extractWorkoutIntent('50 kilos 15 reps')).toMatchObject({
            intent: 'log_set', reps: 15, weight: 50, unit: 'kg',
        });
    });

    test('libras se detectan correctamente', () => {
        const r = extractWorkoutIntent('diez reps con cien libras');
        expect(r.unit).toBe('lbs');
        expect(r.weight).toBe(100);
    });

    test('solo 2 números → heurística reps+weight', () => {
        const r = extractWorkoutIntent('hice 12 con 80');
        expect(r.intent).toBe('log_set');
        expect(r.reps).toBe(12);
        expect(r.weight).toBe(80);
        expect(r.confidence).toBeLessThan(0.8);
    });

    test('decimales en peso', () => {
        const r = extractWorkoutIntent('ocho reps con 22.5 kilos');
        expect(r.weight).toBe(22.5);
    });
});

describe('extractWorkoutIntent — palabras en español', () => {
    test('quince reps cincuenta kilos', () => {
        const r = extractWorkoutIntent('quince reps cincuenta kilos');
        expect(r.reps).toBe(15);
        expect(r.weight).toBe(50);
    });

    test('compuesto: treinta y cinco reps', () => {
        const r = extractWorkoutIntent('treinta y cinco reps con cuarenta kilos');
        expect(r.reps).toBe(35);
        expect(r.weight).toBe(40);
    });

    test('compuesto: ochenta kilos', () => {
        const r = extractWorkoutIntent('diez reps con ochenta kilos');
        expect(r.weight).toBe(80);
    });
});

describe('extractWorkoutIntent — intents especiales', () => {
    test('termine → complete_only', () => {
        expect(extractWorkoutIntent('termine la serie').intent).toBe('complete_only');
    });

    test('listo → complete_only', () => {
        expect(extractWorkoutIntent('listo').intent).toBe('complete_only');
    });

    test('cancelar → cancel', () => {
        expect(extractWorkoutIntent('cancelar').intent).toBe('cancel');
    });

    test('texto sin sentido → unknown', () => {
        expect(extractWorkoutIntent('agua por favor').intent).toBe('unknown');
    });

    test('listo con números NO es complete_only', () => {
        const r = extractWorkoutIntent('listo 10 reps 60 kilos');
        expect(r.intent).toBe('log_set');
    });
});

describe('extractWorkoutIntent — variaciones STT reales', () => {
    test('sin keyword "reps": "aguante plancha cuarenta segundos"', () => {
        // Sin keyword reps ni kilos, solo un número → unknown o reps sin weight
        const r = extractWorkoutIntent('aguante plancha cuarenta segundos');
        expect(['unknown', 'log_set']).toContain(r.intent);
    });

    test('texto con acento STT: "hice quince reps con cincuenta kilos"', () => {
        const r = extractWorkoutIntent('hice quince reps con cincuenta kilos');
        expect(r.reps).toBe(15);
        expect(r.weight).toBe(50);
    });
});
