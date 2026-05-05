<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    action: { type: String, default: '' },
    report: { type: Object, default: null },
});
const emit = defineEmits(['confirm', 'cancel']);

const reason = ref('');

watch(() => props.open, (o) => { if (o) reason.value = ''; });

function actionLabel() {
    return {
        dismiss: 'descartar',
        hide: 'ocultar',
        warn: 'avisar',
        ban_client: 'banear cliente',
    }[props.action] || props.action;
}
</script>

<template>
  <Transition enter-active-class="duration-200" enter-from-class="opacity-0">
    <div v-if="open" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4" @click.self="emit('cancel')">
      <div class="rounded-2xl bg-wc-bg-secondary border border-wc-border p-6 w-full max-w-md">
        <h3 class="font-display text-xl text-wc-text mb-3">Confirmar acción</h3>
        <p class="text-sm text-wc-text-secondary mb-3">
          Vas a <strong>{{ actionLabel() }}</strong> el post de {{ report?.post_author_name }}.
        </p>
        <textarea v-model="reason" rows="2" placeholder="Razón (opcional, queda en audit log)" class="w-full rounded-lg border border-wc-border bg-wc-bg p-2 text-sm text-wc-text mb-4 resize-none"></textarea>
        <div class="flex gap-3">
          <button @click="emit('cancel')" class="flex-1 rounded-full border border-wc-border text-wc-text-secondary py-2">Cancelar</button>
          <button @click="emit('confirm', { reason })" class="flex-1 rounded-full bg-wc-accent text-white py-2 font-semibold">Confirmar</button>
        </div>
      </div>
    </div>
  </Transition>
</template>
