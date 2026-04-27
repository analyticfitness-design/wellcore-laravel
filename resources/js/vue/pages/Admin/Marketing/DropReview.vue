<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAdminMarketingStore } from '../../../stores/adminMarketing';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import StatusPill from '../../../components/admin/marketing/StatusPill.vue';
import DropContentEditor from '../../../components/admin/marketing/DropContentEditor.vue';
import DropAssetUploader from '../../../components/admin/marketing/DropAssetUploader.vue';

const route = useRoute();
const router = useRouter();
const store = useAdminMarketingStore();

const dropId = Number(route.params.id);
const isApproving = ref(false);
const isRegenerating = ref(false);
const actionError = ref(null);

onMounted(() => {
    store.fetchDrop(dropId);
});

async function saveContent(newContent) {
    actionError.value = null;
    try {
        await store.updateDropContent(dropId, newContent);
    } catch (e) {
        actionError.value = e?.response?.data?.message ?? e?.message ?? 'Error al guardar contenido';
    }
}

async function approve() {
    if (!confirm('Aprobar y publicar este drop al coach?')) return;
    isApproving.value = true;
    actionError.value = null;
    try {
        await store.approveDrop(dropId);
        router.push({ name: 'admin-marketing-queue' });
    } catch (e) {
        actionError.value = e?.response?.data?.message ?? e?.message ?? 'Error al aprobar';
    } finally {
        isApproving.value = false;
    }
}

async function requestRegenerate() {
    const reason = prompt('Razon para regenerar (opcional):');
    if (reason === null) return; // user cancelled
    isRegenerating.value = true;
    actionError.value = null;
    try {
        await store.requestRegenerate(dropId, reason || undefined);
        router.push({ name: 'admin-marketing-queue' });
    } catch (e) {
        actionError.value = e?.response?.data?.message ?? e?.message ?? 'Error al solicitar regeneracion';
    } finally {
        isRegenerating.value = false;
    }
}
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl px-6 py-10 space-y-8">

      <!-- Back link -->
      <div>
        <RouterLink
          :to="{ name: 'admin-marketing-queue' }"
          class="inline-flex items-center gap-1.5 font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary hover:text-wc-text transition-colors"
        >
          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
          Volver a la cola
        </RouterLink>
      </div>

      <!-- Loading -->
      <div v-if="store.isLoadingDrop" class="space-y-4">
        <div v-for="n in 3" :key="n" class="h-20 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>

      <!-- Drop found -->
      <template v-else-if="store.selectedDrop">
        <!-- Header -->
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-tertiary">
              WC · ADMIN / REVIEW / {{ store.selectedDrop.iso_year }}-W{{ String(store.selectedDrop.iso_week).padStart(2, '0') }}
            </p>
            <h1 class="mt-2 font-display text-4xl uppercase tracking-tight text-wc-text">
              Revisar drop
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-3">
              <StatusPill :status="store.selectedDrop.status" />
              <span class="font-mono text-xs text-wc-text-tertiary">
                Coach: {{ store.selectedDrop.coach?.name ?? `#${store.selectedDrop.coach_id}` }}
              </span>
              <RouterLink
                v-if="store.selectedDrop.coach_id"
                :to="`/admin/marketing/coaches/${store.selectedDrop.coach_id}/profile`"
                class="font-mono text-[10px] uppercase tracking-wide text-wc-accent hover:underline"
              >
                Ver perfil marketing →
              </RouterLink>
            </div>
          </div>

          <!-- Action buttons -->
          <div class="flex flex-wrap items-center gap-3">
            <button
              v-if="actionError"
              disabled
              class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 font-mono text-xs text-red-400"
            >
              {{ actionError }}
            </button>
            <button
              @click="requestRegenerate"
              :disabled="isRegenerating || isApproving"
              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-5 py-2.5 font-mono text-xs uppercase tracking-[0.1em] text-wc-text transition-colors hover:border-wc-text-secondary disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ isRegenerating ? 'Solicitando...' : 'Pedir regenerar' }}
            </button>
            <button
              @click="approve"
              :disabled="isApproving || isRegenerating"
              class="rounded-lg bg-wc-accent px-5 py-2.5 font-mono text-xs uppercase tracking-[0.1em] text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ isApproving ? 'Aprobando...' : 'Aprobar y publicar' }}
            </button>
          </div>
        </div>

        <!-- Split layout: intake snapshot | content editor -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <!-- Left: intake snapshot -->
          <div class="flex flex-col gap-4">
            <div>
              <h2 class="font-display text-2xl uppercase tracking-tight text-wc-text">Intake del coach</h2>
              <p class="mt-1 text-xs text-wc-text-tertiary">Datos declarados por el coach en su semana de intake.</p>
            </div>
            <div class="overflow-auto rounded-xl border border-wc-border bg-wc-bg-secondary p-4 lg:max-h-[80vh]">
              <pre class="whitespace-pre-wrap font-mono text-[11px] leading-relaxed text-wc-text-secondary">{{ JSON.stringify(store.selectedDrop.intake_snapshot, null, 2) }}</pre>
            </div>
          </div>

          <!-- Right: content editor -->
          <div class="flex flex-col gap-4">
            <div>
              <h2 class="font-display text-2xl uppercase tracking-tight text-wc-text">Drop content</h2>
              <p class="mt-1 text-xs text-wc-text-tertiary">Edita el contenido generado antes de aprobar.</p>
            </div>
            <DropContentEditor
              :content="store.selectedDrop.content"
              @save="saveContent"
            />
          </div>
        </div>

        <!-- Assets uploader (full width, below split) -->
        <DropAssetUploader
          :drop-id="store.selectedDrop.id"
          :assets="store.selectedDrop.content?.assets ?? []"
          :stories="store.selectedDrop.content?.stories ?? []"
          :reels="store.selectedDrop.content?.reels ?? []"
        />
      </template>

      <!-- Not found -->
      <div v-else class="py-16 text-center">
        <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-text-tertiary">Drop no encontrado.</p>
        <RouterLink
          :to="{ name: 'admin-marketing-queue' }"
          class="mt-4 inline-block font-mono text-xs text-wc-accent hover:underline"
        >
          Volver a la cola
        </RouterLink>
      </div>

    </div>
  </AdminLayout>
</template>
