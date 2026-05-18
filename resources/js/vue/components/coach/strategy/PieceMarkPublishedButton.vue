<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCoachStrategyStore } from '../../../stores/coachStrategy';

const { t } = useI18n();

const props = defineProps({
    pieceKey: { type: String, required: true },
    dropId: { type: Number, required: true },
    state: { type: String, default: 'pending' },
});

const store = useCoachStrategyStore();

const open = ref(false);
const showPublishForm = ref(false);
const url = ref('');
const notes = ref('');
const submitting = ref(false);
const root = ref(null);

const stateConfig = computed(() => {
    switch (props.state) {
        case 'in_progress':
            return { label: t('coach_growth.strategy.piece_state_in_progress'), cls: 'border-amber-500/60 text-amber-400' };
        case 'published':
            return { label: t('coach_growth.strategy.piece_state_published'), cls: 'border-emerald-500/60 bg-emerald-500/10 text-emerald-400' };
        case 'skipped':
            return { label: t('coach_growth.strategy.piece_state_skipped'), cls: 'border-wc-border text-wc-text-tertiary opacity-50' };
        default:
            return { label: t('coach_growth.strategy.piece_state_default'), cls: 'border-wc-border text-wc-text-secondary' };
    }
});

function toggleMenu() {
    open.value = !open.value;
    showPublishForm.value = false;
}

function closeAll() {
    open.value = false;
    showPublishForm.value = false;
}

function onClickOutside(e) {
    if (root.value && !root.value.contains(e.target)) {
        closeAll();
    }
}
document.addEventListener('click', onClickOutside);
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside));

async function publishNow() {
    submitting.value = true;
    try {
        await store.markPiecePublished(props.pieceKey, url.value || null, notes.value || null);
        url.value = '';
        notes.value = '';
        closeAll();
    } finally {
        submitting.value = false;
    }
}

async function inProgressNow() {
    submitting.value = true;
    try {
        await store.markPieceInProgress(props.pieceKey);
        closeAll();
    } finally {
        submitting.value = false;
    }
}

async function skipNow() {
    if (!confirm(t('coach_growth.strategy.piece_skip_confirm'))) return;
    submitting.value = true;
    try {
        await store.markPieceSkipped(props.pieceKey);
        closeAll();
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div ref="root" class="relative inline-block text-left">
        <button
            type="button"
            @click.stop="toggleMenu"
            class="inline-flex items-center gap-2 rounded-md border px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] transition-colors"
            :class="stateConfig.cls"
        >
            {{ stateConfig.label }}
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <Transition name="fade">
            <div
                v-if="open"
                class="absolute right-0 z-30 mt-2 w-72 rounded-lg border border-wc-border bg-wc-bg-secondary p-2 shadow-xl"
            >
                <div v-if="!showPublishForm" class="space-y-1">
                    <button
                        type="button"
                        @click.stop="showPublishForm = true"
                        class="block w-full rounded px-3 py-2 text-left text-sm text-wc-text hover:bg-wc-bg-tertiary"
                    >
                        {{ t('coach_growth.strategy.piece_mark_published') }}
                    </button>
                    <button
                        type="button"
                        :disabled="submitting"
                        @click.stop="inProgressNow"
                        class="block w-full rounded px-3 py-2 text-left text-sm text-wc-text hover:bg-wc-bg-tertiary disabled:opacity-50"
                    >
                        {{ t('coach_growth.strategy.piece_mark_in_progress') }}
                    </button>
                    <button
                        type="button"
                        :disabled="submitting"
                        @click.stop="skipNow"
                        class="block w-full rounded px-3 py-2 text-left text-sm text-wc-text-secondary hover:bg-wc-bg-tertiary disabled:opacity-50"
                    >
                        {{ t('coach_growth.strategy.piece_skip') }}
                    </button>
                </div>

                <form v-else @submit.prevent="publishNow" class="space-y-3 p-2">
                    <label class="block text-xs uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_growth.strategy.piece_url_label') }}</label>
                    <input
                        v-model="url"
                        type="url"
                        :placeholder="t('coach_growth.strategy.piece_url_placeholder')"
                        class="w-full rounded border border-wc-border bg-wc-bg px-2 py-1.5 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                    />
                    <label class="block text-xs uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_growth.strategy.piece_notes_label') }}</label>
                    <textarea
                        v-model="notes"
                        rows="2"
                        class="w-full rounded border border-wc-border bg-wc-bg px-2 py-1.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                    ></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click.stop="showPublishForm = false" class="text-xs text-wc-text-secondary">{{ t('coach_growth.strategy.piece_cancel') }}</button>
                        <button type="submit" :disabled="submitting" class="rounded bg-wc-accent px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-white disabled:opacity-50">
                            {{ submitting ? t('coach_growth.strategy.piece_saving') : t('coach_growth.strategy.piece_publish') }}
                        </button>
                    </div>
                </form>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
