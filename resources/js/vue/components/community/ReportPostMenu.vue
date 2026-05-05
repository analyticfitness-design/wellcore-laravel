<script setup>
import { ref } from 'vue';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import { useHaptics } from '../../composables/useHaptics';

const props = defineProps({ postId: { type: Number, required: true } });
const emit = defineEmits(['reported']);

const api = useApi();
const toast = useToast();
const haptics = useHaptics();

const menuOpen = ref(false);
const modalOpen = ref(false);
const reason = ref('');
const detail = ref('');
const submitting = ref(false);

const REASONS = [
    { value: 'spam', label: 'Spam o promoción' },
    { value: 'offensive', label: 'Contenido ofensivo' },
    { value: 'off_topic', label: 'Off-topic' },
    { value: 'other', label: 'Otro' },
];

function openModal() {
    menuOpen.value = false;
    modalOpen.value = true;
}

async function submit() {
    if (!reason.value) {
        toast.warn('Selecciona una razón.');
        return;
    }
    submitting.value = true;
    try {
        await api.post(`/api/v/community/posts/${props.postId}/report`, {
            reason: reason.value,
            reason_detail: detail.value || null,
        });
        haptics.success();
        toast.success('Reporte enviado. Revisaremos a la brevedad.');
        emit('reported', props.postId);
        close();
    } catch (err) {
        haptics.error();
        toast.apiError(err, 'No pudimos enviar el reporte.');
    } finally {
        submitting.value = false;
    }
}

function close() {
    modalOpen.value = false;
    reason.value = '';
    detail.value = '';
}
</script>

<template>
  <div class="relative">
    <button @click="menuOpen = !menuOpen" class="rounded-lg p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-tertiary" aria-label="Más opciones">
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zm0 6a.75.75 0 110-1.5.75.75 0 010 1.5zm0 6a.75.75 0 110-1.5.75.75 0 010 1.5z" />
      </svg>
    </button>

    <Transition enter-active-class="duration-150" enter-from-class="opacity-0 scale-95">
      <div v-if="menuOpen" class="absolute right-0 top-full mt-1 w-48 rounded-xl border border-wc-border bg-wc-bg-secondary shadow-xl z-20 py-1">
        <button @click="openModal" class="w-full text-left px-3 py-2 text-sm text-rose-500 hover:bg-rose-500/10 flex items-center gap-2">
          <span>🚩</span><span>Reportar post</span>
        </button>
      </div>
    </Transition>

    <Transition enter-active-class="duration-200" enter-from-class="opacity-0">
      <div v-if="modalOpen" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4" @click.self="close">
        <div class="rounded-2xl bg-wc-bg-secondary border border-wc-border w-full max-w-md p-6">
          <header class="flex items-center justify-between mb-4">
            <h3 class="font-display text-xl text-wc-text">Reportar post</h3>
            <button @click="close" class="text-wc-text-tertiary hover:text-wc-text">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </header>
          <p class="text-sm text-wc-text-secondary mb-3">¿Por qué reportas este post?</p>
          <div class="space-y-2 mb-4">
            <label v-for="r in REASONS" :key="r.value" class="flex items-center gap-2 cursor-pointer">
              <input type="radio" :value="r.value" v-model="reason" class="accent-wc-accent" />
              <span class="text-sm text-wc-text">{{ r.label }}</span>
            </label>
          </div>
          <textarea v-model="detail" rows="3" maxlength="500" placeholder="Detalles (opcional)" class="w-full rounded-lg border border-wc-border bg-wc-bg p-2 text-sm text-wc-text mb-4 resize-none"></textarea>
          <div class="flex gap-3">
            <button @click="close" class="flex-1 rounded-full border border-wc-border text-wc-text-secondary py-2">Cancelar</button>
            <button @click="submit" :disabled="!reason || submitting" class="flex-1 rounded-full bg-wc-accent text-white py-2 font-semibold disabled:opacity-50">
              {{ submitting ? 'Enviando…' : 'Enviar reporte' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
