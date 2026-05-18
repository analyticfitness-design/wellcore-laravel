<script setup>
import { onMounted, ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import CoachLayout from '../../layouts/CoachLayout.vue';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import AvatarConic from '../../components/coach/ios/AvatarConic.vue';
import EmptyState from '../../components/coach/ios/EmptyState.vue';

const api = useApi();
const toast = useToast();
const { t } = useI18n();

const loading = ref(true);
const fetchError = ref('');
const photos = ref([]);
const allClients = ref([]);
const pendingCount = ref(0);
const showReviewed = ref(false);
const selectedClientId = ref('');
const noteMap = ref({});
const savingNote = ref({});
const reacting = ref({});

async function fetchPhotos() {
    loading.value = true;
    fetchError.value = '';
    try {
        const params = {};
        if (showReviewed.value) params.reviewed = 1;
        if (selectedClientId.value) params.client_id = selectedClientId.value;
        const { data } = await api.get('/api/v/coach/food-photos', { params });
        photos.value = data.photos || [];
        allClients.value = data.all_clients || [];
        pendingCount.value = data.pending_count || 0;
        // Hidratar noteMap con notas existentes
        photos.value.forEach((p) => {
            if (noteMap.value[p.id] === undefined) noteMap.value[p.id] = p.coach_note || '';
        });
    } catch (err) {
        fetchError.value = t('coach_inbox.food_load_error');
    } finally {
        loading.value = false;
    }
}

async function react(photoId, reaction) {
    reacting.value[photoId] = true;
    try {
        await api.post(`/api/v/coach/food-photos/${photoId}/react`, { reaction });
        await fetchPhotos();
    } catch (err) {
        toast.error(t('coach_inbox.food_react_error'));
    } finally {
        reacting.value[photoId] = false;
    }
}

async function saveNote(photoId) {
    savingNote.value[photoId] = true;
    try {
        await api.patch(`/api/v/coach/food-photos/${photoId}/note`, {
            note: noteMap.value[photoId] || '',
        });
    } catch (err) {
        toast.error(t('coach_inbox.food_save_note_error'));
    } finally {
        savingNote.value[photoId] = false;
    }
}

function toggleFilter() {
    showReviewed.value = !showReviewed.value;
    fetchPhotos();
}

onMounted(fetchPhotos);

const headerCount = computed(() =>
    pendingCount.value === 1
        ? t('coach_inbox.food_pending_count_one')
        : t('coach_inbox.food_pending_count_other', { count: pendingCount.value })
);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6 p-4 md:p-6">
      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="font-display text-2xl tracking-wide text-wc-text">{{ t('coach_inbox.food_title') }}</h1>
          <p class="text-sm text-wc-text-secondary">{{ headerCount }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <select v-model="selectedClientId" @change="fetchPhotos"
                  class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text">
            <option value="">{{ t('coach_inbox.food_filter_all_clients') }}</option>
            <option v-for="c in allClients" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <button @click="toggleFilter"
                  class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text transition hover:bg-wc-bg-tertiary">
            {{ showReviewed ? t('coach_inbox.food_filter_view_pending') : t('coach_inbox.food_filter_view_reviewed') }}
          </button>
        </div>
      </div>

      <!-- Fetch error -->
      <div v-if="fetchError" class="flex items-center justify-between rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
        <span>{{ fetchError }}</span>
        <button @click="fetchPhotos" class="ml-4 shrink-0 rounded-lg border border-red-500/30 px-3 py-1 text-xs font-medium hover:bg-red-500/10 transition-colors">{{ t('coach_inbox.food_retry') }}</button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 3" :key="i" class="h-72 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>

      <!-- Empty -->
      <EmptyState
        v-else-if="photos.length === 0"
        :kind="showReviewed ? 'activity' : 'success'"
        :title="showReviewed ? t('coach_inbox.food_empty_reviewed_title') : t('coach_inbox.food_empty_pending_title')"
        :subtitle="showReviewed ? t('coach_inbox.food_empty_reviewed_subtitle') : t('coach_inbox.food_empty_pending_subtitle')"
      />

      <!-- Photos grid -->
      <div v-else class="grid grid-cols-1 gap-4 anim-entry anim-entry-2 md:grid-cols-2">
        <div v-for="photo in photos" :key="photo.id"
             class="overflow-hidden rounded-[14px] border border-[var(--b1)]"
             style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
          <!-- Card header -->
          <div class="flex items-center gap-3 border-b border-wc-border p-4">
            <AvatarConic
              :initial="(photo.client_name || 'C').charAt(0).toUpperCase()"
              tone="accent"
              size="md"
            />
            <div class="min-w-0 flex-1">
              <p class="truncate font-medium text-wc-text">{{ photo.client_name }}</p>
              <p class="text-xs text-wc-text-tertiary">
                {{ photo.meal_name }} · {{ photo.photo_date }} · {{ photo.created_diff }}
              </p>
            </div>
          </div>

          <!-- Image -->
          <img :src="photo.photo_url" :alt="t('coach_inbox.food_photo_alt', { meal: photo.meal_name })"
               class="h-64 w-full object-cover" loading="lazy">

          <!-- Descripcion del cliente -->
          <div v-if="photo.client_note"
               class="border-t border-wc-border bg-wc-bg px-4 py-3">
            <p class="text-[11px] uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_inbox.food_client_description') }}</p>
            <p class="mt-1 text-sm text-wc-text-secondary">{{ photo.client_note }}</p>
          </div>

          <!-- Actions -->
          <div class="space-y-3 p-4">
            <div v-if="photo.coach_seen" class="flex items-center gap-2 text-sm">
              <span v-if="photo.coach_reaction === 'bien'"
                    class="rounded-full bg-green-500/10 px-2 py-0.5 text-green-400">✅ {{ t('coach_inbox.food_reaction_good') }}</span>
              <span v-else-if="photo.coach_reaction === 'mejorar'"
                    class="rounded-full bg-amber-500/10 px-2 py-0.5 text-amber-400">⚠️ {{ t('coach_inbox.food_reaction_improve') }}</span>
              <span v-else class="rounded-full bg-wc-bg-tertiary px-2 py-0.5">{{ t('coach_inbox.food_reaction_seen') }}</span>
            </div>
            <div v-else class="flex gap-2">
              <button @click="react(photo.id, 'bien')" :disabled="reacting[photo.id]"
                      class="flex-1 rounded-lg border border-green-500/30 bg-green-500/10 py-2 text-sm font-semibold text-green-400 transition hover:bg-green-500/20 disabled:opacity-50">
                ✅ {{ t('coach_inbox.food_btn_good') }}
              </button>
              <button @click="react(photo.id, 'mejorar')" :disabled="reacting[photo.id]"
                      class="flex-1 rounded-lg border border-amber-500/30 bg-amber-500/10 py-2 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/20 disabled:opacity-50">
                ⚠️ {{ t('coach_inbox.food_btn_improve') }}
              </button>
            </div>

            <textarea v-model="noteMap[photo.id]"
                      @blur="saveNote(photo.id)"
                      rows="2"
                      :placeholder="t('coach_inbox.food_note_placeholder')"
                      class="w-full rounded-lg border border-wc-border bg-wc-bg p-2 text-sm text-wc-text"></textarea>
            <p v-if="savingNote[photo.id]" class="text-xs text-wc-text-tertiary">{{ t('coach_inbox.food_note_saving') }}</p>
          </div>
        </div>
      </div>

      <p v-if="!loading && photos.length === 40" class="text-center text-xs text-wc-text-tertiary">
        {{ t('coach_inbox.food_showing_latest') }}
      </p>
    </div>
  </CoachLayout>
</template>
