<script setup>
import { ref, onMounted } from 'vue';

const STORAGE_KEY = 'coach_community_tour_seen';
const visible = ref(false);
const step = ref(0);

const STEPS = [
    {
        title: 'Bienvenido al Hub de Comunidad',
        body: 'Aquí ves, moderas y participas en la comunidad de tus clientes. Tienes 5 herramientas a tu disposición.',
        emoji: '\u{1F44B}',
    },
    {
        title: 'Latido del Equipo',
        body: 'El score muestra la salud general de tu equipo. Top performers y clientes en riesgo de churn aparecen aquí.',
        emoji: '\u{1F497}',
    },
    {
        title: 'Modera con un click',
        body: 'En cualquier post de tus clientes puedes fijar, marcar como Coach Pick, o eliminar. Las acciones son instantáneas.',
        emoji: '\u{1F6E0}',
    },
    {
        title: 'Mensaje al equipo',
        body: 'Usa el botón flotante para anunciar in-feed o mandar push notifications a clientes específicos.',
        emoji: '\u{1F4E3}',
    },
];

const emit = defineEmits(['done']);

function next() {
    if (step.value < STEPS.length - 1) {
        step.value++;
    } else {
        finish();
    }
}

function skip() {
    finish();
}

function finish() {
    localStorage.setItem(STORAGE_KEY, '1');
    visible.value = false;
    emit('done');
}

onMounted(() => {
    if (!localStorage.getItem(STORAGE_KEY)) {
        setTimeout(() => (visible.value = true), 600);
    }
});
</script>

<template>
  <Transition
    enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
    leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0"
  >
    <div v-if="visible" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="w-full max-w-md rounded-2xl bg-wc-bg-secondary border border-wc-border shadow-2xl p-6 text-center">
        <div class="text-5xl mb-3">{{ STEPS[step].emoji }}</div>
        <h3 class="font-display text-2xl tracking-wide text-wc-text mb-2">{{ STEPS[step].title }}</h3>
        <p class="text-sm text-wc-text-secondary mb-5">{{ STEPS[step].body }}</p>
        <div class="flex items-center justify-center gap-1 mb-5">
          <div v-for="(_, i) in STEPS" :key="i" :class="i === step ? 'bg-wc-accent w-6' : 'bg-wc-border w-1.5'" class="h-1.5 rounded-full transition-all"></div>
        </div>
        <div class="flex items-center gap-3">
          <button @click="skip" class="flex-1 rounded-full px-4 py-2 text-sm font-semibold text-wc-text-tertiary hover:bg-wc-bg-tertiary">
            Saltar
          </button>
          <button @click="next" class="flex-1 rounded-full px-4 py-2 text-sm font-semibold bg-wc-accent text-white hover:bg-wc-accent/90">
            {{ step === STEPS.length - 1 ? 'Listo' : 'Siguiente' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>
