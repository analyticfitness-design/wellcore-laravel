<script setup>
import { computed, ref } from 'vue';
import { coachStrategyApi } from '../../../api/coachStrategy';
import { useViewportAnimate } from '../../../composables/dashboard/useViewportAnimate';

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

const weekNumberPadded = computed(() => String(props.drop.iso_week ?? 0).padStart(2, '0'));
const weekLabel = computed(
    () => `WC · ESTRATEGIA / SEMANA-${weekNumberPadded.value} / ${props.drop.iso_year}`,
);

const briefTitle = computed(() => props.drop.content?.brief?.title ?? '');
const weeklyTheme = computed(() => props.drop.content?.brief?.weekly_theme ?? '');

// Ring animation
const circumference = 2 * Math.PI * 36; // ≈ 226.19
const ringOffset = computed(() => circumference - (circumference * progressPct.value) / 100);
const { targetRef: ringRef, visible: ringVisible } = useViewportAnimate({ threshold: 0.3 });

// Title HTML: escape, then wrap **bold** as <span class="outline">. No <br> insertion.
function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

const titleHtml = computed(() => {
    const safe = escapeHtml(weeklyTheme.value);
    // Match **word** or __word__ markdown bold and convert to outlined span
    return safe.replace(/\*\*(.+?)\*\*|__(.+?)__/g, (_, a, b) => {
        const word = (a ?? b ?? '').toUpperCase();
        return `<span class="outline">${word}</span>`;
    });
});
</script>

<template>
    <section class="hero">
        <div class="hero-bg-num" aria-hidden="true">{{ weekNumberPadded }}</div>
        <div class="hero-glass">
            <div class="hero-inner">
                <div class="hero-stripe">
                    <span class="ic ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </span>
                    <span class="stripe-label">{{ weekLabel }}</span>
                </div>

                <h1 class="hero-title" v-html="titleHtml"></h1>

                <p v-if="briefTitle" class="hero-subtitle">{{ briefTitle }}</p>

                <div class="hero-bottom">
                    <div v-if="drop.attribution" class="attrib-pill">
                        <span class="ic ic-amber ic-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </span>
                        {{ drop.attribution }}
                    </div>

                    <div class="hero-right">
                        <div class="ring-wrap" ref="ringRef">
                            <svg class="ring-svg" width="72" height="72" viewBox="0 0 72 72">
                                <circle class="ring-track" cx="36" cy="36" r="36" stroke-width="5" />
                                <circle
                                    class="ring-fill"
                                    cx="36"
                                    cy="36"
                                    r="36"
                                    stroke-width="5"
                                    :stroke-dashoffset="ringVisible ? ringOffset : circumference"
                                />
                            </svg>
                            <div class="ring-labels">
                                <span class="ring-num">{{ publishedPieces }}/<span>{{ totalPieces }}</span></span>
                                <span class="ring-sub">PIEZAS</span>
                            </div>
                        </div>

                        <button
                            v-if="assetCount > 0"
                            type="button"
                            class="dl-btn"
                            @click="downloadAllAssets"
                            :disabled="downloadingZip"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            {{ downloadingZip ? 'Generando ZIP...' : `Descargar todo (${assetCount})` }}
                        </button>
                        <span v-if="zipError" class="font-mono text-[10px] text-red-400">{{ zipError }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
