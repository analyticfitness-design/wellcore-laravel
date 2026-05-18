<script setup>
/**
 * BioField.vue — textarea de bio (max 160) con counter en vivo + slot
 * preview a la derecha (≥720px) o debajo (<720px).
 *
 * Counter ámbar si length > 140.
 *
 * v-model standard.
 */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    id:          { type: String, default: 'bio' },
    label:       { type: String, default: '' },
    hintLabel:   { type: String, default: '' },
    placeholder: { type: String, default: '' },
    maxlength:   { type: Number, default: 160 },
    error:       { type: String, default: '' },
    disabled:    { type: Boolean, default: false },
});

const model = defineModel({ default: '' });

const labelText       = computed(() => props.label       || t('client_account.profile_field_bio'));
const hintLabelText   = computed(() => props.hintLabel   || t('client_account.profile_field_bio_hint'));
const placeholderText = computed(() => props.placeholder || t('client_account.profile_field_bio_placeholder'));

const length = computed(() => (model.value || '').length);
const counter = computed(() => `${length.value}/${props.maxlength}`);
const counterWarn = computed(() => length.value > Math.max(0, props.maxlength - 20));

const describedBy = computed(() => {
    if (props.error) return `${props.id}-error`;
    return undefined;
});
</script>

<template>
  <div class="bio-field">
    <div class="bio-grid">
      <div class="bio-input-col">
        <div class="field-label-row">
          <label :for="id" class="field-label">
            {{ labelText }}
            <span v-if="hintLabelText" class="field-label__hint">{{ hintLabelText }}</span>
          </label>
          <span
            class="field-counter tabular-nums"
            :class="{ 'is-warn': counterWarn }"
            aria-live="polite"
          >{{ counter }}</span>
        </div>

        <textarea
          :id="id"
          v-model="model"
          class="textarea"
          :class="{ 'is-invalid': !!error }"
          :placeholder="placeholderText"
          :maxlength="maxlength"
          :disabled="disabled"
          :aria-invalid="error ? 'true' : 'false'"
          :aria-describedby="describedBy"
          rows="4"
        />

        <p
          v-if="error"
          :id="`${id}-error`"
          class="field-error"
          role="alert"
        >{{ error }}</p>
      </div>

      <div class="bio-preview-col">
        <slot name="preview" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.bio-field { width: 100%; min-width: 0; }

.bio-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}
@media (min-width: 720px) {
  .bio-grid {
    grid-template-columns: 1fr 280px;
    gap: 20px;
    align-items: start;
  }
}

.bio-input-col {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
}

.bio-preview-col { min-width: 0; }

.field-label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.field-label {
  font-size: 14px;
  font-weight: 500;
  color: var(--color-wc-text);
}
.field-label__hint {
  margin-left: 6px;
  font-size: 12px;
  font-weight: 400;
  color: var(--color-wc-text-tertiary);
}

.field-counter {
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
}
.field-counter.is-warn { color: #F59E0B; }

.textarea {
  width: 100%;
  min-height: 96px;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  color: var(--color-wc-text);
  font-size: 16px;
  font-family: inherit;
  line-height: 1.5;
  resize: vertical;
  transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}
.textarea::placeholder {
  color: var(--color-wc-text-quaternary, var(--color-wc-text-tertiary));
}
.textarea:hover:not(:disabled) {
  border-color: var(--color-wc-border-strong, var(--color-wc-border));
}
.textarea:focus,
.textarea:focus-visible {
  outline: none;
  border-color: var(--color-wc-accent-glow, #EF4444);
  background: var(--color-wc-bg-tertiary);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
}
.textarea.is-invalid {
  border-color: var(--color-wc-accent, #DC2626);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.10);
}
.textarea:disabled { opacity: 0.6; cursor: not-allowed; }

.field-error {
  margin: 0;
  font-size: 12px;
  color: var(--color-wc-accent, #DC2626);
  line-height: 1.4;
}

@media (prefers-reduced-motion: reduce) {
  .textarea { transition-duration: 0.01ms; }
}
</style>
