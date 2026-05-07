import { ref, computed } from 'vue';
import { useApi } from '../useApi';
import { useCancellableFetch } from '../useCancellableFetch';

export function useMetrics() {
  const api = useApi();
  const { getSignal } = useCancellableFetch();

  const loading = ref(true);
  const error = ref(null);

  const history = ref([]);
  const weightTrend = ref([]);
  const weeklyCheckins = ref([]);
  const latestComposition = ref(null);
  const trainingVolume = ref([]);
  const photos = ref([]);

  // Computed aliases for v2 API
  const entries = computed(() => weightTrend.value.length ? weightTrend.value : history.value);
  const latestEntry = computed(() => history.value[0] || null);
  const weeklyVolume = computed(() => trainingVolume.value);

  // Computed helpers
  const hasData = computed(() => history.value.length > 0);
  const recordsCount = computed(() => history.value.length);
  const hasWeight = computed(() => weightTrend.value.length > 0);
  const hasCheckins = computed(() => weeklyCheckins.value.length > 0);
  const hasComposition = computed(() => latestComposition.value !== null);
  const hasTraining = computed(() => trainingVolume.value.length > 0);

  const daysSinceLast = computed(() => {
    if (!history.value.length) return null;
    const last = history.value[0]?.log_date || history.value[0]?.date || history.value[0]?.fecha;
    if (!last) return null;
    return Math.floor((Date.now() - new Date(last + 'T00:00:00').getTime()) / 86400000);
  });

  // Streak: consecutive weeks with at least 1 checkin
  const streak = computed(() => {
    if (!weeklyCheckins.value.length) return 0;
    let s = 0;
    for (const week of weeklyCheckins.value) {
      if (week.status === 'full' || week.status === 'partial') s++;
      else break;
    }
    return s;
  });

  async function refresh() {
    loading.value = true;
    error.value = null;
    const signal = getSignal();
    try {
      const response = await api.get('/api/v/client/metrics', { signal });
      const d = response.data;
      history.value = d.history || [];
      weightTrend.value = d.weightTrend || d.chartData || [];
      weeklyCheckins.value = d.weeklyCheckins || [];
      latestComposition.value = d.latestComposition || null;
      trainingVolume.value = d.trainingVolume || [];
      photos.value = d.photos || [];
    } catch (err) {
      if (err.name !== 'CanceledError' && err.name !== 'AbortError') {
        error.value = err.response?.data?.message || 'Error al cargar métricas';
      }
    } finally {
      loading.value = false;
    }
  }

  return {
    loading, error,
    // v2 API
    entries, latestEntry, weeklyVolume, photos, streak,
    // raw data
    history, weightTrend, weeklyCheckins, latestComposition, trainingVolume,
    // computed helpers
    hasData, recordsCount, hasWeight, hasCheckins, hasComposition, hasTraining,
    daysSinceLast,
    // methods
    refresh,
    // legacy alias
    fetchMetrics: refresh,
  };
}
