import { ref, computed } from 'vue';
import { useApi } from './useApi';

/**
 * useProgressPhotos — fetches /api/v/client/photos and groups raw photos
 * (keyed by 'YYYY-MM-DD' from the legacy endpoint) into normalized session
 * objects ordered newest-first.
 *
 * Backend response shape (legacy, preserved):
 *   {
 *     photos: { 'YYYY-MM-DD': [ { id, photo_date, tipo, filename, url } ] },
 *     genero: 'mujer' | 'hombre'
 *   }
 *
 * Returns:
 *   {
 *     photos,            // raw map keyed by date
 *     sessions,          // [{ date, photos: { frente, perfil, espalda }, hasAll }]
 *     sortedDates,       // [ 'YYYY-MM-DD' ] desc
 *     latestSession,     // sessions[0] or null
 *     weekCount,         // sessions count in last 7 days
 *     genero,            // 'mujer' | 'hombre'
 *     loading, error,
 *     refetch()
 *   }
 *
 * Module-level singleton state so multiple components stay in sync after
 * an upload/delete without re-fetching from each consumer.
 */

const ANGLES = ['frente', 'perfil', 'espalda'];

const _photos = ref({});
const _genero = ref('hombre');
const _loading = ref(false);
const _error = ref(null);
let _api = null;

function _byAngle(list, angle) {
  return list.find((p) => p.tipo === angle) || null;
}

const sortedDates = computed(() =>
  Object.keys(_photos.value).sort((a, b) => b.localeCompare(a))
);

const sessions = computed(() =>
  sortedDates.value.map((date) => {
    const list = _photos.value[date] || [];
    const map = {
      frente: _byAngle(list, 'frente'),
      perfil: _byAngle(list, 'perfil'),
      espalda: _byAngle(list, 'espalda'),
    };
    return {
      date,
      photos: map,
      hasAll: ANGLES.every((a) => map[a] !== null),
    };
  })
);

const latestSession = computed(() => sessions.value[0] || null);

const weekCount = computed(() => {
  const now = Date.now();
  const SEVEN_DAYS = 7 * 24 * 60 * 60 * 1000;
  return sortedDates.value.filter((d) => {
    const ts = new Date(d + 'T12:00:00').getTime();
    return !isNaN(ts) && now - ts <= SEVEN_DAYS;
  }).length;
});

async function refetch() {
  if (!_api) _api = useApi();
  _loading.value = true;
  _error.value = null;
  try {
    const response = await _api.get('/api/v/client/photos');
    _photos.value = response.data?.photos || {};
    _genero.value = response.data?.genero ?? 'hombre';
  } catch (err) {
    _error.value = err.response?.data?.message || 'Error al cargar fotos';
  } finally {
    _loading.value = false;
  }
}

export function useProgressPhotos() {
  return {
    photos: _photos,
    sessions,
    sortedDates,
    latestSession,
    weekCount,
    genero: _genero,
    loading: _loading,
    error: _error,
    refetch,
  };
}
