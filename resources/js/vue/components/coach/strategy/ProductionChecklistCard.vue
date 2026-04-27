<script setup>
import { ref, reactive, computed } from 'vue';
import { useCoachStrategyStore } from '../../../stores/coachStrategy';

const props = defineProps({
    checklist: { type: Object, required: true },
    dropId: { type: Number, required: true },
    pieces: { type: Array, default: () => [] },
});

const store = useCoachStrategyStore();

const phaseLabels = {
    pre: 'Pre-producción',
    cam: 'Cámara',
    edit: 'Edición',
    pub: 'Publicación',
};

const phases = computed(() => props.checklist?.phases ?? []);

function itemKey(phase, itemIdx) {
    return `phase_${phase.key}_item_${itemIdx}`;
}

function pieceForItem(phase, itemIdx) {
    const key = itemKey(phase, itemIdx);
    return props.pieces.find((p) => p.piece_key === key) ?? null;
}

function isChecked(phase, itemIdx) {
    const piece = pieceForItem(phase, itemIdx);
    return piece?.state === 'published';
}

const pendingKeys = reactive(new Set());

async function toggleItem(phase, itemIdx) {
    const key = itemKey(phase, itemIdx);
    if (pendingKeys.has(key)) return;
    pendingKeys.add(key);
    try {
        if (isChecked(phase, itemIdx)) {
            await store.markPieceInProgress(key);
        } else {
            await store.markPiecePublished(key, null, null);
        }
    } catch (e) {
        console.error('checklist toggle failed', e);
    } finally {
        pendingKeys.delete(key);
    }
}

function countChecked(phase) {
    const items = phase.items ?? [];
    return items.filter((_, i) => isChecked(phase, i)).length;
}

const openPhases = ref([0]);
function togglePhase(idx) {
    const pos = openPhases.value.indexOf(idx);
    if (pos >= 0) openPhases.value.splice(pos, 1);
    else openPhases.value.push(idx);
}
function isPhaseOpen(idx) {
    return openPhases.value.includes(idx);
}
</script>

<template>
    <article
        class="space-y-4 rounded-xl border border-wc-border bg-wc-bg-secondary py-10 pl-12 pr-6 transition-all duration-[240ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:translate-y-[-2px] hover:shadow-[0_20px_40px_-15px_rgba(220,38,38,0.25)]"
    >
        <div
            v-for="(phase, phaseIdx) in phases"
            :key="phase.key ?? phaseIdx"
            class="overflow-hidden rounded-lg border border-wc-border"
        >
            <button
                type="button"
                @click="togglePhase(phaseIdx)"
                class="flex w-full items-center justify-between px-5 py-4 transition-colors hover:bg-wc-bg-tertiary"
            >
                <div class="flex items-center gap-3">
                    <svg
                        class="h-3.5 w-3.5 text-wc-text-tertiary transition-transform"
                        :class="isPhaseOpen(phaseIdx) ? 'rotate-90' : ''"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-tertiary">
                        {{ phase.key }}
                    </span>
                    <span class="font-display text-lg uppercase tracking-tight text-wc-text">
                        {{ phase.title ?? phaseLabels[phase.key] ?? `Fase ${phaseIdx + 1}` }}
                    </span>
                </div>
                <span class="font-mono text-xs tabular-nums text-wc-text-secondary">
                    {{ countChecked(phase) }} / {{ (phase.items ?? []).length }}
                </span>
            </button>

            <div
                v-show="isPhaseOpen(phaseIdx)"
                class="divide-y divide-wc-border/50 border-t border-wc-border"
            >
                <div
                    v-for="(item, itemIdx) in (phase.items ?? [])"
                    :key="itemIdx"
                    class="px-5 py-3 transition-colors hover:bg-wc-bg-tertiary/40"
                >
                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            type="checkbox"
                            :checked="isChecked(phase, itemIdx)"
                            :disabled="pendingKeys.has(itemKey(phase, itemIdx))"
                            @change="toggleItem(phase, itemIdx)"
                            class="mt-0.5 h-4 w-4 flex-shrink-0 rounded border-wc-border accent-wc-accent"
                        />
                        <span class="flex-1">
                            <span
                                class="block text-sm leading-relaxed"
                                :class="isChecked(phase, itemIdx) ? 'text-wc-text-tertiary line-through' : 'text-wc-text'"
                            >
                                {{ item.title ?? item }}
                            </span>
                            <span
                                v-if="item.detail"
                                class="mt-1 block font-editorial text-sm italic leading-relaxed text-wc-text-secondary"
                            >
                                {{ item.detail }}
                            </span>
                            <ul
                                v-if="item.subitems?.length"
                                class="mt-2 list-disc space-y-0.5 pl-5 text-xs text-wc-text-tertiary"
                            >
                                <li v-for="(sub, sIdx) in item.subitems" :key="sIdx">{{ sub }}</li>
                            </ul>
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </article>
</template>
