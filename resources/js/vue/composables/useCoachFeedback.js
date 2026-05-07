import { ref, reactive } from 'vue';
import { useApi } from './useApi';

/**
 * useCoachFeedback — fetches coach notes attached to a specific photo.
 * Endpoint exists in Fase 0: GET /api/v/client/photos/:id/notes
 *
 * Cache shape: notesByPhotoId[id] = [{ id, body, marker, x, y, created_at, read_at }]
 * Module-level singleton so opening the panel for the same photo twice
 * doesn't refetch unless explicitly invalidated.
 *
 *   notesFor(photoId)        // returns ref-like array (reactive)
 *   loadingFor(photoId)      // ref<bool>
 *   fetchNotes(photoId)      // populates cache
 *   markRead(photoId)        // POST .../notes/read (best-effort)
 *   reply(photoId, text)     // POST .../notes/reply (best-effort)
 *   invalidate(photoId)      // drop cache entry
 */

const _notesByPhotoId = reactive({});
const _loadingByPhotoId = reactive({});
const _errorByPhotoId = reactive({});
let _api = null;

function _api_() {
  if (!_api) _api = useApi();
  return _api;
}

async function fetchNotes(photoId) {
  if (!photoId) return [];
  _loadingByPhotoId[photoId] = true;
  _errorByPhotoId[photoId] = null;
  try {
    const { data } = await _api_().get(`/api/v/client/photos/${photoId}/notes`);
    // Accept either { notes: [...] } or bare array
    const list = Array.isArray(data) ? data : (data?.notes || []);
    _notesByPhotoId[photoId] = list;
    return list;
  } catch (err) {
    _errorByPhotoId[photoId] = err.response?.data?.message || 'No se pudieron cargar las notas';
    _notesByPhotoId[photoId] = [];
    return [];
  } finally {
    _loadingByPhotoId[photoId] = false;
  }
}

async function markRead(photoId) {
  // El endpoint GET /photos/{id}/notes ya marca las notas como leídas
  // automáticamente en el backend (idempotente). Esta función queda como
  // no-op para mantener API pública estable; actualizamos read_at local
  // por si la UI necesita reflejar el cambio sin re-fetch.
  if (!photoId) return;
  const list = _notesByPhotoId[photoId] || [];
  const now = new Date().toISOString();
  list.forEach((n) => { if (!n.read_at) n.read_at = now; });
}

async function reply(photoId, text) {
  if (!photoId || !text?.trim()) return false;
  try {
    await _api_().post(`/api/v/client/photos/${photoId}/notes/reply`, { body: text.trim() });
    return true;
  } catch (err) {
    _errorByPhotoId[photoId] = err.response?.data?.message || 'No pudimos enviar tu respuesta';
    return false;
  }
}

function invalidate(photoId) {
  if (photoId) {
    delete _notesByPhotoId[photoId];
    delete _loadingByPhotoId[photoId];
    delete _errorByPhotoId[photoId];
  } else {
    Object.keys(_notesByPhotoId).forEach((k) => delete _notesByPhotoId[k]);
  }
}

export function useCoachFeedback() {
  return {
    notesByPhotoId: _notesByPhotoId,
    loadingByPhotoId: _loadingByPhotoId,
    errorByPhotoId: _errorByPhotoId,
    notesFor: (id) => _notesByPhotoId[id] || [],
    loadingFor: (id) => !!_loadingByPhotoId[id],
    errorFor: (id) => _errorByPhotoId[id] || null,
    fetchNotes,
    markRead,
    reply,
    invalidate,
  };
}
