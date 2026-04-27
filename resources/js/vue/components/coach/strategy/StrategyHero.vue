<script setup>
import { computed, ref } from 'vue';
import { coachStrategyApi } from '../../../api/coachStrategy';

const props = defineProps({
    drop: { type: Object, required: true },
});

const downloadingZip = ref(false);
const zipError = ref(null);

const assetCount = computed(() => props.drop.content?.assets?.length ?? 0);

async function downloadAllAssets() {
    if (!assetCount.value) return;
    downloadingZip.value = true;
    zipError.value = null;
    try {
        await coachStrategyApi.downloadZip(props.drop.id);
    } catch (e) {
        zipError.value = e?.response?.data?.message ?? 'Error al generar ZIP';
    } finally {
        downloadingZip.value = false;
    }
}

const totalPieces = computed(() => props.drop.pieces?.length ?? 0);
const publishedPieces = computed(
    () => props.drop.pieces?.filter((p) => p.state === 'published').length ?? 0,
);
const progressPct = computed(() => {
    if (totalPieces.value === 0) return 0;
    return Math.round((publishedPieces.value / totalPieces.value) * 100);
});

const weekLabel = computed(
    () => `WC · ESTRATEGIA / SEMANA-${String(props.drop.iso_week).padStart(2, '0')} / ${props.drop.iso_year}`,
);

const weeklyTheme = computed(() => props.drop.content?.brief?.weekly_theme ?? '');
const briefTitle = computed(() => props.drop.content?.brief?.title ?? '');
</script>

<template>
    <section class="relative pt-12">
        <span class="font-mono text-[11px] uppercase tracking-[0.3em] text-wc-text-tertiary">
            {{ weekLabel }}
        </span>

        <h1
            class="mt-6 max-w-5xl font-display text-[clamp(2.75rem,7vw,5rem)] uppercase leading-[0.95] tracking-tight text-wc-text"
        >
            {{ weeklyTheme }}
        </h1>

        <p
            v-if="briefTitle"
            class="mt-6 max-w-3xl font-editorial text-2xl italic leading-snug text-wc-text-secondary"
        >
            {{ briefTitle }}
        </p>

        <p
            v-if="drop.attribution"
            class="mt-8 font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary"
        >
            {{ drop.attribution }}
        </p>

        <div class="mt-10 flex flex-wrap items-center gap-6">
            <div class="flex-1 min-w-[280px]">
                <div class="flex items-baseline justify-between">
                    <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                        Avance semanal
                    </span>
                    <span class="font-data text-sm tabular-nums text-wc-text">
                        {{ publishedPieces }} / {{ totalPieces }}
                        <span class="ml-2 text-wc-text-tertiary">·</span>
                        <span class="ml-2 text-wc-accent">{{ progressPct }}%</span>
                    </span>
                </div>
                <div class="mt-2 h-px w-full bg-wc-border">
                    <div
                        class="h-full bg-wc-accent transition-all duration-700 ease-out"
                        :style="{ width: `${progressPct}%` }"
                    ></div>
                </div>
            </div>
            <div v-if="assetCount > 0" class="flex flex-col items-end gap-2">
                <button
                    type="button"
                    @click="downloadAllAssets"
                    :disabled="downloadingZip"
                    class="flex items-center gap-2 rounded-lg border border-wc-accent bg-wc-accent/10 px-5 py-2.5 font-mono text-xs uppercase tracking-[0.15em] text-wc-accent transition-colors hover:bg-wc-accent hover:text-white disabled:opacity-50"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                    </svg>
                    {{ downloadingZip ? 'Generando ZIP...' : `Descargar todo (${assetCount})` }}
                </button>
                <span v-if="zipError" class="font-mono text-[10px] text-red-400">{{ zipError }}</span>
            </div>
        </div>
    </section>
</template>
