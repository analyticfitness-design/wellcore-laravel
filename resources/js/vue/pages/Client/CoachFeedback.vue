<template>
  <ClientLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">COACH FEEDBACK</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Califica a tu coach y comparte tu experiencia</p>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 animate-pulse">
          <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-full bg-wc-border"></div>
            <div class="space-y-2 flex-1">
              <div class="h-3 w-20 rounded bg-wc-border"></div>
              <div class="h-5 w-40 rounded bg-wc-border"></div>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 animate-pulse h-48"></div>
      </template>

      <!-- Error -->
      <div v-else-if="fetchError" class="rounded-xl border border-wc-accent/30 bg-wc-accent/10 p-6 text-center">
        <p class="text-sm text-wc-accent">{{ fetchError }}</p>
        <button @click="fetchData" class="mt-3 text-sm text-wc-text-secondary hover:text-wc-text transition-colors">
          Reintentar
        </button>
      </div>

      <template v-else>

        <!-- No coach assigned -->
        <div v-if="!data.coachId" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-10 text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-wc-accent/10">
            <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
          </div>
          <h2 class="mb-2 font-display text-xl tracking-wide text-wc-text">Sin coach asignado</h2>
          <p class="mb-6 text-sm text-wc-text-secondary">No tienes un coach asignado todavía. Escríbenos para que te conectemos con el coach perfecto para tu objetivo.</p>
          <router-link
            to="/client/chat"
            class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
            </svg>
            Ir al Chat
          </router-link>
        </div>

        <template v-else>

          <!-- Coach info card -->
          <div v-if="data.coach" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center gap-4">
              <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-wc-accent/20 overflow-hidden">
                <img
                  v-if="data.coach.photo_url"
                  :src="data.coach.photo_url"
                  :alt="data.coach.name ?? ''"
                  class="h-14 w-14 object-cover"
                  loading="lazy"
                  decoding="async"
                />
                <span v-else class="font-display text-2xl text-wc-accent">{{ data.coach.name?.charAt(0) }}</span>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Tu Coach</p>
                <h2 class="font-display text-xl tracking-wide text-wc-text">{{ data.coach.name }}</h2>
                <p v-if="data.coach.city" class="text-sm text-wc-text-secondary">{{ data.coach.city }}</p>
              </div>
              <div v-if="data.ratings?.length" class="ml-auto text-right">
                <p class="font-display text-3xl tracking-wide text-yellow-400">{{ avgRating }}</p>
                <p class="text-xs text-wc-text-secondary">
                  {{ data.ratings.length }} {{ data.ratings.length === 1 ? 'valoración' : 'valoraciones' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Success flash -->
          <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
          >
            <div v-if="showSuccess" class="flex items-center justify-between rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3">
              <div class="flex items-center gap-2 text-sm text-green-400">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Valoración enviada. ¡Gracias por tu feedback!
              </div>
              <button @click="showSuccess = false" class="text-green-400 hover:text-green-300 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </Transition>

          <!-- Rating form -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="mb-4 font-display text-lg tracking-wide text-wc-text">NUEVA VALORACIÓN</h2>

            <div class="space-y-5">
              <!-- Star selector -->
              <div>
                <label class="mb-2 block text-sm font-medium text-wc-text">
                  Calificación <span class="text-wc-accent">*</span>
                </label>
                <div class="flex items-center gap-1">
                  <button
                    v-for="i in 5"
                    :key="i"
                    type="button"
                    @mouseenter="hoveredStar = i"
                    @mouseleave="hoveredStar = 0"
                    @click="form.rating = i"
                    class="transition-transform hover:scale-110 focus:outline-none"
                    :title="`${i} ${i === 1 ? 'estrella' : 'estrellas'}`"
                  >
                    <svg
                      class="h-9 w-9 transition-colors"
                      :class="(hoveredStar >= i || form.rating >= i) ? 'text-yellow-400 fill-yellow-400' : 'text-wc-border fill-transparent'"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                  </button>

                  <!-- Star label -->
                  <Transition
                    enter-active-class="transition duration-200"
                    enter-from-class="opacity-0 translate-x-2"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition duration-150"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                  >
                    <span v-if="form.rating > 0" class="ml-3 text-sm text-wc-text-secondary">
                      {{ starLabels[form.rating - 1] }}
                    </span>
                  </Transition>
                </div>
                <p v-if="formErrors.rating" class="mt-1 text-xs text-wc-accent">{{ formErrors.rating }}</p>
              </div>

              <!-- Comment textarea -->
              <div>
                <label for="coach-comment" class="mb-2 block text-sm font-medium text-wc-text">
                  Comentario <span class="text-wc-text-secondary">(opcional)</span>
                </label>
                <textarea
                  id="coach-comment"
                  v-model="form.comment"
                  rows="3"
                  placeholder="Comparte tu experiencia con tu coach..."
                  class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-secondary/50 focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none transition-colors"
                  :class="{ 'border-wc-accent': formErrors.comment }"
                ></textarea>
                <p v-if="formErrors.comment" class="mt-1 text-xs text-wc-accent">{{ formErrors.comment }}</p>
              </div>

              <!-- Submit -->
              <div class="flex items-center gap-3">
                <button
                  @click="submitRating"
                  :disabled="submitting"
                  class="rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                >
                  {{ submitting ? 'Enviando...' : 'Enviar Valoración' }}
                </button>
                <button
                  v-if="form.rating > 0"
                  @click="clearForm"
                  type="button"
                  class="text-sm text-wc-text-secondary hover:text-wc-text transition-colors"
                >
                  Limpiar
                </button>
              </div>
            </div>
          </div>

          <!-- Rating history -->
          <div v-if="data.ratings?.length" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="mb-4 font-display text-lg tracking-wide text-wc-text">HISTORIAL DE VALORACIONES</h2>
            <div class="space-y-3">
              <div
                v-for="entry in data.ratings"
                :key="entry.id"
                class="rounded-lg border border-wc-border bg-wc-bg p-4"
              >
                <div class="flex items-start justify-between gap-3">
                  <div class="flex gap-0.5">
                    <svg
                      v-for="s in 5"
                      :key="s"
                      class="h-4 w-4"
                      :class="s <= entry.rating ? 'text-yellow-400 fill-yellow-400' : 'text-wc-border fill-transparent'"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                  </div>
                  <span class="text-xs text-wc-text-secondary">{{ entry.created_at }}</span>
                </div>
                <p v-if="entry.comment" class="mt-2 text-sm text-wc-text-secondary leading-relaxed">{{ entry.comment }}</p>
              </div>
            </div>
          </div>

          <!-- Empty history -->
          <div v-else class="rounded-xl border border-dashed border-wc-border p-6 text-center">
            <p class="text-sm text-wc-text-secondary">Aún no has enviado ninguna valoración. ¡Sé el primero en calificar a tu coach!</p>
          </div>

        </template>
      </template>

    </div>
  </ClientLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import ClientLayout from '../../layouts/ClientLayout.vue';
import { useApi } from '../../composables/useApi';

const api = useApi();

// State
const loading   = ref(true);
const fetchError = ref(null);
const data      = reactive({ coachId: null, coach: null, ratings: [] });

// Form
const form        = reactive({ rating: 0, comment: '' });
const hoveredStar = ref(0);
const formErrors  = reactive({ rating: null, comment: null });
const submitting  = ref(false);
const showSuccess = ref(false);

const starLabels = ['Malo', 'Regular', 'Bueno', 'Muy bueno', 'Excelente'];

const avgRating = computed(() => {
  if (!data.ratings?.length) return '0.0';
  const sum = data.ratings.reduce((acc, r) => acc + r.rating, 0);
  return (sum / data.ratings.length).toFixed(1);
});

async function fetchData() {
  loading.value   = true;
  fetchError.value = null;
  try {
    const response = await api.get('/api/v/client/coach-feedback');
    data.coachId = response.data.coachId ?? null;
    data.coach   = response.data.coach   ?? null;
    data.ratings = response.data.ratings ?? [];
  } catch (err) {
    fetchError.value = err?.response?.data?.message || 'Error al cargar los datos. Intenta de nuevo.';
  } finally {
    loading.value = false;
  }
}

async function submitRating() {
  // Clear previous errors
  formErrors.rating  = null;
  formErrors.comment = null;

  if (!form.rating) {
    formErrors.rating = 'Selecciona una calificación de 1 a 5 estrellas.';
    return;
  }

  submitting.value = true;
  try {
    const response = await api.post('/api/v/client/coach-feedback', {
      rating:  form.rating,
      comment: form.comment || null,
    });
    // Prepend new rating to history
    if (response.data.rating) data.ratings.unshift(response.data.rating);
    clearForm();
    showSuccess.value = true;
    setTimeout(() => { showSuccess.value = false; }, 5000);
  } catch (err) {
    const errors = err?.response?.data?.errors;
    if (errors?.rating?.[0])  formErrors.rating  = errors.rating[0];
    if (errors?.comment?.[0]) formErrors.comment = errors.comment[0];
    // Handle cooldown message
    if (err?.response?.data?.message) formErrors.rating = err.response.data.message;
  } finally {
    submitting.value = false;
  }
}

function clearForm() {
  form.rating     = 0;
  form.comment    = '';
  hoveredStar.value = 0;
  formErrors.rating  = null;
  formErrors.comment = null;
}

onMounted(fetchData);
</script>
