<script setup>
import { ref, computed } from 'vue';
import { useAdminMarketingStore } from '../../../stores/adminMarketing';

const props = defineProps({
    dropId:  { type: Number, required: true },
    assets:  { type: Array,  default: () => [] },
    stories: { type: Array,  default: () => [] },
    reels:   { type: Array,  default: () => [] },
});

const store = useAdminMarketingStore();

const ROLE_OPTIONS = [
    { value: 'story_main',       label: 'Story · cover' },
    { value: 'story_slide',      label: 'Story · slide' },
    { value: 'reel_thumbnail',   label: 'Reel · miniatura' },
    { value: 'reel_scene',       label: 'Reel · escena' },
    { value: 'launch_sequence',  label: 'Secuencia de lanzamiento' },
    { value: 'brand_cover',      label: 'Cover de marca' },
    { value: 'other',            label: 'Otro' },
];

const fileRef = ref(null);
const dragActive = ref(false);
const uploading = ref(false);
const progress = ref(0);
const error = ref(null);

const meta = ref({
    role: 'launch_sequence',
    linked_to_type: '',  // '', 'story', 'reel', 'drop'
    linked_to_day: '',
    linked_to_reel_key: '',
    linked_to_slide_index: '',
    caption: '',
    order: '',
    notes: '',
});

const sortedAssets = computed(() => {
    const list = [...(props.assets ?? [])];
    list.sort((a, b) => {
        const oa = a.order ?? 999;
        const ob = b.order ?? 999;
        if (oa !== ob) return oa - ob;
        return (a.uploaded_at ?? '').localeCompare(b.uploaded_at ?? '');
    });
    return list;
});

const dayOptions = ['LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB', 'DOM'];

function buildLinkedTo() {
    const lt = {};
    if (meta.value.linked_to_type) lt.type = meta.value.linked_to_type;
    if (meta.value.linked_to_day) lt.day = meta.value.linked_to_day;
    if (meta.value.linked_to_reel_key) lt.reel_key = meta.value.linked_to_reel_key;
    if (meta.value.linked_to_slide_index !== '' && meta.value.linked_to_slide_index !== null) {
        lt.slide_index = parseInt(meta.value.linked_to_slide_index, 10);
    }
    return Object.keys(lt).length ? lt : null;
}

async function handleFiles(fileList) {
    if (!fileList || !fileList.length) return;
    error.value = null;
    uploading.value = true;
    progress.value = 0;
    try {
        for (const file of Array.from(fileList)) {
            const orderValue = meta.value.order !== '' ? parseInt(meta.value.order, 10) : null;
            await store.uploadAsset(
                props.dropId,
                file,
                {
                    role:      meta.value.role || null,
                    linked_to: buildLinkedTo(),
                    caption:   meta.value.caption || null,
                    notes:     meta.value.notes || null,
                    order:     Number.isInteger(orderValue) ? orderValue : null,
                },
                (p) => { progress.value = p; },
            );
            // Auto-bump order so the next upload of the same batch gets the next slot.
            if (meta.value.order !== '' && Number.isInteger(orderValue)) {
                meta.value.order = String(orderValue + 1);
            }
        }
    } catch (e) {
        error.value = e?.response?.data?.message ?? e?.message ?? 'Error al subir';
    } finally {
        uploading.value = false;
        progress.value = 0;
        if (fileRef.value) fileRef.value.value = '';
    }
}

function onDrop(e) {
    e.preventDefault();
    dragActive.value = false;
    handleFiles(e.dataTransfer?.files);
}

function onDragOver(e) {
    e.preventDefault();
    dragActive.value = true;
}

async function removeAsset(asset) {
    if (!confirm(`Eliminar asset ${asset.filename}?`)) return;
    try {
        await store.deleteAsset(props.dropId, asset.id);
    } catch (e) {
        error.value = e?.response?.data?.message ?? e?.message ?? 'Error al eliminar';
    }
}

function linkLabel(asset) {
    const lt = asset.linked_to;
    if (!lt) return asset.role ? roleLabel(asset.role) : '—';
    if (lt.type === 'story' && lt.day) return `Story · ${lt.day}`;
    if (lt.type === 'reel' && lt.reel_key) return lt.reel_key === 'reel_1' ? 'Reel 1' : 'Reel 2';
    if (lt.type === 'slide' && lt.day) return `Slide · ${lt.day}${Number.isInteger(lt.slide_index) ? ` · ${lt.slide_index + 1}` : ''}`;
    return asset.role ? roleLabel(asset.role) : '—';
}

function roleLabel(role) {
    return ROLE_OPTIONS.find((r) => r.value === role)?.label ?? role;
}
</script>

<template>
    <section class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="font-display text-xl uppercase tracking-tight text-wc-text">Assets · Imágenes / video</h3>
                <p class="mt-1 text-xs text-wc-text-tertiary">Sube los JPG / PNG / MP4 que el coach va a publicar. Quedan asociados al drop y descargables desde su panel.</p>
            </div>
            <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ assets.length }} archivo(s)</span>
        </div>

        <!-- Meta: role + linked_to + caption + order -->
        <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
            <label class="block">
                <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Rol</span>
                <select v-model="meta.role" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text">
                    <option v-for="opt in ROLE_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
            </label>
            <label class="block">
                <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Asociar a</span>
                <select v-model="meta.linked_to_type" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text">
                    <option value="">(sin asociar)</option>
                    <option value="story">Story (un día)</option>
                    <option value="reel">Reel</option>
                    <option value="slide">Slide específico</option>
                    <option value="drop">Drop completo</option>
                </select>
            </label>
            <label v-if="meta.linked_to_type === 'story' || meta.linked_to_type === 'slide'" class="block">
                <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Día</span>
                <select v-model="meta.linked_to_day" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text">
                    <option value="">—</option>
                    <option v-for="d in dayOptions" :key="d" :value="d">{{ d }}</option>
                </select>
            </label>
            <label v-if="meta.linked_to_type === 'reel'" class="block">
                <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Reel</span>
                <select v-model="meta.linked_to_reel_key" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text">
                    <option value="">—</option>
                    <option value="reel_1">Reel 1</option>
                    <option value="reel_2">Reel 2</option>
                </select>
            </label>
            <label v-if="meta.linked_to_type === 'slide'" class="block">
                <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Slide #</span>
                <input v-model="meta.linked_to_slide_index" type="number" min="0" max="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text" />
            </label>
            <label class="block">
                <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Orden (secuencia)</span>
                <input v-model="meta.order" type="number" min="0" max="100" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text" />
            </label>
            <label class="block md:col-span-2">
                <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Caption (opcional)</span>
                <input v-model="meta.caption" maxlength="200" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text" />
            </label>
            <label class="block md:col-span-3">
                <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Notas internas (opcional)</span>
                <input v-model="meta.notes" maxlength="400" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text" />
            </label>
        </div>

        <!-- Drop zone -->
        <div
            class="rounded-xl border-2 border-dashed p-8 text-center transition-colors"
            :class="dragActive ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg/40'"
            @dragenter.prevent="dragActive = true"
            @dragover="onDragOver"
            @dragleave="dragActive = false"
            @drop="onDrop"
        >
            <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-text-secondary">Arrastra archivos o haz click</p>
            <p class="mt-1 font-mono text-[10px] text-wc-text-tertiary">JPG / PNG / WEBP / MP4 — máx 50 MB</p>
            <input ref="fileRef" type="file" multiple accept="image/jpeg,image/png,image/webp,video/mp4" class="hidden" @change="(e) => handleFiles(e.target.files)" />
            <button type="button" @click="fileRef?.click()" :disabled="uploading"
                class="mt-4 rounded-lg border border-wc-border bg-wc-bg px-4 py-2 font-mono text-xs uppercase tracking-[0.15em] text-wc-text transition-colors hover:border-wc-accent hover:text-wc-accent disabled:opacity-50">
                {{ uploading ? `Subiendo ${progress}%` : 'Seleccionar archivos' }}
            </button>
            <p v-if="error" class="mt-3 font-mono text-xs text-red-400">{{ error }}</p>
        </div>

        <!-- Assets grid -->
        <div v-if="sortedAssets.length" class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
            <article v-for="asset in sortedAssets" :key="asset.id"
                class="group relative overflow-hidden rounded-lg border border-wc-border bg-wc-bg">
                <div class="relative aspect-[9/16] bg-black">
                    <img v-if="asset.kind === 'image'" :src="asset.url" :alt="asset.filename" loading="lazy" class="absolute inset-0 h-full w-full object-cover" />
                    <div v-else class="absolute inset-0 flex items-center justify-center text-wc-text-tertiary font-mono text-[10px] uppercase tracking-[0.15em]">
                        VIDEO · {{ asset.filename }}
                    </div>
                    <button @click="removeAsset(asset)" type="button"
                        class="absolute right-2 top-2 hidden rounded-full bg-black/70 p-1.5 text-white backdrop-blur transition-opacity hover:bg-red-500/90 group-hover:block"
                        aria-label="Eliminar">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-2.5 space-y-1">
                    <p class="truncate font-mono text-[10px] uppercase tracking-[0.1em] text-wc-text">{{ asset.filename }}</p>
                    <p class="truncate font-mono text-[9px] uppercase tracking-[0.1em] text-wc-accent">{{ linkLabel(asset) }}</p>
                    <p v-if="asset.width && asset.height" class="font-mono text-[9px] text-wc-text-tertiary">{{ asset.width }} × {{ asset.height }}</p>
                </div>
            </article>
        </div>
    </section>
</template>
