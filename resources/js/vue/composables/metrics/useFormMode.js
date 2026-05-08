import { ref, watch } from 'vue';

const STORAGE_KEY = 'wc_metrics_form_mode';

// Singleton — un solo estado para toda la sesión
const stored = localStorage.getItem(STORAGE_KEY);
const mode = ref(stored === 'full' ? 'full' : 'quick');

watch(mode, (val) => {
  localStorage.setItem(STORAGE_KEY, val);
});

export function useFormMode() {
  function setQuick() { mode.value = 'quick'; }
  function setFull() { mode.value = 'full'; }
  function toggle() { mode.value = mode.value === 'quick' ? 'full' : 'quick'; }

  return { mode, setQuick, setFull, toggle };
}
