/**
 * usePhotoValidation — heuristic client-side checks that produce display
 * chips for the upload preview ("✓ Luz", "⚠ Luz baja", "✓ Encuadre").
 *
 * Phase 1 scope: lighting only (avg luma via canvas).
 * `framing` is hard-coded to 'good' until v2.1 (where we'll add aspect/edge
 * detection). Hard-coding keeps the chip slot present visually so layouts
 * don't shift later.
 *
 *   computeChips(file) → Promise<{ lighting: 'good'|'low', framing: 'good' }>
 *
 * Stateless composable — safe to call from anywhere, no setup required.
 */

const LUMA_LOW_THRESHOLD = 60; // 0..255 — below this we flag "Luz baja"

function _loadImage(file) {
  return new Promise((resolve, reject) => {
    const url = URL.createObjectURL(file);
    const img = new Image();
    img.onload = () => {
      resolve({ img, url });
    };
    img.onerror = () => {
      URL.revokeObjectURL(url);
      reject(new Error('image load failed'));
    };
    img.src = url;
  });
}

function _avgLuma(img) {
  // Downsample to a small canvas — enough signal for a brightness heuristic
  // and cheap (≈1ms). Larger sizes don't change the result meaningfully.
  const W = 64;
  const H = Math.max(1, Math.round((img.naturalHeight / img.naturalWidth) * W));
  const canvas = document.createElement('canvas');
  canvas.width = W;
  canvas.height = H;
  const ctx = canvas.getContext('2d', { willReadFrequently: true });
  if (!ctx) return 128;
  ctx.drawImage(img, 0, 0, W, H);
  const { data } = ctx.getImageData(0, 0, W, H);
  let sum = 0;
  let count = 0;
  // ITU-R BT.601 luma approximation
  for (let i = 0; i < data.length; i += 4) {
    sum += 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
    count++;
  }
  return count ? sum / count : 128;
}

export async function computeChips(file) {
  const fallback = { lighting: 'good', framing: 'good' };
  if (!file) return fallback;
  if (typeof window === 'undefined' || typeof Image === 'undefined') return fallback;
  try {
    const { img, url } = await _loadImage(file);
    let luma = 128;
    try {
      luma = _avgLuma(img);
    } catch {
      // Tainted canvas (rare for local blob URLs, but defensive) — treat as good
    } finally {
      URL.revokeObjectURL(url);
    }
    return {
      lighting: luma < LUMA_LOW_THRESHOLD ? 'low' : 'good',
      framing: 'good',
    };
  } catch {
    return fallback;
  }
}

export function usePhotoValidation() {
  return { computeChips };
}
