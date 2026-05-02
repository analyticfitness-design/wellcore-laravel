<script setup>
const props = defineProps({
  sections: { type: Array, required: true },
  activeSection: { type: String, required: true },
  isSuperAdmin: { type: Boolean, default: false },
});

const emit = defineEmits(['select']);
</script>

<template>
  <nav class="settings-sidebar" aria-label="Secciones de configuracion">
    <ul class="settings-sidebar-list" role="list">
      <li v-for="sec in sections" :key="sec.id">
        <button
          type="button"
          class="settings-sidebar-item"
          :class="{
            'settings-sidebar-item--active': activeSection === sec.id,
            'settings-sidebar-item--locked': sec.superadminOnly && !isSuperAdmin,
          }"
          :aria-current="activeSection === sec.id ? 'page' : undefined"
          :title="sec.superadminOnly && !isSuperAdmin ? 'Solo Superadmin puede modificar esta seccion' : undefined"
          @click="emit('select', sec.id)"
        >
          <span class="settings-sidebar-icon" v-html="sec.icon" aria-hidden="true"></span>
          <span class="settings-sidebar-label">{{ sec.label }}</span>
          <span v-if="sec.superadminOnly && !isSuperAdmin" class="settings-sidebar-lock" aria-label="Restringido">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </span>
        </button>
      </li>
    </ul>
  </nav>
</template>

<style scoped>
.settings-sidebar {
  width: var(--settings-sidebar-w, 240px);
  flex-shrink: 0;
  border-right: 1px solid var(--c-border);
  padding: 12px 0;
}
.settings-sidebar-list {
  list-style: none;
  margin: 0;
  padding: 0;
}
.settings-sidebar-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 10px 20px;
  background: none;
  border: none;
  cursor: pointer;
  font-family: var(--font-display);
  font-size: 10px;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  color: var(--c-text-2);
  text-align: left;
  transition: color 0.15s var(--ease-out), background 0.15s var(--ease-out);
  position: relative;
  min-height: var(--tap-comfort, 48px);
}
.settings-sidebar-item:hover {
  color: var(--c-text);
  background: rgba(255,255,255,0.03);
}
.settings-sidebar-item--active {
  color: var(--c-text);
  background: var(--c-accent-dim);
}
.settings-sidebar-item--active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 4px;
  bottom: 4px;
  width: 2px;
  background: var(--c-accent);
  border-radius: 0 2px 2px 0;
}
.settings-sidebar-item--locked {
  opacity: 0.5;
}
.settings-sidebar-icon {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  color: inherit;
}
.settings-sidebar-icon :deep(svg) {
  width: 14px;
  height: 14px;
  stroke: currentColor;
}
.settings-sidebar-label {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.settings-sidebar-lock {
  flex-shrink: 0;
  color: var(--c-text-3);
}
</style>
