<script setup>
import { ref, computed, watch } from 'vue';
import { useCoachAnnounce } from '../../composables/useCoachAnnounce';
import { useToast } from '../../composables/useToast';
import { useHaptics } from '../../composables/useHaptics';

const announce = useCoachAnnounce();
const toast = useToast();
const haptics = useHaptics();

const PIN_OPTIONS = [
    { value: 0, label: 'No fijar' },
    { value: 24, label: '24h' },
    { value: 48, label: '48h' },
    { value: 168, label: '1 semana' },
];

const PUSH_MAX_CHARS = 200;
const POST_MAX_CHARS = 1000;

const showConfirmStep = ref(false);
const fileInputRef = ref(null);
const imagePreview = ref(null);

const charLimit = computed(() => announce.mode.value === 'push' ? PUSH_MAX_CHARS : POST_MAX_CHARS);
const charCount = computed(() => announce.message.value.length);
const charOver = computed(() => charCount.value > charLimit.value);

const canSend = computed(() => {
    return !announce.sending.value
        && announce.message.value.trim().length > 0
        && !charOver.value;
});

watch(() => announce.segment.value, () => {
    announce.previewCount();
}, { deep: true });

watch(() => announce.isOpen.value, (open) => {
    if (open) announce.previewCount();
});

function selectImage(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        toast.error('Imagen excede 5MB.');
        return;
    }
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        toast.error('Formato no válido. Usa JPG, PNG o WebP.');
        return;
    }
    if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
    announce.image.value = file;
    imagePreview.value = URL.createObjectURL(file);
}

function removeImage() {
    if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
    announce.image.value = null;
    imagePreview.value = null;
}

async function attemptSend() {
    if (!canSend.value) return;
    if ((announce.recipientCount.value || 0) > 20) {
        showConfirmStep.value = true;
        return;
    }
    await doSend();
}

async function doSend() {
    showConfirmStep.value = false;
    try {
        const res = await announce.send();
        haptics.success();
        toast.success(`Mensaje enviado a ${res.recipients_count || res.delivered_count} clientes.`);
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
            imagePreview.value = null;
        }
    } catch (err) {
        haptics.error();
        toast.apiError(err, 'No pudimos enviar el mensaje.');
    }
}

function cancel() {
    showConfirmStep.value = false;
}

function close() {
    if (announce.sending.value) return;
    announce.close();
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = null;
    }
}
</script>

<template>
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0" enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-150"
    leave-from-class="opacity-100" leave-to-class="opacity-0"
  >
    <div v-if="announce.isOpen.value" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-6" @click.self="close">
      <Transition
        appear
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-y-full sm:translate-y-4 sm:scale-95 opacity-0"
        enter-to-class="translate-y-0 scale-100 opacity-100"
      >
        <div class="w-full sm:max-w-lg bg-wc-bg-secondary rounded-t-2xl sm:rounded-2xl shadow-2xl border border-wc-border max-h-[92vh] flex flex-col overflow-hidden">
          <header class="flex items-center justify-between px-5 py-4 border-b border-wc-border">
            <h2 class="font-display text-xl tracking-wider text-wc-text">Mensaje al equipo</h2>
            <button @click="close" class="text-wc-text-tertiary hover:text-wc-text" aria-label="Cerrar">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </header>

          <div v-if="!showConfirmStep" class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
            <div class="grid grid-cols-2 rounded-lg border border-wc-border overflow-hidden">
              <button
                @click="announce.mode.value = 'post'"
                :class="announce.mode.value === 'post' ? 'bg-wc-accent text-white' : 'bg-wc-bg text-wc-text-secondary'"
                class="py-2.5 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
              >
                <span>\u{1F4E2}</span> <span>Anuncio in-feed</span>
              </button>
              <button
                @click="announce.mode.value = 'push'"
                :class="announce.mode.value === 'push' ? 'bg-wc-accent text-white' : 'bg-wc-bg text-wc-text-secondary'"
                class="py-2.5 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
              >
                <span>\u{1F514}</span> <span>Push notification</span>
              </button>
            </div>

            <div>
              <label class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary block mb-2">
                Tu mensaje
              </label>
              <textarea
                v-model="announce.message.value"
                :placeholder="announce.mode.value === 'push' ? 'Mensaje breve, max 200 caracteres' : 'Comparte motivación, anuncios o reconocimientos…'"
                :maxlength="charLimit"
                rows="4"
                class="w-full rounded-lg border border-wc-border bg-wc-bg p-3 text-sm text-wc-text resize-none focus:border-wc-accent focus:outline-none"
              />
              <div class="mt-1 flex justify-end text-[11px]" :class="charOver ? 'text-rose-500' : 'text-wc-text-tertiary'">
                {{ charCount }} / {{ charLimit }}
              </div>
            </div>

            <div v-if="announce.mode.value === 'post'">
              <label class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary block mb-2">
                Imagen (opcional)
              </label>
              <input ref="fileInputRef" type="file" accept="image/jpeg,image/png,image/webp" @change="selectImage" class="hidden" />
              <div v-if="!imagePreview" @click="fileInputRef?.click()" class="rounded-lg border-2 border-dashed border-wc-border bg-wc-bg p-4 text-center cursor-pointer hover:border-wc-accent/40">
                <p class="text-xs text-wc-text-tertiary">Subir imagen (max 5MB)</p>
              </div>
              <div v-else class="relative rounded-lg overflow-hidden">
                <img :src="imagePreview" alt="" class="w-full h-40 object-cover" />
                <button @click="removeImage" class="absolute top-2 right-2 bg-black/60 text-white rounded-full p-1.5">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <div v-if="announce.mode.value === 'post'">
              <label class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary block mb-2">
                Fijar al tope del feed
              </label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="opt in PIN_OPTIONS" :key="opt.value"
                  @click="announce.pinHours.value = opt.value"
                  :class="announce.pinHours.value === opt.value ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
                  class="rounded-full px-3 py-1 text-xs font-semibold transition-colors"
                >{{ opt.label }}</button>
              </div>
            </div>

            <div class="rounded-lg bg-wc-bg-tertiary px-3 py-2 text-xs text-wc-text-tertiary">
              <span v-if="announce.recipientCount.value !== null">
                {{ announce.recipientCount.value }} clientes activos {{ announce.mode.value === 'push' ? 'recibirán push' : 'verán este post' }}.
              </span>
              <span v-else>Calculando recipientes…</span>
            </div>
          </div>

          <div v-else class="flex-1 px-5 py-6 space-y-4 text-center">
            <div class="text-4xl">⚠️</div>
            <h3 class="font-display text-2xl text-wc-text">¿Confirmar envío?</h3>
            <p class="text-sm text-wc-text-secondary">
              Vas a enviar a <strong class="text-wc-text">{{ announce.recipientCount.value }}</strong> clientes activos.
            </p>
            <p class="text-xs text-wc-text-tertiary">Esta acción es irreversible.</p>
          </div>

          <footer class="flex items-center gap-3 px-5 py-4 border-t border-wc-border">
            <template v-if="!showConfirmStep">
              <button @click="close" :disabled="announce.sending.value" class="flex-1 rounded-full px-4 py-2.5 text-sm font-semibold border border-wc-border text-wc-text-secondary hover:bg-wc-bg-tertiary disabled:opacity-50">
                Cancelar
              </button>
              <button @click="attemptSend" :disabled="!canSend" class="flex-1 rounded-full px-4 py-2.5 text-sm font-semibold bg-wc-accent text-white hover:bg-wc-accent/90 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ announce.sending.value ? 'Enviando…' : 'Enviar al equipo' }}
              </button>
            </template>
            <template v-else>
              <button @click="cancel" class="flex-1 rounded-full px-4 py-2.5 text-sm font-semibold border border-wc-border text-wc-text-secondary hover:bg-wc-bg-tertiary">
                Cancelar
              </button>
              <button @click="doSend" :disabled="announce.sending.value" class="flex-1 rounded-full px-4 py-2.5 text-sm font-semibold bg-wc-accent text-white hover:bg-wc-accent/90 disabled:opacity-50">
                Sí, enviar
              </button>
            </template>
          </footer>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
