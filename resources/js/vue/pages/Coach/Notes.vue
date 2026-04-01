<script setup>
import { ref, onMounted, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const notes = ref([]);
const noteTypeFilter = ref('all');
const showForm = ref(false);
const saving = ref(false);
const success = ref(false);

// Form fields
const noteClient = ref('');
const noteType = ref('general');
const noteText = ref('');
const editingId = ref(null);
const clients = ref([]);

const noteStats = computed(() => {
    const all = notes.value.length;
    const general = notes.value.filter(n => n.type === 'general').length;
    const seguimiento = notes.value.filter(n => n.type === 'seguimiento').length;
    const alerta = notes.value.filter(n => n.type === 'alerta').length;
    const logro = notes.value.filter(n => n.type === 'logro').length;
    return { total: all, general, seguimiento, alerta, logro };
});

const filteredNotes = computed(() => {
    if (noteTypeFilter.value === 'all') return notes.value;
    return notes.value.filter(n => n.type === noteTypeFilter.value);
});

const typeColors = {
    general: 'bg-sky-500/10 text-sky-500 border-sky-500/20',
    seguimiento: 'bg-violet-500/10 text-violet-500 border-violet-500/20',
    alerta: 'bg-amber-500/10 text-amber-500 border-amber-500/20',
    logro: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
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
    noteType.value = note.type;
    noteText.value = note.note;
    showForm.value = true;
}

async function saveNote() {
    if (!noteText.value.trim()) return;
    saving.value = true;
    try {
        const payload = { client_id: noteClient.value || null, type: noteType.value, note: noteText.value };
        if (editingId.value) {
            await api.put(`/api/v/coach/notes/${editingId.value}`, payload);
            const idx = notes.value.findIndex(n => n.id === editingId.value);
            if (idx !== -1) Object.assign(notes.value[idx], payload);
        } else {
            const { data } = await api.post('/api/v/coach/notes', payload);
            notes.value.unshift(data.note || { id: Date.now(), ...payload, date: 'Ahora', client_name: '' });
        }
        showForm.value = false;
        success.value = true;
        setTimeout(() => { success.value = false; }, 3000);
    } catch (e) {
        // silent
    } finally {
        saving.value = false;
    }
}

async function deleteNote(id) {
    if (!confirm('Eliminar esta nota?')) return;
    try {
        await api.delete(`/api/v/coach/notes/${id}`);
        notes.value = notes.value.filter(n => n.id !== id);
    } catch (e) {
        // silent
    }
}

async function loadData() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/notes');
        notes.value = data.notes || [];
        clients.value = data.clients || [];
    } catch (e) {
        // silent
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
        <div v-if="success" class="flex items-center gap-3 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          Nota guardada
        </div>
      </Transition>

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Notas</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">Gestiona tus notas de clientes</p>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Nueva nota
        </button>
      </div>

      <!-- Type Stats -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        <button
          v-for="f in [{ key: 'all', label: 'Todas', count: noteStats.total }, { key: 'general', label: 'General', count: noteStats.general }, { key: 'seguimiento', label: 'Seguimiento', count: noteStats.seguimiento }, { key: 'alerta', label: 'Alerta', count: noteStats.alerta }, { key: 'logro', label: 'Logro', count: noteStats.logro }]"
          :key="f.key"
          @click="noteTypeFilter = f.key"
          class="rounded-xl border p-3 text-center transition-colors"
          :class="noteTypeFilter === f.key ? 'border-wc-accent bg-wc-accent/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/50'"
        >
          <p class="font-data text-2xl font-bold text-wc-text">{{ f.count }}</p>
          <p class="text-xs text-wc-text-tertiary">{{ f.label }}</p>
        </button>
      </div>

      <!-- Note form modal -->
      <div v-if="showForm" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-wc-text">{{ editingId ? 'Editar nota' : 'Nueva nota' }}</h3>
          <button @click="showForm = false" class="text-wc-text-tertiary hover:text-wc-text">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Cliente (opcional)</label>
            <select v-model="noteClient" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              <option value="">Sin cliente</option>
              <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Tipo</label>
            <select v-model="noteType" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              <option value="general">General</option>
              <option value="seguimiento">Seguimiento</option>
              <option value="alerta">Alerta</option>
              <option value="logro">Logro</option>
            </select>
          </div>
        </div>
        <div>
          <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Nota</label>
          <textarea v-model="noteText" rows="4" placeholder="Escribe tu nota..." class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"></textarea>
        </div>
        <button
          @click="saveNote"
          :disabled="saving || !noteText.trim()"
          class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
        >{{ saving ? 'Guardando...' : 'Guardar nota' }}</button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <!-- Notes list -->
      <div v-else-if="filteredNotes.length > 0" class="space-y-3">
        <div
          v-for="note in filteredNotes"
          :key="note.id"
          class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold capitalize" :class="typeColors[note.type] || typeColors.general">{{ note.type }}</span>
                <span v-if="note.client_name" class="text-xs text-wc-text-secondary">{{ note.client_name }}</span>
                <span class="text-[10px] text-wc-text-tertiary">{{ note.date }}</span>
              </div>
              <p class="mt-2 text-sm text-wc-text leading-relaxed">{{ note.note }}</p>
            </div>
            <div class="flex items-center gap-1">
              <button @click="editNote(note)" class="flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-secondary hover:text-wc-text transition-colors" aria-label="Editar">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                </svg>
              </button>
              <button @click="deleteNote(note.id)" class="flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-red-500/10 hover:text-red-500 transition-colors" aria-label="Eliminar">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
        </svg>
        <p class="mt-3 text-sm font-medium text-wc-text">Sin notas</p>
        <p class="mt-1 text-xs text-wc-text-tertiary">Crea tu primera nota</p>
      </div>
    </div>
  </CoachLayout>
</template>
