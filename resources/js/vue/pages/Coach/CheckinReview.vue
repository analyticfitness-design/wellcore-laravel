<script setup>
import { ref, onMounted, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const checkins = ref([]);
const showReplied = ref(false);
const replyingTo = ref(null);
const replyText = ref('');
const submitting = ref(false);

const pendingCount = computed(() => checkins.value.filter(c => !c.coach_reply).length);

const filteredCheckins = computed(() => {
    if (showReplied.value) return checkins.value;
    return checkins.value.filter(c => !c.coach_reply);
});

function startReply(id) {
    replyingTo.value = id;
    replyText.value = '';
}

function cancelReply() {
    replyingTo.value = null;
    replyText.value = '';
}

async function submitReply(checkinId) {
    if (!replyText.value.trim()) return;
    submitting.value = true;
    try {
        await api.post(`/api/v/coach/checkins/${checkinId}/reply`, { reply: replyText.value });
        const checkin = checkins.value.find(c => c.id === checkinId);
        if (checkin) {
            checkin.coach_reply = replyText.value;
            checkin.replied_at = 'Ahora';
        }
        replyingTo.value = null;
        replyText.value = '';
    } catch (e) {
        // silent
    } finally {
        submitting.value = false;
    }
}

async function loadCheckins() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/checkins');
        checkins.value = data.checkins || [];
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

onMounted(loadCheckins);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Check-ins</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">{{ pendingCount }} check-in{{ pendingCount !== 1 ? 's' : '' }} pendiente{{ pendingCount !== 1 ? 's' : '' }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="showReplied = !showReplied"
            class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition-colors"
            :class="showReplied ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            {{ showReplied ? 'Mostrando todos' : 'Solo pendientes' }}
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <!-- Check-in cards -->
      <div v-else-if="filteredCheckins.length > 0" class="space-y-4">
        <div
          v-for="checkin in filteredCheckins"
          :key="checkin.id"
          class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden"
          :class="{ 'border-l-2 border-l-orange-500': !checkin.coach_reply }"
        >
          <!-- Header -->
          <div class="flex items-center gap-4 p-4 sm:p-5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
              <span class="text-sm font-semibold text-wc-accent">{{ (checkin.client_name || 'C').charAt(0) }}</span>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <p class="text-sm font-medium text-wc-text">{{ checkin.client_name }}</p>
                <span class="inline-flex shrink-0 rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">{{ checkin.client_plan }}</span>
                <span v-if="!checkin.coach_reply" class="inline-flex shrink-0 rounded-full bg-orange-500/10 px-2 py-0.5 text-[10px] font-semibold text-orange-500">Pendiente</span>
                <span v-else class="inline-flex shrink-0 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-500">Respondido</span>
              </div>
              <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ checkin.checkin_date }} -- {{ checkin.checkin_date_ago }}</p>
            </div>
          </div>

          <!-- Metrics -->
          <div class="border-t border-wc-border bg-wc-bg-secondary/30 px-4 py-4 sm:px-5">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
              <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Bienestar</p>
                <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ checkin.bienestar ?? '-' }}</p>
                <div class="mt-1 h-1 w-full rounded-full bg-wc-bg-secondary">
                  <div class="h-1 rounded-full" :class="(checkin.bienestar || 0) >= 7 ? 'bg-emerald-500' : (checkin.bienestar || 0) >= 4 ? 'bg-yellow-500' : 'bg-red-500'" :style="{ width: Math.min((checkin.bienestar || 0) * 10, 100) + '%' }"></div>
                </div>
              </div>
              <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Dias entrenados</p>
                <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ checkin.dias_entrenados ?? '-' }}</p>
                <p class="mt-1 text-[10px] text-wc-text-tertiary">de 7 dias</p>
              </div>
              <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Nutricion</p>
                <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ checkin.nutricion ?? '-' }}</p>
                <div class="mt-1 h-1 w-full rounded-full bg-wc-bg-secondary">
                  <div class="h-1 rounded-full" :class="(checkin.nutricion || 0) >= 7 ? 'bg-emerald-500' : (checkin.nutricion || 0) >= 4 ? 'bg-yellow-500' : 'bg-red-500'" :style="{ width: Math.min((checkin.nutricion || 0) * 10, 100) + '%' }"></div>
                </div>
              </div>
              <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">RPE</p>
                <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ checkin.rpe ?? '-' }}</p>
                <p class="mt-1 text-[10px] text-wc-text-tertiary">esfuerzo percibido</p>
              </div>
            </div>

            <!-- Comment -->
            <div v-if="checkin.comentario" class="mt-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
              <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Comentario del cliente</p>
              <p class="mt-2 text-sm text-wc-text-secondary leading-relaxed">{{ checkin.comentario }}</p>
            </div>

            <!-- Coach reply (existing) -->
            <div v-if="checkin.coach_reply" class="mt-4 rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-3">
              <div class="flex items-center gap-2">
                <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-500">Tu respuesta</p>
                <span v-if="checkin.replied_at" class="text-[10px] text-wc-text-tertiary">-- {{ checkin.replied_at }}</span>
              </div>
              <p class="mt-2 text-sm text-wc-text-secondary leading-relaxed">{{ checkin.coach_reply }}</p>
            </div>

            <!-- Reply form -->
            <div v-if="!checkin.coach_reply">
              <div v-if="replyingTo === checkin.id" class="mt-4 space-y-3">
                <label class="text-xs font-medium text-wc-text-secondary">Tu respuesta</label>
                <textarea
                  v-model="replyText"
                  rows="3"
                  placeholder="Escribe tu respuesta al check-in..."
                  class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"
                ></textarea>
                <div class="flex items-center gap-2">
                  <button
                    @click="submitReply(checkin.id)"
                    :disabled="submitting || !replyText.trim()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    Enviar respuesta
                  </button>
                  <button @click="cancelReply" class="inline-flex items-center rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                    Cancelar
                  </button>
                </div>
              </div>
              <div v-else class="mt-4">
                <button
                  @click="startReply(checkin.id)"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                  </svg>
                  Responder
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-emerald-500/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <p class="mt-3 text-sm font-medium text-wc-text">{{ showReplied ? 'No hay check-ins registrados' : 'Todos los check-ins respondidos' }}</p>
        <p class="mt-1 text-xs text-wc-text-tertiary">{{ showReplied ? 'Tus clientes aun no han enviado check-ins' : 'Excelente trabajo -- tus clientes estan al dia' }}</p>
      </div>
    </div>
  </CoachLayout>
</template>
