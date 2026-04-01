<script setup>
import { ref, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

// ─── Plan data (static, module-level equivalent) ────────────────────
const plans = [
  {
    key: 'rise',
    name: 'RISE',
    price: '$99.900 COP',
    type: 'Pago unico · 30 dias',
    desc: 'Programa de 30 dias para iniciar la transformacion',
  },
  {
    key: 'esencial',
    name: 'Esencial',
    price: '$299.000 COP/mes',
    type: 'Mensual',
    desc: 'Entrenamiento personalizado + protocolo de habitos',
  },
  {
    key: 'metodo',
    name: 'Metodo',
    price: '$399.000 COP/mes',
    type: 'Mensual · Mas popular',
    desc: 'Entreno + nutricion + ajustes semanales con coach',
  },
  {
    key: 'elite',
    name: 'Elite',
    price: '$549.000 COP/mes',
    type: 'Mensual · Premium',
    desc: 'Todo incluido + check-ins 1:1 + analisis avanzados',
  },
  {
    key: 'presencial',
    name: 'Presencial',
    price: '$450k-$650k COP/mes',
    type: 'Mensual · Bucaramanga',
    desc: 'Sesiones cara a cara + seguimiento digital',
  },
];

// ─── Mode state ─────────────────────────────────────────────────────
const mode = ref('invitation');

// ─── Shared form state ──────────────────────────────────────────────
const selectedPlan = ref('metodo');
const recipientName = ref('');
const recipientEmail = ref('');
const sending = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const fieldErrors = ref({});
const sentHistory = ref([]);

// ─── Gift-specific form state ───────────────────────────────────────
const gifterName = ref('');
const gifterEmail = ref('');
const giftMessage = ref('');

// ─── Computed ───────────────────────────────────────────────────────
const selectedPlanData = computed(() => {
  return plans.find(p => p.key === selectedPlan.value) || plans[2];
});

const giftMessageLength = computed(() => giftMessage.value.length);

const isGiftFormReady = computed(() => {
  return gifterName.value.trim() && gifterEmail.value.trim()
    && recipientName.value.trim() && recipientEmail.value.trim()
    && selectedPlan.value;
});

// ─── Actions ────────────────────────────────────────────────────────
function selectPlan(key) {
  selectedPlan.value = key;
}

function switchMode(newMode) {
  mode.value = newMode;
  clearMessages();
}

function clearMessages() {
  successMessage.value = '';
  errorMessage.value = '';
  fieldErrors.value = {};
}

async function sendInvitation() {
  clearMessages();
  sending.value = true;

  try {
    const response = await api.post('/api/v/admin/send-plan-invitation', {
      recipient_name: recipientName.value,
      recipient_email: recipientEmail.value,
      plan: selectedPlan.value,
    });

    if (response.data.sent) {
      successMessage.value = response.data.message;
      sentHistory.value.push(response.data.entry);
      recipientName.value = '';
      recipientEmail.value = '';
    } else {
      errorMessage.value = response.data.message || 'Error desconocido al enviar.';
    }
  } catch (err) {
    if (err.response?.status === 422) {
      fieldErrors.value = err.response.data.errors || {};
    } else if (err.response?.data?.message) {
      errorMessage.value = err.response.data.message;
    } else {
      errorMessage.value = 'Error de conexion. Intenta de nuevo.';
    }
  } finally {
    sending.value = false;
  }
}

async function sendGift() {
  clearMessages();
  sending.value = true;

  try {
    const response = await api.post('/api/v/admin/send-gift-invitation', {
      gifter_name: gifterName.value,
      gifter_email: gifterEmail.value,
      recipient_name: recipientName.value,
      recipient_email: recipientEmail.value,
      gift_message: giftMessage.value || null,
      plan: selectedPlan.value,
    });

    if (response.data.sent) {
      successMessage.value = response.data.message;
      sentHistory.value.push(response.data.entry);
      recipientName.value = '';
      recipientEmail.value = '';
      gifterName.value = '';
      gifterEmail.value = '';
      giftMessage.value = '';
    } else {
      errorMessage.value = response.data.message || 'Error desconocido al enviar.';
    }
  } catch (err) {
    if (err.response?.status === 422) {
      fieldErrors.value = err.response.data.errors || {};
    } else if (err.response?.data?.message) {
      errorMessage.value = err.response.data.message;
    } else {
      errorMessage.value = 'Error de conexion. Intenta de nuevo.';
    }
  } finally {
    sending.value = false;
  }
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">ENVIAR INVITACION DE PLAN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Envia un correo profesional con la informacion del plan seleccionado y el link de pago directo.</p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-0 border-b border-wc-border">
        <button
          @click="switchMode('invitation')"
          class="relative px-5 py-3 text-sm font-semibold transition-colors"
          :class="mode === 'invitation'
            ? 'text-wc-accent'
            : 'text-wc-text-tertiary hover:text-wc-text-secondary'"
        >
          <div class="flex items-center gap-2">
            <!-- Send icon -->
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
            Invitacion de plan
          </div>
          <!-- Active indicator bar -->
          <div
            v-if="mode === 'invitation'"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-wc-accent"
          ></div>
        </button>

        <button
          @click="switchMode('gift')"
          class="relative px-5 py-3 text-sm font-semibold transition-colors"
          :class="mode === 'gift'
            ? 'text-emerald-500'
            : 'text-wc-text-tertiary hover:text-wc-text-secondary'"
        >
          <div class="flex items-center gap-2">
            <!-- Gift icon -->
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H4.5a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
            Enviar como regalo
          </div>
          <!-- Active indicator bar -->
          <div
            v-if="mode === 'gift'"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500"
          ></div>
        </button>
      </div>

      <!-- Plan selector -->
      <div>
        <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Selecciona el plan</p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
          <button
            v-for="plan in plans"
            :key="plan.key"
            @click="selectPlan(plan.key)"
            class="group relative rounded-xl border p-4 text-left transition-all duration-200"
            :class="selectedPlan === plan.key
              ? (mode === 'gift'
                ? 'border-emerald-500 bg-emerald-500/10 ring-1 ring-emerald-500/30'
                : 'border-red-500 bg-red-500/10 ring-1 ring-red-500/30')
              : 'border-wc-border bg-wc-bg-secondary hover:border-wc-accent/40 hover:bg-wc-bg-tertiary'"
          >
            <!-- Selected indicator -->
            <Transition name="fade">
              <div
                v-if="selectedPlan === plan.key"
                class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full"
                :class="mode === 'gift' ? 'bg-emerald-500' : 'bg-red-500'"
              >
                <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>
            </Transition>

            <p class="text-base font-bold text-wc-text">{{ plan.name }}</p>
            <p class="mt-0.5 text-sm font-semibold" :class="mode === 'gift' ? 'text-emerald-500' : 'text-red-500'">{{ plan.price }}</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">{{ plan.type }}</p>
            <p class="mt-2 text-xs leading-relaxed text-wc-text-secondary">{{ plan.desc }}</p>
          </button>
        </div>
      </div>

      <!-- ═══ INVITATION MODE (original) ═══ -->
      <Transition name="fade" mode="out-in">
        <div v-if="mode === 'invitation'" key="invitation" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
          <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-wc-text-tertiary">Datos del destinatario</h2>

          <form @submit.prevent="sendInvitation" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <!-- Name -->
              <div>
                <label for="recipientName" class="mb-1 block text-sm font-medium text-wc-text">Nombre</label>
                <input
                  v-model="recipientName"
                  type="text"
                  id="recipientName"
                  placeholder="Nombre del prospecto"
                  class="w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary transition focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500/30"
                />
                <p v-if="fieldErrors.recipient_name" class="mt-1 text-xs text-red-400">
                  {{ fieldErrors.recipient_name[0] }}
                </p>
              </div>

              <!-- Email -->
              <div>
                <label for="recipientEmail" class="mb-1 block text-sm font-medium text-wc-text">Email</label>
                <input
                  v-model="recipientEmail"
                  type="email"
                  id="recipientEmail"
                  placeholder="correo@ejemplo.com"
                  class="w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary transition focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500/30"
                />
                <p v-if="fieldErrors.recipient_email" class="mt-1 text-xs text-red-400">
                  {{ fieldErrors.recipient_email[0] }}
                </p>
              </div>
            </div>

            <!-- Selected plan summary -->
            <div class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg px-4 py-3">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-500/15">
                <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm text-wc-text">
                  Se enviara invitacion del plan
                  <strong class="text-red-500">{{ selectedPlanData.name }}</strong>
                  ({{ selectedPlanData.price }})
                </p>
              </div>
            </div>

            <!-- Validation error for plan -->
            <p v-if="fieldErrors.plan" class="text-xs text-red-400">
              {{ fieldErrors.plan[0] }}
            </p>

            <!-- Submit row -->
            <div class="flex flex-wrap items-center gap-3">
              <button
                type="submit"
                :disabled="sending"
                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-red-700 disabled:cursor-wait disabled:opacity-60"
              >
                <!-- Send icon -->
                <svg v-if="!sending" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
                <!-- Spinner -->
                <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Enviar Invitacion
              </button>

              <!-- Success message -->
              <Transition name="fade">
                <div v-if="successMessage" class="flex items-center gap-2 rounded-lg bg-green-500/10 px-4 py-2 text-sm text-green-400">
                  <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  {{ successMessage }}
                </div>
              </Transition>

              <!-- Error message -->
              <Transition name="fade">
                <div v-if="errorMessage" class="flex items-center gap-2 rounded-lg bg-red-500/10 px-4 py-2 text-sm text-red-400">
                  <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                  </svg>
                  {{ errorMessage }}
                </div>
              </Transition>
            </div>
          </form>
        </div>

        <!-- ═══ GIFT MODE ═══ -->
        <div v-else key="gift" class="space-y-4">

          <!-- Gift form card -->
          <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-6">
            <div class="mb-5 flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/15">
                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H4.5a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
              </div>
              <div>
                <h2 class="text-sm font-semibold uppercase tracking-widest text-emerald-500">Enviar como regalo</h2>
                <p class="text-xs text-wc-text-tertiary">Un cliente regala un plan a otra persona</p>
              </div>
            </div>

            <form @submit.prevent="sendGift" class="space-y-5">

              <!-- Quien regala -->
              <div>
                <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Quien regala</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label for="gifterName" class="mb-1 block text-sm font-medium text-wc-text">Nombre de quien regala</label>
                    <input
                      v-model="gifterName"
                      type="text"
                      id="gifterName"
                      placeholder="Ej: Carlos Rodriguez"
                      class="w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
                    />
                    <p v-if="fieldErrors.gifter_name" class="mt-1 text-xs text-red-400">
                      {{ fieldErrors.gifter_name[0] }}
                    </p>
                  </div>
                  <div>
                    <label for="gifterEmail" class="mb-1 block text-sm font-medium text-wc-text">Email de quien regala</label>
                    <input
                      v-model="gifterEmail"
                      type="email"
                      id="gifterEmail"
                      placeholder="carlos@ejemplo.com"
                      class="w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
                    />
                    <p v-if="fieldErrors.gifter_email" class="mt-1 text-xs text-red-400">
                      {{ fieldErrors.gifter_email[0] }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Divider -->
              <div class="border-t border-wc-border/50"></div>

              <!-- Quien recibe -->
              <div>
                <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Quien recibe el regalo</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label for="giftRecipientName" class="mb-1 block text-sm font-medium text-wc-text">Nombre del destinatario</label>
                    <input
                      v-model="recipientName"
                      type="text"
                      id="giftRecipientName"
                      placeholder="Ej: Maria Lopez"
                      class="w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
                    />
                    <p v-if="fieldErrors.recipient_name" class="mt-1 text-xs text-red-400">
                      {{ fieldErrors.recipient_name[0] }}
                    </p>
                  </div>
                  <div>
                    <label for="giftRecipientEmail" class="mb-1 block text-sm font-medium text-wc-text">Email del destinatario</label>
                    <input
                      v-model="recipientEmail"
                      type="email"
                      id="giftRecipientEmail"
                      placeholder="maria@ejemplo.com"
                      class="w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
                    />
                    <p v-if="fieldErrors.recipient_email" class="mt-1 text-xs text-red-400">
                      {{ fieldErrors.recipient_email[0] }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Divider -->
              <div class="border-t border-wc-border/50"></div>

              <!-- Mensaje personalizado -->
              <div>
                <label for="giftMessage" class="mb-1 block text-sm font-medium text-wc-text">Mensaje personalizado <span class="text-wc-text-tertiary">(opcional)</span></label>
                <textarea
                  v-model="giftMessage"
                  id="giftMessage"
                  rows="3"
                  maxlength="500"
                  placeholder="Escribe un mensaje para acompanar el regalo..."
                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/30"
                ></textarea>
                <div class="mt-1 flex items-center justify-between">
                  <p v-if="fieldErrors.gift_message" class="text-xs text-red-400">
                    {{ fieldErrors.gift_message[0] }}
                  </p>
                  <span v-else></span>
                  <span class="text-xs font-data text-wc-text-tertiary">{{ giftMessageLength }}/500</span>
                </div>
              </div>

              <!-- Validation error for plan -->
              <p v-if="fieldErrors.plan" class="text-xs text-red-400">
                {{ fieldErrors.plan[0] }}
              </p>

              <!-- Gift summary card -->
              <Transition name="fade">
                <div v-if="isGiftFormReady" class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 px-5 py-4">
                  <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-emerald-500">Resumen del regalo</p>
                  <div class="space-y-1.5">
                    <p class="text-sm text-wc-text">
                      <span class="mr-1">&#127873;</span>
                      <strong>{{ gifterName }}</strong>
                      le regala
                      <strong class="text-emerald-500">{{ selectedPlanData.name }}</strong>
                      a
                      <strong>{{ recipientName }}</strong>
                    </p>
                    <p class="text-sm font-data text-wc-text-secondary">
                      Valor del plan: <strong class="text-emerald-400">{{ selectedPlanData.price }}</strong>
                    </p>
                    <p v-if="giftMessage.trim()" class="text-sm italic text-wc-text-tertiary">
                      "{{ giftMessage }}"
                    </p>
                  </div>
                </div>
              </Transition>

              <!-- Submit row -->
              <div class="flex flex-wrap items-center gap-3">
                <button
                  type="submit"
                  :disabled="sending"
                  class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60"
                >
                  <!-- Gift icon -->
                  <svg v-if="!sending" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H4.5a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                  </svg>
                  <!-- Spinner -->
                  <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                  Enviar Regalo
                </button>

                <!-- Success message -->
                <Transition name="fade">
                  <div v-if="successMessage" class="flex items-center gap-2 rounded-lg bg-green-500/10 px-4 py-2 text-sm text-green-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ successMessage }}
                  </div>
                </Transition>

                <!-- Error message -->
                <Transition name="fade">
                  <div v-if="errorMessage" class="flex items-center gap-2 rounded-lg bg-red-500/10 px-4 py-2 text-sm text-red-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    {{ errorMessage }}
                  </div>
                </Transition>
              </div>
            </form>
          </div>
        </div>
      </Transition>

      <!-- Session history -->
      <Transition name="fade">
        <div v-if="sentHistory.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
          <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-wc-text-tertiary">Enviados en esta sesion</h2>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-wc-border text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
                  <th class="pb-2">Tipo</th>
                  <th class="pb-2">Nombre</th>
                  <th class="pb-2">Email</th>
                  <th class="pb-2">Plan</th>
                  <th class="pb-2">Hora</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(entry, idx) in [...sentHistory].reverse()"
                  :key="idx"
                  class="border-b border-wc-border/50 last:border-0"
                >
                  <td class="py-2.5">
                    <span v-if="entry.type === 'gift'" class="inline-block rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-500">
                      Regalo
                    </span>
                    <span v-else class="inline-block rounded-full bg-blue-500/10 px-2.5 py-0.5 text-xs font-semibold text-blue-500">
                      Invitacion
                    </span>
                  </td>
                  <td class="py-2.5 text-sm text-wc-text">
                    {{ entry.name }}
                    <span v-if="entry.gifter" class="text-wc-text-tertiary">(de {{ entry.gifter }})</span>
                  </td>
                  <td class="py-2.5 text-sm text-wc-text-secondary">{{ entry.email }}</td>
                  <td class="py-2.5">
                    <span
                      class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold"
                      :class="entry.type === 'gift'
                        ? 'bg-emerald-500/10 text-emerald-400'
                        : 'bg-red-500/10 text-red-400'"
                    >{{ entry.plan }}</span>
                  </td>
                  <td class="py-2.5 text-sm font-data text-wc-text-tertiary">{{ entry.time }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </Transition>

    </div>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
