import { ref, reactive, onBeforeUnmount } from 'vue';
import { useApi } from './useApi';

/**
 * usePhotoUpload — encapsulates dropzone selection, HEIC→JPEG conversion,
 * size validation, preview management and the multipart upload loop
 * (one POST per angle, matching the legacy backend contract).
 *
 * **CRÍTICO**: HEIC dynamic import preserved EXACTLY as in legacy:
 *   const mod = await import('heic2any');
 * This keeps the ~140KB lib in a separate chunk so the initial bundle
 * stays slim for users that never upload from iPhone.
 *
 * Returns:
 *   {
 *     uploadFiles,    // reactive {frente, perfil, espalda}
 *     uploadPreviews, // reactive {frente, perfil, espalda} (object URLs)
 *     fieldErrors,    // reactive map of per-angle / per-field errors
 *     uploading,      // ref<bool>
 *     uploadError,    // ref<string|null> general error
 *     uploadedCount,  // computed-equivalent function
 *     hasUploads,     // computed-equivalent function
 *     onFileSelect(angle, event),
 *     setFile(angle, file),       // direct programmatic set (for camera capture)
 *     removeFile(angle),
 *     uploadAll(date),            // resolves true on success
 *     translateMessage(msg),
 *     fieldErrorFor(angle),
 *     reset(),
 *   }
 *
 * Each call returns a NEW instance (NOT singleton) — every upload context
 * needs its own state (e.g. dev page shows 3 zones, prod page another set).
 */

const ANGLES = ['frente', 'perfil', 'espalda'];
const MAX_FILE_MB = 12;
const HEIC_MIMES = ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'];

const VALIDATION_MESSAGES = {
  'validation.required': 'Selecciona una foto.',
  'validation.image': 'El archivo debe ser una imagen.',
  'validation.mimes': 'Formato de imagen no soportado.',
  'validation.max': 'La imagen es demasiado grande.',
  'validation.file': 'Archivo invalido.',
  'validation.date': 'Fecha invalida.',
};

export function translateMessage(msg) {
  if (!msg) return '';
  if (typeof msg !== 'string') return String(msg);
  if (VALIDATION_MESSAGES[msg]) return VALIDATION_MESSAGES[msg];
  if (msg.startsWith('validation.')) return 'Campo invalido.';
  return msg;
}

function isHeic(file) {
  const type = (file.type || '').toLowerCase();
  const name = (file.name || '').toLowerCase();
  return HEIC_MIMES.includes(type) || name.endsWith('.heic') || name.endsWith('.heif');
}

async function convertHeicToJpeg(file) {
  // Dynamic import: keeps heic2any (~140KB) out of initial bundle
  const mod = await import('heic2any');
  const heic2any = mod.default || mod;
  const blob = await heic2any({ blob: file, toType: 'image/jpeg', quality: 0.85 });
  const jpegBlob = Array.isArray(blob) ? blob[0] : blob;
  return new File([jpegBlob], file.name.replace(/\.(heic|heif)$/i, '.jpg'), { type: 'image/jpeg' });
}

export function usePhotoUpload() {
  const api = useApi();

  const uploadFiles = reactive({ frente: null, perfil: null, espalda: null });
  const uploadPreviews = reactive({ frente: null, perfil: null, espalda: null });
  const fieldErrors = reactive({});
  const uploading = ref(false);
  const uploadError = ref(null);

  function uploadedCount() {
    return ANGLES.filter((a) => uploadFiles[a] !== null).length;
  }
  function hasUploads() {
    return uploadedCount() > 0;
  }

  function fieldErrorFor(angle) {
    const raw = fieldErrors?.[angle];
    if (!raw) return '';
    // Allow either string (set inline) or array of strings (Laravel 422 shape)
    return translateMessage(Array.isArray(raw) ? raw[0] : raw);
  }

  function _revokePreview(angle) {
    if (uploadPreviews[angle]) {
      URL.revokeObjectURL(uploadPreviews[angle]);
      uploadPreviews[angle] = null;
    }
  }

  async function setFile(angle, rawFile) {
    if (!rawFile) return;
    fieldErrors[angle] = null;

    // Size check BEFORE processing — prevents opening 30MB HEIC into memory
    if (rawFile.size > MAX_FILE_MB * 1024 * 1024) {
      fieldErrors[angle] = `La foto pesa más de ${MAX_FILE_MB}MB. Reduce el tamaño.`;
      return;
    }

    let file = rawFile;
    if (isHeic(rawFile)) {
      try {
        fieldErrors[angle] = 'Convirtiendo formato HEIC...';
        file = await convertHeicToJpeg(rawFile);
        fieldErrors[angle] = null;
        if (file.size > MAX_FILE_MB * 1024 * 1024) {
          fieldErrors[angle] = `Tras convertir pesa más de ${MAX_FILE_MB}MB.`;
          return;
        }
      } catch (e) {
        // eslint-disable-next-line no-console
        console.error('HEIC conversion error:', e);
        fieldErrors[angle] = 'No se pudo convertir HEIC. Cambia formato a JPEG en Ajustes > Cámara > Formatos > Más compatible.';
        return;
      }
    }

    uploadFiles[angle] = file;
    _revokePreview(angle);
    uploadPreviews[angle] = URL.createObjectURL(file);
  }

  async function onFileSelect(angle, event) {
    const rawFile = event?.target?.files?.[0];
    await setFile(angle, rawFile);
    if (event?.target) event.target.value = '';
  }

  function removeFile(angle) {
    uploadFiles[angle] = null;
    fieldErrors[angle] = null;
    _revokePreview(angle);
  }

  function reset() {
    for (const a of ANGLES) removeFile(a);
    uploadError.value = null;
    Object.keys(fieldErrors).forEach((k) => { fieldErrors[k] = null; });
  }

  /**
   * uploadAll — POSTs each selected angle individually (legacy contract).
   * Returns true on full success, false otherwise.
   */
  async function uploadAll(date) {
    if (!hasUploads()) {
      uploadError.value = 'Selecciona al menos una foto para subir.';
      return false;
    }

    uploading.value = true;
    uploadError.value = null;
    Object.keys(fieldErrors).forEach((k) => { fieldErrors[k] = null; });

    const entries = ANGLES
      .map((a) => [a, uploadFiles[a]])
      .filter(([, f]) => f !== null);
    const failed = [];

    for (const [angle, file] of entries) {
      const formData = new FormData();
      formData.append('photo_date', date);
      formData.append('tipo', angle);
      formData.append('photo', file);

      try {
        await api.post('/api/v/client/photos', formData);
      } catch (err) {
        if (err.response?.status === 422) {
          const errs = err.response.data?.errors || {};
          // Per-field error mapping
          if (errs[angle]) fieldErrors[angle] = errs[angle];
          if (errs.photo) fieldErrors[angle] = errs.photo;
          if (errs.photo_date) fieldErrors.photo_date = errs.photo_date;
          const msgs = Object.values(errs).flat().map(translateMessage);
          failed.push(`${angle}: ${msgs.join(', ')}`);
        } else {
          failed.push(`${angle}: ${err.response?.data?.message || 'Error al subir'}`);
        }
      }
    }

    uploading.value = false;

    if (failed.length) {
      uploadError.value = failed.join(' | ');
      return false;
    }

    reset();
    return true;
  }

  onBeforeUnmount(() => {
    for (const a of ANGLES) _revokePreview(a);
  });

  return {
    uploadFiles,
    uploadPreviews,
    fieldErrors,
    uploading,
    uploadError,
    uploadedCount,
    hasUploads,
    onFileSelect,
    setFile,
    removeFile,
    uploadAll,
    translateMessage,
    fieldErrorFor,
    reset,
  };
}
