import { ref, computed } from 'vue';
import { useProgressPhotos } from './useProgressPhotos';

/**
 * usePhotoComparison — selects two session dates (A/B) for side-by-side
 * comparison and exposes the resolved sessions plus a swap helper.
 *
 * Module-level singleton: state persists when user toggles between gallery
 * and compare views without losing selection.
 *
 * Returns:
 *   {
 *     fromDate, toDate,        // refs of 'YYYY-MM-DD' strings
 *     sessionA, sessionB,      // computed session objects (or null)
 *     availableDates,          // computed sorted desc
 *     ready,                   // both selected and resolvable
 *     swap(),                  // swap A <-> B
 *     setFromDate(d), setToDate(d),
 *     reset(),
 *   }
 */

const _fromDate = ref('');
const _toDate = ref('');

export function usePhotoComparison() {
  const store = useProgressPhotos();

  const sessionByDate = (date) => {
    if (!date) return null;
    return store.sessions.value.find((s) => s.date === date) || null;
  };

  const sessionA = computed(() => sessionByDate(_fromDate.value));
  const sessionB = computed(() => sessionByDate(_toDate.value));
  const ready = computed(() => sessionA.value !== null && sessionB.value !== null);

  function swap() {
    const tmp = _fromDate.value;
    _fromDate.value = _toDate.value;
    _toDate.value = tmp;
  }

  function setFromDate(d) { _fromDate.value = d || ''; }
  function setToDate(d)   { _toDate.value   = d || ''; }

  function reset() {
    _fromDate.value = '';
    _toDate.value = '';
  }

  return {
    fromDate: _fromDate,
    toDate: _toDate,
    sessionA,
    sessionB,
    availableDates: store.sortedDates,
    ready,
    swap,
    setFromDate,
    setToDate,
    reset,
  };
}
