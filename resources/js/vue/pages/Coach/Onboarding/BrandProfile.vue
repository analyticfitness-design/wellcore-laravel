<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCoachStrategyStore } from '../../../stores/coachStrategy';
import CoachLayout from '../../../layouts/CoachLayout.vue';
import BrandProfileForm from '../../../components/coach/onboarding/BrandProfileForm.vue';

const store = useCoachStrategyStore();
const router = useRouter();

onMounted(() => store.fetchProfile());

async function handleSubmit(payload) {
    try {
        await store.submitProfile(payload);
        router.push({ name: 'coach-strategy' });
    } catch (e) {
        // Errores se muestran en el form
        console.error('Submit failed:', e?.response?.data ?? e);
    }
}
</script>

<template>
  <CoachLayout>
    <div class="onboarding-page mx-auto max-w-3xl px-6 py-12">
      <header class="mb-12">
        <p class="font-mono text-xs uppercase tracking-[0.2em] text-wc-text-tertiary">
          WC &middot; ONBOARDING / BRAND-PROFILE
        </p>
        <h1 class="mt-3 font-display text-5xl uppercase tracking-tight text-wc-text">
          Tu perfil de marca
        </h1>
        <p class="mt-3 font-editorial italic text-lg text-wc-text-secondary">
          Para construir tu estrategia personalizada, el equipo necesita conocerte.
        </p>
      </header>

      <BrandProfileForm
        :initial="store.profile"
        :is-saving="store.isLoadingProfile"
        @submit="handleSubmit"
      />
    </div>
  </CoachLayout>
</template>
