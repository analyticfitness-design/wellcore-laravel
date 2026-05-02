<script setup>
import { computed } from 'vue';
import { useAdminClientListStore } from '../../../stores/adminClientList';

const store = useAdminClientListStore();

const isFirst = computed(() => store.pagination.currentPage <= 1);
const isLast = computed(() => store.pagination.currentPage >= store.pagination.lastPage);

// Rango con ellipsis: [1, ..., 4, 5, 6, ..., 12]
const range = computed(() => {
    const current = store.pagination.currentPage;
    const last = store.pagination.lastPage || 1;
    if (last <= 1) return [1];
    const delta = 1;
    const pages = new Set([1, last, current - delta, current, current + delta]);
    const arr = [...pages]
        .filter((p) => p >= 1 && p <= last)
        .sort((a, b) => a - b);

    const out = [];
    let prev = 0;
    for (const p of arr) {
        if (prev && p - prev > 1) out.push('…');
        out.push(p);
        prev = p;
    }
    return out;
});

function goPrev() {
    if (!isFirst.value) store.setPage(store.pagination.currentPage - 1);
}
function goNext() {
    if (!isLast.value) store.setPage(store.pagination.currentPage + 1);
}
function go(p) {
    if (typeof p === 'number') store.setPage(p);
}
</script>

<template>
  <div v-if="store.pagination.total > 0" class="pagination" role="navigation" aria-label="Paginación">
    <div class="page-summary">
      <span class="summary-mono">
        {{ store.rangeFrom }}–{{ store.rangeTo }} de {{ store.pagination.total }}
      </span>
    </div>

    <div class="page-controls">
      <button
        type="button"
        class="page-btn"
        :disabled="isFirst"
        :aria-disabled="isFirst"
        aria-label="Página anterior"
        @click="goPrev"
      >
        ← ANT
      </button>

      <div class="page-numbers">
        <template v-for="(p, idx) in range" :key="`pg-${idx}-${p}`">
          <span v-if="p === '…'" class="ellipsis">…</span>
          <button
            v-else
            type="button"
            class="page-num"
            :class="{ 'page-num--active': p === store.pagination.currentPage }"
            :aria-current="p === store.pagination.currentPage ? 'page' : undefined"
            @click="go(p)"
          >
            {{ p }}
          </button>
        </template>
      </div>

      <button
        type="button"
        class="page-btn"
        :disabled="isLast"
        :aria-disabled="isLast"
        aria-label="Página siguiente"
        @click="goNext"
      >
        SIG →
      </button>
    </div>
  </div>
</template>

<style scoped>
.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding-top: 12px;
}

.summary-mono {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

.page-controls {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.page-btn,
.page-num {
    height: var(--tap-comfort, 48px);
    min-width: var(--tap-comfort, 48px);
    padding: 0 10px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.5);
    color: var(--c-text-2);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.page-btn:hover:not(:disabled),
.page-num:hover {
    background: rgba(255, 255, 255, 0.04);
    color: var(--c-text);
    border-color: rgba(255,255,255,0.12);
}
.page-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.page-num--active {
    background: var(--c-accent-dim);
    border-color: var(--c-accent);
    color: #F87171;
}

.page-numbers {
    display: flex;
    align-items: center;
    gap: 4px;
}

.ellipsis {
    font-family: var(--font-display);
    color: var(--c-text-3);
    padding: 0 4px;
    user-select: none;
}
</style>
