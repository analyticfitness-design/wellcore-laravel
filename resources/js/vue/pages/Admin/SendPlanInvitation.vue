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
    type: 'Pago unico \u00b7 30 dias',
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
    type: 'Mensual \u00b7 Mas popular',
    desc: 'Entreno + nutricion + ajustes semanales con coach',
  },
  {
    key: 'elite',
    name: 'Elite',
    price: '$549.000 COP/mes',
    type: 'Mensual \u00b7 Premium',
    desc: 'Todo incluido + check-ins 1:1 + analisis avanzados',
  },
  {
    key: 'presencial',
    name: 'Presencial',
    price: '$450k-$650k COP/mes',
    type: 'Mensual \u00b7 Bucaramanga',
    desc: 'Sesiones cara a cara + seguimiento digital',
  },
];

// ─── Form state ─────────────────────────────────────────────────────
const selectedPlan = ref('metodo');
const recipientName = ref('');
const recipientEmail = ref('');
const sending = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const fieldErrors = ref({});
const sentHistory = ref([]);

// ─── Computed ───────────────────────────────────────────────────────
const selectedPlanData = computed(() => {
  return plans.find(p => p.key === selectedPlan.value) || plans[2];
});

// ─── Actions ────────────────────────────────────────────────────────
function selectPlan(key) {
  selectedPlan.value = key;
}

async function sendInvitation() {
  successMessage.value = '';
  errorMessage.value = '';
  fieldErrors.value = {};
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
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">ENVIAR INVITACION DE PLAN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Envia un correo profesional con la informacion del plan seleccionado y el link de pago directo.</p>
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
              ? 'border-red-500 bg-red-500/10 ring-1 ring-red-500/30'
              : 'border-wc-border bg-wc-bg-secondary hover:border-wc-accent/40 hover:bg-wc-bg-tertiary'"
          >
            <!-- Selected indicator -->
            <Transition name="fade">
              <div
                v-if="selectedPlan === plan.key"
                class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500"
              >
                <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>
            </Transition>

            <p class="text-base font-bold text-wc-text">{{ plan.name }}</p>
            <p class="mt-0.5 text-sm font-semibold text-red-500">{{ plan.price }}</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">{{ plan.type }}</p>
            <p class="mt-2 text-xs leading-relaxed text-wc-text-secondary">{{ plan.desc }}</p>
          </button>
        </div>
      </div>

      <!-- Send form -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
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

      <!-- Session history -->
      <Transition name="fade">
        <div v-if="sentHistory.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
          <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-wc-text-tertiary">Enviados en esta sesion</h2>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-wc-border text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
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
                  <td class="py-2.5 text-sm text-wc-text">{{ entry.name }}</td>
                  <td class="py-2.5 text-sm text-wc-text-secondary">{{ entry.email }}</td>
                  <td class="py-2.5">
                    <span class="inline-block rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-semibold text-red-400">{{ entry.plan }}</span>
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
