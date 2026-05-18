<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { coachStrategyApi } from '../../../api/coachStrategy';

const { t } = useI18n();

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

function onKey(e) {
    if (lightboxIndex.value === null) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') prev();
    if (e.key === 'ArrowRight') next();
}
onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => window.removeEventListener('keydown', onKey));
</script>

<template>
    <section v-if="sortedAssets.length" class="gallery">
        <div class="gallery-head">
            <div style="display:flex;align-items:center;gap:.625rem;">
                <span class="ic ic-violet ic-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 21l6.75-6.75 2.25 2.25 3-3L21 21M3.75 3h16.5M3.75 3v16.5"/>
                    </svg>
                </span>
                <span class="gal-label">{{ sortedAssets.length === 1 ? t('coach_growth.strategy.gallery_label_one', { count: sortedAssets.length }) : t('coach_growth.strategy.gallery_label_many', { count: sortedAssets.length }) }}</span>
            </div>
        </div>

        <div class="gallery-grid">
            <div
                v-for="(asset, idx) in sortedAssets"
                :key="asset.id || idx"
                class="asset-card"
                @click="openLightbox(idx)"
            >
                <span class="asset-order" aria-hidden="true">{{ String(idx + 1).padStart(2, '0') }}</span>
                <span v-if="asset.role" class="asset-role-tag">{{ asset.role }}</span>
                <div class="asset-thumb-wrap">
                    <img
                        v-if="isImage(asset)"
                        :src="asset.url"
                        :alt="asset.filename"
                        loading="lazy"
                        class="asset-thumb-img"
                    />
                    <video
                        v-else-if="isVideo(asset)"
                        :src="asset.url"
                        muted
                        playsinline
                        preload="metadata"
                        class="asset-thumb-img"
                    ></video>
                    <div v-else class="asset-fallback">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        <span>{{ fileExt(asset) }}</span>
                    </div>
                </div>
                <div class="asset-overlay">
                    <span class="asset-overlay-file">{{ asset.filename }}</span>
                    <button
                        type="button"
                        class="asset-overlay-dl"
                        :disabled="downloadingId === asset.id"
                        @click.stop="downloadOne(asset)"
                    >{{ t('coach_growth.strategy.gallery_download') }}</button>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="currentAsset"
                    id="lightbox"
                    class="open"
                    role="dialog"
                    aria-modal="true"
                    tabindex="0"
                    @click.self="closeLightbox"
                >
                    <button type="button" class="lb-close-btn" @click="closeLightbox">{{ t('coach_growth.strategy.gallery_lb_close') }}</button>
                    <button
                        v-if="sortedAssets.length > 1"
                        type="button"
                        class="lb-nav lb-prev"
                        :aria-label="t('coach_growth.strategy.gallery_lb_prev')"
                        @click="prev"
                    >‹</button>
                    <button
                        v-if="sortedAssets.length > 1"
                        type="button"
                        class="lb-nav lb-next"
                        :aria-label="t('coach_growth.strategy.gallery_lb_next')"
                        @click="next"
                    >›</button>

                    <img
                        v-if="isImage(currentAsset)"
                        id="lb-img"
                        :src="currentAsset.url"
                        :alt="currentAsset.filename"
                    />
                    <video
                        v-else-if="isVideo(currentAsset)"
                        id="lb-img"
                        :src="currentAsset.url"
                        controls
                        autoplay
                        playsinline
                    ></video>
                    <div v-else class="lb-fallback">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        <span>{{ fileExt(currentAsset) }} · {{ currentAsset.filename }}</span>
                        <span v-if="fileSizeKB(currentAsset)" class="lb-fallback-size">{{ fileSizeKB(currentAsset) }}</span>
                    </div>

                    <div class="lb-bottom">
                        <span class="lb-meta">
                            <span class="lb-counter">{{ lightboxIndex + 1 }} / {{ sortedAssets.length }}</span>
                            · <span>{{ currentAsset.filename }}</span>
                        </span>
                        <button
                            type="button"
                            class="lb-dl-btn"
                            :disabled="downloadingId === currentAsset.id"
                            @click="downloadOne(currentAsset)"
                        >
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                            </svg>
                            {{ downloadingId === currentAsset.id ? t('coach_growth.strategy.gallery_lb_downloading') : t('coach_growth.strategy.gallery_lb_download') }}
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>
