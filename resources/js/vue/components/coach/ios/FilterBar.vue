<script setup>
defineProps({
  searchPlaceholder: { type: String, default: 'Buscar…' },
  searchValue: { type: String, default: '' },
  filters: { type: Array, default: () => [] },
  activeFilter: { type: String, default: '' },
});

const emit = defineEmits(['update:searchValue', 'filter-change']);
</script>

<template>
  <div class="anim-entry anim-entry-2 mb-5 flex flex-col gap-3">
    <div
      class="flex items-center gap-2 px-3 h-10 border rounded-[12px] focus-within:border-[var(--b2)] transition"
      style="background: var(--s1); border-color: var(--b1); transition-duration: var(--t-tap);"
    >
      <svg class="h-3.5 w-3.5 shrink-0" style="stroke: var(--color-wc-text-3);" fill="none" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
      </svg>
      <input
        type="search"
        :value="searchValue"
        @input="emit('update:searchValue', $event.target.value)"
        :placeholder="searchPlaceholder"
        class="flex-1 bg-transparent border-none outline-none text-sm text-wc-text placeholder:text-[var(--color-wc-text-3)]"
      />
    </div>
    <div v-if="filters.length" class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
      <button
        v-for="f in filters"
        :key="f.id"
        @click="emit('filter-change', f.id)"
        :class="[
          'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold whitespace-nowrap transition',
          activeFilter === f.id
            ? 'bg-wc-accent text-white glow-accent'
            : 'border border-[var(--b1)] text-[var(--color-wc-text-2)] hover:bg-[var(--s2)]',
        ]"
        :style="activeFilter !== f.id ? 'background: var(--s1); transition-duration: var(--t-tap);' : 'transition-duration: var(--t-tap);'"
      >
        {{ f.label }}
        <span
          v-if="f.count !== undefined"
          :class="[
            'inline-flex items-center justify-center min-w-4 h-4 rounded-full px-1 text-[9px] font-bold',
            activeFilter === f.id ? 'bg-white/20' : '',
          ]"
          :style="activeFilter !== f.id ? 'background: var(--s3); color: var(--color-wc-text-3);' : ''"
        >
          {{ f.count }}
        </span>
      </button>
    </div>
  </div>
</template>
