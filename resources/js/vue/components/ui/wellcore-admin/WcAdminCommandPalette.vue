<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
  shortcuts: { type: Array, default: () => [] }, // [{ id, label, route, meta, icon, section }]
});

const isOpen = ref(false);
const query = ref('');
const router = useRouter();

const SECTIONS = ['Sugerencias', 'Navegación'];

const filtered = computed(() => {
  const q = query.value.trim().toLowerCase();
  if (!q) return props.shortcuts;
  return props.shortcuts.filter(s => s.label.toLowerCase().includes(q));
});

function open() { isOpen.value = true; query.value = ''; }
function close() { isOpen.value = false; }

function handleKeydown(e) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault();
    isOpen.value ? close() : open();
  } else if (e.key === 'Escape' && isOpen.value) {
    close();
  }
}

function go(item) {
  close();
  if (item?.route) router.push(item.route);
}

defineExpose({ open, close });

onMounted(() => window.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
  <div :class="['cmd-overlay', { open: isOpen }]" @click.self="close">
    <div class="cmd-panel" role="dialog" aria-label="Command palette">
      <div class="cmd-search">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#A1A1AA" stroke-width="2">
          <circle cx="11" cy="11" r="7"></circle>
          <path d="M21 21l-4.3-4.3"></path>
        </svg>
        <input
          type="text"
          v-model="query"
          placeholder="Buscar acciones, clientes, herramientas…"
          @keydown.enter="filtered[0] && go(filtered[0])"
        />
        <kbd>esc</kbd>
      </div>
      <div
        v-for="(sec, sIdx) in SECTIONS"
        :key="sec"
        class="cmd-section"
        :style="sIdx > 0 ? 'border-top:1px solid rgba(255,255,255,.05)' : null"
      >
        <div class="h">{{ sec }}</div>
        <div
          v-for="(item, idx) in filtered.filter(s => s.section === sec)"
          :key="item.id"
          :class="['cmd-item', { active: idx === 0 }]"
          @click="go(item)"
        >
          <span class="ic">
            <component v-if="item.icon" :is="item.icon" />
          </span>
          {{ item.label }}
          <span v-if="item.meta" class="meta">{{ item.meta }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
