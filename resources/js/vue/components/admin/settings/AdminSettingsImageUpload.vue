<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  label: { type: String, required: true },
  modelValue: { default: null }, // URL o base64
  maxSizePx: { type: Number, default: 512 },
  hint: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const preview = ref(props.modelValue || null);
const error = ref('');
const dragging = ref(false);

const hasImage = computed(() => !!preview.value);

function onFileChange(e) {
  const file = e.target.files[0];
  if (!file) return;
  processFile(file);
}

function onDrop(e) {
  dragging.value = false;
  const file = e.dataTransfer.files[0];
  if (!file) return;
  processFile(file);
}

function processFile(file) {
  error.value = '';
  if (!file.type.startsWith('image/')) {
    error.value = 'Solo se permiten imagenes (PNG, JPG, WEBP).';
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    error.value = 'El archivo no puede superar 5 MB.';
    return;
  }

  const reader = new FileReader();
  reader.onload = (ev) => {
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement('canvas');
      const scale = Math.min(1, props.maxSizePx / Math.max(img.width, img.height));
      canvas.width = Math.round(img.width * scale);
      canvas.height = Math.round(img.height * scale);
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      const base64 = canvas.toDataURL('image/webp', 0.88);
      preview.value = base64;
      emit('update:modelValue', base64);
    };
    img.src = ev.target.result;
  };
  reader.readAsDataURL(file);
}

function remove() {
  preview.value = null;
  error.value = '';
  emit('update:modelValue', null);
  if (fileInput.value) fileInput.value.value = '';
}
</script>

<template>
  <div class="siu-wrap">
    <span class="siu-label">{{ label }}</span>

    <div
      class="siu-dropzone"
      :class="{
        'siu-dropzone--has-image': hasImage,
        'siu-dropzone--dragging': dragging,
        'siu-dropzone--disabled': disabled,
      }"
      @dragover.prevent="dragging = !disabled"
      @dragleave="dragging = false"
      @drop.prevent="!disabled && onDrop($event)"
    >
      <img v-if="hasImage" :src="preview" class="siu-preview" alt="Vista previa del logo" />

      <template v-if="!hasImage">
        <svg class="siu-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          <circle cx="8.5" cy="8.5" r="1.5"/>
          <polyline points="21 15 16 10 5 21"/>
        </svg>
        <p class="siu-prompt">Arrastra una imagen o <button type="button" class="siu-browse" :disabled="disabled" @click="fileInput?.click()">elige archivo</button></p>
        <p class="siu-sub">PNG, JPG, WEBP — max 5 MB — se redimension a {{ maxSizePx }}px</p>
      </template>

      <input
        ref="fileInput"
        type="file"
        accept="image/png,image/jpeg,image/webp"
        class="sr-only"
        :disabled="disabled"
        @change="onFileChange"
      />
    </div>

    <div v-if="hasImage" class="siu-actions">
      <button type="button" class="siu-action siu-action--change" :disabled="disabled" @click="fileInput?.click()">Cambiar</button>
      <button type="button" class="siu-action siu-action--remove" :disabled="disabled" @click="remove">Eliminar</button>
    </div>

    <p v-if="hint && !error" class="siu-hint">{{ hint }}</p>
    <p v-if="error" class="siu-error" role="alert">{{ error }}</p>
  </div>
</template>

<style scoped>
.siu-wrap { display: flex; flex-direction: column; gap: 8px; }

.siu-label {
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  color: var(--c-text-3);
}

.siu-dropzone {
  border: 1px dashed rgba(255,255,255,0.12);
  border-radius: var(--r-sm, 12px);
  padding: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 130px;
  background: rgba(255,255,255,0.02);
  transition: border-color 0.15s, background 0.15s;
  position: relative;
  cursor: default;
}
.siu-dropzone--dragging {
  border-color: var(--c-accent);
  background: var(--c-accent-dim);
}
.siu-dropzone--disabled { opacity: 0.45; pointer-events: none; }
.siu-dropzone--has-image { padding: 12px; min-height: 80px; }

.siu-preview {
  max-height: 100px;
  max-width: 100%;
  object-fit: contain;
  border-radius: 6px;
}

.siu-icon { color: var(--c-text-3); }
.siu-prompt {
  font-family: var(--font-sans);
  font-size: 13px;
  color: var(--c-text-2);
  text-align: center;
  margin: 0;
}
.siu-browse {
  background: none;
  border: none;
  color: var(--c-accent);
  cursor: pointer;
  font: inherit;
  text-decoration: underline;
}
.siu-sub {
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.0px;
  color: var(--c-text-3);
  text-transform: uppercase;
  margin: 0;
  text-align: center;
}

.siu-actions {
  display: flex;
  gap: 8px;
}
.siu-action {
  height: 28px;
  padding: 0 12px;
  border-radius: 6px;
  border: 1px solid var(--c-border);
  background: transparent;
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  cursor: pointer;
  transition: color 0.12s, border-color 0.12s;
}
.siu-action--change { color: var(--c-text-2); }
.siu-action--change:hover { color: var(--c-text); border-color: rgba(255,255,255,0.12); }
.siu-action--remove { color: #F87171; border-color: rgba(220,38,38,0.25); }
.siu-action--remove:hover { background: var(--c-accent-dim); }
.siu-action:disabled { opacity: 0.4; cursor: not-allowed; }

.siu-hint {
  font-family: var(--font-sans);
  font-size: 11px;
  color: var(--c-text-3);
  margin: 0;
}
.siu-error {
  font-family: var(--font-sans);
  font-size: 11px;
  color: #F87171;
  margin: 0;
}
</style>
