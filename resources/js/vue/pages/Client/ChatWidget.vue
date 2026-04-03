<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const error = ref(null);
const messages = ref([]);
const coachName = ref('Coach');
const hasCoach = ref(false);

// Message input
const newMessage = ref('');
const sending = ref(false);
const validationErrors = ref({});
const messagesContainer = ref(null);
const textareaRef = ref(null);

// Polling — module-level mutable (not reactive) to avoid proxy overhead
let pollInterval = null;
let isPageVisible = true;

// Fetch chat data
async function fetchChat() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/client/chat');
        const d = response.data;
        messages.value = d.messages || [];
        coachName.value = d.coach_name || 'Coach';
        hasCoach.value = d.has_coach ?? true;
        await scrollToBottom();
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar el chat';
    } finally {
        loading.value = false;
    }
}

// Poll for new messages — visibility-aware (matches Livewire wire:poll.30s.visible)
async function pollMessages() {
    if (!hasCoach.value || !isPageVisible) return;
    try {
        const response = await api.get('/api/v/client/chat');
        const d = response.data;
        const newMsgs = d.messages || [];
        if (newMsgs.length !== messages.value.length) {
            messages.value = newMsgs;
            await scrollToBottom();
        }
    } catch {
        // Fail silently on poll
    }
}

// Visibility change handler — pauses polling when tab/page is hidden
function handleVisibilityChange() {
    isPageVisible = !document.hidden;
}

// Send message
async function sendMessage() {
    const text = newMessage.value.trim();
    if (!text || sending.value) return;

    validationErrors.value = {};
    sending.value = true;
    try {
        const response = await api.post('/api/v/client/chat', {
            message: text,
        });
        newMessage.value = '';
        // Reset textarea height after clearing
        if (textareaRef.value) {
            textareaRef.value.style.height = 'auto';
        }
        if (response.data.message) {
            messages.value.push(response.data.message);
        } else {
            // Re-fetch to get the latest
            await pollMessages();
        }
        await scrollToBottom();
    } catch (err) {
        if (err.response?.status === 422) {
            validationErrors.value = err.response.data.errors || {};
        }
    } finally {
        sending.value = false;
    }
}

// Handle Enter key — send on Enter, newline on Shift+Enter
function handleKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

// Auto-resize textarea as user types
function autoResizeTextarea() {
    const el = textareaRef.value;
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// Scroll to bottom
async function scrollToBottom() {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
}

// Format timestamp — dd/mm HH:mm to match Blade's format('d/m H:i')
function formatTime(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit' }) +
        ' ' + date.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
}

function getCoachInitial() {
    return (coachName.value || 'C').charAt(0).toUpperCase();
}

onMounted(async () => {
    await fetchChat();
    // Poll every 30s to match Livewire wire:poll.30s.visible
    pollInterval = setInterval(pollMessages, 30000);
    // Listen for visibility changes to pause polling when tab is hidden
    document.addEventListener('visibilitychange', handleVisibilityChange);
});

onBeforeUnmount(() => {
    if (pollInterval) clearInterval(pollInterval);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});
</script>

<template>
  <ClientLayout>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="space-y-2">
        <div class="h-9 w-64 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        <div class="h-5 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>
      <div class="h-[32rem] animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </div>
      <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">Error al cargar</h2>
      <p class="mt-2 text-sm text-wc-text-secondary">{{ error }}</p>
      <button
        @click="fetchChat"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
      >
        Reintentar
      </button>
    </div>

    <!-- Chat Content -->
    <div v-else class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">CHAT CON TU COACH</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Comunicate directamente con tu coach</p>
      </div>

      <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
        <!-- Coach Info Bar -->
        <div class="flex items-center gap-3 border-b border-wc-border px-5 py-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/20">
            <span class="text-sm font-bold text-wc-accent">{{ getCoachInitial() }}</span>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-wc-text">{{ coachName }}</p>
            <div v-if="hasCoach" class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-green-500"></span>
              <span class="text-sm text-wc-text-tertiary">Coach</span>
            </div>
            <span v-else class="text-sm text-wc-text-tertiary/70">Sin coach asignado</span>
          </div>
        </div>

        <!-- No coach state -->
        <div v-if="!hasCoach" class="flex flex-col items-center justify-center px-6 py-16 text-center">
          <svg class="h-14 w-14 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
          </svg>
          <h3 class="mt-4 font-display text-xl text-wc-text">SIN COACH ASIGNADO</h3>
          <p class="mt-2 max-w-xs text-sm text-wc-text-secondary">
            Aun no tienes un coach asignado. Contacta a soporte para que te asignen uno.
          </p>
        </div>

        <!-- Messages Area -->
        <template v-else>
          <div
            ref="messagesContainer"
            class="h-[28rem] space-y-4 overflow-y-auto px-5 py-4 sm:h-[32rem]"
          >
            <!-- Empty state -->
            <div v-if="messages.length === 0" class="flex h-full flex-col items-center justify-center text-center">
              <svg class="h-16 w-16 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
              </svg>
              <h3 class="mt-4 font-display text-xl text-wc-text">SIN MENSAJES</h3>
              <p class="mt-2 max-w-xs text-sm text-wc-text-secondary">Envia un mensaje a tu coach para comenzar la conversacion.</p>
            </div>

            <!-- Messages -->
            <template v-for="msg in messages" :key="msg.id">
              <!-- Coach message (left) -->
              <div v-if="msg.direction === 'coach_to_client'" class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-wc-bg-secondary">
                  <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                  </svg>
                </div>
                <div class="max-w-[75%]">
                  <div class="rounded-2xl rounded-tl-sm bg-wc-bg-secondary px-4 py-2.5">
                    <p class="text-sm text-wc-text">{{ msg.message }}</p>
                  </div>
                  <p class="mt-1 px-1 text-xs text-wc-text-tertiary">{{ formatTime(msg.created_at) }}</p>
                </div>
              </div>

              <!-- Client message (right) -->
              <div v-else class="flex items-start justify-end gap-3">
                <div class="max-w-[75%]">
                  <div class="rounded-2xl rounded-tr-sm bg-wc-accent px-4 py-2.5">
                    <p class="text-sm text-white">{{ msg.message }}</p>
                  </div>
                  <p class="mt-1 px-1 text-right text-xs text-wc-text-tertiary">
                    {{ formatTime(msg.created_at) }}
                    <svg v-if="msg.read_at" class="ml-1 inline h-3 w-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                  </p>
                </div>
              </div>
            </template>
          </div>

          <!-- Input Area -->
          <div class="border-t border-wc-border px-4 py-3">
            <form @submit.prevent="sendMessage" class="flex items-end gap-3">
              <div class="flex-1">
                <textarea
                  ref="textareaRef"
                  v-model="newMessage"
                  rows="1"
                  maxlength="2000"
                  placeholder="Escribe un mensaje..."
                  class="block w-full resize-none rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                  :disabled="sending"
                  @keydown="handleKeydown"
                  @input="autoResizeTextarea"
                ></textarea>
                <p v-if="validationErrors.message" class="mt-1 text-xs text-red-500">
                  {{ validationErrors.message[0] }}
                </p>
              </div>
              <button
                type="submit"
                :disabled="sending || !newMessage.trim()"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-wc-accent text-white transition-all hover:bg-wc-accent-hover active:scale-95 disabled:opacity-60"
                aria-label="Enviar mensaje"
              >
                <svg v-if="!sending" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
                <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </button>
            </form>
          </div>
        </template>
      </div>
    </div>
  </ClientLayout>
</template>
