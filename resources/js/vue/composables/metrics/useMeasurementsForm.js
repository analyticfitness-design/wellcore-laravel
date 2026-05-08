import { ref, reactive } from 'vue';
import { useApi } from '../useApi';

const DRAFT_KEY = 'wc_metrics_draft';

export function useMeasurementsForm() {
  const api = useApi();
  const saving = ref(false);
  const saveError = ref(null);
  const formErrors = ref({});
  const lastPeso = ref('');

  const form = reactive({
    peso: '',
    porcentajeMusculo: '',
    porcentajeGrasa: '',
    notas: '',
    chest: '',
    waist: '',
    hip: '',
    thigh: '',
    arm: '',
  });

  // Dirty tracking for draft save (full mode only)
  const hasDraft = ref(false);

  function saveDraft() {
    if (!form.peso && !form.porcentajeMusculo && !form.porcentajeGrasa) return;
    localStorage.setItem(DRAFT_KEY, JSON.stringify({ ...form }));
    hasDraft.value = true;
  }

  function loadDraft() {
    try {
      const raw = localStorage.getItem(DRAFT_KEY);
      if (!raw) return;
      const d = JSON.parse(raw);
      Object.assign(form, d);
      hasDraft.value = true;
    } catch {}
  }

  function clearDraft() {
    localStorage.removeItem(DRAFT_KEY);
    hasDraft.value = false;
  }

  function updateField(key, value) {
    if (key in form) form[key] = value;
  }

  function resetForm() {
    Object.assign(form, {
      peso: '', porcentajeMusculo: '', porcentajeGrasa: '',
      notas: '', chest: '', waist: '', hip: '', thigh: '', arm: '',
    });
    formErrors.value = {};
    saveError.value = null;
    clearDraft();
  }

  function validate() {
    formErrors.value = {};
    if (!form.peso) {
      formErrors.value = { peso: ['El peso es obligatorio.'] };
      return false;
    }
    return true;
  }

  async function submit() {
    if (saving.value) return false;
    saveError.value = null;
    formErrors.value = {};
    if (!validate()) return false;

    saving.value = true;
    lastPeso.value = form.peso;

    try {
      await api.post('/api/v/client/metrics', {
        peso: form.peso || null,
        porcentaje_musculo: form.porcentajeMusculo || null,
        porcentaje_grasa: form.porcentajeGrasa || null,
        notas: form.notas || null,
        chest: form.chest || null,
        waist: form.waist || null,
        hip: form.hip || null,
        thigh: form.thigh || null,
        arm: form.arm || null,
      });
      resetForm();
      return true;
    } catch (err) {
      if (err.response?.status === 422) {
        formErrors.value = err.response.data.errors || {};
        const msgs = Object.values(err.response.data.errors || {}).flat();
        if (msgs.length) saveError.value = msgs.join(' ');
      } else {
        saveError.value = err.response?.data?.message || 'Error al guardar el registro';
      }
      return false;
    } finally {
      saving.value = false;
    }
  }

  return {
    form, saving, saveError, formErrors, lastPeso, hasDraft,
    saveDraft, loadDraft, clearDraft, resetForm, validate, submit, updateField,
  };
}
