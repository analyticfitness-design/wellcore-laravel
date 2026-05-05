<script setup>
import { ref, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import { usePushSubscription } from '../../composables/usePushSubscription';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const toast = useToast();
const push = usePushSubscription();

const loading = ref(true);
const saving = ref(false);
const prefs = ref(null);

const TOGGLES = [
    { key: 'notify_pr_broken', label: 'Cuando un cliente rompe un PR' },
    { key: 'notify_streak_milestone', label: 'Cuando un cliente alcanza un milestone (7/30/100 días)' },
    { key: 'notify_post_created', label: 'Cuando un cliente hace un post (silencioso por defecto)' },
    { key: 'notify_comment_on_my_reply', label: 'Cuando alguien comenta después de mi respuesta' },
    { key: 'notify_at_risk_client', label: 'Cuando un cliente lleva 5+ días sin actividad' },
    { key: 'notify_official_post_engagement', label: 'Cuando un cliente reacciona a mi post oficial' },
    { key: 'notify_admin_broadcast', label: 'Cuando WellCore admin envía un anuncio' },
];

let saveTimeout = null;

async function load() {
    loading.value = true;
    try {
        const res = await api.get('/api/v/coach/notifications/preferences');
        prefs.value = res.data;
    } catch (err) {
        toast.apiError(err, 'No pudimos cargar preferencias.');
    } finally {
        loading.value = false;
    }
}

function debouncedSave() {
    if (saveTimeout) clearTimeout(saveTimeout);
    saveTimeout = setTimeout(savePrefs, 500);
}

async function savePrefs() {
    if (!prefs.value) return;
    saving.value = true;
    try {
        const res = await api.patch('/api/v/coach/notifications/preferences', prefs.value);
        prefs.value = res.data;
    } catch (err) {
        toast.apiError(err, 'No pudimos guardar.');
    } finally {
        saving.value = false;
    }
}

async function activatePush() {
    try {
        const result = await push.request();
        if (result === 'granted') {
            toast.success('Notificaciones browser activadas.');
        }
    } catch (err) {
        toast.error(err.message);
    }
}

watch(prefs, () => debouncedSave(), { deep: true });

onMounted(() => {
    load();
});
</script>

<template>
  <CoachLayout>
    <div class="max-w-2xl mx-auto py-6 space-y-6">
      <header>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Notificaciones</h1>
        <p class="text-sm text-wc-text-tertiary mt-1">
          Decide qué eventos de tu equipo quieres seguir y cómo recibirlos.
        </p>
      </header>

      <div v-if="loading" class="space-y-2">
        <div v-for="i in 8" :key="i" class="h-12 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
      </div>

      <template v-else-if="prefs">
        <div class="space-y-6 anim-entry anim-entry-2">
          <section class="rounded-[14px] border border-[var(--b1)] p-5" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
            <h2 class="font-semibold text-wc-text mb-3">Canales</h2>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-wc-text">Push (browser)</p>
                  <p class="text-xs text-wc-text-tertiary">Notificaciones del navegador en tiempo real.</p>
                </div>
                <div class="flex items-center gap-2">
                  <span v-if="push.permission.value === 'granted'" class="text-xs text-emerald-500 font-semibold">Activado</span>
                  <button v-else-if="push.permission.value === 'default'" @click="activatePush" class="rounded-full bg-wc-accent text-white text-xs font-semibold px-3 py-1.5">
                    Activar
                  </button>
                  <span v-else class="text-xs text-rose-500 font-semibold">Bloqueado</span>
                  <input type="checkbox" v-model="prefs.push_enabled" class="h-5 w-9 accent-wc-accent" />
                </div>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-wc-text">In-app (campana)</p>
                  <p class="text-xs text-wc-text-tertiary">Aparecen en el ícono de campana del topbar.</p>
                </div>
                <input type="checkbox" v-model="prefs.in_app_enabled" class="h-5 w-9 accent-wc-accent" />
              </div>
            </div>
          </section>

          <section class="rounded-[14px] border border-[var(--b1)] p-5" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
            <h2 class="font-semibold text-wc-text mb-3">Cuándo notificarme</h2>
            <div class="space-y-3">
              <label v-for="t in TOGGLES" :key="t.key" class="flex items-center justify-between gap-3 cursor-pointer">
                <span class="text-sm text-wc-text-secondary">{{ t.label }}</span>
                <input type="checkbox" v-model="prefs[t.key]" class="h-5 w-9 accent-wc-accent" />
              </label>
            </div>
          </section>

          <p v-if="saving" class="text-xs text-wc-text-tertiary text-center">Guardando…</p>
        </div>
      </template>
    </div>
  </CoachLayout>
</template>
