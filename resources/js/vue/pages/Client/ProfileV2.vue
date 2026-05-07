<script setup>
/**
 * ProfileV2.vue — Página completa del Profile Editor v2.
 *
 * Composición:
 *   - ClientLayout
 *   - ProfileHeader (eyebrow + h1 + sub)
 *   - IdentityHero con CompletionRing > AvatarUploader anidado en su slot.
 *     Click en chip de campo faltante → scrollIntoView del input + flash ring.
 *   - PersonalDataForm (DATOS PERSONALES)
 *   - FitnessDataForm (DATOS DE ENTRENAMIENTO)
 *   - StickySaveBar (visible cuando isDirty)
 *   - Toast success "Perfil actualizado correctamente"
 *
 * Lógica orquestada con composables Fase 1:
 *   - useProfileForm: state, initial, isDirty, dirtyCount, load, save, discard,
 *     setAvatarUrl, setCompletion, formErrors, saving, loading, error.
 *     Ya registra Ctrl+S y beforeunload guard internamente — NO duplicar aquí.
 *   - useProfileCompletion: tier, message, set, refresh.
 *
 * Reglas:
 *   - Snake_case en payload PUT preservado por useProfileForm.
 *   - Tokens WellCore + tipografías font-display/font-sans/font-data.
 *   - Latino neutro tuteo, sin mencionar el equipo técnico ni herramientas internas.
 *   - prefers-reduced-motion respetado en transitions.
 *   - Touch targets ≥44px en interactivos críticos.
 */
import { ref, computed, onMounted, nextTick } from 'vue';
import ClientLayout from '../../layouts/ClientLayout.vue';
import { useProfileForm } from '../../composables/useProfileForm';
import { useProfileCompletion } from '../../composables/useProfileCompletion';

import ProfileHeader from '../../components/profile/ProfileHeader.vue';
import IdentityHero from '../../components/profile/IdentityHero.vue';
import CompletionRing from '../../components/profile/CompletionRing.vue';
import AvatarUploader from '../../components/profile/AvatarUploader.vue';
import PersonalDataForm from '../../components/profile/PersonalDataForm.vue';
import FitnessDataForm from '../../components/profile/FitnessDataForm.vue';
import StickySaveBar from '../../components/profile/StickySaveBar.vue';

// ─────────────────────────────────────────────────────────────────────────
// Composables
// ─────────────────────────────────────────────────────────────────────────
const form = useProfileForm();
const completion = useProfileCompletion();

const {
    state,
    avatarUrl,
    formErrors,
    loading,
    saving,
    error,
    isDirty,
    dirtyCount,
    load,
    save,
    discard,
    setAvatarUrl,
} = form;

// ─────────────────────────────────────────────────────────────────────────
// Toast success local (3s, fade in/out)
// ─────────────────────────────────────────────────────────────────────────
const showSuccess = ref(false);
let successTimer = null;

function showSuccessToast() {
    showSuccess.value = true;
    if (successTimer) clearTimeout(successTimer);
    successTimer = setTimeout(() => {
        showSuccess.value = false;
        successTimer = null;
    }, 3000);
}

// ─────────────────────────────────────────────────────────────────────────
// Actions
// ─────────────────────────────────────────────────────────────────────────
async function handleLoad() {
    try {
        const result = await load();
        // useProfileForm ya hidrata su completion interna via load(); reflejamos
        // ese valor en el composable de completion para tener el ref reactivo.
        if (result?.completion) {
            completion.set(result.completion);
        } else if (form.completion?.value) {
            completion.set(form.completion.value);
        }
    } catch (_) {
        // useProfileForm ya guardó error.value; el template renderiza fallback.
    }
}

async function handleSave() {
    const res = await save();
    if (res?.ok) {
        showSuccessToast();
        // Sincronizar completion (useProfileForm ya re-fetchó internamente)
        if (form.completion?.value) {
            completion.set(form.completion.value);
        } else {
            await completion.refresh();
        }
    }
}

function handleDiscard() {
    discard();
}

async function handleAvatarUploaded(url) {
    setAvatarUrl(url);
    await completion.refresh();
}

// ─────────────────────────────────────────────────────────────────────────
// Chip click → scroll + flash highlight del campo
// ─────────────────────────────────────────────────────────────────────────
const FIELD_ID_MAP = {
    name: 'profile-name',
    email: 'profile-email',
    city: 'profile-city',
    birthDate: 'profile-birthDate',
    birth_date: 'profile-birthDate',
    whatsapp: 'profile-whatsapp',
    bio: 'profile-bio',
    peso: 'profile-peso',
    altura: 'profile-altura',
    objetivo: 'profile-objetivo',
    nivel: 'profile-nivel',
    lugarEntreno: 'profile-lugarEntreno',
    lugar_entreno: 'profile-lugarEntreno',
    diasDisponibles: 'profile-diasDisponibles',
    dias_disponibles: 'profile-diasDisponibles',
    restricciones: 'profile-restricciones',
    avatar: 'profile-avatar',
    avatarUrl: 'profile-avatar',
};

function flashElement(el) {
    if (!el) return;
    el.classList.add('is-flash');
    setTimeout(() => el.classList.remove('is-flash'), 1100);
}

async function onChipClick(item) {
    if (!item) return;
    const key = item.key || item.field || '';
    const id = FIELD_ID_MAP[key] || (key ? `profile-${key}` : null);
    if (!id) return;

    await nextTick();
    const target = document.getElementById(id);
    if (!target) return;

    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    // Flash highlight + focus para inputs interactivos
    flashElement(target);
    if (typeof target.focus === 'function') {
        // Pequeño delay para que el scroll suave no sea interrumpido
        setTimeout(() => {
            try { target.focus({ preventScroll: true }); } catch { target.focus(); }
        }, 220);
    }
}

// ─────────────────────────────────────────────────────────────────────────
// Derived
// ─────────────────────────────────────────────────────────────────────────
const completionScore = computed(() => completion.state.value.score ?? 0);
const completionMissing = computed(() => completion.state.value.missing ?? []);
const completionTier = computed(() => completion.tier.value);
const completionMessage = computed(() => completion.message.value);

// ─────────────────────────────────────────────────────────────────────────
// Lifecycle
// ─────────────────────────────────────────────────────────────────────────
onMounted(handleLoad);
</script>

<template>
  <ClientLayout>
    <div class="profile-v2">
      <!-- Toast success ─────────────────────────────────────────── -->
      <Transition name="toast">
        <div
          v-if="showSuccess"
          class="profile-toast"
          role="status"
          aria-live="polite"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="20 6 9 17 4 12" />
          </svg>
          <span>Perfil actualizado correctamente</span>
        </div>
      </Transition>

      <!-- Header ───────────────────────────────────────────────── -->
      <ProfileHeader />

      <!-- Loading skeleton ─────────────────────────────────────── -->
      <div
        v-if="loading"
        class="profile-skeleton"
        aria-busy="true"
        aria-live="polite"
      >
        <div class="skeleton-card skeleton-card--hero"></div>
        <div class="skeleton-card skeleton-card--form"></div>
        <span class="sr-only">Cargando tu perfil…</span>
      </div>

      <!-- Error state ──────────────────────────────────────────── -->
      <div
        v-else-if="error && !state.name"
        class="profile-error"
        role="alert"
      >
        <div class="profile-error__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        </div>
        <p class="profile-error__msg">{{ error }}</p>
        <button
          type="button"
          class="profile-error__retry"
          @click="handleLoad"
        >Reintentar</button>
      </div>

      <!-- Main content ─────────────────────────────────────────── -->
      <template v-else>
        <!-- IdentityHero: ring + avatar + meta + chips -->
        <div id="profile-avatar" class="profile-hero-anchor">
          <IdentityHero
            :name="state.name"
            :email="state.email"
            :completion-score="completionScore"
            :completion-tier="completionTier"
            :completion-message="completionMessage"
            :missing="completionMissing"
            @chip-click="onChipClick"
          >
            <CompletionRing :score="completionScore" :size="120" :stroke-width="4">
              <AvatarUploader
                :avatar-url="avatarUrl || ''"
                :name="state.name"
                :size="100"
                @uploaded="handleAvatarUploaded"
              />
            </CompletionRing>
          </IdentityHero>
        </div>

        <!-- Form sections -->
        <form
          class="profile-form"
          @submit.prevent="handleSave"
          novalidate
        >
          <fieldset :disabled="saving" class="profile-form__fieldset">
            <PersonalDataForm
              :state="state"
              :errors="formErrors"
              :avatar-url="avatarUrl || ''"
            />
            <FitnessDataForm
              :state="state"
              :errors="formErrors"
            />

            <!-- Submit oculto para que Enter desde un input dispare save() -->
            <button type="submit" class="sr-only" tabindex="-1" aria-hidden="true">Guardar</button>
          </fieldset>
        </form>

        <!-- Sticky save bar (solo cuando hay cambios) -->
        <StickySaveBar
          :visible="isDirty"
          :dirty-count="dirtyCount"
          :saving="saving"
          @save="handleSave"
          @discard="handleDiscard"
        />

        <!-- Spacer para que la save bar no tape el último campo -->
        <div v-show="isDirty" class="profile-savebar-spacer" aria-hidden="true"></div>
      </template>
    </div>
  </ClientLayout>
</template>

<style scoped>
.profile-v2 {
  width: 100%;
  max-width: 1080px;
  margin: 0 auto;
  padding: 32px 16px 48px;
  font-size: 16px;
}
@media (min-width: 1024px) {
  .profile-v2 { padding: 40px 32px 64px; }
}

/* ── Skeleton ────────────────────────────────────────────────── */
.profile-skeleton {
  margin-top: 32px;
  display: grid;
  gap: 24px;
}
.skeleton-card {
  border-radius: 20px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  position: relative;
  overflow: hidden;
}
.skeleton-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(255, 255, 255, 0.04) 50%,
    transparent 100%
  );
  animation: skeleton-shimmer 1.6s ease-in-out infinite;
}
.skeleton-card--hero { height: 200px; }
.skeleton-card--form { height: 520px; }
@keyframes skeleton-shimmer {
  from { transform: translateX(-100%); }
  to   { transform: translateX(100%); }
}
@media (prefers-reduced-motion: reduce) {
  .skeleton-card::after { animation: none; }
}

/* ── Error ──────────────────────────────────────────────────── */
.profile-error {
  margin-top: 48px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  padding: 48px 24px;
  border-radius: 20px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  text-align: center;
}
.profile-error__icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(220, 38, 38, 0.12);
  color: var(--color-wc-accent, #DC2626);
}
.profile-error__icon svg { width: 28px; height: 28px; }
.profile-error__msg {
  margin: 0;
  font-size: 14px;
  color: var(--color-wc-text-secondary);
  max-width: 48ch;
}
.profile-error__retry {
  height: 44px;
  min-height: 44px;
  padding: 0 24px;
  border-radius: 10px;
  border: 1px solid transparent;
  background: var(--color-wc-accent, #DC2626);
  color: #fff;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.15s ease, transform 0.15s ease;
}
.profile-error__retry:hover { background: var(--color-wc-accent-hover, #B91C1C); }
.profile-error__retry:active { transform: translateY(1px); }
.profile-error__retry:focus-visible {
  outline: 2px solid var(--color-wc-accent-glow, #EF4444);
  outline-offset: 2px;
}

/* ── Hero anchor + flash highlight ──────────────────────────── */
.profile-hero-anchor {
  margin-top: 24px;
  scroll-margin-top: 24px;
  border-radius: 20px;
  transition: box-shadow 0.4s ease;
}

/* `.is-flash` aplicado por flashElement() durante 1s al hacer click en chip.
   Compatible con cualquier elemento, sea container del hero, input o textarea. */
:deep(.is-flash) {
  animation: profile-flash 1s ease-out;
}
@keyframes profile-flash {
  0% {
    box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.45),
                0 0 0 0 rgba(220, 38, 38, 0.20);
  }
  40% {
    box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.40),
                0 0 0 8px rgba(220, 38, 38, 0.16);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(220, 38, 38, 0),
                0 0 0 0 rgba(220, 38, 38, 0);
  }
}
@media (prefers-reduced-motion: reduce) {
  :deep(.is-flash) { animation: none; }
}

/* ── Form ───────────────────────────────────────────────────── */
.profile-form {
  width: 100%;
}
.profile-form__fieldset {
  border: 0;
  padding: 0;
  margin: 0;
  min-width: 0;
}

/* ── Save bar spacer (evita que la barra tape contenido) ────── */
.profile-savebar-spacer {
  height: 96px;
}
@media (max-width: 1023px) {
  .profile-savebar-spacer { height: 140px; }
}

/* ── Toast ──────────────────────────────────────────────────── */
.profile-toast {
  position: fixed;
  right: 16px;
  bottom: 96px;
  z-index: 60;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 12px;
  border: 1px solid rgba(16, 185, 129, 0.35);
  background: rgba(16, 185, 129, 0.12);
  color: #10B981;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 12px 32px -12px rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  max-width: calc(100vw - 32px);
}
.profile-toast svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}
@media (min-width: 1024px) {
  .profile-toast { right: 24px; bottom: 96px; }
}

/* Light mode legibilidad */
:global(html:not(.dark)) .profile-toast {
  background: rgba(16, 185, 129, 0.10);
  color: #047857;
}

/* Toast transition */
.toast-enter-active,
.toast-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
.toast-enter-to,
.toast-leave-from {
  opacity: 1;
  transform: translateY(0);
}
@media (prefers-reduced-motion: reduce) {
  .toast-enter-active,
  .toast-leave-active { transition-duration: 0.01ms; }
}

/* ── Utility ────────────────────────────────────────────────── */
.sr-only {
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
</style>
