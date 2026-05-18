<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import ClientLayout from '../../layouts/ClientLayout.vue';

const { t } = useI18n();
const api = useApi();
const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const prefs = ref(null);

const TOGGLES = computed(() => [
    { key: 'notify_post_reactions', label: t('client_account.notif_prefs_post_reactions') },
    { key: 'notify_comments_on_my_post', label: t('client_account.notif_prefs_comments_on_my_post') },
    { key: 'notify_mentions', label: t('client_account.notif_prefs_mentions') },
    { key: 'notify_coach_messages', label: t('client_account.notif_prefs_coach_messages') },
    { key: 'notify_coach_announcements', label: t('client_account.notif_prefs_coach_announcements') },
    { key: 'notify_wellcore_announcements', label: t('client_account.notif_prefs_wellcore_announcements') },
]);

let saveTimeout = null;

async function load() {
    loading.value = true;
    try {
        const res = await api.get('/api/v/client/notifications/preferences');
        prefs.value = res.data;
    } catch (err) {
        toast.apiError(err, t('client_account.notif_prefs_load_error'));
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
        toast.apiError(err, t('client_account.notif_prefs_save_error'));
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
        <h1 class="font-display text-3xl tracking-wide text-wc-text">{{ t('client_account.notif_prefs_title') }}</h1>
        <p class="text-sm text-wc-text-tertiary mt-1">{{ t('client_account.notif_prefs_subtitle') }}</p>
      </header>

      <div v-if="loading" class="space-y-2">
        <div v-for="i in 8" :key="i" class="h-12 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
      </div>

      <template v-else-if="prefs">
        <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
          <h2 class="font-semibold text-wc-text mb-3">{{ t('client_account.notif_prefs_channels') }}</h2>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-wc-text">{{ t('client_account.notif_prefs_push') }}</p>
              <input type="checkbox" v-model="prefs.push_enabled" class="h-5 w-9 accent-wc-accent" />
            </div>
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-wc-text">{{ t('client_account.notif_prefs_in_app') }}</p>
              <input type="checkbox" v-model="prefs.in_app_enabled" class="h-5 w-9 accent-wc-accent" />
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
          <h2 class="font-semibold text-wc-text mb-3">{{ t('client_account.notif_prefs_when') }}</h2>
          <div class="space-y-3">
            <label v-for="toggle in TOGGLES" :key="toggle.key" class="flex items-center justify-between gap-3 cursor-pointer">
              <span class="text-sm text-wc-text-secondary">{{ toggle.label }}</span>
              <input type="checkbox" v-model="prefs[toggle.key]" class="h-5 w-9 accent-wc-accent" />
            </label>
          </div>
        </section>

        <p v-if="saving" class="text-xs text-wc-text-tertiary text-center">{{ t('client_account.notif_prefs_saving') }}</p>
      </template>
    </div>
  </ClientLayout>
</template>
