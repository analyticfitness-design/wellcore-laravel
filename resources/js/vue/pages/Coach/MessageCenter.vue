<!-- Message templates — static data from ResponseTemplateService (context: "message") -->
<script>
const MESSAGE_TEMPLATES = [
  { title: 'Bienvenida', body: 'Bienvenido/a a WellCore! Estoy aqui para guiarte en tu transformacion. Revisa tu plan de entrenamiento y no dudes en escribirme si tienes preguntas.' },
  { title: 'Recordatorio check-in', body: 'Recuerda completar tu check-in semanal para que pueda evaluar tu progreso y ajustar el plan si es necesario. Tu feedback es clave para tus resultados.' },
  { title: 'Felicitacion general', body: 'Queria felicitarte por tu compromiso y dedicacion. Los resultados se estan notando y quiero que sepas que tu esfuerzo vale la pena. Sigue asi!' },
  { title: 'Recordatorio rutina', body: 'Recuerda que tu nueva rutina ya esta disponible en el dashboard. Revisa los ejercicios y avisame si tienes alguna duda antes de empezar.' },
  { title: 'Seguimiento semanal', body: 'Como vas con el entrenamiento esta semana? Queria hacer un seguimiento rapido. Cuentame como te has sentido y si has tenido algun problema con los ejercicios.' },
  { title: 'Disponibilidad', body: 'Estoy disponible para resolver cualquier duda que tengas. Puedes escribirme por aqui o crear un ticket si necesitas algo especifico. Estamos para ayudarte.' },
];
</script>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useApi } from '../../composables/useApi';
import { useAuthStore } from '../../stores/auth';
import { useSmartPolling } from '../../composables/useSmartPolling';
import CoachLayout from '../../layouts/CoachLayout.vue';
import WcPageHeader from '../../components/WcPageHeader.vue';
import AvatarConic from '../../components/coach/ios/AvatarConic.vue';
import EmptyState from '../../components/coach/ios/EmptyState.vue';

const api = useApi();
const authStore = useAuthStore();

const loading = ref(true);
const clients = ref([]);
const selectedClientId = ref(null);
const selectedClient = ref(null);
const messages = ref([]);
const newMessage = ref('');
const sending = ref(false);
const clientsError = ref(false);
const messagesError = ref(false);
const sendError = ref('');

// Coach id — needed to build the channel name
const coachId = ref(Number(authStore.userId) || null);

// Template selector state
const templateOpen = ref(false);
const templateSearch = ref('');
const templateSearchInput = ref(null);

// Module-level mutable handles — NOT reactive refs
let echoChannel = null;
let smartPolling = null;
let sidebarInterval = null;

// Filtered templates based on search
const filteredTemplates = computed(() => {
  if (!templateSearch.value) return MESSAGE_TEMPLATES;
  const q = templateSearch.value.toLowerCase();
  return MESSAGE_TEMPLATES.filter(
    (t) => t.title.toLowerCase().includes(q) || t.body.toLowerCase().includes(q)
  );
});

function toggleTemplates() {
  templateOpen.value = !templateOpen.value;
  if (templateOpen.value) {
    templateSearch.value = '';
    nextTick(() => templateSearchInput.value?.focus());
  }
}

function selectTemplate(template) {
  newMessage.value = template.body;
  templateOpen.value = false;
  templateSearch.value = '';
}

// Unsubscribe from the current Echo channel (if any) before switching clients
function leaveCurrentEchoChannel() {
  if (echoChannel && coachId.value && selectedClientId.value) {
    const channelName = `conversation.${coachId.value}-${selectedClientId.value}`;
    window.Echo?.leave(channelName);
    echoChannel = null;
  }
}

// Subscribe Echo to the active conversation channel
function subscribeEcho(clientId) {
  if (!window.Echo || !coachId.value || !clientId) return;
  const channelName = `conversation.${coachId.value}-${clientId}`;
  echoChannel = window.Echo.private(channelName);

  echoChannel.listen('.message.sent', async (event) => {
    // Only append if this message belongs to the currently open conversation
    if (selectedClientId.value !== clientId) return;
    if (messages.value.some((m) => m.id === event.message?.id)) return;
    if (event.message) {
      messages.value.push(event.message);
      await nextTick();
      scrollToBottom();
    }
    // Refresh unread count in sidebar too
    loadClients();
  });

  echoChannel.listen('.reaction.toggled', (event) => {
    if (!event.message_id || !event.counts) return;
    const msg = messages.value.find((m) => m.id === event.message_id);
    if (msg) msg.reactions = event.counts;
  });
}

function selectClient(client) {
  // Stop smart polling and leave previous Echo channel
  if (smartPolling) {
    smartPolling.stop();
    smartPolling = null;
  }
  leaveCurrentEchoChannel();

  selectedClientId.value = client.id;
  selectedClient.value = client;
  loadMessages(client.id);
}

async function loadClients() {
  try {
    const { data } = await api.get('/api/v/coach/messages');
    clients.value = data.clients || [];
    clientsError.value = false;
    // Sync coachId from API response if not already set from auth store
    if (!coachId.value && data.coach_id) {
      coachId.value = Number(data.coach_id);
    }
  } catch {
    clientsError.value = true;
  }
}

async function loadMessages(clientId) {
  messagesError.value = false;
  try {
    const { data } = await api.get(`/api/v/coach/messages?client_id=${clientId}`);
    messages.value = data.conversation || [];
    // Update unread count for this client in the sidebar after marking as read
    const idx = clients.value.findIndex((c) => c.id === clientId);
    if (idx !== -1) clients.value[idx].unread_count = 0;
    await nextTick();
    scrollToBottom();

    // Set up realtime or polling for the selected conversation
    if (window.Echo) {
      subscribeEcho(clientId);
    } else {
      smartPolling = useSmartPolling(
        () => pollMessagesCallback(clientId),
        { min: 10_000, max: 60_000, start: 15_000 }
      );
    }
  } catch {
    messagesError.value = true;
  }
}

// Smart polling callback for a specific client conversation
async function pollMessagesCallback(clientId) {
  // Only poll for the currently selected client
  if (selectedClientId.value !== clientId) return { messageCount: 0 };
  try {
    const { data } = await api.get(`/api/v/coach/messages?client_id=${clientId}`);
    const incoming = data.conversation || [];
    const count = incoming.length;
    if (count !== messages.value.length) {
      messages.value = incoming;
      await nextTick();
      scrollToBottom();
    }
    return { messageCount: count };
  } catch {
    return { messageCount: messages.value.length };
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
  sendError.value = '';
  const text = newMessage.value;
  try {
    await api.post('/api/v/coach/messages', {
      client_id: selectedClientId.value,
      message: text,
    });
    newMessage.value = '';
    // Always push locally for immediate feedback — Echo's NewMessageSent
    // only broadcasts metadata (sender_id, preview…), not the full message
    // object, so waiting for Echo would leave the conversation blank.
    messages.value.push({
      id: Date.now(),
      message: text,
      is_coach: true,
      time: 'Ahora',
    });
    await nextTick();
    scrollToBottom();
  } catch {
    // Restore typed text so the coach doesn't lose their message
    newMessage.value = text;
    sendError.value = 'No se pudo enviar el mensaje.';
  } finally {
    sending.value = false;
  }
}

function handleKeydown(e) {
  if (e.key === 'Escape' && templateOpen.value) {
    templateOpen.value = false;
  }
}

onMounted(async () => {
  await loadClients();
  loading.value = false;

  sidebarInterval = setInterval(loadClients, 30_000);
  document.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
  leaveCurrentEchoChannel();
  clearInterval(sidebarInterval);
  sidebarInterval = null;
  if (smartPolling) {
    smartPolling.stop();
    smartPolling = null;
  }
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <WcPageHeader contextLabel="PRINCIPAL" title="MENSAJES" subtitle="Conversaciones con tus clientes" />

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12" style="min-height: 70vh;">
          <div class="lg:col-span-4 space-y-0">
            <div v-for="n in 5" :key="n" class="animate-pulse border-b border-wc-border bg-wc-bg-tertiary px-4 py-4 first:rounded-t-xl last:rounded-b-xl last:border-b-0">
              <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-wc-border/50"></div>
                <div class="flex-1 space-y-2">
                  <div class="h-3 w-24 rounded bg-wc-border/50"></div>
                  <div class="h-2.5 w-36 rounded bg-wc-border/30"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="lg:col-span-8 animate-pulse rounded-card border border-wc-border bg-wc-bg-tertiary flex items-center justify-center">
            <div class="h-16 w-16 rounded-full bg-wc-border/30"></div>
          </div>
        </div>
      </template>

      <!-- Message center layout -->
      <div v-else class="anim-entry anim-entry-2 grid grid-cols-1 gap-4 lg:grid-cols-12" style="min-height: 70vh;">

        <!-- Client list panel -->
        <div class="rounded-[14px] border border-[var(--b1)] lg:col-span-4 overflow-hidden flex flex-col" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
          <div class="border-b border-[var(--b1)] px-4 py-3">
            <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">Clientes</p>
          </div>
          <div class="flex-1 overflow-y-auto">
            <div v-if="clientsError" class="px-4 py-3 text-center text-xs text-red-400">
              Error al cargar clientes.
              <button @click="loadClients" class="ml-1 underline hover:no-underline">Reintentar</button>
            </div>
            <ul v-if="clients.length > 0" class="divide-y divide-[var(--b1)]">
              <li v-for="client in clients" :key="client.id">
                <button
                  @click="selectClient(client)"
                  class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors"
                  :class="selectedClientId === client.id ? 'bg-wc-accent/5 border-l-2 border-l-wc-accent' : 'hover:bg-wc-bg-secondary/50'"
                >
                  <!-- Avatar conic with unread indicator -->
                  <div class="relative shrink-0">
                    <AvatarConic
                      :initial="(client.name || 'C').charAt(0).toUpperCase()"
                      :image-url="client.avatar_url || client.photo_url || ''"
                      tone="accent"
                      size="sm"
                    />
                    <div v-if="client.unread_count > 0" class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-wc-accent text-[9px] font-bold text-white">
                      {{ client.unread_count > 9 ? '9+' : client.unread_count }}
                    </div>
                  </div>
                  <!-- Name + preview -->
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
            <EmptyState
              v-else
              kind="messages"
              title="Sin clientes asignados"
              subtitle="Cuando se te asigne un cliente, aparecerá aquí."
            />
          </div>
        </div>

        <!-- Conversation panel -->
        <div class="rounded-[14px] border border-[var(--b1)] lg:col-span-8 overflow-hidden flex flex-col" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
          <template v-if="selectedClient">
            <!-- Conversation header -->
            <div class="flex items-center gap-3 border-b border-[var(--b1)] px-4 py-3">
              <AvatarConic
                :initial="(selectedClient.name || 'C').charAt(0).toUpperCase()"
                :image-url="selectedClient.avatar_url || selectedClient.photo_url || ''"
                tone="accent"
                size="sm"
              />
              <div class="flex-1">
                <p class="text-sm font-medium text-wc-text">{{ selectedClient.name }}</p>
                <p class="text-xs text-wc-text-tertiary">{{ selectedClient.plan || 'Sin plan' }}</p>
              </div>
              <!-- Realtime indicator -->
              <div v-if="echoChannel" class="flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[10px] font-medium text-emerald-500">En vivo</span>
              </div>
            </div>

            <!-- Messages load error -->
            <div v-if="messagesError" class="flex items-center justify-between border-b border-wc-border bg-red-500/10 px-4 py-2 text-xs text-red-400">
              <span>No se pudieron cargar los mensajes.</span>
              <button @click="loadMessages(selectedClientId)" class="ml-2 shrink-0 underline hover:no-underline">Reintentar</button>
            </div>

            <!-- Messages -->
            <div id="messages-container" class="flex-1 overflow-y-auto px-4 py-4 space-y-3" style="max-height: 55vh;">
              <template v-if="messages.length > 0">
                <div v-for="msg in messages" :key="msg.id" class="flex" :class="msg.is_coach ? 'justify-end' : 'justify-start'">
                  <div
                    class="max-w-[75%] rounded-card px-3 py-2"
                    :class="msg.is_coach ? 'bg-wc-accent text-white' : 'bg-wc-bg-secondary text-wc-text'"
                  >
                    <p class="text-sm leading-relaxed">{{ msg.message }}</p>
                    <p class="mt-1 text-[10px]" :class="msg.is_coach ? 'text-white/60' : 'text-wc-text-tertiary'">{{ msg.time }}</p>
                  </div>
                </div>
              </template>
              <EmptyState
                v-else
                kind="messages"
                title="Inicia la conversación"
                subtitle="Envía el primer mensaje para empezar el chat."
              />
            </div>

            <!-- Message input with template selector -->
            <div class="border-t border-[var(--b1)] px-4 py-3">
              <p v-if="sendError" class="mb-2 text-xs text-red-400">{{ sendError }}</p>
              <form @submit.prevent="sendMessage" class="flex items-center gap-2">
                <!-- Template selector -->
                <div class="relative inline-flex">
                  <button
                    @click="toggleTemplates"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-button bg-wc-bg-secondary px-3 py-2.5 text-xs font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text border border-wc-border transition-colors"
                    title="Plantillas de respuesta rapida"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="hidden sm:inline">Plantillas</span>
                  </button>

                  <!-- Template dropdown -->
                  <Transition name="fade">
                    <div
                      v-if="templateOpen"
                      class="absolute bottom-full mb-2 left-0 z-50 w-80 rounded-card border border-wc-border bg-wc-bg shadow-2xl"
                    >
                      <!-- Search header -->
                      <div class="border-b border-wc-border p-3">
                        <div class="relative">
                          <svg class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                          </svg>
                          <input
                            ref="templateSearchInput"
                            v-model="templateSearch"
                            type="text"
                            placeholder="Buscar plantilla..."
                            class="w-full rounded-button border border-wc-border bg-wc-bg-secondary py-1.5 pl-8 pr-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                            @keydown.escape="templateOpen = false"
                          />
                        </div>
                      </div>

                      <!-- Template list -->
                      <div class="max-h-72 overflow-y-auto p-1.5">
                        <button
                          v-for="(tpl, idx) in filteredTemplates"
                          :key="idx"
                          @click="selectTemplate(tpl)"
                          type="button"
                          class="w-full rounded-button px-2.5 py-2 text-left transition-colors hover:bg-wc-bg-secondary group"
                        >
                          <span class="text-sm font-medium text-wc-text group-hover:text-wc-accent transition-colors">
                            {{ tpl.title }}
                          </span>
                          <p class="mt-0.5 text-xs leading-relaxed text-wc-text-tertiary line-clamp-2">
                            {{ tpl.body }}
                          </p>
                        </button>

                        <!-- No results -->
                        <div v-if="filteredTemplates.length === 0" class="px-3 py-6 text-center">
                          <p class="text-xs text-wc-text-tertiary">No se encontraron plantillas para "<span class="font-medium text-wc-text-secondary">{{ templateSearch }}</span>"</p>
                        </div>
                      </div>

                      <!-- Footer hint -->
                      <div class="border-t border-wc-border px-3 py-2">
                        <p class="text-[10px] text-wc-text-tertiary text-center">
                          {{ MESSAGE_TEMPLATES.length }} plantillas disponibles
                        </p>
                      </div>
                    </div>
                  </Transition>
                </div>

                <!-- Click-outside overlay to close template dropdown -->
                <Teleport to="body">
                  <div
                    v-if="templateOpen"
                    class="fixed inset-0 z-40"
                    @click="templateOpen = false"
                  ></div>
                </Teleport>

                <input
                  v-model="newMessage"
                  type="text"
                  placeholder="Escribe un mensaje..."
                  class="flex-1 rounded-button border border-wc-border bg-wc-bg-secondary py-2.5 px-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                  :disabled="sending"
                />
                <button
                  type="submit"
                  :disabled="sending || !newMessage.trim()"
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-button bg-wc-accent text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
                >
                  <svg v-if="!sending" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                  </svg>
                  <svg v-else class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                </button>
              </form>
            </div>
          </template>

          <!-- No client selected -->
          <div v-else class="flex flex-1 items-center justify-center">
            <EmptyState
              kind="messages"
              title="Selecciona un cliente"
              subtitle="Elige un cliente del panel izquierdo para ver la conversación."
            />
          </div>
        </div>
      </div>
    </div>
  </CoachLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: scale(0.95); }
</style>
