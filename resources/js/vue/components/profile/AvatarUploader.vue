<script setup>
/**
 * AvatarUploader.vue — disco 100×100 + botón cámara flotante + upload.
 *
 * Pensado para usarse dentro del slot de CompletionRing. El disco tiene
 * `inset: 10px` respecto al ring de 120×120 (ring stroke 4px + 6px margin).
 *
 * Flow:
 *   1. Click en cámara → file input oculto.
 *   2. Validación: image/jpeg, png, webp · máx 5MB.
 *   3. Preview vía URL.createObjectURL.
 *   4. Upload automático via POST /api/v/client/avatar (multipart/form-data,
 *      campo "avatar"). En éxito emit('uploaded', avatar_url) + toast success.
 *   5. revokeObjectURL en cada cambio + onBeforeUnmount.
 *
 * Props:
 *   - avatarUrl: URL pública actual (string|null)
 *   - name: para fallback de iniciales
 *   - size: lado del disco en px (default 100, lo cual encaja en ring 120)
 *
 * Emits:
 *   - uploaded(url): tras upload OK
 *   - update:avatarUrl: equivalente para v-model
 *   - error(err): si quieres handleo extra externo (toast ya se dispara aquí)
 */
import { ref, computed, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';

const props = defineProps({
    avatarUrl: { type: String, default: null },
    name:      { type: String, default: '' },
    /**
     * Tamaño total del wrapper (ring exterior). El avatar interno usa inset
     * de `discInset` para dejar espacio al stroke del progress ring (HTML v2).
     * Default 120 — encaja con CompletionRing 120 y deja 10px de margen.
     */
    size:      { type: Number, default: 120 },
    discInset: { type: Number, default: 10 },
    disabled:  { type: Boolean, default: false },
});
const emit = defineEmits(['uploaded', 'update:avatarUrl', 'error']);

const api = useApi();
const toast = useToast();

const fileInput = ref(null);
const previewUrl = ref(null);
const uploading = ref(false);
let objectUrl = null;

const ACCEPT = 'image/jpeg,image/jpg,image/png,image/webp';
const MAX_BYTES = 5 * 1024 * 1024;

const displayUrl = computed(() => previewUrl.value || props.avatarUrl);

const initials = computed(() => {
    const n = (props.name || '').trim();
    if (!n) return '?';
    const parts = n.split(/\s+/).slice(0, 2);
    const out = parts.map((w) => w[0] || '').join('').toUpperCase();
    return out || '?';
});

function openPicker() {
    if (props.disabled || uploading.value) return;
    fileInput.value?.click();
}

function clearObjectUrl() {
    if (objectUrl) {
        try { URL.revokeObjectURL(objectUrl); } catch {}
        objectUrl = null;
    }
}

async function onFileSelect(e) {
    const file = e.target.files?.[0];
    if (!file) return;

    if (!/^image\/(jpe?g|png|webp)$/i.test(file.type)) {
        toast.warn('Formato no soportado. Usa JPG, PNG o WebP.');
        if (fileInput.value) fileInput.value.value = '';
        return;
    }
    if (file.size > MAX_BYTES) {
        toast.warn('La foto debe ser menor a 5 MB.');
        if (fileInput.value) fileInput.value.value = '';
        return;
    }

    clearObjectUrl();
    objectUrl = URL.createObjectURL(file);
    previewUrl.value = objectUrl;

    await upload(file);
    // Reset input para permitir re-seleccionar el mismo archivo si es necesario.
    if (fileInput.value) fileInput.value.value = '';
}

async function upload(file) {
    if (uploading.value) return;
    uploading.value = true;
    try {
        const formData = new FormData();
        formData.append('avatar', file);
        const res = await api.post('/api/v/client/avatar', formData);
        const url = res.data?.avatar_url || res.data?.avatarUrl || null;
        if (url) {
            emit('uploaded', url);
            emit('update:avatarUrl', url);
        }
        toast.success('Foto de perfil actualizada.');
        // El padre actualiza props.avatarUrl. Soltamos el preview para mostrar
        // la URL real en próximo render.
        clearObjectUrl();
        previewUrl.value = null;
    } catch (err) {
        toast.apiError(err, 'No pudimos subir tu foto. Intenta de nuevo.');
        emit('error', err);
        // Restaurar: descartar preview si falló para no engañar al usuario.
        clearObjectUrl();
        previewUrl.value = null;
    } finally {
        uploading.value = false;
    }
}

onBeforeUnmount(clearObjectUrl);
</script>

<template>
  <div class="avatar-uploader" :style="{ width: size + 'px', height: size + 'px' }">
    <div class="avatar-disc" :style="{ inset: discInset + 'px' }">
      <img
        v-if="displayUrl"
        :src="displayUrl"
        :alt="name ? `Foto de ${name}` : 'Tu foto de perfil'"
        class="avatar-img"
        draggable="false"
      />
      <span v-else class="avatar-initials font-display" aria-hidden="true">{{ initials }}</span>

      <!-- Loading overlay durante upload -->
      <div v-if="uploading" class="avatar-uploading" aria-live="polite">
        <svg class="avatar-spinner" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="32 32" />
        </svg>
      </div>
    </div>

    <!-- Badge ✓ verde si hay avatar definitivo -->
    <span
      v-if="avatarUrl && !previewUrl && !uploading"
      class="avatar-check"
      aria-label="Foto subida"
      title="Foto subida"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <polyline points="20 6 9 17 4 12" />
      </svg>
    </span>

    <!-- Botón cámara flotante -->
    <button
      type="button"
      class="avatar-cam"
      :disabled="disabled || uploading"
      @click="openPicker"
      :aria-label="avatarUrl ? 'Cambiar foto de perfil' : 'Subir foto de perfil'"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
        <circle cx="12" cy="13" r="4" />
      </svg>
    </button>

    <input
      ref="fileInput"
      type="file"
      class="avatar-file-input"
      :accept="ACCEPT"
      @change="onFileSelect"
      tabindex="-1"
      aria-hidden="true"
    />
  </div>
</template>

<style scoped>
.avatar-uploader {
  position: relative;
  display: inline-block;
}

.avatar-disc {
  position: absolute;
  /* `inset` se setea inline desde el script para respetar discInset */
  border-radius: 999px;
  background: var(--color-wc-bg-tertiary);
  border: 1px solid var(--color-wc-border);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.avatar-initials {
  font-size: 36px;
  font-weight: 600;
  color: var(--color-wc-text-secondary);
  letter-spacing: 0.02em;
  user-select: none;
}

.avatar-uploading {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.45);
  border-radius: 999px;
  color: #fff;
}
.avatar-spinner {
  width: 28px;
  height: 28px;
  animation: avatar-spin 0.9s linear infinite;
}
@keyframes avatar-spin { to { transform: rotate(360deg); } }
@media (prefers-reduced-motion: reduce) {
  .avatar-spinner { animation-duration: 0.01ms; }
}

.avatar-check {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 22px;
  height: 22px;
  border-radius: 999px;
  background: #10B981;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--color-wc-bg-secondary);
  z-index: 2;
  pointer-events: none;
}
.avatar-check svg { width: 12px; height: 12px; }

.avatar-cam {
  position: absolute;
  right: 2px;
  bottom: 2px;
  width: 44px;
  height: 44px;
  min-width: 44px;
  min-height: 44px;
  border-radius: 999px;
  background: var(--color-wc-text);
  color: var(--color-wc-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid var(--color-wc-bg-secondary);
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
  z-index: 3;
}
.avatar-cam:hover:not(:disabled) { transform: scale(1.06); }
.avatar-cam:active:not(:disabled) { transform: scale(0.96); }
.avatar-cam:disabled { opacity: 0.5; cursor: not-allowed; }
.avatar-cam:focus-visible {
  outline: 2px solid var(--color-wc-accent-glow, #EF4444);
  outline-offset: 2px;
}
.avatar-cam svg { width: 18px; height: 18px; }

.avatar-file-input {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

@media (prefers-reduced-motion: reduce) {
  .avatar-cam { transition-duration: 0.01ms; }
}
</style>
