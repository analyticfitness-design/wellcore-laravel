<script setup>
import { ref, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();
const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const prefs = ref(null);

const TOGGLES = [
    { key: 'notify_post_reactions', label: 'Cuando alguien reacciona a mi post' },
    { key: 'notify_comments_on_my_post', label: 'Cuando comentan en mi post' },
    { key: 'notify_mentions', label: 'Cuando alguien me menciona' },
    { key: 'notify_coach_messages', label: 'Cuando mi coach me escribe' },
    { key: 'notify_coach_announcements', label: 'Anuncios de mi coach' },
    { key: 'notify_wellcore_announcements', label: 'Anuncios de WellCore' },
];

let saveTimeout = null;

async function load() {
    loading.value = true;
    try {
        const res = await api.get('/api/v/client/notifications/preferences');
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
        const res = await api.patch('/api/v/client/notifications/preferences', prefs.value);
        prefs.value = res.data;
    } catch (err) {
        toast.apiError(err, 'No pudimos guardar.');
    } finally {
        saving.value = false;
    }
}

watch(prefs, () => debouncedSave(), { deep: true });
onMounted(() => load());
</script>

<template>
  <ClientLayout>
    <div class="max-w-2xl mx-auto py-6 space-y-6 px-4">
      <header>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Notificaciones</h1>
        <p class="text-sm text-wc-text-tertiary mt-1">Decide qué eventos quieres recibir y cómo.</p>
      </header>

      <div v-if="loading" class="space-y-2">
        <div v-for="i in 8" :key="i" class="h-12 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
      </div>

      <template v-else-if="prefs">
        <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
          <h2 class="font-semibold text-wc-text mb-3">Canales</h2>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-wc-text">Push (browser)</p>
              <input type="checkbox" v-model="prefs.push_enabled" class="h-5 w-9 accent-wc-accent" />
            </div>
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-wc-text">In-app (campana)</p>
              <input type="checkbox" v-model="prefs.in_app_enabled" class="h-5 w-9 accent-wc-accent" />
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
          <h2 class="font-semibold text-wc-text mb-3">Cuándo notificarme</h2>
          <div class="space-y-3">
            <label v-for="t in TOGGLES" :key="t.key" class="flex items-center justify-between gap-3 cursor-pointer">
              <span class="text-sm text-wc-text-secondary">{{ t.label }}</span>
              <input type="checkbox" v-model="prefs[t.key]" class="h-5 w-9 accent-wc-accent" />
            </label>
          </div>
        </section>

        <p v-if="saving" class="text-xs text-wc-text-tertiary text-center">Guardando…</p>
      </template>
    </div>
  </ClientLayout>
</template>
