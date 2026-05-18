<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import { usePushSubscription } from '../../composables/usePushSubscription';
import CoachLayout from '../../layouts/CoachLayout.vue';

const { t } = useI18n();
const api = useApi();
const toast = useToast();
const push = usePushSubscription();

const loading = ref(true);
const saving = ref(false);
const prefs = ref(null);

const TOGGLES = computed(() => [
    { key: 'notify_pr_broken', label: t('coach_ops.notif_event_pr_broken') },
    { key: 'notify_streak_milestone', label: t('coach_ops.notif_event_streak_milestone') },
    { key: 'notify_post_created', label: t('coach_ops.notif_event_post_created') },
    { key: 'notify_comment_on_my_reply', label: t('coach_ops.notif_event_comment_on_reply') },
    { key: 'notify_at_risk_client', label: t('coach_ops.notif_event_at_risk_client') },
    { key: 'notify_official_post_engagement', label: t('coach_ops.notif_event_official_engagement') },
    { key: 'notify_admin_broadcast', label: t('coach_ops.notif_event_admin_broadcast') },
]);

let saveTimeout = null;

async function load() {
    loading.value = true;
    try {
        const res = await api.get('/api/v/coach/notifications/preferences');
        prefs.value = res.data;
    } catch (err) {
        toast.apiError(err, t('coach_ops.notif_load_error'));
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
        toast.apiError(err, t('coach_ops.notif_save_error'));
    } finally {
        saving.value = false;
    }
}

async function activatePush() {
    try {
        const result = await push.request();
        if (result === 'granted') {
            toast.success(t('coach_ops.notif_push_granted_toast'));
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
        <h1 class="font-display text-3xl tracking-wide text-wc-text">{{ t('coach_ops.notif_title') }}</h1>
        <p class="text-sm text-wc-text-tertiary mt-1">
          {{ t('coach_ops.notif_subtitle') }}
        </p>
      </header>

      <div v-if="loading" class="space-y-2">
        <div v-for="i in 8" :key="i" class="h-12 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
      </div>

      <template v-else-if="prefs">
        <div class="space-y-6 anim-entry anim-entry-2">
          <section class="rounded-[14px] border border-[var(--b1)] p-5" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
            <h2 class="font-semibold text-wc-text mb-3">{{ t('coach_ops.notif_channels_heading') }}</h2>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-wc-text">{{ t('coach_ops.notif_push_label') }}</p>
                  <p class="text-xs text-wc-text-tertiary">{{ t('coach_ops.notif_push_desc') }}</p>
                </div>
                <div class="flex items-center gap-2">
                  <span v-if="push.permission.value === 'granted'" class="text-xs text-emerald-500 font-semibold">{{ t('coach_ops.notif_push_granted') }}</span>
                  <button v-else-if="push.permission.value === 'default'" @click="activatePush" class="rounded-full bg-wc-accent text-white text-xs font-semibold px-3 py-1.5">
                    {{ t('coach_ops.notif_push_request') }}
                  </button>
                  <span v-else class="text-xs text-rose-500 font-semibold">{{ t('coach_ops.notif_push_blocked') }}</span>
                  <input type="checkbox" v-model="prefs.push_enabled" class="h-5 w-9 accent-wc-accent" />
                </div>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-wc-text">{{ t('coach_ops.notif_in_app_label') }}</p>
                  <p class="text-xs text-wc-text-tertiary">{{ t('coach_ops.notif_in_app_desc') }}</p>
                </div>
                <input type="checkbox" v-model="prefs.in_app_enabled" class="h-5 w-9 accent-wc-accent" />
              </div>
            </div>
          </section>

          <section class="rounded-[14px] border border-[var(--b1)] p-5" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
            <h2 class="font-semibold text-wc-text mb-3">{{ t('coach_ops.notif_events_heading') }}</h2>
            <div class="space-y-3">
              <label v-for="toggle in TOGGLES" :key="toggle.key" class="flex items-center justify-between gap-3 cursor-pointer">
                <span class="text-sm text-wc-text-secondary">{{ toggle.label }}</span>
                <input type="checkbox" v-model="prefs[toggle.key]" class="h-5 w-9 accent-wc-accent" />
              </label>
            </div>
          </section>

          <p v-if="saving" class="text-xs text-wc-text-tertiary text-center">{{ t('coach_ops.notif_saving') }}</p>
        </div>
      </template>
    </div>
  </CoachLayout>
</template>
