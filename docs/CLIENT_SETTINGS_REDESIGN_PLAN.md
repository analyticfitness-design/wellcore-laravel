# CLIENT SETTINGS REDESIGN PLAN
# WellCore Laravel — Vue 3 SFC Refactor
# Fecha: 2026-04-24

---

## 1. Executive Summary

Rediseño visual y UX completo del módulo CLIENT SETTINGS (`/client/settings`).

- **Stack de implementación**: Vue 3 SFC + Composition API (`<script setup>`) + Pinia (auth store) + axios via `useApi` + Tailwind CSS 4 + tokens `wc-*`
- **Scope estricto**: solo `ClientSettings.vue` (reescritura) + 9 archivos nuevos (4 subcomponentes tab, 4 primitivos UI, 2 composables). NINGÚN otro archivo existente se toca salvo `app.css` (solo adición de 2 keyframes si faltan).
- **NO se tocan**: endpoints API PHP (`/api/v/client/settings`, `/api/v/client/settings/password`), schema DB, `ClientLayout.vue`, `router/index.js`, `useApi.js`, `useToast.js`, Livewire (ya está muerto en esta ruta), cualquier otro `.vue` fuera del scope.
- **Fuente de verdad visual**: `C:\Users\GODSF\Downloads\WellCore-Client-Settings-ADAPTADO-NUEVO.html`
- **Referencia de producción actual**: `C:\Users\GODSF\Downloads\WellCore-Client-Settings-REAL-PRODUCCION.html`

Criterios de aceptación cuantitativos:
- Lighthouse Performance ≥ 90, Accessibility ≥ 95
- 0 hex literales en Vue files (solo tokens `var(--color-wc-*)` o clases Tailwind `wc-*`)
- 0 fuentes ajenas a Oswald / Raleway / JetBrains Mono
- LCP < 1.2 s en conexión 4G simulada
- Tab switch animado < 300 ms

---

## 2. Análisis del Estado Actual

### 2.1 ClientSettings.vue actual (`resources/js/vue/pages/Client/ClientSettings.vue`, 670 líneas)

**Composables usados:**
- `useApi()` — axios instance con Bearer token (línea 3)
- `useToast()` — sistema global de toasts (línea 4)

**Stores Pinia:**
- `useAuthStore` (indirectamente, via `useApi` que lee `authStore.token`)

**Endpoints API consumidos:**
- `GET /api/v/client/settings` — carga `{ name, email, phone }`
- `PUT /api/v/client/settings` — guarda nombre, email, teléfono
- `PUT /api/v/client/settings/password` — cambia contraseña (throttled)

**Refs / reactive actuales:**
- `tab` (ref) — estado de tab activa: `'perfil' | 'notificaciones' | 'apariencia' | 'seguridad'`
- `loadingProfile`, `savingProfile`, `savingPassword` (ref boolean)
- `profileForm` (ref objeto: name, email, phone)
- `profileErrors`, `passwordErrors` (ref objetos de validación 422)
- `passwordForm` (ref: current_password, password, password_confirmation)
- `notifications` (reactive: checkin, coach, achievements, payments, weekly) — persiste en `localStorage['wc_notifications']`
- `soundEnabled` (ref) — persiste en `localStorage['wc_sound_enabled']`
- Flags de success inline: `showProfileSuccess`, `showPasswordSuccess` (setTimeout 3s)

**Secciones actuales:**
1. Perfil — formulario nombre/email/teléfono con validación 422
2. Notificaciones — 5 switches + 1 switch de sonido (6 total)
3. Apariencia — selector dark/light (2 tarjetas, sin opción "auto")
4. Seguridad — cambio de contraseña con validación mismatch + security tip estático

**Gaps vs diseño nuevo (ADAPTADO-NUEVO.html):**
| Gap | Actual | Objetivo |
|-----|--------|----------|
| Sistema de tabs | Pill buttons con `bg-wc-accent` activo | Underline tabs sticky con animación border-bottom slide |
| Avatar hero | Ausente | Monograma 80px con borde rojo + nombre + email + plan badge + coach asignado |
| Formulario perfil | 1 columna, max-w-xl | 2 columnas (md:grid-cols-2), grid-flow responsive |
| Apariencia | 2 opciones (dark/light) | 3 opciones (dark/light/auto) con .theme-preview gradient |
| Notificaciones | Switch inline raw en template | WcSwitch primitivo extraído, spring animation cubic-bezier(.34,1.56,.64,1) |
| Plan info card | Ausente | Tarjeta gradiente accent con glows + "Ver plan" CTA |
| Zona de cuenta | Ausente | Sección peligro en tab Seguridad: Exportar datos + Cerrar sesión |
| Toast de success | Toasts duplicados inline (2 Transitions) | Unificado via `useToast().success()` existente |
| Card lift hover | Ausente | `transition: transform .25s, box-shadow .25s` → translateY(-2px) |
| Tab deep link | Ausente | `?tab=notificaciones` vía `useRoute().query.tab` en `onMounted` |
| Form labels | Texto normal | Uppercase 10px tracking-widest font-mono (estilo `.form-label` del diseño) |
| Keyframe tab-panel | Ausente | `wc-fade-in` 280ms ease |
| Push notification card | Ausente | Card "Notificaciones push" con estado activo/inactivo (localStorage) |

---

## 3. Arquitectura Objetivo

### 3.1 File Tree

```
resources/js/vue/pages/Client/
├── ClientSettings.vue                     [REESCRIBIR — orquestador ~220 líneas]
└── settings/
    ├── SettingsProfileTab.vue             [NUEVO — avatar hero + form 2 cols]
    ├── SettingsNotificationsTab.vue       [NUEVO — push card + 6 switches]
    ├── SettingsAppearanceTab.vue          [NUEVO — theme grid 3 opciones + quick toggle]
    └── SettingsSecurityTab.vue            [NUEVO — password form + recomendaciones + zona cuenta]

resources/js/vue/components/ui/
├── ToastContainer.vue                     [EXISTENTE — no tocar]
├── WcIcon.vue                             [EXISTENTE — no tocar]
├── WcTabs.vue                             [NUEVO — primitivo tabs con underline animado]
├── WcSwitch.vue                           [NUEVO — primitivo switch con spring animation]
├── WcCard.vue                             [NUEVO — card con hover lift + header slot]
└── WcAvatarHero.vue                       [NUEVO — avatar 80px monograma + meta + badges]

resources/js/vue/composables/
├── useApi.js                              [EXISTENTE — no tocar]
├── useToast.js                            [EXISTENTE — no tocar]
├── useSettings.js                         [NUEVO — fetch/save profile, password]
└── useTheme.js                            [NUEVO — dark/light/auto con mediaQuery listener]

resources/css/app.css                      [SOLO ADITIVO — agregar wc-fade-in si no existe]
```

### 3.2 Componentes y Responsabilidades

#### `ClientSettings.vue` (orquestador)
- Responsabilidad única: estado del tab activo + deep link ?tab= + render condicional via `defineAsyncComponent`
- Imports: `WcTabs`, los 4 subcomponentes tab via `defineAsyncComponent`, `useRoute`
- NO tiene lógica de negocio — delega todo a composables y tabs
- Propaga el tab activo via prop al componente montado

#### `useSettings.js` (composable)
- Estado compartido entre todos los tabs (singleton-like via módulo)
- Expone: `profileForm`, `profileErrors`, `loadingProfile`, `savingProfile`, `fetchSettings()`, `updateProfile()`, `passwordForm`, `passwordErrors`, `savingPassword`, `changePassword()`, `notifications`, `soundEnabled`
- Previene doble fetch: `hasFetched` flag
- Debounce 500ms en `updateProfile` (solo en saves automáticos si se agregan en futuro)

#### `useTheme.js` (composable)
- Estado: `theme` ref (`'dark' | 'light' | 'auto'`)
- `selectTheme(t)`: aplica clase `.dark` al `<html>`, guarda `localStorage['wc_theme']`
- `auto`: escucha `window.matchMedia('(prefers-color-scheme: dark)')` + addEventListener change
- Inicializa desde `localStorage['wc_theme']` en `onMounted`; si no existe, lee `localStorage['darkMode']` (compatibilidad con código anterior)
- Expone: `theme`, `isDark` (computed), `selectTheme()`, `toggleTheme()`

#### `WcTabs.vue`
- Props: `tabs: Array<{ id, label, icon? }>`, `modelValue: String`
- Emits: `update:modelValue`
- Renderiza la barra sticky `top-[128px]` (64px topbar + 64px impersonation banner potencial) con `border-bottom` + underline activo en `wc-accent`
- ARIA: `role="tablist"`, cada tab `role="tab"`, `aria-selected`, `aria-controls`
- Overflow-x auto en mobile, scrollbar oculta
- Animación underline: `transition: width .2s, left .2s` — se calcula via refs en el DOM

#### `WcSwitch.vue`
- Props: `modelValue: Boolean`, `disabled: Boolean`, `label?: String`, `description?: String`, `id: String`
- Emits: `update:modelValue`
- Thumb animation: `transition: transform .35s cubic-bezier(.34,1.56,.64,1)` (spring overshoot)
- Track: 44×24px, `bg-wc-accent` on, `bg-wc-bg-secondary border border-wc-border-strong` off
- `role="switch"`, `:aria-checked="modelValue.toString()"`, `focus:ring-2 focus:ring-wc-accent`
- Wrapper con label+description a la izquierda del switch

#### `WcCard.vue`
- Props: `title?: String`, `subtitle?: String`, `icon?: String` (SVG path data), `danger?: Boolean`
- Slots: `header-extra` (slot adicional en el header), `default` (body content)
- Clases base: `rounded-xl border border-wc-border bg-wc-bg-tertiary transition-transform transition-shadow duration-250`
- Hover: `hover:-translate-y-0.5 hover:border-wc-border-strong hover:shadow-lg`
- `danger` prop: cambia border a `rgba(220,38,38,.2)` y bg a `rgba(220,38,38,.06)`

#### `WcAvatarHero.vue`
- Props: `name: String`, `email: String`, `plan?: String`, `coachName?: String`, `memberCode?: String`, `status?: String`
- Renderiza: monograma (iniciales Oswald 32px), borde 3px accent/25, nombre uppercase Oswald, email+ciudad en text-secondary, badges de código (font-mono) y plan+estado

#### `SettingsProfileTab.vue`
- Usa: `useSettings()` para form/save, `WcCard`, `WcAvatarHero`
- Form en 2 columnas (md:grid-cols-2), nombre en col-span-2
- Validación 422 inline debajo de cada campo
- Loading skeleton: `animate-pulse h-80 rounded-xl`
- Al guardar exitoso: llama `toast.success('Perfil actualizado')` (usa `useToast`)
- Plan info card (gradiente accent): "Tu plan actual" + CTA "Ver plan" → `router.push('/client/plan')`

#### `SettingsNotificationsTab.vue`
- Usa: `useSettings()`, `WcCard`, `WcSwitch`
- Card 1: Push notifications — detecta `Notification.permission`, muestra estado activo/inactivo, botón "Activar" llama `Notification.requestPermission()`
- Card 2: 6 switches via `WcSwitch` con v-model:
  - `notifications.checkin`, `.coach`, `.achievements`, `.payments`, `.weekly`, `soundEnabled`
- Watch en `notifications` (deep) + `soundEnabled` → `localStorage`

#### `SettingsAppearanceTab.vue`
- Usa: `useTheme()`, `WcCard`
- Theme grid 3 cols (grid-cols-3, en mobile cols-1): dark, light, auto
- `.theme-preview` con gradient automático para "auto"
- Quick toggle bar debajo del grid

#### `SettingsSecurityTab.vue`
- Usa: `useSettings()`, `WcCard`
- Form 2 cols (contraseña actual col-span-2, nueva+confirmar en cols)
- Validación mismatch + min 10 chars client-side (existente)
- Password error banner con ícono
- Recomendaciones de seguridad: lista 3 items con check rojo
- "Zona de cuenta" al final: `WcCard` con `danger` prop + botones "Exportar mis datos" y "Cerrar sesión"
  - Exportar: `toast.info('Solicitud enviada a tu correo')`
  - Cerrar sesión: `authStore.logout()` → router.push('/login')

---

## 4. Fases de Implementación (con commit por fase)

### Fase 0 — Branch + Scaffold (20 min)
- [ ] Crear rama: `git checkout -b feat/client-settings-redesign`
- [ ] Crear directorio `resources/js/vue/pages/Client/settings/`
- [ ] Crear todos los archivos vacíos (solo `<script setup></script><template></template>`)
- [ ] Crear `resources/js/vue/composables/useSettings.js` y `useTheme.js` vacíos
- [ ] Verificar que `resources/js/vue/components/ui/` existe (ya tiene `WcIcon.vue`, `ToastContainer.vue`)
- [ ] Commit: `scaffold(settings): crear estructura de directorios y archivos vacíos`

### Fase 1 — UI Primitivos (1 hr)
- [ ] Implementar `WcSwitch.vue` completo con spring animation
- [ ] Implementar `WcCard.vue` con slots y hover lift
- [ ] Implementar `WcTabs.vue` con underline animado y ARIA
- [ ] Implementar `WcAvatarHero.vue` con props completas
- [ ] Verificar renders básicos en aislamiento (npm run dev + ruta /client/settings)
- [ ] Commit: `feat(ui): WcSwitch, WcCard, WcTabs, WcAvatarHero primitivos`

### Fase 2 — Composables (30 min)
- [ ] Implementar `useSettings.js`: migrar toda la lógica de `ClientSettings.vue` actual (fetchSettings, updateProfile, changePassword, notifications localStorage, soundEnabled)
- [ ] Agregar flag `hasFetched` para no duplicar fetch entre tabs
- [ ] Implementar `useTheme.js`: dark/light/auto + mediaQuery listener + compatibilidad con `localStorage['darkMode']` existente
- [ ] Commit: `feat(composables): useSettings y useTheme extraídos`

### Fase 3 — 4 Subcomponentes Tab (2-3 hrs)
- [ ] `SettingsProfileTab.vue` — avatar hero + form 2 cols + plan info card + loading skeleton + error retry + toast al guardar
- [ ] `SettingsNotificationsTab.vue` — push permission card + 6 WcSwitches + footer note
- [ ] `SettingsAppearanceTab.vue` — theme grid 3 cols + quick toggle bar
- [ ] `SettingsSecurityTab.vue` — form 2 cols + error banner + recomendaciones lista + zona de cuenta danger
- [ ] Commit: `feat(settings): 4 subcomponentes tab completos`

### Fase 4 — Orquestador ClientSettings.vue (1 hr)
- [ ] Reescribir `ClientSettings.vue` completo (~220 líneas)
- [ ] `defineAsyncComponent` para los 4 tabs (lazy load)
- [ ] Deep link: leer `useRoute().query.tab` en `onMounted`, setear tab inicial
- [ ] Montar `WcTabs` como barra de navegación local (dentro de `<ClientLayout>`)
- [ ] `<KeepAlive>` wrapping los tabs para no re-fetchear al navegar
- [ ] Commit: `feat(settings): orquestador ClientSettings reescrito con lazy tabs y deep link`

### Fase 5 — Animaciones (1 hr)
- [ ] Tab panel: agregar `wc-fade-in` keyframe en `app.css` (verificar si ya existe)
- [ ] `<Transition>` wrapper por tab-panel con `enter-active: animate-wc-pop`
- [ ] WcSwitch spring overshoot verificado en mobile
- [ ] Card hover lift verificado en desktop + touch
- [ ] Toast de success: verificar que usa ToastContainer existente (no inline)
- [ ] Commit: `feat(settings): animaciones tab-panel, switch spring, card lift`

### Fase 6 — Responsive + A11y (1 hr)
- [ ] Mobile 390px: sin scroll horizontal, form 1 col, theme-grid 1 col, avatar hero column layout
- [ ] `@media (max-width:480px)`: avatar 64px → font-size 24px
- [ ] WcTabs: overflow-x auto sin scrollbar visible (`scrollbar-width:none`)
- [ ] ARIA: `role="tablist"` en WcTabs, `role="tab"` + `aria-selected` en cada botón, `role="tabpanel"` + `aria-labelledby` en cada panel
- [ ] Focus management: al cambiar tab via teclado, focar el panel activo
- [ ] `prefers-reduced-motion`: respetar con `@media` en `app.css` (ya existe el bloque)
- [ ] Commit: `a11y(settings): ARIA tabs, focus management, responsive 390px`

### Fase 7 — Tests (1 hr)
- [ ] Verificar que Vitest está configurado (si no, skip y documentar en checklist)
- [ ] Test unitario `useTheme.js`: selectTheme('dark') aplica clase, selectTheme('auto') escucha mediaQuery
- [ ] Test unitario `useSettings.js`: fetchSettings popula profileForm, updateProfile llama PUT
- [ ] Test componente `WcSwitch.vue`: toggle emite update:modelValue, aria-checked refleja estado
- [ ] Smoke test manual en navegador: navegar a /client/settings, cambiar tabs, guardar perfil, verificar toast, cambiar contraseña con mismatch, verificar error inline
- [ ] Commit: `test(settings): tests unitarios useTheme, useSettings, WcSwitch`

### Fase 8 — Push (sin deploy automático)
- [ ] `git push origin feat/client-settings-redesign`
- [ ] Abrir PR hacia `main`
- [ ] NO ejecutar `npm run build` en producción
- [ ] NO ejecutar rebuild Docker en EasyPanel
- [ ] Verificar en PR que build de CI pasa
- [ ] Hacer merge en `main` cuando PR aprobado
- [ ] Después del merge: gitpull-load en EasyPanel vía MCP (NO rebuild Docker)

---

## 5. Código Crítico

### 5.1 ClientSettings.vue (orquestador — ~220 líneas)

```vue
<script setup>
import { ref, computed, onMounted, defineAsyncComponent } from 'vue';
import { useRoute } from 'vue-router';
import ClientLayout from '../../layouts/ClientLayout.vue';
import WcTabs from '../../components/ui/WcTabs.vue';

const route = useRoute();

const TABS = [
  { id: 'perfil',         label: 'Perfil',          iconPath: 'M15.75 6a3.75 3.75 0 1 1-7.5 0...' },
  { id: 'notificaciones', label: 'Notificaciones',   iconPath: 'M14.857 17.082a23.848...' },
  { id: 'apariencia',     label: 'Apariencia',       iconPath: 'M4.098 19.902a3.75...' },
  { id: 'seguridad',      label: 'Seguridad',        iconPath: 'M16.5 10.5V6.75...' },
];

const VALID_TABS = TABS.map(t => t.id);
const activeTab = ref('perfil');

const SettingsProfileTab = defineAsyncComponent(
  () => import('./settings/SettingsProfileTab.vue')
);
const SettingsNotificationsTab = defineAsyncComponent(
  () => import('./settings/SettingsNotificationsTab.vue')
);
const SettingsAppearanceTab = defineAsyncComponent(
  () => import('./settings/SettingsAppearanceTab.vue')
);
const SettingsSecurityTab = defineAsyncComponent(
  () => import('./settings/SettingsSecurityTab.vue')
);

onMounted(() => {
  const queryTab = route.query.tab;
  if (queryTab && VALID_TABS.includes(queryTab)) {
    activeTab.value = queryTab;
  }
});
</script>

<template>
  <ClientLayout>
    <div class="mb-6">
      <h1 class="font-display text-3xl tracking-wide text-wc-text">CONFIGURACIÓN</h1>
      <p class="mt-1 text-sm text-wc-text-secondary">Gestiona tu cuenta, preferencias y seguridad</p>
    </div>

    <WcTabs :tabs="TABS" v-model="activeTab" class="mb-6" />

    <KeepAlive>
      <component
        :is="activeTab === 'perfil'         ? SettingsProfileTab
              : activeTab === 'notificaciones' ? SettingsNotificationsTab
              : activeTab === 'apariencia'     ? SettingsAppearanceTab
              : SettingsSecurityTab"
        :key="activeTab"
      />
    </KeepAlive>
  </ClientLayout>
</template>
```

### 5.2 WcSwitch.vue (primitivo completo)

```vue
<script setup>
defineProps({
  modelValue: { type: Boolean, required: true },
  disabled:   { type: Boolean, default: false },
  id:         { type: String,  required: true },
  label:      { type: String,  default: '' },
  description:{ type: String,  default: '' },
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <div class="flex items-center justify-between gap-4 py-3.5 border-b border-wc-border last:border-0 first:pt-0">
    <div class="min-w-0">
      <p v-if="label"       class="text-sm font-semibold text-wc-text">{{ label }}</p>
      <p v-if="description" class="mt-0.5 text-[11px] text-wc-text-tertiary leading-relaxed">{{ description }}</p>
    </div>
    <button
      type="button"
      :id="id"
      role="switch"
      :aria-checked="modelValue.toString()"
      :disabled="disabled"
      @click="!disabled && emit('update:modelValue', !modelValue)"
      class="relative flex-shrink-0 w-11 h-6 rounded-full border-2 border-transparent
             focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg
             transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      :class="modelValue
        ? 'bg-wc-accent shadow-[0_0_0_4px_rgba(220,38,38,0.12)]'
        : 'bg-wc-bg-secondary border border-wc-border-strong'"
    >
      <span
        class="pointer-events-none inline-block w-5 h-5 rounded-full bg-white shadow-sm"
        :class="modelValue ? 'translate-x-5' : 'translate-x-0'"
        style="transition: transform .35s cubic-bezier(.34,1.56,.64,1)"
      />
    </button>
  </div>
</template>
```

### 5.3 useSettings.js (composable completo)

```js
import { ref, reactive, watch } from 'vue';
import { useApi }   from './useApi';
import { useToast } from './useToast';

// Singleton state — shared across all tab components
const hasFetched    = ref(false);
const loadingProfile = ref(false);
const savingProfile  = ref(false);
const savingPassword = ref(false);
const profileError   = ref(null);

const profileForm = ref({ name: '', email: '', phone: '' });
const profileErrors = ref({});

const passwordForm = ref({
  current_password: '', password: '', password_confirmation: '',
});
const passwordErrors  = ref({});
const passwordGenericError = ref(null);

const notifications = reactive({
  checkin: true, coach: true, achievements: true,
  payments: true, weekly: true,
});
const soundEnabled = ref(true);

function loadPrefsFromStorage() {
  try {
    const s = localStorage.getItem('wc_notifications');
    if (s) Object.assign(notifications, JSON.parse(s));
  } catch {}
  soundEnabled.value = localStorage.getItem('wc_sound_enabled') !== 'false';
}

watch(notifications, () => {
  localStorage.setItem('wc_notifications', JSON.stringify({ ...notifications }));
}, { deep: true });

watch(soundEnabled, val => {
  localStorage.setItem('wc_sound_enabled', val ? 'true' : 'false');
});

export function useSettings() {
  const api   = useApi();
  const toast = useToast();

  async function fetchSettings() {
    if (hasFetched.value) return;
    loadingProfile.value = true;
    profileError.value   = null;
    try {
      const { data } = await api.get('/api/v/client/settings');
      profileForm.value = { name: data.name || '', email: data.email || '', phone: data.phone || '' };
      hasFetched.value  = true;
    } catch (err) {
      profileError.value = err.response?.data?.message || 'Error al cargar configuración';
    } finally {
      loadingProfile.value = false;
    }
  }

  async function updateProfile() {
    savingProfile.value  = true;
    profileErrors.value  = {};
    try {
      await api.put('/api/v/client/settings', { ...profileForm.value });
      if (profileForm.value.name) {
        localStorage.setItem('wc_user_name', profileForm.value.name);
      }
      toast.success('Perfil actualizado correctamente');
    } catch (err) {
      if (err.response?.status === 422) {
        profileErrors.value = err.response.data.errors || {};
      } else {
        toast.apiError(err, 'No pudimos guardar los cambios.');
      }
    } finally {
      savingProfile.value = false;
    }
  }

  async function changePassword() {
    passwordErrors.value      = {};
    passwordGenericError.value = null;
    if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
      passwordErrors.value = { password_confirmation: ['Las contraseñas no coinciden.'] };
      return;
    }
    if (passwordForm.value.password.length < 10) {
      passwordErrors.value = { password: ['La contraseña debe tener al menos 10 caracteres.'] };
      return;
    }
    savingPassword.value = true;
    try {
      await api.put('/api/v/client/settings/password', { ...passwordForm.value });
      passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
      toast.success('Contraseña actualizada correctamente');
    } catch (err) {
      if (err.response?.status === 422) {
        passwordErrors.value = err.response.data.errors || {};
      } else {
        passwordGenericError.value = err.response?.data?.message || 'Error al cambiar contraseña';
      }
    } finally {
      savingPassword.value = false;
    }
  }

  return {
    hasFetched, loadingProfile, savingProfile, savingPassword,
    profileError, profileForm, profileErrors,
    passwordForm, passwordErrors, passwordGenericError,
    notifications, soundEnabled,
    fetchSettings, updateProfile, changePassword,
    loadPrefsFromStorage,
  };
}
```

### 5.4 useTheme.js (composable completo)

```js
import { ref, computed, onMounted, onUnmounted } from 'vue';

const theme     = ref('dark');  // 'dark' | 'light' | 'auto'
let mediaQuery  = null;
let mqListener  = null;

function applyTheme(t) {
  const html = document.documentElement;
  if (t === 'dark'  || (t === 'auto' && mediaQuery?.matches)) {
    html.classList.add('dark');
  } else {
    html.classList.remove('dark');
  }
  // Sync wc-theme-color meta tag (para status bar PWA)
  const meta = document.getElementById('wc-theme-color');
  if (meta) meta.setAttribute('content', html.classList.contains('dark') ? '#09090B' : '#FAFAF8');
}

export function useTheme() {
  const isDark = computed(() => theme.value === 'dark' || (theme.value === 'auto' && mediaQuery?.matches));

  function selectTheme(t) {
    theme.value = t;
    localStorage.setItem('wc_theme', t);
    applyTheme(t);
  }

  function toggleTheme() {
    selectTheme(theme.value === 'dark' ? 'light' : 'dark');
  }

  onMounted(() => {
    mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mqListener = () => { if (theme.value === 'auto') applyTheme('auto'); };
    mediaQuery.addEventListener('change', mqListener);

    // Migración: leer wc_theme primero; si no existe, leer darkMode (legacy)
    const stored = localStorage.getItem('wc_theme');
    if (stored && ['dark', 'light', 'auto'].includes(stored)) {
      theme.value = stored;
    } else {
      const legacy = localStorage.getItem('darkMode');
      theme.value  = (legacy === 'false') ? 'light' : 'dark';
    }
    applyTheme(theme.value);
  });

  onUnmounted(() => {
    if (mediaQuery && mqListener) mediaQuery.removeEventListener('change', mqListener);
  });

  return { theme, isDark, selectTheme, toggleTheme };
}
```

### 5.5 SettingsNotificationsTab.vue (referencia — tab más densa)

```vue
<script setup>
import { onMounted, ref } from 'vue';
import { useSettings } from '../../../composables/useSettings';
import WcCard   from '../../../components/ui/WcCard.vue';
import WcSwitch from '../../../components/ui/WcSwitch.vue';

const { notifications, soundEnabled, loadPrefsFromStorage } = useSettings();

const pushStatus = ref('unknown');  // 'granted' | 'denied' | 'default' | 'unknown'

async function checkPushStatus() {
  if (!('Notification' in window)) { pushStatus.value = 'denied'; return; }
  pushStatus.value = Notification.permission;
}

async function requestPush() {
  const perm = await Notification.requestPermission();
  pushStatus.value = perm;
}

onMounted(() => {
  loadPrefsFromStorage();
  checkPushStatus();
});
</script>

<template>
  <div class="max-w-2xl space-y-5 animate-wc-rise">

    <!-- Push card -->
    <WcCard title="Notificaciones Push" subtitle="Recibe alertas en tu navegador aunque no estés en la página">
      <template #icon><!-- bell SVG --></template>
      <div v-if="pushStatus === 'granted'"
           class="flex items-center justify-between gap-4 rounded-xl border border-wc-success/25 bg-wc-success/8 px-4 py-3">
        <div class="flex items-center gap-3">
          <!-- check SVG wc-success -->
          <div>
            <p class="text-sm font-semibold text-emerald-400">Push activado</p>
            <p class="text-[11px] text-wc-text-tertiary">Recibirás notificaciones en este navegador</p>
          </div>
        </div>
        <button class="rounded-full border border-wc-border-strong px-4 py-1.5 text-xs font-semibold text-wc-text-secondary hover:text-wc-text transition-colors">
          Desactivar
        </button>
      </div>
      <div v-else class="flex items-center justify-between gap-4 rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3">
        <p class="text-sm text-wc-text-secondary">Activa las notificaciones push para no perderte nada</p>
        <button @click="requestPush"
                class="rounded-full bg-wc-accent px-4 py-1.5 text-xs font-bold text-white hover:opacity-90 transition-opacity">
          Activar
        </button>
      </div>
    </WcCard>

    <!-- Preferences card -->
    <WcCard title="Preferencias de Notificación" subtitle="Elige qué notificaciones deseas recibir">
      <template #icon><!-- bell SVG --></template>
      <WcSwitch id="sw-checkin"      v-model="notifications.checkin"      label="Recordatorios de check-in"          description="Recibe avisos para no olvidar tu check-in semanal" />
      <WcSwitch id="sw-coach"        v-model="notifications.coach"        label="Mensajes del coach"                 description="Notificaciones cuando tu coach te envía feedback" />
      <WcSwitch id="sw-achievements" v-model="notifications.achievements" label="Logros y rachas"                   description="Celebra records personales y rachas de entrenamiento" />
      <WcSwitch id="sw-payments"     v-model="notifications.payments"     label="Pagos y planes"                    description="Confirmaciones de pago y asignaciones de nuevos planes" />
      <WcSwitch id="sw-weekly"       v-model="notifications.weekly"       label="Resumen semanal"                   description="Recibe un resumen de tu progreso cada semana" />
      <WcSwitch id="sw-sound"        v-model="soundEnabled"               label="Sonido al completar entrenamiento" description="Reproduce un sonido sutil cuando completas tu entrenamiento" />
      <p class="mt-4 text-[11px] text-wc-text-tertiary">Las preferencias se guardan automáticamente en este dispositivo.</p>
    </WcCard>
  </div>
</template>
```

---

## 6. Performance

- **`defineAsyncComponent`** en los 4 tabs: el bundle de cada tab solo se carga cuando se activa por primera vez
- **`<KeepAlive>`** en el orquestador: evita re-montar y re-fetchear datos al navegar entre tabs
- **Singleton state en `useSettings`**: el flag `hasFetched` previene llamadas duplicadas al API; una sola request GET `/api/v/client/settings` por sesión de página
- **Debounce futuro**: si se agrega auto-save, usar `useDebounceFn` de VueUse con 500ms
- **Bundle impact estimado**: los 4 subcomponentes + 4 primitivos UI + 2 composables ≈ +12 KB gzip (razonable)
- **Sin N+1**: `useSettings` no hace fetch de datos adicionales; todo viene del mismo endpoint existente

---

## 7. Integridad

- **Endpoints PHP intactos**: `GET /api/v/client/settings`, `PUT /api/v/client/settings`, `PUT /api/v/client/settings/password` — ninguno se modifica
- **Router intacto**: `router/index.js` línea 29 no cambia; la ruta `/client/settings` ya apunta a `ClientSettings.vue` lazy
- **localStorage retrocompatible**:
  - `wc_notifications` → misma estructura de objeto; `useSettings` lee/escribe igual
  - `wc_sound_enabled` → misma key; `useSettings` la lee
  - `darkMode` → `useTheme` lo lee como fallback antes de migrar a `wc_theme`
  - Al primer uso de `useTheme`, si existe `darkMode: 'true'` → escribe `wc_theme: 'dark'`; si `darkMode: 'false'` → `wc_theme: 'light'`
- **Impersonation banner**: renderizado por `ClientLayout.vue` (no tocado); el banner tiene `pt-10` que ya está en el layout. El `WcTabs` sticky debe usar `top-[128px]` (64px topbar + variable banner si existe) — se puede calcular con CSS var si se agrega en el futuro
- **ToastContainer y ToastContainer.vue**: no se duplica — los toasts de `useSettings` llaman a `useToast().success()` que va al sistema global existente en `ClientLayout`
- **`wc_user_name` localStorage**: `updateProfile` lo actualiza igual que antes (línea 109 del archivo original)

---

## 8. Riesgos y Mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|-------------|---------|------------|
| `useTheme` mediaQuery listener no limpia en SSR/hydration | Baja (no hay SSR) | Medio | `onUnmounted` siempre hace `removeEventListener`; guard `typeof window !== 'undefined'` |
| `Notification.requestPermission()` solo funciona en HTTPS | Baja (prod es HTTPS) | Alto | Guard: `if (window.isSecureContext && 'Notification' in window)` antes de llamar |
| `<KeepAlive>` + `defineAsyncComponent` puede causar warning de `key` si el componente cambia | Media | Bajo | Usar `:key="activeTab"` en `<component>` con KeepAlive; incluir el tab en la key del KeepAlive |
| Tab underline animado con `width/left` calculados via JS puede flashear en primer render | Media | Bajo | Usar CSS `border-bottom: 2px solid transparent` en inactive y `border-bottom: 2px solid var(--color-wc-accent)` en active — sin JS para el underline; la transición CSS es suficiente y más robusta |
| API `GET /api/v/client/settings` retorna campos extra futuros que rompen `profileForm` | Baja | Bajo | Destructuring explícito: `profileForm.value = { name: data.name || '', email: data.email || '', phone: data.phone || '' }` — campos extra ignorados |

---

## 9. Checklist Pre-Merge

- [ ] Mobile 390px: sin scroll horizontal en ningún tab
- [ ] Lighthouse Performance ≥ 90 (Chrome DevTools, mobile throttled)
- [ ] Lighthouse Accessibility ≥ 95
- [ ] 0 hex literales en todos los `.vue` del scope (búsqueda: `rg '#[0-9A-Fa-f]{3,6}' resources/js/vue/pages/Client/settings/ resources/js/vue/components/ui/Wc*.vue`)
- [ ] Dark mode: tokens se aplican correctamente al cambiar a light y volver a dark
- [ ] Light mode: no hay elementos invisible-on-light (texto blanco sobre fondo blanco)
- [ ] `theme: 'auto'` sigue el sistema operativo al cambiar preferencia del OS
- [ ] Guardar perfil: muestra toast "Perfil actualizado correctamente"
- [ ] Guardar perfil con email inválido: muestra error inline bajo el campo (validación 422)
- [ ] Cambiar contraseña con mismatch: muestra error inline sin llamar al API
- [ ] Cambiar contraseña exitoso: limpia el form y muestra toast
- [ ] 6 Switches en tab Notificaciones: todos persisten al recargar la página
- [ ] Deep link `?tab=seguridad`: abre directamente el tab Seguridad
- [ ] Deep link `?tab=invalido`: fallback silencioso al tab 'perfil'
- [ ] Impersonation banner (top): sigue visible y funcional
- [ ] Logout en "Zona de cuenta" → redirige a `/login`
- [ ] Vitest: todos los tests pasan (`npx vitest run`)
- [ ] NO hay `npm run build` ejecutado — solo git push

---

*Archivo generado: 2026-04-24 — WellCore Laravel*
*Fuente de verdad visual: C:\Users\GODSF\Downloads\WellCore-Client-Settings-ADAPTADO-NUEVO.html*
*Estado producción referencia: C:\Users\GODSF\Downloads\WellCore-Client-Settings-REAL-PRODUCCION.html*