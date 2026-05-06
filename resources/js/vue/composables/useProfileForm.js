import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useApi } from './useApi';
import { useToast } from './useToast';

/**
 * useProfileForm — estado, dirty tracking, save/discard del Profile Editor v2.
 *
 * State shape (13 campos): name, email, city, birthDate, whatsapp, bio,
 * peso, altura, objetivo, nivel, lugarEntreno, diasDisponibles[], restricciones.
 *
 * Wiring:
 *   const { state, initial, isDirty, dirtyCount, completion,
 *           load, save, discard, formErrors, saving, loading, error,
 *           setAvatarUrl, avatarUrl } = useProfileForm();
 *
 * - load() hidrata state e initial via GET /api/v/client/profile.
 * - save() PUT con snake_case (birth_date, lugar_entreno, dias_disponibles).
 *   Tras success, initial = clone(state). Refresca completion.
 * - discard() restaura state = clone(initial).
 * - beforeunload guard activo cuando isDirty.
 * - Ctrl+S handler global cuando isDirty (preventDefault + save()).
 */

const EMPTY_STATE = () => ({
    name: '',
    email: '',
    city: '',
    birthDate: '',
    whatsapp: '',
    bio: '',
    peso: '',
    altura: '',
    objetivo: '',
    nivel: '',
    lugarEntreno: '',
    diasDisponibles: [],
    restricciones: '',
});

const FIELD_KEYS = Object.keys(EMPTY_STATE());

function clone(obj) {
    return JSON.parse(JSON.stringify(obj));
}

function isInsideEditableElement(target) {
    if (!target) return false;
    const tag = target.tagName;
    if (!tag) return false;
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return true;
    if (target.isContentEditable) return true;
    return false;
}

export function useProfileForm() {
    const api = useApi();
    const toast = useToast();

    const state = ref(EMPTY_STATE());
    const initial = ref(EMPTY_STATE());

    const loading = ref(false);
    const saving = ref(false);
    const error = ref(null);
    const formErrors = ref({});

    const completion = ref({ score: 0, missing: [] });
    const avatarUrl = ref(null);

    // ── Dirty tracking ────────────────────────────────────────────────
    const isDirty = computed(() => {
        return JSON.stringify(state.value) !== JSON.stringify(initial.value);
    });

    const dirtyCount = computed(() => {
        let count = 0;
        for (const key of FIELD_KEYS) {
            const a = state.value[key];
            const b = initial.value[key];
            if (Array.isArray(a) || Array.isArray(b)) {
                if (JSON.stringify(a ?? []) !== JSON.stringify(b ?? [])) count++;
            } else {
                if ((a ?? '') !== (b ?? '')) count++;
            }
        }
        return count;
    });

    // ── Load ──────────────────────────────────────────────────────────
    async function load() {
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get('/api/v/client/profile');
            const d = res.data ?? {};
            const next = {
                name: d.name || '',
                email: d.email || '',
                city: d.city || '',
                birthDate: d.birthDate || '',
                whatsapp: d.whatsapp || '',
                bio: d.bio || '',
                peso: d.peso ?? '',
                altura: d.altura ?? '',
                objetivo: d.objetivo || '',
                nivel: d.nivel || '',
                lugarEntreno: d.lugarEntreno || '',
                diasDisponibles: Array.isArray(d.diasDisponibles) ? [...d.diasDisponibles] : [],
                restricciones: d.restricciones || '',
            };
            state.value = next;
            initial.value = clone(next);
            avatarUrl.value = d.avatarUrl || null;
            completion.value = d.completion ?? { score: 0, missing: [] };
            if (d.name) {
                try { localStorage.setItem('wc_user_name', d.name); } catch {}
            }
            return { completion: completion.value, avatarUrl: avatarUrl.value };
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al cargar el perfil.';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    // ── Save ──────────────────────────────────────────────────────────
    async function save() {
        if (saving.value) return { ok: false };
        saving.value = true;
        formErrors.value = {};
        try {
            const payload = {
                name: state.value.name,
                email: state.value.email,
                city: state.value.city,
                birth_date: state.value.birthDate,
                whatsapp: state.value.whatsapp,
                bio: state.value.bio,
                peso: state.value.peso === '' || state.value.peso === null ? null : state.value.peso,
                altura: state.value.altura === '' || state.value.altura === null ? null : state.value.altura,
                objetivo: state.value.objetivo,
                nivel: state.value.nivel,
                lugar_entreno: state.value.lugarEntreno,
                dias_disponibles: state.value.diasDisponibles,
                restricciones: state.value.restricciones,
            };
            await api.put('/api/v/client/profile', payload);

            if (state.value.name) {
                try { localStorage.setItem('wc_user_name', state.value.name); } catch {}
            }

            // Re-baseline initial = state (deep clone).
            initial.value = clone(state.value);

            // Refresh completion silently.
            try {
                const res = await api.get('/api/v/client/profile');
                completion.value = res.data?.completion ?? completion.value;
            } catch { /* silent */ }

            return { ok: true };
        } catch (err) {
            if (err.response?.status === 422) {
                formErrors.value = err.response.data?.errors || {};
                toast.warn('Revisa los campos resaltados.');
            } else {
                toast.apiError(err, 'No pudimos guardar tu perfil. Intenta de nuevo.');
            }
            return { ok: false, error: err };
        } finally {
            saving.value = false;
        }
    }

    function discard() {
        state.value = clone(initial.value);
        formErrors.value = {};
    }

    function setAvatarUrl(url) {
        avatarUrl.value = url || null;
    }

    function setCompletion(c) {
        if (c && typeof c === 'object') completion.value = c;
    }

    // ── beforeunload guard ────────────────────────────────────────────
    function onBeforeUnload(e) {
        if (!isDirty.value) return;
        e.preventDefault();
        // Chrome legacy: returnValue must be set.
        e.returnValue = '';
        return '';
    }

    // ── Ctrl+S handler ────────────────────────────────────────────────
    function onKeyDown(e) {
        const isSave = (e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S');
        if (!isSave) return;
        // Even if not dirty we still preventDefault to avoid "Save Page" dialog
        // while the editor is mounted.
        e.preventDefault();
        if (!isDirty.value || saving.value) return;
        save();
    }

    let beforeUnloadAttached = false;
    function attachBeforeUnload() {
        if (beforeUnloadAttached || typeof window === 'undefined') return;
        window.addEventListener('beforeunload', onBeforeUnload);
        beforeUnloadAttached = true;
    }
    function detachBeforeUnload() {
        if (!beforeUnloadAttached || typeof window === 'undefined') return;
        window.removeEventListener('beforeunload', onBeforeUnload);
        beforeUnloadAttached = false;
    }

    // Atttach beforeunload only when dirty; detach when clean (saves perf).
    const stopWatch = watch(isDirty, (dirty) => {
        if (dirty) attachBeforeUnload();
        else detachBeforeUnload();
    });

    onMounted(() => {
        if (typeof window !== 'undefined') {
            window.addEventListener('keydown', onKeyDown);
        }
    });

    onBeforeUnmount(() => {
        detachBeforeUnload();
        if (typeof window !== 'undefined') {
            window.removeEventListener('keydown', onKeyDown);
        }
        stopWatch();
    });

    return {
        // state
        state,
        initial,
        avatarUrl,
        completion,
        formErrors,
        loading,
        saving,
        error,
        // computed
        isDirty,
        dirtyCount,
        // actions
        load,
        save,
        discard,
        setAvatarUrl,
        setCompletion,
    };
}
