<script setup>
/**
 * CoachFeedbackPanel — lateral panel that appears when the user opens a
 * coach-reviewed photo. Displays:
 *   - hero photo with thumbnails for the other two angles
 *   - coach avatar + name + timestamp
 *   - quoted "summary" line
 *   - numbered notes list (markers map to coordinates over the photo when
 *     supplied — Phase 1 just shows the marker pill, no overlay rendering)
 *   - reply textarea + send button
 *
 * Props:
 *   open:       bool
 *   session:    session object (for thumbnails)
 *   activePhoto: photo object (the currently shown one)
 *   coachName:  string  (default 'Marina Pérez')
 *   coachAvatar: string url   optional
 *   summary:    string  optional quote line
 *
 * Emits:
 *   close
 *   change-active(photo)
 *
 * Notes are loaded via useCoachFeedback composable when activePhoto.id changes.
 */
import { ref, computed, watch } from 'vue';
import { useCoachFeedback } from '../../composables/useCoachFeedback';

const props = defineProps({
  open: { type: Boolean, default: false },
  session: { type: Object, default: null },
  activePhoto: { type: Object, default: null },
  coachName: { type: String, default: 'Marina Pérez' },
  coachAvatar: { type: String, default: '' },
  summary: { type: String, default: '' },
});

const emit = defineEmits(['close', 'change-active', 'delete-photo']);

const fb = useCoachFeedback();

const replyText = ref('');
const sending = ref(false);

const ANGLES = ['frente', 'perfil', 'espalda'];
const notes = computed(() => fb.notesFor(props.activePhoto?.id));
const loading = computed(() => fb.loadingFor(props.activePhoto?.id));

async function send() {
  if (!props.activePhoto?.id || !replyText.value.trim()) return;
  sending.value = true;
  const ok = await fb.reply(props.activePhoto.id, replyText.value);
  sending.value = false;
  if (ok) replyText.value = '';
}

watch(
  () => props.activePhoto?.id,
  (id) => {
    if (props.open && id) {
      fb.fetchNotes(id);
      fb.markRead(id);
    }
  }
);

watch(
  () => props.open,
  (v) => {
    if (v && props.activePhoto?.id) {
      fb.fetchNotes(props.activePhoto.id);
      fb.markRead(props.activePhoto.id);
    }
  }
);

const initials = computed(() =>
  props.coachName
    .split(' ')
    .map((p) => p[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
);
</script>

<template>
  <Transition name="slide-right">
    <aside
      v-if="open"
      class="fixed inset-y-0 right-0 z-40 flex w-full max-w-md flex-col overflow-hidden border-l border-wc-border bg-wc-bg shadow-2xl"
      role="dialog"
      aria-modal="true"
      aria-labelledby="coach-feedback-title"
    >
      <!-- Header -->
      <header class="flex items-center justify-between border-b border-wc-border px-4 py-3">
        <h2 id="coach-feedback-title" class="font-display text-base font-semibold uppercase tracking-wider text-wc-text">
          Notas de tu coach
        </h2>
        <div class="flex items-center gap-1.5">
          <button
            v-if="activePhoto?.id"
            type="button"
            class="flex h-9 w-9 items-center justify-center rounded-full border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary transition-colors hover:border-red-500/40 hover:text-red-400"
            aria-label="Eliminar foto"
            @click="$emit('delete-photo', activePhoto)"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
              <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
            </svg>
          </button>
          <button
            type="button"
            class="flex h-9 w-9 items-center justify-center rounded-full border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary transition-colors hover:text-wc-text"
            aria-label="Cerrar panel"
            @click="$emit('close')"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
              <path d="M18 6 6 18M6 6l12 12" />
            </svg>
          </button>
        </div>
      </header>

      <div class="flex-1 overflow-y-auto px-4 py-4">
        <!-- Photo + thumbs -->
        <div class="relative mb-4 aspect-[3/4] overflow-hidden rounded-2xl bg-wc-bg-tertiary">
          <img
            v-if="activePhoto?.url"
            :src="activePhoto.url"
            :alt="activePhoto.tipo || 'foto'"
            class="absolute inset-0 h-full w-full object-cover"
          />
          <div
            v-if="activePhoto?.tipo"
            class="absolute left-3 top-3 rounded bg-black/60 px-2 py-0.5 font-mono text-[10px] uppercase tracking-widest text-white backdrop-blur"
          >
            {{ activePhoto.tipo }}
          </div>
          <div v-if="session?.photos" class="absolute inset-x-3 bottom-3 flex gap-1.5">
            <button
              v-for="angle in ANGLES"
              :key="angle"
              type="button"
              class="flex-1 overflow-hidden rounded-md border-[1.5px] transition-opacity"
              :class="activePhoto?.tipo === angle ? 'border-white opacity-100' : 'border-transparent opacity-50 hover:opacity-80'"
              :aria-label="`Ver ${angle}`"
              @click="session.photos[angle] && $emit('change-active', session.photos[angle])"
            >
              <div class="aspect-[3/4] w-full bg-wc-bg-tertiary">
                <img
                  v-if="session.photos[angle]?.url"
                  :src="session.photos[angle].url"
                  :alt="angle"
                  class="h-full w-full object-cover"
                />
              </div>
            </button>
          </div>
        </div>

        <!-- Coach line -->
        <div class="mb-3 flex items-center gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary p-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-700 to-amber-900 font-display text-sm font-semibold text-white">
            <img v-if="coachAvatar" :src="coachAvatar" alt="" class="h-full w-full rounded-full object-cover" />
            <span v-else>{{ initials }}</span>
          </div>
          <div class="min-w-0 flex-1">
            <h5 class="font-display text-[13px] font-semibold uppercase tracking-wider text-wc-text">
              Coach {{ coachName }}
            </h5>
            <p class="text-[11px] text-wc-text-tertiary">Tu coach 1:1</p>
          </div>
        </div>

        <!-- Summary quote -->
        <p
          v-if="summary"
          class="mb-4 border-l-2 border-wc-accent py-2 pl-3.5 text-[15px] leading-relaxed text-wc-text"
        >
          {{ summary }}
        </p>

        <!-- Loading -->
        <div v-if="loading" class="space-y-2">
          <div v-for="n in 3" :key="n" class="h-16 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
        </div>

        <!-- Notes -->
        <div v-else-if="notes.length" class="space-y-2">
          <article
            v-for="(note, idx) in notes"
            :key="note.id || idx"
            class="grid grid-cols-[auto_1fr] gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary p-3.5"
          >
            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-wc-accent font-mono text-[10px] font-semibold text-white">
              {{ idx + 1 }}
            </div>
            <div class="min-w-0">
              <h6
                v-if="note.title"
                class="mb-1 font-display text-[10px] font-semibold uppercase tracking-widest text-wc-text-secondary"
              >
                {{ note.title }}
              </h6>
              <p class="text-[13px] leading-snug text-wc-text">{{ note.body }}</p>
            </div>
          </article>
        </div>

        <!-- No notes -->
        <p v-else class="rounded-xl border border-dashed border-wc-border bg-wc-bg-tertiary p-4 text-center text-sm text-wc-text-tertiary">
          Tu coach aún no ha dejado notas en esta foto.
        </p>
      </div>

      <!-- Reply -->
      <footer class="border-t border-wc-border bg-wc-bg-secondary p-3">
        <label class="sr-only" for="coach-reply">Responder al coach</label>
        <textarea
          id="coach-reply"
          v-model="replyText"
          rows="2"
          placeholder="Responde a tu coach..."
          class="w-full resize-none rounded-xl border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-base text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
        ></textarea>
        <div class="mt-2 flex justify-end">
          <button
            type="button"
            :disabled="!replyText.trim() || sending"
            class="inline-flex min-h-[40px] items-center gap-2 rounded-lg bg-wc-accent px-4 text-xs font-semibold uppercase tracking-wider text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent/40 disabled:cursor-not-allowed disabled:opacity-50"
            @click="send"
          >
            {{ sending ? 'Enviando...' : 'Enviar' }}
          </button>
        </div>
      </footer>
    </aside>
  </Transition>
</template>

<style scoped>
.slide-right-enter-active, .slide-right-leave-active {
  transition: transform 0.3s ease, opacity 0.25s ease;
}
.slide-right-enter-from, .slide-right-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
