<script setup>
import { computed } from 'vue';

const props = defineProps({
    drop: { type: Object, required: true },
});

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

        <div class="mt-10 flex items-center gap-6">
            <div class="flex-1">
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
        </div>
    </section>
</template>
