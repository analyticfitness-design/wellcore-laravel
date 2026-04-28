<script setup>
import { ref, reactive, computed } from 'vue';
import { useCoachStrategyStore } from '../../../stores/coachStrategy';
import { useViewportAnimate } from '../../../composables/dashboard/useViewportAnimate';

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

const phaseColorMap = {
    pre: 'amber',
    cam: 'red',
    edit: 'violet',
    pub: 'sky',
};
const phaseColorCycle = ['amber', 'red', 'violet', 'sky', 'emerald', 'orange'];

function phaseColor(phaseIdx) {
    const key = phases.value[phaseIdx]?.key;
    return phaseColorMap[key] ?? phaseColorCycle[phaseIdx % phaseColorCycle.length];
}

const phases = computed(() => props.checklist?.phases ?? []);

const circumference = 2 * Math.PI * 17; // ≈ 106.81

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
function onToggle(idx, ev) {
    const isOpen = ev?.target?.open ?? false;
    const pos = openPhases.value.indexOf(idx);
    if (isOpen && pos < 0) openPhases.value.push(idx);
    else if (!isOpen && pos >= 0) openPhases.value.splice(pos, 1);
}

// Viewport animation flag for ring fill
const { targetRef: cardRef, visible: ringsVisible } = useViewportAnimate({ threshold: 0.2 });

function ringOffset(phase /*, phaseIdx */) {
    if (!ringsVisible.value) return circumference;
    const items = phase.items ?? [];
    if (items.length === 0) return circumference;
    return circumference - (circumference * countChecked(phase) / items.length);
}
</script>

<template>
    <div ref="cardRef" class="checklist">
        <details
            v-for="(phase, phaseIdx) in phases"
            :key="phase.key ?? phaseIdx"
            class="phase-block"
            :open="isPhaseOpen(phaseIdx)"
            @toggle="onToggle(phaseIdx, $event)"
        >
            <summary class="phase-summary">
                <span class="phase-chev">▶</span>
                <span class="ic ic-sm" :class="`ic-${phaseColor(phaseIdx)}`">
                    <!-- amber: lightbulb -->
                    <svg v-if="phaseColor(phaseIdx) === 'amber'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                    </svg>
                    <!-- red: camera -->
                    <svg v-else-if="phaseColor(phaseIdx) === 'red'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                    <!-- violet: scissors-edit -->
                    <svg v-else-if="phaseColor(phaseIdx) === 'violet'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                    </svg>
                    <!-- sky / fallback: publish-upload -->
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25"/>
                    </svg>
                </span>
                <span class="phase-label">{{ String(phaseIdx + 1).padStart(2, '0') }} /</span>
                <span class="phase-name">{{ phase.title ?? phaseLabels[phase.key] ?? `Fase ${phaseIdx + 1}` }}</span>
                <div class="mini-ring-wrap">
                    <svg class="mini-ring-svg" width="40" height="40" viewBox="0 0 40 40">
                        <circle class="mini-ring-bg" cx="20" cy="20" r="17"/>
                        <circle
                            class="mini-ring-fill"
                            cx="20" cy="20" r="17"
                            :stroke-dasharray="circumference"
                            :stroke-dashoffset="ringOffset(phase, phaseIdx)"
                        />
                    </svg>
                    <span class="mini-ring-num">{{ countChecked(phase) }}/{{ (phase.items ?? []).length }}</span>
                </div>
            </summary>
            <div class="phase-items-wrap">
                <div
                    class="phase-complete"
                    :class="{ visible: countChecked(phase) === (phase.items ?? []).length && (phase.items ?? []).length > 0 }"
                >✓ PHASE COMPLETE</div>
                <div
                    v-for="(item, itemIdx) in (phase.items ?? [])"
                    :key="itemIdx"
                    class="phase-item"
                >
                    <input
                        type="checkbox"
                        class="phase-cb"
                        :checked="isChecked(phase, itemIdx)"
                        :disabled="pendingKeys.has(itemKey(phase, itemIdx))"
                        @change="toggleItem(phase, itemIdx)"
                    />
                    <div class="phase-item-content">
                        <span class="phase-item-title" :class="{ done: isChecked(phase, itemIdx) }">{{ item.title ?? item }}</span>
                        <span v-if="item.detail" class="phase-item-detail">{{ item.detail }}</span>
                        <ul v-if="item.subitems?.length" class="subitems">
                            <li v-for="(sub, sIdx) in item.subitems" :key="sIdx">{{ sub }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </details>
    </div>
</template>
