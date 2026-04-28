<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    form: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const iframeKey = ref(0);

const previewUrl = computed(() => {
    if (!props.form) return '';
    return `/admin/forms-preview/${props.form.area}/${props.form.slug}`;
});

const tagClass = computed(() => ({
    'Inscripcion': 'form-tag--amber',
    'Cliente':     'form-tag--sky',
    'RISE':        'form-tag--violet',
}[props.form?.tag] ?? 'form-tag--default'));

function reload() { iframeKey.value++; }

function openInTab() {
    if (previewUrl.value) window.open(previewUrl.value, '_blank', 'noopener,noreferrer');
}

function onKeydown(e) {
    if (e.key === 'Escape') emit('close');
}

onMounted(() => document.addEventListener('keydown', onKeydown));
onUnmounted(() => document.removeEventListener('keydown', onKeydown));

watch(() => props.form, (val) => {
    if (val) iframeKey.value++;
});
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="modal-enter-active"
      enter-from-class="modal-enter-from"
      enter-to-class="modal-enter-to"
      leave-active-class="modal-leave-active"
      leave-from-class="modal-enter-to"
      leave-to-class="modal-enter-from"
    >
      <div
        v-if="form"
        class="preview-modal"
        role="dialog"
        aria-modal="true"
        :aria-label="`Vista previa: ${form?.name}`"
      >
        <!-- Toolbar -->
        <div class="preview-toolbar">
          <div class="preview-toolbar__left">
            <span v-if="form" class="form-tag" :class="tagClass">{{ form.tag }}</span>
            <div class="preview-toolbar__info">
              <h2 class="preview-toolbar__name">{{ form?.name?.toUpperCase() }}</h2>
              <p class="preview-toolbar__desc">{{ form?.description }}</p>
            </div>
          </div>

          <div class="preview-toolbar__actions">
            <button class="preview-btn-icon" aria-label="Recargar preview" @click="reload">
              <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
              </svg>
            </button>

            <button class="preview-btn-text" @click="openInTab">
              <svg aria-hidden="true" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
              </svg>
              NUEVA PESTANA
            </button>

            <button class="preview-btn-close" aria-label="Cerrar preview" @click="$emit('close')">
              <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Iframe -->
        <div class="preview-body">
          <iframe
            v-if="form"
            :key="iframeKey"
            :src="previewUrl"
            loading="lazy"
            sandbox="allow-same-origin allow-scripts allow-forms"
            title="Vista previa del formulario"
            class="preview-iframe"
          ></iframe>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.preview-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    background: rgba(10, 10, 10, 0.97);
    backdrop-filter: blur(8px);
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s var(--ease-out);
}
.modal-enter-from,
.modal-enter-to { } /* handled by transition classes above */
:global(.modal-enter-from),
:global(.modal-leave-to) { opacity: 0; }
:global(.modal-enter-to),
:global(.modal-leave-from) { opacity: 1; }

.preview-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-secondary);
    flex-shrink: 0;
}

.preview-toolbar__left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.form-tag {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 99px;
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    font-weight: 500;
    border: 1px solid transparent;
}
.form-tag--amber { background: var(--color-wc-amber-soft); color: var(--color-wc-amber-text); border-color: rgba(245,158,11,0.2); }
.form-tag--sky   { background: var(--color-wc-blue-soft);  color: var(--color-wc-blue-text);  border-color: rgba(59,130,246,0.2); }
.form-tag--violet{ background: rgba(139,92,246,0.1);       color: #a78bfa;                    border-color: rgba(139,92,246,0.2); }
.form-tag--default{ background: rgba(255,255,255,0.05); color: var(--color-wc-text-secondary); }

.preview-toolbar__info {
    min-width: 0;
}
.preview-toolbar__name {
    font-family: var(--font-display);
    font-size: 15px;
    letter-spacing: 0.04em;
    color: var(--color-wc-text);
    margin: 0;
    line-height: 1.1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.preview-toolbar__desc {
    font-family: var(--font-sans);
    font-size: 10px;
    color: var(--color-wc-text-tertiary);
    margin: 2px 0 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.preview-toolbar__actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.preview-btn-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary);
    color: var(--color-wc-text-secondary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.preview-btn-icon:hover { color: var(--color-wc-text); border-color: var(--color-wc-border-2); }

.preview-btn-text {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 34px;
    padding: 0 12px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary);
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.preview-btn-text:hover { color: var(--color-wc-text); border-color: var(--color-wc-border-2); }

.preview-btn-close {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: none;
    background: var(--color-wc-accent);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s var(--ease-out);
}
.preview-btn-close:hover { background: #b91c1c; }

.preview-body {
    flex: 1;
    overflow: hidden;
    background: var(--color-wc-bg);
    position: relative;
}
.preview-iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: none;
}

@media (prefers-reduced-motion: reduce) {
    .modal-enter-active, .modal-leave-active { transition: none; }
    .preview-btn-icon, .preview-btn-text, .preview-btn-close { transition: none; }
}
</style>
