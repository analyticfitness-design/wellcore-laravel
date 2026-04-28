<script setup>
import { computed, ref } from 'vue';
import { coachStrategyApi } from '../../../api/coachStrategy';

const props = defineProps({
    dropId: { type: [Number, String], required: true },
    assets: { type: Array, default: () => [] },
});

const lightboxIndex = ref(null);
const downloadingId = ref(null);

const sortedAssets = computed(() => {
    return [...(props.assets ?? [])].sort((a, b) => {
        const oa = a.order ?? 999;
        const ob = b.order ?? 999;
        if (oa !== ob) return oa - ob;
        return String(a.role ?? '').localeCompare(String(b.role ?? ''));
    });
});

function isImage(asset) {
    const mime = (asset.mime_type || '').toLowerCase();
    if (mime.startsWith('image/')) return true;
    return /\.(jpg|jpeg|png|gif|webp|avif|svg)$/i.test(asset.filename || asset.url || '');
}

function isVideo(asset) {
    const mime = (asset.mime_type || '').toLowerCase();
    if (mime.startsWith('video/')) return true;
    return /\.(mp4|mov|webm|m4v)$/i.test(asset.filename || asset.url || '');
}

function fileExt(asset) {
    const m = /\.([a-z0-9]+)$/i.exec(asset.filename || '');
    return m ? m[1].toUpperCase() : 'FILE';
}

function fileSizeKB(asset) {
    if (!asset.size) return null;
    const kb = asset.size / 1024;
    return kb >= 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${Math.round(kb)} KB`;
}

function openLightbox(idx) {
    lightboxIndex.value = idx;
}
function closeLightbox() {
    lightboxIndex.value = null;
}
function prev() {
    if (lightboxIndex.value === null) return;
    lightboxIndex.value = (lightboxIndex.value - 1 + sortedAssets.value.length) % sortedAssets.value.length;
}
function next() {
    if (lightboxIndex.value === null) return;
    lightboxIndex.value = (lightboxIndex.value + 1) % sortedAssets.value.length;
}

async function downloadOne(asset) {
    if (downloadingId.value) return;
    downloadingId.value = asset.id;
    try {
        await coachStrategyApi.downloadSingle(props.dropId, asset);
    } finally {
        setTimeout(() => { downloadingId.value = null; }, 600);
    }
}

const currentAsset = computed(() =>
    lightboxIndex.value === null ? null : sortedAssets.value[lightboxIndex.value],
);
</script>

<template>
    <div v-if="sortedAssets.length" class="mt-12">
        <div class="mb-4 flex items-baseline justify-between">
            <h3 class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                Assets visuales · {{ sortedAssets.length }}
            </h3>
            <span class="font-mono text-[10px] text-wc-text-tertiary">
                Click en cada pieza para verla en grande
            </span>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            <div
                v-for="(asset, idx) in sortedAssets"
                :key="asset.id || idx"
                class="group relative overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary transition-colors hover:border-wc-accent"
            >
                <button
                    type="button"
                    @click="openLightbox(idx)"
                    class="block aspect-square w-full overflow-hidden bg-black"
                >
                    <img
                        v-if="isImage(asset)"
                        :src="asset.url"
                        :alt="asset.filename"
                        loading="lazy"
                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                    <video
                        v-else-if="isVideo(asset)"
                        :src="asset.url"
                        muted
                        playsinline
                        preload="metadata"
                        class="h-full w-full object-cover"
                    ></video>
                    <div
                        v-else
                        class="flex h-full w-full flex-col items-center justify-center gap-1 text-wc-text-tertiary"
                    >
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span class="font-mono text-[10px] uppercase tracking-wider">{{ fileExt(asset) }}</span>
                    </div>
                </button>

                <div class="px-2 py-2">
                    <div class="truncate font-mono text-[10px] uppercase tracking-wider text-wc-text-secondary" :title="asset.filename">
                        {{ asset.role || asset.filename }}
                    </div>
                    <div class="mt-0.5 flex items-center justify-between">
                        <span class="truncate font-mono text-[9px] text-wc-text-tertiary" :title="asset.filename">
                            {{ asset.filename }}
                        </span>
                        <button
                            type="button"
                            @click.stop="downloadOne(asset)"
                            :disabled="downloadingId === asset.id"
                            :title="`Descargar ${asset.filename}`"
                            class="ml-2 inline-flex shrink-0 items-center gap-1 rounded border border-wc-border bg-wc-bg-tertiary px-2 py-1 text-wc-text-secondary transition-colors hover:border-wc-accent hover:text-wc-accent disabled:opacity-50"
                        >
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="currentAsset"
                    class="fixed inset-0 z-[110] flex items-center justify-center bg-black/95 p-4"
                    @click.self="closeLightbox"
                    @keyup.esc="closeLightbox"
                    tabindex="0"
                >
                    <button
                        type="button"
                        @click="closeLightbox"
                        class="absolute right-4 top-4 rounded-full border border-wc-border bg-black/50 p-2 text-white transition-colors hover:border-wc-accent hover:text-wc-accent"
                        title="Cerrar"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <button
                        v-if="sortedAssets.length > 1"
                        type="button"
                        @click="prev"
                        class="absolute left-4 rounded-full border border-wc-border bg-black/50 p-3 text-white transition-colors hover:border-wc-accent hover:text-wc-accent"
                        title="Anterior"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <button
                        v-if="sortedAssets.length > 1"
                        type="button"
                        @click="next"
                        class="absolute right-4 rounded-full border border-wc-border bg-black/50 p-3 text-white transition-colors hover:border-wc-accent hover:text-wc-accent"
                        title="Siguiente"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <div class="flex max-h-[90vh] max-w-[90vw] flex-col items-center gap-4">
                        <img
                            v-if="isImage(currentAsset)"
                            :src="currentAsset.url"
                            :alt="currentAsset.filename"
                            class="max-h-[78vh] max-w-full rounded-lg object-contain"
                        />
                        <video
                            v-else-if="isVideo(currentAsset)"
                            :src="currentAsset.url"
                            controls
                            autoplay
                            playsinline
                            class="max-h-[78vh] max-w-full rounded-lg"
                        ></video>
                        <div
                            v-else
                            class="flex flex-col items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-12 py-16 text-wc-text-secondary"
                        >
                            <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span class="font-mono text-xs">{{ fileExt(currentAsset) }} · {{ currentAsset.filename }}</span>
                            <span v-if="fileSizeKB(currentAsset)" class="font-mono text-[10px] text-wc-text-tertiary">
                                {{ fileSizeKB(currentAsset) }}
                            </span>
                        </div>

                        <div class="flex w-full max-w-md items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-mono text-[11px] uppercase tracking-wider text-white">
                                    {{ currentAsset.role || currentAsset.filename }}
                                </div>
                                <div class="truncate font-mono text-[10px] text-wc-text-tertiary">
                                    {{ currentAsset.filename }}
                                    <span v-if="sortedAssets.length > 1" class="ml-2">
                                        · {{ lightboxIndex + 1 }} / {{ sortedAssets.length }}
                                    </span>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="downloadOne(currentAsset)"
                                :disabled="downloadingId === currentAsset.id"
                                class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-wc-accent bg-wc-accent/10 px-4 py-2 font-mono text-xs uppercase tracking-[0.15em] text-wc-accent transition-colors hover:bg-wc-accent hover:text-white disabled:opacity-50"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                                </svg>
                                {{ downloadingId === currentAsset.id ? 'Descargando...' : 'Descargar' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
