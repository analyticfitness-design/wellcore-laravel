<script setup>
/**
 * VisualSelect.vue — combobox custom accesible con opciones que tienen
 * icono + label + descripción (ej. principiante / intermedio / avanzado).
 *
 * Accesibilidad:
 *   - Trigger: role=combobox, aria-haspopup=listbox, aria-expanded, aria-controls.
 *   - Listbox: role=listbox, aria-activedescendant.
 *   - Opciones: role=option, aria-selected.
 *   - Keyboard:
 *       Trigger  → Enter/Space/ArrowDown abre y enfoca primero o seleccionado.
 *       Listbox  → ArrowUp/Down navega, Enter/Space selecciona, Esc cierra
 *                  y devuelve focus al trigger.
 *       Tab      → cierra y deja focus seguir al siguiente.
 *
 * Click outside cierra.
 *
 * Props:
 *   - id, label, error, hint
 *   - options: [{ value, label, desc?, icon?: SVG path string OR slot key }]
 *   - placeholder
 *
 * v-model (string) standard.
 */
import { ref, computed, watch, onBeforeUnmount, useTemplateRef, nextTick } from 'vue';

const props = defineProps({
    id:          { type: String, required: true },
    label:       { type: String, default: '' },
    placeholder: { type: String, default: 'Selecciona una opción' },
    options:     { type: Array, default: () => [] }, // [{ value, label, desc, icon }]
    error:       { type: String, default: '' },
    hint:        { type: String, default: '' },
    disabled:    { type: Boolean, default: false },
});

const model = defineModel({ default: '' });

const open = ref(false);
const activeIndex = ref(-1);

const rootRef = useTemplateRef('rootRef');
const triggerRef = useTemplateRef('triggerRef');
const listboxRef = useTemplateRef('listboxRef');

const selectedOption = computed(() =>
    props.options.find((o) => String(o.value) === String(model.value)) || null
);

const listboxId = computed(() => `${props.id}-listbox`);
const optionId = (i) => `${props.id}-opt-${i}`;
const describedBy = computed(() => {
    if (props.error) return `${props.id}-error`;
    if (props.hint) return `${props.id}-hint`;
    return undefined;
});

function syncActiveToSelected() {
    const idx = props.options.findIndex((o) => String(o.value) === String(model.value));
    activeIndex.value = idx >= 0 ? idx : 0;
}

async function openMenu() {
    if (props.disabled) return;
    if (open.value) return;
    open.value = true;
    syncActiveToSelected();
    await nextTick();
    // Scroll a la opción activa.
    const el = listboxRef.value?.querySelector(`[data-idx="${activeIndex.value}"]`);
    el?.scrollIntoView({ block: 'nearest' });
}

function closeMenu(returnFocus = true) {
    if (!open.value) return;
    open.value = false;
    if (returnFocus) {
        triggerRef.value?.focus();
    }
}

function toggleMenu() {
    if (open.value) closeMenu(false);
    else openMenu();
}

function selectIndex(i) {
    const opt = props.options[i];
    if (!opt) return;
    model.value = opt.value;
    closeMenu(true);
}

function onTriggerKey(e) {
    if (props.disabled) return;
    switch (e.key) {
        case 'Enter':
        case ' ':
        case 'Spacebar':
            e.preventDefault();
            toggleMenu();
            break;
        case 'ArrowDown':
        case 'ArrowUp':
            e.preventDefault();
            if (!open.value) {
                openMenu();
            } else {
                moveActive(e.key === 'ArrowDown' ? 1 : -1);
            }
            break;
        case 'Escape':
            if (open.value) {
                e.preventDefault();
                closeMenu(true);
            }
            break;
        case 'Tab':
            if (open.value) closeMenu(false);
            break;
    }
}

function onListKey(e) {
    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            moveActive(1);
            break;
        case 'ArrowUp':
            e.preventDefault();
            moveActive(-1);
            break;
        case 'Home':
            e.preventDefault();
            activeIndex.value = 0;
            scrollActiveIntoView();
            break;
        case 'End':
            e.preventDefault();
            activeIndex.value = props.options.length - 1;
            scrollActiveIntoView();
            break;
        case 'Enter':
        case ' ':
        case 'Spacebar':
            e.preventDefault();
            selectIndex(activeIndex.value);
            break;
        case 'Escape':
            e.preventDefault();
            closeMenu(true);
            break;
        case 'Tab':
            closeMenu(false);
            break;
    }
}

function moveActive(delta) {
    if (!props.options.length) return;
    const len = props.options.length;
    let next = activeIndex.value + delta;
    if (next < 0) next = len - 1;
    if (next >= len) next = 0;
    activeIndex.value = next;
    scrollActiveIntoView();
}

function scrollActiveIntoView() {
    nextTick(() => {
        const el = listboxRef.value?.querySelector(`[data-idx="${activeIndex.value}"]`);
        el?.scrollIntoView({ block: 'nearest' });
    });
}

function onClickOutside(e) {
    if (!open.value) return;
    if (rootRef.value && !rootRef.value.contains(e.target)) {
        closeMenu(false);
    }
}

watch(open, (v) => {
    if (v) {
        document.addEventListener('mousedown', onClickOutside, true);
        document.addEventListener('touchstart', onClickOutside, true);
    } else {
        document.removeEventListener('mousedown', onClickOutside, true);
        document.removeEventListener('touchstart', onClickOutside, true);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onClickOutside, true);
    document.removeEventListener('touchstart', onClickOutside, true);
});
</script>

<template>
  <div class="vselect" ref="rootRef">
    <label v-if="label" :for="id" class="field-label">{{ label }}</label>

    <button
      :id="id"
      ref="triggerRef"
      type="button"
      class="select-trigger"
      :class="{ 'is-open': open, 'is-invalid': !!error, 'is-disabled': disabled }"
      role="combobox"
      :aria-haspopup="'listbox'"
      :aria-expanded="open"
      :aria-controls="listboxId"
      :aria-invalid="error ? 'true' : 'false'"
      :aria-describedby="describedBy"
      :disabled="disabled"
      @click="toggleMenu"
      @keydown="onTriggerKey"
    >
      <span class="select-trigger__icon" v-if="selectedOption?.icon">
        <span v-html="selectedOption.icon" aria-hidden="true" />
      </span>

      <span class="select-trigger__main">
        <template v-if="selectedOption">
          <span class="select-trigger__label">{{ selectedOption.label }}</span>
          <span v-if="selectedOption.desc" class="select-trigger__desc">{{ selectedOption.desc }}</span>
        </template>
        <span v-else class="select-trigger__placeholder">{{ placeholder }}</span>
      </span>
    </button>

    <ul
      v-show="open"
      :id="listboxId"
      ref="listboxRef"
      class="select-pop"
      role="listbox"
      tabindex="-1"
      :aria-activedescendant="open && options[activeIndex] ? optionId(activeIndex) : undefined"
      @keydown="onListKey"
    >
      <li
        v-for="(opt, i) in options"
        :key="opt.value"
        :id="optionId(i)"
        :data-idx="i"
        class="select-opt"
        role="option"
        :aria-selected="String(opt.value) === String(model)"
        :class="{ 'is-active': i === activeIndex, 'is-selected': String(opt.value) === String(model) }"
        @mouseenter="activeIndex = i"
        @mousedown.prevent="selectIndex(i)"
      >
        <span v-if="opt.icon" class="select-opt__icon">
          <span v-html="opt.icon" aria-hidden="true" />
        </span>
        <span class="select-opt__main">
          <span class="select-opt__label">{{ opt.label }}</span>
          <span v-if="opt.desc" class="select-opt__desc">{{ opt.desc }}</span>
        </span>
      </li>
    </ul>

    <p v-if="error" :id="`${id}-error`" class="field-error" role="alert">{{ error }}</p>
    <p v-else-if="hint" :id="`${id}-hint`" class="field-hint">{{ hint }}</p>
  </div>
</template>

<style scoped>
.vselect {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
}

.field-label {
  font-size: 14px;
  font-weight: 500;
  color: var(--color-wc-text);
  letter-spacing: 0.005em;
}

.select-trigger {
  width: 100%;
  min-height: 48px;
  padding: 8px 44px 8px 14px;
  border-radius: 10px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  color: var(--color-wc-text);
  font-size: 16px;
  font-family: inherit;
  text-align: left;
  display: flex;
  align-items: center;
  gap: 12px;
  position: relative;
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}
.select-trigger:hover:not(:disabled) {
  border-color: var(--color-wc-border-strong, var(--color-wc-border));
}
.select-trigger.is-open,
.select-trigger:focus-visible {
  outline: none;
  border-color: var(--color-wc-accent-glow, #EF4444);
  background: var(--color-wc-bg-tertiary);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
}
.select-trigger.is-invalid {
  border-color: var(--color-wc-accent, #DC2626);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.10);
}
.select-trigger.is-disabled,
.select-trigger:disabled { opacity: 0.6; cursor: not-allowed; }

.select-trigger::after {
  content: '';
  position: absolute;
  right: 16px;
  top: 50%;
  width: 8px;
  height: 8px;
  border-right: 1.5px solid var(--color-wc-text-tertiary);
  border-bottom: 1.5px solid var(--color-wc-text-tertiary);
  transform: translateY(-70%) rotate(45deg);
  transition: transform 0.2s ease;
}
.select-trigger.is-open::after { transform: translateY(-30%) rotate(-135deg); }

.select-trigger__placeholder { color: var(--color-wc-text-tertiary); }

.select-trigger__icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: var(--color-wc-bg-prominent, var(--color-wc-bg-tertiary));
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-wc-text-secondary);
  flex-shrink: 0;
}
.select-trigger__icon :deep(svg) { width: 14px; height: 14px; }

.select-trigger__main {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
  flex: 1;
}
.select-trigger__label {
  font-size: 15px;
  color: var(--color-wc-text);
  font-weight: 500;
  line-height: 1.2;
}
.select-trigger__desc {
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  line-height: 1.3;
}

.select-pop {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  z-index: 30;
  margin: 0;
  padding: 6px;
  list-style: none;
  border-radius: 14px;
  border: 1px solid var(--color-wc-border-strong, var(--color-wc-border));
  background: var(--color-wc-bg-tertiary);
  box-shadow: 0 16px 40px -16px rgba(0, 0, 0, 0.6),
              0 4px 12px rgba(0, 0, 0, 0.4);
  max-height: 320px;
  overflow-y: auto;
}

.select-opt {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.12s ease;
  min-height: 44px;
}
.select-opt.is-active,
.select-opt:hover {
  background: var(--color-wc-bg-prominent, var(--color-wc-bg-secondary));
}
.select-opt.is-selected {
  background: rgba(220, 38, 38, 0.10);
}
.select-opt.is-selected .select-opt__icon {
  background: rgba(220, 38, 38, 0.18);
  color: var(--color-wc-accent-glow, #EF4444);
}

.select-opt__icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--color-wc-bg-prominent, var(--color-wc-bg-secondary));
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-wc-text-secondary);
  flex-shrink: 0;
  transition: background 0.12s ease, color 0.12s ease;
}
.select-opt__icon :deep(svg) { width: 16px; height: 16px; }
.select-opt:hover .select-opt__icon { color: var(--color-wc-text); }

.select-opt__main { flex: 1; min-width: 0; }
.select-opt__label {
  display: block;
  font-size: 15px;
  font-weight: 500;
  color: var(--color-wc-text);
  line-height: 1.2;
}
.select-opt__desc {
  display: block;
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  margin-top: 1px;
  line-height: 1.3;
}

.field-error {
  margin: 0;
  font-size: 12px;
  color: var(--color-wc-accent, #DC2626);
  line-height: 1.4;
}
.field-hint {
  margin: 0;
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  line-height: 1.4;
}

@media (prefers-reduced-motion: reduce) {
  .select-trigger,
  .select-trigger::after,
  .select-opt { transition-duration: 0.01ms; }
}
</style>
