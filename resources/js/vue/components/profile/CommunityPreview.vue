<script setup>
/**
 * CommunityPreview.vue — tarjeta dashed que muestra cómo se verá el cliente
 * en la comunidad (mini avatar 36px + nombre + meta + bio).
 *
 * Reactivo a name / bio / city / birthDate / avatarUrl.
 *
 * Si bio está vacía → estado italic placeholder.
 */
import { computed } from 'vue';

const props = defineProps({
    name:       { type: String, default: '' },
    bio:        { type: String, default: '' },
    city:       { type: String, default: '' },
    birthDate:  { type: String, default: '' }, // YYYY-MM-DD
    avatarUrl:  { type: String, default: '' },
    plan:       { type: String, default: '' }, // opcional
});

function calcAge(iso) {
    if (!iso) return null;
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return null;
    const today = new Date();
    let age = today.getFullYear() - d.getFullYear();
    const m = today.getMonth() - d.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < d.getDate())) age--;
    if (age < 0 || age > 120) return null;
    return age;
}

const age = computed(() => calcAge(props.birthDate));

const initials = computed(() => {
    const n = (props.name || '').trim();
    if (!n) return '?';
    const parts = n.split(/\s+/).slice(0, 2);
    return parts.map((w) => w[0] || '').join('').toUpperCase() || '?';
});

const meta = computed(() => {
    const parts = [];
    if (age.value !== null) parts.push(`${age.value} años`);
    if (props.city) parts.push(props.city);
    if (props.plan) parts.push(props.plan);
    return parts.join(' · ');
});

const displayName = computed(() => props.name || 'Tu nombre');
</script>

<template>
  <div class="preview-card" aria-label="Vista previa de cómo se verá tu perfil en la comunidad">
    <p class="preview-cap font-display">VISTA EN LA COMUNIDAD</p>

    <div class="preview-row">
      <div class="preview-av">
        <img
          v-if="avatarUrl"
          :src="avatarUrl"
          :alt="`Foto de ${displayName}`"
          class="preview-av__img"
          draggable="false"
        />
        <span v-else class="font-display preview-av__initials" aria-hidden="true">{{ initials }}</span>
      </div>

      <div class="preview-body">
        <p class="preview-name">{{ displayName }}</p>
        <p v-if="meta" class="preview-meta">{{ meta }}</p>

        <p v-if="bio" class="preview-bio">{{ bio }}</p>
        <p v-else class="preview-bio preview-bio--empty">
          Sin descripción todavía.
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.preview-card {
  padding: 14px;
  border-radius: 14px;
  border: 1px dashed var(--color-wc-border-strong, var(--color-wc-border));
  background: var(--color-wc-bg-secondary);
  min-width: 0;
}

.preview-cap {
  margin: 0 0 10px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-quaternary, var(--color-wc-text-tertiary));
}

.preview-row {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.preview-av {
  width: 36px;
  height: 36px;
  border-radius: 999px;
  background: var(--color-wc-bg-prominent, var(--color-wc-bg-tertiary));
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
  border: 1px solid var(--color-wc-border);
}
.preview-av__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.preview-av__initials {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-wc-text-secondary);
  letter-spacing: 0.02em;
}

.preview-body {
  min-width: 0;
  flex: 1;
}

.preview-name {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: var(--color-wc-text);
  line-height: 1.3;
  word-break: break-word;
}

.preview-meta {
  margin: 1px 0 0;
  font-size: 11px;
  color: var(--color-wc-text-tertiary);
  line-height: 1.3;
  word-break: break-word;
}

.preview-bio {
  margin: 6px 0 0;
  font-size: 13px;
  line-height: 1.5;
  color: var(--color-wc-text-secondary);
  word-break: break-word;
  white-space: pre-wrap;
}
.preview-bio--empty {
  color: var(--color-wc-text-quaternary, var(--color-wc-text-tertiary));
  font-style: italic;
}
</style>
