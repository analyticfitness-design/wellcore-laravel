<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();

const loading = ref(true);
const sending = ref(false);
const error = ref(null);

// Chat data
const podId = ref(null);
const podName = ref('');
const memberCount = ref(0);
const messages = ref([]);
const newMessage = ref('');

let pollInterval = null;

async function fetchChat() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/rise/chat');
        podId.value = response.data.podId || null;
        podName.value = response.data.podName || '';
        memberCount.value = response.data.memberCount || 0;
        messages.value = response.data.messages || [];
        await nextTick();
        scrollToBottom();
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar chat';
    } finally {
        loading.value = false;
    }
}

async function pollMessages() {
    if (!podId.value) return;
    try {
        const response = await api.get('/api/v/rise/chat');
        messages.value = response.data.messages || [];
        await nextTick();
        scrollToBottom();
    } catch {
        // Silently fail on poll
    }
}

async function sendMessage() {
    if (!newMessage.value.trim() || !podId.value) return;

    sending.value = true;
    try {
        await api.post('/api/v/rise/chat', {
            message: newMessage.value.trim(),
        });
        newMessage.value = '';
        await pollMessages();
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al enviar mensaje';
    } finally {
        sending.value = false;
    }
}

function scrollToBottom() {
    const el = document.getElementById('chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
}

onMounted(async () => {
    await fetchChat();
    // Poll every 5 seconds
    pollInterval = setInterval(pollMessages, 5000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
  <RiseLayout>
    <div v-if="loading" class="space-y-4">
      <div class="h-10 w-64 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="h-[60vh] animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
    </div>

    <div v-else class="flex h-[calc(100vh-10rem)] flex-col space-y-0">
      <!-- Page header -->
      <div class="mb-4">
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">CHAT GRUPAL RISE</h1>
        <div v-if="podName" class="mt-1 flex items-center gap-2">
          <p class="text-sm text-wc-text-tertiary">{{ podName }}</p>
          <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-medium text-emerald-500">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            {{ memberCount }} {{ memberCount === 1 ? 'miembro' : 'miembros' }}
          </span>
        </div>
        <p v-else class="mt-1 text-sm text-wc-text-tertiary">Conecta con tu grupo RISE</p>
      </div>

      <!-- Chat container -->
      <div class="flex flex-1 flex-col overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">

        <!-- Messages area -->
        <div id="chat-messages" class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
          <template v-if="podId">
            <template v-if="messages.length > 0">
              <div v-for="(msg, idx) in messages" :key="idx">
                <!-- Own message -->
                <div v-if="msg.isOwn" class="flex justify-end gap-2">
                  <div class="max-w-[75%] sm:max-w-[60%]">
                    <div class="rounded-2xl rounded-br-sm bg-wc-accent px-4 py-2.5 text-sm text-white">
                      {{ msg.message }}
                    </div>
                    <p class="mt-0.5 text-right text-[10px] text-wc-text-tertiary">{{ msg.time }}</p>
                  </div>
                </div>

                <!-- Other user's message -->
                <div v-else class="flex gap-2">
                  <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-wc-accent/20 to-wc-accent/10">
                    <span class="text-xs font-semibold text-wc-accent">{{ msg.initial }}</span>
                  </div>
                  <div class="max-w-[75%] sm:max-w-[60%]">
                    <p class="mb-0.5 text-[11px] font-medium text-wc-accent">{{ msg.name }}</p>
                    <div class="rounded-2xl rounded-bl-sm bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text">
                      {{ msg.message }}
                    </div>
                    <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ msg.time }}</p>
                  </div>
                </div>
              </div>
            </template>

            <!-- Empty state -->
            <div v-else class="flex h-full flex-col items-center justify-center py-12">
              <div class="flex h-16 w-16 items-center justify-center rounded-full bg-wc-bg-secondary">
                <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
              </div>
              <p class="mt-4 text-sm font-medium text-wc-text">Sin mensajes aun</p>
              <p class="mt-1 text-xs text-wc-text-tertiary">Se el primero en escribir!</p>
            </div>
          </template>

          <!-- No pod assigned -->
          <div v-else class="flex h-full flex-col items-center justify-center py-12">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-wc-accent/10">
              <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
              </svg>
            </div>
            <p class="mt-4 text-sm font-medium text-wc-text">Sin grupo asignado</p>
            <p class="mt-1 text-center text-xs text-wc-text-tertiary">Aun no has sido asignado a un pod de accountability. Tu coach te asignara pronto.</p>
          </div>
        </div>

        <!-- Input bar -->
        <div v-if="podId" class="border-t border-wc-border bg-wc-bg-secondary/50 p-3">
          <form @submit.prevent="sendMessage" class="flex items-center gap-2">
            <input
              type="text"
              v-model="newMessage"
              placeholder="Escribe un mensaje..."
              class="flex-1 rounded-full border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              autocomplete="off"
              @keydown.enter.prevent="sendMessage"
            >
            <button
              type="submit"
              :disabled="sending || !newMessage.trim()"
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover transition-all disabled:opacity-50"
            >
              <svg v-if="!sending" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
              </svg>
              <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
            </button>
          </form>
        </div>
      </div>
    </div>
  </RiseLayout>
</template>
