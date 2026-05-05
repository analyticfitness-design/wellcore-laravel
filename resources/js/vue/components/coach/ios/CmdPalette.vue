<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
  open: { type: Boolean, default: false },
  sections: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:open', 'execute']);

const router = useRouter();
const inputRef = ref(null);
const query = ref('');
const selectedIdx = ref(0);

const filteredSections = computed(() => {
  if (!query.value.trim()) return props.sections;
  const q = query.value.trim().toLowerCase();
  return props.sections
    .map(s => ({
      ...s,
      items: (s.items || []).filter(i => i.label.toLowerCase().includes(q)),
    }))
    .filter(s => s.items.length > 0);
});

const filteredFlat = computed(() => {
  const arr = [];
  filteredSections.value.forEach(s => (s.items || []).forEach(it => arr.push({ ...it, _section: s.label })));
  return arr;
});

function close() { emit('update:open', false); }

function execute(item) {
  emit('execute', item);
  if (item.action) item.action();
  if (item.to) router.push(item.to);
  close();
}

function rgbaFromHex(hex, alpha) {
  if (!hex || !hex.startsWith('#')) return `rgba(255,255,255,0.07)`;
  const h = hex.replace('#', '');
  const bigint = parseInt(h.length === 3 ? h.split('').map(c => c + c).join('') : h, 16);
  const r = (bigint >> 16) & 255;
  const g = (bigint >> 8) & 255;
  const b = bigint & 255;
  return `rgba(${r},${g},${b},${alpha})`;
}

watch(() => props.open, async (val) => {
  if (val) {
    document.body.style.overflow = 'hidden';
    await nextTick();
    inputRef.value?.focus();
    selectedIdx.value = 0;
    query.value = '';
  } else {
    document.body.style.overflow = '';
  }
});

function onKey(e) {
  if (!props.open) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault();
      emit('update:open', true);
    }
    return;
  }
  if (e.key === 'Escape') { e.preventDefault(); close(); return; }
  const total = filteredFlat.value.length;
  if (total === 0) return;
  if (e.key === 'ArrowDown') {
    e.preventDefault();
    selectedIdx.value = (selectedIdx.value + 1) % total;
  }
  if (e.key === 'ArrowUp') {
    e.preventDefault();
    selectedIdx.value = (selectedIdx.value - 1 + total) % total;
  }
  if (e.key === 'Enter') {
    e.preventDefault();
    const item = filteredFlat.value[selectedIdx.value];
    if (item) execute(item);
  }
}

onMounted(() => window.addEventListener('keydown', onKey));
onUnmounted(() => {
  window.removeEventListener('keydown', onKey);
  document.body.style.overflow = '';
});

function indexOfItem(itemId) {
  return filteredFlat.value.findIndex(f => f.id === itemId);
}
</script>

<template>
  <Teleport to="body">
    <div class="coach-ios">
    <div
      :class="['palette-backdrop', { open }]"
      @click="close"
      :aria-hidden="!open"
    />
    <div
      :class="['cmd-palette', { open }]"
      role="dialog"
      :aria-modal="open"
      aria-label="Buscar y ejecutar comandos"
    >
      <div class="flex items-center gap-2.5 p-3.5 px-4 border-b" style="border-color: var(--b1);">
        <svg class="h-4 w-4 flex-shrink-0" style="stroke: var(--color-wc-text-3);" fill="none" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input
          ref="inputRef"
          v-model="query"
          type="text"
          placeholder="Buscar o ejecutar comando…"
          autocomplete="off"
          class="flex-1 bg-transparent border-none outline-none font-sans text-[15px] font-medium text-wc-text"
          style="caret-color: var(--color-wc-accent);"
        />
        <kbd class="px-1.5 py-0.5 border rounded text-[10px]" style="background: var(--s2); border-color: var(--b1); color: var(--color-wc-text-3);">Esc</kbd>
      </div>
      <div class="overflow-y-auto flex-1" style="max-height: 40vh;">
        <template v-for="section in filteredSections" :key="section.label">
          <div class="px-4 py-2 pb-1 text-[9px] font-bold tracking-[0.12em] uppercase" style="color: var(--color-wc-text-3);">
            {{ section.label }}
          </div>
          <button
            v-for="item in (section.items || [])"
            :key="item.id"
            :class="[
              'flex items-center gap-3 px-4 py-2.5 cursor-pointer w-full text-left transition',
              indexOfItem(item.id) === selectedIdx ? 'bg-wc-accent/[0.12]' : 'hover:bg-[var(--s2)]',
            ]"
            style="transition-duration: var(--t-tap);"
            @click="execute(item)"
          >
            <span class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                  :style="{ background: rgbaFromHex(item.iconColor || '', 0.12) }">
              <svg
                v-if="item.iconSvgPath"
                class="h-3.5 w-3.5"
                fill="none" viewBox="0 0 24 24" stroke-width="2"
                :stroke="item.iconStrokeColor || 'currentColor'"
                aria-hidden="true"
                v-html="item.iconSvgPath"
              />
            </span>
            <span class="flex-1 text-[13px] font-semibold text-wc-text">{{ item.label }}</span>
            <kbd v-if="item.kbd" class="text-[10px]" style="color: var(--color-wc-text-3);">{{ item.kbd }}</kbd>
          </button>
        </template>
        <div v-if="filteredFlat.length === 0" class="p-6 text-center text-[13px]" style="color: var(--color-wc-text-3);">
          Sin resultados para "{{ query }}"
        </div>
      </div>
      <div class="flex items-center gap-3 px-4 py-2 border-t text-[10px]" style="border-color: var(--b1); color: var(--color-wc-text-3);">
        <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 border rounded text-[9px]" style="background: var(--s2); border-color: var(--b1);">↑↓</kbd> Navegar</span>
        <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 border rounded text-[9px]" style="background: var(--s2); border-color: var(--b1);">↵</kbd> Seleccionar</span>
        <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 border rounded text-[9px]" style="background: var(--s2); border-color: var(--b1);">Esc</kbd> Cerrar</span>
      </div>
    </div>
    </div>
  </Teleport>
</template>
