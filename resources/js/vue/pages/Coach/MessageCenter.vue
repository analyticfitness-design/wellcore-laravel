<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const clients = ref([]);
const selectedClientId = ref(null);
const selectedClient = ref(null);
const messages = ref([]);
const newMessage = ref('');
const sending = ref(false);
let pollInterval = null;

function selectClient(client) {
    selectedClientId.value = client.id;
    selectedClient.value = client;
    loadMessages(client.id);
}

async function loadClients() {
    try {
        const { data } = await api.get('/api/v/coach/messages');
        clients.value = data.clients || [];
    } catch (e) {
        // silent
    }
}

async function loadMessages(clientId) {
    try {
        const { data } = await api.get(`/api/v/coach/messages?client_id=${clientId}`);
        messages.value = data.messages || [];
        await nextTick();
        scrollToBottom();
    } catch (e) {
        // silent
    }
}

function scrollToBottom() {
    const container = document.getElementById('messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

async function sendMessage() {
    if (!newMessage.value.trim() || !selectedClientId.value) return;
    sending.value = true;
    try {
        await api.post('/api/v/coach/messages', {
            client_id: selectedClientId.value,
            message: newMessage.value,
        });
        messages.value.push({
            id: Date.now(),
            message: newMessage.value,
            is_coach: true,
            time: 'Ahora',
        });
        newMessage.value = '';
        await nextTick();
        scrollToBottom();
    } catch (e) {
        // silent
    } finally {
        sending.value = false;
    }
}

onMounted(async () => {
    await loadClients();
    loading.value = false;
    // Poll for new messages
    pollInterval = setInterval(() => {
        if (selectedClientId.value) {
            loadMessages(selectedClientId.value);
        }
        loadClients();
    }, 10000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mensajes</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Comunicacion con tus clientes</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <!-- Message center layout -->
      <div v-else class="grid grid-cols-1 gap-4 lg:grid-cols-12" style="min-height: 70vh;">

        <!-- Client list panel -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary lg:col-span-4 overflow-hidden flex flex-col">
          <div class="border-b border-wc-border px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Clientes</p>
          </div>
          <div class="flex-1 overflow-y-auto">
            <ul v-if="clients.length > 0" class="divide-y divide-wc-border">
              <li v-for="client in clients" :key="client.id">
                <button
                  @click="selectClient(client)"
                  class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors"
                  :class="selectedClientId === client.id ? 'bg-wc-accent/5 border-l-2 border-l-wc-accent' : 'hover:bg-wc-bg-secondary/50'"
                >
                  <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                    <span class="text-sm font-semibold text-wc-accent">{{ (client.name || 'C').charAt(0) }}</span>
                    <div v-if="client.unread_count > 0" class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-wc-accent text-[9px] font-bold text-white">
                      {{ client.unread_count > 9 ? '9+' : client.unread_count }}
                    </div>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                      <p class="text-sm font-medium text-wc-text truncate" :class="{ 'font-semibold': client.unread_count > 0 }">{{ client.name }}</p>
                      <span v-if="client.last_message_time" class="shrink-0 text-[10px] text-wc-text-tertiary">{{ client.last_message_time }}</span>
                    </div>
                    <p class="text-xs text-wc-text-tertiary truncate" :class="{ 'text-wc-text-secondary font-medium': client.unread_count > 0 }">{{ client.last_message_preview || 'Sin mensajes' }}</p>
                  </div>
                </button>
              </li>
            </ul>
            <div v-else class="flex flex-col items-center justify-center py-12 text-center px-4">
              <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
              <p class="mt-2 text-sm text-wc-text-tertiary">Sin clientes asignados</p>
            </div>
          </div>
        </div>

        <!-- Conversation panel -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary lg:col-span-8 overflow-hidden flex flex-col">
          <template v-if="selectedClient">
            <!-- Conversation header -->
            <div class="flex items-center gap-3 border-b border-wc-border px-4 py-3">
              <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                <span class="text-sm font-semibold text-wc-accent">{{ (selectedClient.name || 'C').charAt(0) }}</span>
              </div>
              <div>
                <p class="text-sm font-medium text-wc-text">{{ selectedClient.name }}</p>
                <p class="text-xs text-wc-text-tertiary">{{ selectedClient.plan_label || 'Cliente' }}</p>
              </div>
            </div>

            <!-- Messages -->
            <div id="messages-container" class="flex-1 overflow-y-auto px-4 py-4 space-y-3" style="max-height: 55vh;">
              <template v-if="messages.length > 0">
                <div v-for="msg in messages" :key="msg.id" class="flex" :class="msg.is_coach ? 'justify-end' : 'justify-start'">
                  <div
                    class="max-w-[75%] rounded-xl px-4 py-2.5"
                    :class="msg.is_coach ? 'bg-wc-accent text-white rounded-br-sm' : 'bg-wc-bg-secondary text-wc-text rounded-bl-sm'"
                  >
                    <p class="text-sm leading-relaxed">{{ msg.message }}</p>
                    <p class="mt-1 text-[10px]" :class="msg.is_coach ? 'text-white/60' : 'text-wc-text-tertiary'">{{ msg.time }}</p>
                  </div>
                </div>
              </template>
              <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <p class="mt-2 text-sm text-wc-text-tertiary">Inicia la conversacion</p>
              </div>
            </div>

            <!-- Message input -->
            <div class="border-t border-wc-border px-4 py-3">
              <form @submit.prevent="sendMessage" class="flex items-center gap-2">
                <input
                  v-model="newMessage"
                  type="text"
                  placeholder="Escribe un mensaje..."
                  class="flex-1 rounded-lg border border-wc-border bg-wc-bg-secondary py-2.5 px-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                  :disabled="sending"
                />
                <button
                  type="submit"
                  :disabled="sending || !newMessage.trim()"
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-accent text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                  </svg>
                </button>
              </form>
            </div>
          </template>

          <!-- No client selected -->
          <div v-else class="flex flex-1 flex-col items-center justify-center py-12 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-wc-bg-secondary">
              <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
              </svg>
            </div>
            <p class="mt-4 text-sm font-medium text-wc-text">Selecciona un cliente</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Elige un cliente del panel izquierdo para ver la conversacion</p>
          </div>
        </div>
      </div>
    </div>
  </CoachLayout>
</template>
