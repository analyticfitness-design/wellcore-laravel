<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import CoachLayout from '../../layouts/CoachLayout.vue';
import WcPageHeader from '../../components/WcPageHeader.vue';
import AvatarConic from '../../components/coach/ios/AvatarConic.vue';
import EmptyState from '../../components/coach/ios/EmptyState.vue';

const { t } = useI18n();
const api = useApi();
const toast = useToast();
const loading = ref(true);
const notes = ref([]);
const noteTypeFilter = ref('all');
const showForm = ref(false);
const saving = ref(false);
const success = ref(false);
const saveError = ref('');
const loadError = ref('');

// Form fields
const noteClient = ref('');
const noteType = ref('general');
const noteText = ref('');
const editingId = ref(null);
const clients = ref([]);

const noteStats = computed(() => {
    const all = notes.value.length;
    const general = notes.value.filter(n => n.note_type === 'general').length;
    const seguimiento = notes.value.filter(n => n.note_type === 'seguimiento').length;
    const alerta = notes.value.filter(n => n.note_type === 'alerta').length;
    const logro = notes.value.filter(n => n.note_type === 'logro').length;
    return { total: all, general, seguimiento, alerta, logro };
});

const filteredNotes = computed(() => {
    if (noteTypeFilter.value === 'all') return notes.value;
    return notes.value.filter(n => n.note_type === noteTypeFilter.value);
});

const typeColors = {
    general:     'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
    seguimiento: 'bg-sky-500/10 text-sky-400 border-sky-500/20',
    alerta:      'bg-amber-500/10 text-amber-400 border-amber-500/20',
    logro:       'border border-emerald-500/30 bg-emerald-500/10 text-emerald-400',
};

function openCreate() {
    editingId.value = null;
    noteClient.value = '';
    noteType.value = 'general';
    noteText.value = '';
    showForm.value = true;
}

function editNote(note) {
    editingId.value = note.id;
    noteClient.value = note.client_id || '';
    noteType.value = note.note_type || 'general';
    noteText.value = note.note;
    showForm.value = true;
}

async function saveNote() {
    if (!noteText.value.trim()) return;
    saving.value = true;
    saveError.value = '';
    try {
        const payload = {
            client_id: noteClient.value ? parseInt(noteClient.value) : null,
            note_type: noteType.value,
            note: noteText.value,
        };
        if (editingId.value) {
            await api.put(`/api/v/coach/notes/${editingId.value}`, payload);
            const idx = notes.value.findIndex(n => n.id === editingId.value);
            if (idx !== -1) Object.assign(notes.value[idx], payload);
        } else {
            const { data } = await api.post('/api/v/coach/notes', payload);
            notes.value.unshift(data.note || { id: Date.now(), ...payload, date: t('coach_growth.notes.now'), client_name: '' });
        }
        showForm.value = false;
        success.value = true;
        setTimeout(() => { success.value = false; }, 3000);
    } catch (e) {
        saveError.value = t('coach_growth.notes.save_error');
    } finally {
        saving.value = false;
    }
}

async function deleteNote(id) {
    if (!confirm(t('coach_growth.notes.delete_confirm'))) return;
    try {
        await api.delete(`/api/v/coach/notes/${id}`);
        notes.value = notes.value.filter(n => n.id !== id);
    } catch (e) {
        toast.error(t('coach_growth.notes.delete_error'));
    }
}

async function loadData() {
    loading.value = true;
    loadError.value = '';
    try {
        const { data } = await api.get('/api/v/coach/notes');
        notes.value = data.notes || [];
        clients.value = data.clients || [];
    } catch (e) {
        loadError.value = t('coach_growth.notes.load_error');
    } finally {
        loading.value = false;
    }
}

onMounted(loadData);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Success toast -->
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="success" class="flex items-center gap-3 rounded-card border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          {{ t('coach_growth.notes.toast_saved') }}
        </div>
      </Transition>

      <WcPageHeader :contextLabel="t('coach_growth.notes.page_context')" :title="t('coach_growth.notes.page_title')" :subtitle="t('coach_growth.notes.page_subtitle')">
        <template #actions>
          <button
            @click="openCreate"
            class="inline-flex items-center gap-2 rounded-button bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            {{ t('coach_growth.notes.new_note_btn') }}
          </button>
        </template>
      </WcPageHeader>

      <!-- Type Stats -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        <button
          v-for="f in [{ key: 'all', label: t('coach_growth.notes.filter_all'), count: noteStats.total }, { key: 'general', label: t('coach_growth.notes.filter_general'), count: noteStats.general }, { key: 'seguimiento', label: t('coach_growth.notes.filter_seguimiento'), count: noteStats.seguimiento }, { key: 'alerta', label: t('coach_growth.notes.filter_alerta'), count: noteStats.alerta }, { key: 'logro', label: t('coach_growth.notes.filter_logro'), count: noteStats.logro }]"
          :key="f.key"
          @click="noteTypeFilter = f.key"
          class="rounded-card border p-3 text-center transition-colors"
          :class="noteTypeFilter === f.key ? 'border-wc-accent bg-wc-accent/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/50'"
        >
          <p class="font-data text-2xl font-bold text-wc-text">{{ f.count }}</p>
          <p class="text-xs text-wc-text-tertiary">{{ f.label }}</p>
        </button>
      </div>

      <!-- Note form modal -->
      <div v-if="showForm" class="rounded-[14px] border border-[var(--b1)] p-5 space-y-4" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-wc-text">{{ editingId ? t('coach_growth.notes.form_title_edit') : t('coach_growth.notes.form_title_new') }}</h3>
          <button @click="showForm = false" class="text-wc-text-tertiary hover:text-wc-text">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-1.5 block font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">{{ t('coach_growth.notes.form_client_label') }}</label>
            <select v-model="noteClient" class="w-full rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              <option value="">{{ t('coach_growth.notes.form_client_none') }}</option>
              <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1.5 block font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">{{ t('coach_growth.notes.form_type_label') }}</label>
            <select v-model="noteType" class="w-full rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              <option value="general">{{ t('coach_growth.notes.type_general') }}</option>
              <option value="seguimiento">{{ t('coach_growth.notes.type_seguimiento') }}</option>
              <option value="alerta">{{ t('coach_growth.notes.type_alerta') }}</option>
              <option value="logro">{{ t('coach_growth.notes.type_logro') }}</option>
            </select>
          </div>
        </div>
        <div>
          <label class="mb-1.5 block font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">{{ t('coach_growth.notes.form_note_label') }}</label>
          <textarea v-model="noteText" rows="4" :placeholder="t('coach_growth.notes.form_note_placeholder')" class="w-full rounded-button border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"></textarea>
        </div>
        <div v-if="saveError" class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-xs text-red-400">{{ saveError }}</div>
        <button
          @click="saveNote"
          :disabled="saving || !noteText.trim()"
          class="inline-flex items-center gap-2 rounded-button bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
        >{{ saving ? t('coach_growth.notes.form_saving') : t('coach_growth.notes.form_save') }}</button>
      </div>

      <!-- Load error -->
      <div v-if="loadError" class="flex items-center justify-between rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
        <span>{{ loadError }}</span>
        <button @click="loadData" class="ml-4 shrink-0 rounded-button border border-red-500/30 px-3 py-1 text-xs font-medium hover:bg-red-500/10 transition-colors">{{ t('coach_growth.notes.retry') }}</button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <!-- Notes list -->
      <div v-else-if="filteredNotes.length > 0" class="space-y-3 anim-entry anim-entry-2">
        <div
          v-for="note in filteredNotes"
          :key="note.id"
          class="rounded-[14px] border border-[var(--b1)] p-4"
          style="background: var(--s2); box-shadow: var(--shadow-card-ios);"
        >
          <div class="flex items-start justify-between gap-3">
            <AvatarConic
              v-if="note.client_name"
              :initial="(note.client_name || 'C').charAt(0).toUpperCase()"
              :tone="note.note_type === 'alerta' ? 'accent' : (note.note_type === 'logro' ? 'gold' : 'accent')"
              size="md"
            />
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold" :class="typeColors[note.note_type] || typeColors.general">{{ t(`coach_growth.notes.type_${note.note_type}`) }}</span>
                <span v-if="note.client_name" class="text-xs text-wc-text-secondary">{{ note.client_name }}</span>
                <span class="text-[10px] text-wc-text-tertiary">{{ note.created_at_ago }}</span>
              </div>
              <p class="mt-2 text-sm text-wc-text leading-relaxed">{{ note.note }}</p>
            </div>
            <div class="flex items-center gap-1">
              <button @click="editNote(note)" class="flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-secondary hover:text-wc-text transition-colors" :aria-label="t('coach_growth.notes.form_aria_edit')">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                </svg>
              </button>
              <button @click="deleteNote(note.id)" class="flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-red-500/10 hover:text-red-500 transition-colors" :aria-label="t('coach_growth.notes.form_aria_delete')">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <EmptyState
        v-else
        kind="activity"
        :title="t('coach_growth.notes.empty_title')"
        :subtitle="t('coach_growth.notes.empty_subtitle')"
      />

    </div>
  </CoachLayout>
</template>
