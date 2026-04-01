<script setup>
import { ref, onMounted, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(false);
const sending = ref(false);
const success = ref(false);

const recipientMode = ref('all');
const selectedClients = ref([]);
const messageText = ref('');
const clients = ref([]);

const builtInTemplates = [
    { id: 'b1', category: 'bienvenida', name: 'Bienvenida nuevo cliente', message: 'Bienvenido/a a WellCore! Soy tu coach y estare acompanandote en tu proceso. Cualquier duda no dudes en escribirme. Vamos con todo!' },
    { id: 'b2', category: 'motivacion', name: 'Motivacion semanal', message: 'Nueva semana, nuevas oportunidades! Recuerda que cada repeticion cuenta y cada decision saludable te acerca a tu objetivo. Tu puedes!' },
    { id: 'b3', category: 'recordatorio', name: 'Recordatorio check-in', message: 'Recuerda enviar tu check-in semanal! Es importante para poder ajustar tu plan y asegurar que sigamos en el camino correcto. Te espero!' },
    { id: 'b4', category: 'seguimiento', name: 'Seguimiento nutricional', message: 'Como vas con la alimentacion esta semana? Recuerda registrar tus comidas para que pueda ayudarte mejor. La nutricion es el 70% del resultado!' },
    { id: 'b5', category: 'motivacion', name: 'Celebracion de logro', message: 'Quiero felicitarte por tu compromiso y constancia! Los resultados se construyen dia a dia y tu lo estas demostrando. Sigue asi!' },
    { id: 'b6', category: 'recordatorio', name: 'Recordatorio fotos progreso', message: 'No olvides subir tus fotos de progreso esta semana. Son una herramienta fundamental para ver tu evolucion. Te sorprenderas de los cambios!' },
];

const showTemplates = ref(false);

const recipientLabel = computed(() => {
    switch (recipientMode.value) {
        case 'all': return 'Todos los clientes';
        case 'plan': return 'Por plan';
        case 'status': return 'Por estado';
        case 'individual': return 'Seleccion individual';
        default: return 'Todos';
    }
});

function useTemplate(template) {
    messageText.value = template.message;
    showTemplates.value = false;
}

async function sendBroadcast() {
    if (!messageText.value.trim()) return;
    sending.value = true;
    success.value = false;
    try {
        await api.post('/api/v/coach/broadcast', {
            mode: recipientMode.value,
            selected_clients: selectedClients.value,
            message: messageText.value,
        });
        success.value = true;
        messageText.value = '';
        selectedClients.value = [];
        setTimeout(() => { success.value = false; }, 4000);
    } catch (e) {
        // silent
    } finally {
        sending.value = false;
    }
}

async function loadClients() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/clients');
        clients.value = data.clients || [];
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

function toggleClient(id) {
    const idx = selectedClients.value.indexOf(id);
    if (idx !== -1) {
        selectedClients.value.splice(idx, 1);
    } else {
        selectedClients.value.push(id);
    }
}

onMounted(loadClients);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Broadcast</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Envia mensajes a multiples clientes</p>
      </div>

      <!-- Success toast -->
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="success" class="flex items-center gap-3 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          Broadcast enviado exitosamente
        </div>
      </Transition>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Compose (2 cols) -->
        <div class="space-y-5 lg:col-span-2">

          <!-- Recipient mode -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="text-sm font-semibold text-wc-text mb-3">Destinatarios</h3>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="mode in [{ key: 'all', label: 'Todos' }, { key: 'plan', label: 'Por plan' }, { key: 'status', label: 'Por estado' }, { key: 'individual', label: 'Individual' }]"
                :key="mode.key"
                @click="recipientMode = mode.key"
                class="rounded-lg border px-4 py-2 text-sm font-medium transition-colors"
                :class="recipientMode === mode.key ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text'"
              >
                {{ mode.label }}
              </button>
            </div>

            <!-- Individual selection -->
            <div v-if="recipientMode === 'individual'" class="mt-4 max-h-48 overflow-y-auto space-y-1">
              <label
                v-for="client in clients"
                :key="client.id"
                class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-wc-bg-secondary/50 cursor-pointer transition-colors"
              >
                <input
                  type="checkbox"
                  :checked="selectedClients.includes(client.id)"
                  @change="toggleClient(client.id)"
                  class="h-4 w-4 rounded border-wc-border text-wc-accent focus:ring-wc-accent"
                />
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                  <span class="text-xs font-semibold text-wc-accent">{{ (client.name || 'C').charAt(0) }}</span>
                </div>
                <span class="text-sm text-wc-text">{{ client.name }}</span>
              </label>
            </div>
          </div>

          <!-- Message compose -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-sm font-semibold text-wc-text">Mensaje</h3>
              <button
                @click="showTemplates = !showTemplates"
                class="inline-flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                Templates
              </button>
            </div>
            <textarea
              v-model="messageText"
              rows="5"
              placeholder="Escribe tu mensaje broadcast..."
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"
            ></textarea>
            <div class="mt-3 flex items-center justify-between">
              <p class="text-xs text-wc-text-tertiary">{{ recipientLabel }}</p>
              <button
                @click="sendBroadcast"
                :disabled="sending || !messageText.trim()"
                class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
              >
                <svg v-if="sending" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
                {{ sending ? 'Enviando...' : 'Enviar broadcast' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Templates panel -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="text-sm font-semibold text-wc-text mb-4">Templates rapidos</h3>
          <div class="space-y-2">
            <button
              v-for="tpl in builtInTemplates"
              :key="tpl.id"
              @click="useTemplate(tpl)"
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-left hover:border-wc-accent/30 transition-colors"
            >
              <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-wc-text">{{ tpl.name }}</p>
                <span class="rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[10px] text-wc-text-tertiary capitalize">{{ tpl.category }}</span>
              </div>
              <p class="mt-1 text-[11px] text-wc-text-tertiary line-clamp-2">{{ tpl.message }}</p>
            </button>
          </div>
        </div>
      </div>
    </div>
  </CoachLayout>
</template>
