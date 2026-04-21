import confetti from 'canvas-confetti';
import { useReducedMotion } from './useReducedMotion';

const WC_COLORS = ['#DC2626', '#f87171', '#fbbf24', '#10b981', '#3b82f6', '#8b5cf6', '#ffffff'];

function wcHexShape() {
    return confetti.shapeFromPath({
        path: 'M 0,-5 L 4.33,-2.5 L 4.33,2.5 L 0,5 L -4.33,2.5 L -4.33,-2.5 Z',
    });
}

function wcStarShape() {
    return confetti.shapeFromPath({
        path: 'M 0,-6 L 1.4,-1.9 L 5.7,-1.9 L 2.3,0.7 L 3.5,4.9 L 0,2.4 L -3.5,4.9 L -2.3,0.7 L -5.7,-1.9 L -1.4,-1.9 Z',
    });
}

function fireBurst() {
    confetti({ particleCount: 80, spread: 70, origin: { y: 0.5 }, colors: WC_COLORS, gravity: 0.9, scalar: 0.9, ticks: 200 });
}

function fireRain() {
    const end = Date.now() + 1500;
    (function frame() {
        confetti({ particleCount: 4, startVelocity: 20, ticks: 200, origin: { x: Math.random(), y: 0 }, colors: WC_COLORS, shapes: ['circle', 'square'], gravity: 0.6, scalar: 0.8 });
        if (Date.now() < end) requestAnimationFrame(frame);
    })();
}

function fireCannon() {
    confetti({ particleCount: 50, angle: 60, spread: 55, origin: { x: 0, y: 0.7 }, colors: WC_COLORS });
    confetti({ particleCount: 50, angle: 120, spread: 55, origin: { x: 1, y: 0.7 }, colors: WC_COLORS });
}

function fireBranded() {
    const hex = wcHexShape();
    const star = wcStarShape();
    confetti({ particleCount: 50, spread: 100, origin: { y: 0.4 }, colors: WC_COLORS, shapes: [hex, star, 'circle'], scalar: 1.2, gravity: 0.7, ticks: 250 });
    setTimeout(() => confetti({ particleCount: 30, spread: 120, origin: { y: 0.5 }, colors: WC_COLORS, shapes: [hex, star], scalar: 0.9 }), 250);
}

const PRESETS = { burst: fireBurst, rain: fireRain, cannon: fireCannon, 'wc-branded': fireBranded };

export function useConfetti() {
    const reducedMotion = useReducedMotion();

    function fire(preset = 'burst') {
        if (reducedMotion.value) return;
        const fn = PRESETS[preset] || fireBurst;
        fn();
    }

    function clear() { confetti.reset(); }

    return { fire, clear, presets: Object.keys(PRESETS) };
}
