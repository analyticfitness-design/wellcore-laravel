<script setup>
import { ref, computed } from 'vue';
import { useInvitationsStore } from '../../../stores/invitationsStore';
import EmailPreview from './EmailPreview.vue';

/**
 * @typedef {'esencial'|'metodo'|'elite'} PlanType
 */

const PLANS = [
    { value: 'esencial', label: 'Esencial', price: '$259.000 COP/mes' },
    { value: 'metodo',   label: 'Metodo',   price: '$339.150 COP/mes' },
    { value: 'elite',    label: 'Elite',    price: '$439.050 COP/mes' },
];

const emit = defineEmits(['success', 'cancel']);

const store = useInvitationsStore();

// Form fields
const email          = ref('');
const name           = ref('');
const plan           = ref('esencial');
const subject        = ref('Te invito a unirte a WellCore');
const intro_message  = ref('');
const cta_label      = ref('Unirme ahora');
const expires_in_days = ref(7);

// UI state
const submitting    = ref(false);
const previewing    = ref(false);
const previewSeen   = ref(false);
const previewHtml   = ref('');
const errors        = ref({});
const errorMessage  = ref('');
const errorCode     = ref('');

const introLength = computed(() => intro_message.value.length);

function clearErrors() {
    errors.value = {};
    errorMessage.value = '';
    errorCode.value = '';
}

async function handlePreview() {
    clearErrors();
    if (!email.value || !subject.value || !plan.value) {
        if (!email.value) errors.value.email = ['El correo es obligatorio.'];
        if (!subject.value) errors.value.subject = ['El asunto es obligatorio.'];
        if (!plan.value) errors.value.plan = ['Selecciona un plan.'];
        return;
    }
    previewing.value = true;
    try {
        const html = await store.previewInvitation({
            email: email.value,
            name: name.value || null,
            plan: plan.value,
            subject: subject.value,
            intro_message: intro_message.value || null,
            cta_label: cta_label.value || null,
            expires_in_days: expires_in_days.value,
        });
        previewHtml.value = html;
        previewSeen.value = true;
    } catch (err) {
        errorMessage.value = 'No se pudo generar la vista previa. Verifica los datos.';
    } finally {
        previewing.value = false;
    }
}

function onPreviewBack() {
    previewHtml.value = '';
}

async function onPreviewConfirm() {
    previewHtml.value = '';
    await handleSubmit();
}

async function handleSubmit() {
    clearErrors();
    submitting.value = true;
    try {
        await store.createInvitation({
            email: email.value,
            name: name.value || null,
            plan: plan.value,
            subject: subject.value,
            intro_message: intro_message.value || null,
            cta_label: cta_label.value || null,
            expires_in_days: expires_in_days.value,
        });
        emit('success');
    } catch (err) {
        const status = err.response?.status;
        const data   = err.response?.data;

        if (status === 422) {
            if (data?.errors) {
                errors.value = data.errors;
            }
            errorCode.value  = data?.error_code ?? '';
            errorMessage.value = data?.message ?? 'Verifica los campos e intenta de nuevo.';
        } else if (status === 429) {
            errorCode.value  = 'RATE_LIMIT';
            errorMessage.value = data?.message ?? 'Has alcanzado el limite de invitaciones. Espera antes de enviar otra.';
        } else {
            errorMessage.value = 'Ocurrio un error al enviar la invitacion. Intenta de nuevo.';
        }
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
  <!-- Email preview panel (shown when previewHtml is set) -->
  <Transition name="fade" mode="out-in">
    <div v-if="previewHtml" key="preview">
      <EmailPreview
        :html="previewHtml"
        @back="onPreviewBack"
        @confirm="onPreviewConfirm"
      />
    </div>

    <!-- Form panel -->
    <div v-else key="form" class="space-y-6">
      <!-- Header -->
      <div>
        <h2 class="font-display text-2xl tracking-wide text-wc-text">Nueva Invitacion</h2>
        <p class="mt-1 text-sm text-wc-text-secondary">
          Envia un correo de invitacion con link de pago Wompi a tu prospecto.
        </p>
      </div>

      <!-- Rate limit error -->
      <Transition name="fade">
        <div
          v-if="errorCode === 'RATE_LIMIT'"
          class="flex items-start gap-3 rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-400"
        >
          <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
          <span>{{ errorMessage }}</span>
        </div>
      </Transition>

      <!-- CLIENT_INACTIVE warning -->
      <Transition name="fade">
        <div
          v-if="errorCode === 'CLIENT_INACTIVE'"
          class="flex items-start gap-3 rounded-lg border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-400"
        >
          <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
          </svg>
          <span>{{ errorMessage }}</span>
        </div>
      </Transition>

      <!-- Generic error -->
      <Transition name="fade">
        <div
          v-if="errorMessage && errorCode !== 'RATE_LIMIT' && errorCode !== 'CLIENT_INACTIVE'"
          class="flex items-start gap-3 rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-400"
        >
          <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
          <span>{{ errorMessage }}</span>
        </div>
      </Transition>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="space-y-5">

        <!-- Row: email + name -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
              Correo electronico <span class="text-wc-accent">*</span>
            </label>
            <input
              v-model="email"
              type="email"
              required
              placeholder="cliente@ejemplo.com"
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
              :class="errors.email ? 'border-red-500/60' : ''"
            />
            <p v-if="errors.email" class="mt-1 text-xs text-red-400">{{ errors.email[0] }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
              Nombre (opcional)
            </label>
            <input
              v-model="name"
              type="text"
              placeholder="Ana Garcia"
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
            />
          </div>
        </div>

        <!-- Plan select -->
        <div>
          <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
            Plan <span class="text-wc-accent">*</span>
          </label>
          <select
            v-model="plan"
            required
            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
            :class="errors.plan ? 'border-red-500/60' : ''"
          >
            <option v-for="p in PLANS" :key="p.value" :value="p.value">
              {{ p.label }} — {{ p.price }}
            </option>
          </select>
          <p v-if="errors.plan" class="mt-1 text-xs text-red-400">{{ errors.plan[0] }}</p>
        </div>

        <!-- Subject -->
        <div>
          <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
            Asunto del correo <span class="text-wc-accent">*</span>
          </label>
          <input
            v-model="subject"
            type="text"
            required
            placeholder="Te invito a unirte a WellCore"
            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
            :class="errors.subject ? 'border-red-500/60' : ''"
          />
          <p v-if="errors.subject" class="mt-1 text-xs text-red-400">{{ errors.subject[0] }}</p>
        </div>

        <!-- Intro message -->
        <div>
          <div class="mb-1.5 flex items-center justify-between">
            <label class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
              Mensaje de introduccion
            </label>
            <span class="text-xs" :class="introLength > 1900 ? 'text-amber-400' : 'text-wc-text-tertiary'">
              {{ introLength }} / 2000
            </span>
          </div>
          <textarea
            v-model="intro_message"
            rows="4"
            maxlength="2000"
            placeholder="Escribe un mensaje personalizado para tu prospecto..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30 resize-y"
            :class="errors.intro_message ? 'border-red-500/60' : ''"
          ></textarea>
          <p v-if="errors.intro_message" class="mt-1 text-xs text-red-400">{{ errors.intro_message[0] }}</p>
        </div>

        <!-- CTA label + expires_in_days -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
              Texto del boton CTA (opcional)
            </label>
            <input
              v-model="cta_label"
              type="text"
              placeholder="Unirme ahora"
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
            />
          </div>
          <div>
            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
              Expira en (dias) <span class="text-wc-accent">*</span>
            </label>
            <input
              v-model.number="expires_in_days"
              type="number"
              min="1"
              max="30"
              required
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
              :class="errors.expires_in_days ? 'border-red-500/60' : ''"
            />
            <p v-if="errors.expires_in_days" class="mt-1 text-xs text-red-400">{{ errors.expires_in_days[0] }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between gap-3 border-t border-wc-border pt-5">
          <button
            type="button"
            @click="emit('cancel')"
            class="rounded-lg bg-wc-bg-tertiary px-4 py-2 text-sm font-semibold text-wc-text hover:bg-zinc-700 transition-colors"
          >
            Cancelar
          </button>

          <div class="flex items-center gap-3">
            <!-- Preview button -->
            <button
              type="button"
              @click="handlePreview"
              :disabled="previewing"
              class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-semibold text-wc-text hover:bg-zinc-700 transition-colors disabled:opacity-50"
            >
              <svg v-if="previewing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
              Vista previa
            </button>

            <!-- Send button (requires preview seen) -->
            <button
              type="submit"
              :disabled="submitting || !previewSeen"
              :title="!previewSeen ? 'Debes ver la vista previa antes de enviar' : ''"
              class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
              </svg>
              Enviar invitacion
            </button>
          </div>
        </div>

        <!-- Preview hint -->
        <p v-if="!previewSeen" class="text-center text-xs text-wc-text-tertiary">
          Haz clic en "Vista previa" para ver el correo antes de enviarlo. El boton "Enviar" se activara al hacerlo.
        </p>
      </form>
    </div>
  </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
