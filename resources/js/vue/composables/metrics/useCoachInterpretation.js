import { ref } from 'vue';
import { useApi } from '../useApi';

export function useCoachInterpretation() {
  const api = useApi();
  const interpretation = ref(null);
  const loading = ref(false);

  async function fetchInterpretation() {
    loading.value = true;
    try {
      const res = await api.get('/api/v/client/checkins', { params: { type: 'interpretation' } });
      interpretation.value = res.data.interpretation || null;
    } catch {
      interpretation.value = null;
    } finally {
      loading.value = false;
    }
  }

  return { interpretation, loading, fetchInterpretation };
}
