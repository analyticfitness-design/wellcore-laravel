<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';

const props = defineProps({
    hashtags: { type: Object, required: true },
    weekNum: { type: [String, Number], default: null },
});

const activeTab = ref(0);
const copiedIdx = ref(null);
let copyTimer = null;

const sets = computed(() => props.hashtags?.sets ?? []);

const promptWeek = computed(() => {
    if (props.weekNum !== null && props.weekNum !== undefined && props.weekNum !== '') {
        return props.weekNum;
    }
    return props.hashtags?.week_number ?? props.hashtags?.week ?? 'current';
});

function setLabel(idx) {
    return String.fromCharCode(65 + idx); // A, B, C...
}

async function copySet(idx) {
    const set = sets.value[idx];
    if (!set) return;
    const text = (set.tags ?? []).map(t => (t.startsWith('#') ? t : `#${t}`)).join(' ');
    try {
        await navigator.clipboard.writeText(text);
    } catch {}

    copiedIdx.value = idx;
    if (copyTimer) clearTimeout(copyTimer);
    copyTimer = setTimeout(() => {
        copiedIdx.value = null;
        copyTimer = null;
    }, 2000);
}

onBeforeUnmount(() => {
    if (copyTimer) {
        clearTimeout(copyTimer);
        copyTimer = null;
    }
});
</script>

<template>
    <div v-if="!sets.length" class="card">
        <p style="text-align:center;color:var(--ink-3,#888);font-family:var(--font-mono,monospace);font-size:.85rem;padding:2rem 0;">
            Sin sets de hashtags disponibles
        </p>
    </div>

    <div v-else class="hashtags-card">
        <div class="terminal-bar">
            <span class="t-dot t-r"></span>
            <span class="t-dot t-y"></span>
            <span class="t-dot t-g"></span>
            <span class="t-prompt">wellcore@coach:~/semana-{{ promptWeek }}/hashtags $</span>
        </div>

        <div class="htabs">
            <button
                v-for="(set, idx) in sets"
                :key="idx"
                type="button"
                class="htab"
                :class="{ active: activeTab === idx }"
                @click="activeTab = idx"
            >
                Set {{ setLabel(idx) }}<template v-if="set.name"> · {{ set.name }}</template>
            </button>
        </div>

        <div
            v-for="(set, idx) in sets"
            :key="idx"
            class="htag-set"
            :class="{ active: activeTab === idx }"
        >
            <div class="htag-pills">
                <span
                    v-for="(tag, i) in set.tags"
                    :key="i"
                    class="htag-pill"
                >{{ tag.startsWith('#') ? tag : `#${tag}` }}</span>
            </div>
            <div class="copy-set-row">
                <button
                    type="button"
                    class="copy-set-btn"
                    :class="{ ok: copiedIdx === idx }"
                    @click="copySet(idx)"
                >
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185"
                        />
                    </svg>
                    <span class="copy-set-label">{{ copiedIdx === idx ? 'Copiado ✓' : 'Copiar set completo' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
