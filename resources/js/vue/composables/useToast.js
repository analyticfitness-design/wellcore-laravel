import { reactive } from 'vue';

/**
 * Sistema global de notificaciones (toasts) para WellCore.
 *
 * Uso:
 *   import { useToast } from '../composables/useToast';
 *   const toast = useToast();
 *   toast.success('Guardado.');
 *   toast.error('Algo falló.');
 *   toast.info('Sin conexión.');
 *   toast.warn('Advertencia.');
 *
 *   // Helper especial para errores de API
 *   try { ... } catch (err) {
 *     toast.apiError(err, 'No pudimos guardar.');
 *   }
 */

// Estado reactivo global (singleton) — array de toasts activos.
const state = reactive({
  toasts: [],
});

const MAX_TOASTS = 5;
const DEFAULT_DURATION = 4000;
const ERROR_DURATION = 6000;
const DEDUP_WINDOW_MS = 1500;

let nextId = 1;

function removeToast(id) {
  const idx = state.toasts.findIndex((t) => t.id === id);
  if (idx !== -1) state.toasts.splice(idx, 1);
}

function push(type, message, opts = {}) {
  // Deduplicación: ignorar si ya existe un toast idéntico (mismo type+message)
  // creado en los últimos DEDUP_WINDOW_MS milisegundos.
  const now = Date.now();
  const duplicate = state.toasts.find(
    (t) => t.type === type && t.message === message && now - t.createdAt < DEDUP_WINDOW_MS
  );
  if (duplicate) return duplicate.id;

  const id = nextId++;
  const duration =
    typeof opts.duration === 'number'
      ? opts.duration
      : type === 'error'
      ? ERROR_DURATION
      : DEFAULT_DURATION;

  const toast = {
    id,
    type,
    title: opts.title || null,
    message,
    duration,
    createdAt: now,
  };

  state.toasts.push(toast);

  // Limitar a MAX_TOASTS visibles — descarta los más antiguos.
  while (state.toasts.length > MAX_TOASTS) {
    state.toasts.shift();
  }

  if (duration > 0) {
    setTimeout(() => removeToast(id), duration);
  }

  return id;
}

function success(message, opts) {
  return push('success', message, opts);
}

function error(message, opts) {
  return push('error', message, opts);
}

function info(message, opts) {
  return push('info', message, opts);
}

function warn(message, opts) {
  return push('warning', message, opts);
}

/**
 * Maneja errores de axios / API de forma estándar.
 * 1. 422 con errors → muestra primer error de cada campo (concatenados).
 * 2. response.data.message → lo muestra.
 * 3. Sin response (network error) → "Sin conexión. Revisa tu internet."
 * 4. Fallback al texto provisto.
 */
function apiError(err, fallback = 'Algo salió mal. Intenta de nuevo.') {
  // Network error (sin response del servidor).
  if (err && !err.response) {
    return error('Sin conexión. Revisa tu internet.');
  }

  const status = err?.response?.status;
  const data = err?.response?.data;

  // 422 — errores de validación de Laravel.
  if (status === 422 && data?.errors && typeof data.errors === 'object') {
    const messages = [];
    for (const field in data.errors) {
      const list = data.errors[field];
      if (Array.isArray(list) && list.length > 0) {
        messages.push(list[0]);
      }
    }
    if (messages.length > 0) {
      return error(messages.join(' · '));
    }
  }

  // Mensaje genérico del servidor.
  if (data?.message && typeof data.message === 'string') {
    return error(data.message);
  }

  // Fallback.
  return error(fallback);
}

function dismiss(id) {
  removeToast(id);
}

function clear() {
  state.toasts.splice(0, state.toasts.length);
}

export function useToast() {
  return {
    toasts: state.toasts,
    success,
    error,
    info,
    warn,
    apiError,
    dismiss,
    clear,
  };
}

// Exponer estado reactivo directamente para el ToastContainer.
export function useToastState() {
  return state;
}
