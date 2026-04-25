<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';

// Catálogo declarativo de formularios — debe espejar FormPreviewLoader.FORM_REGISTRY
const FORMS = [
    // Inscripción / pre-signup (asesorado antes de inscribirse)
    { area: 'public', slug: 'inscripcion',     name: 'Inscripcion',          description: 'Form principal de signup del cliente nuevo. Lo llena el asesorado.', tag: 'Inscripcion' },
    { area: 'public', slug: 'coach-apply',     name: 'Aplicar como Coach',   description: 'Form publico para que coaches potenciales apliquen al equipo.', tag: 'Inscripcion' },
    { area: 'public', slug: 'rise-enroll',     name: 'Inscripcion RISE',     description: 'Form de enrollment al programa RISE. Pre-signup.', tag: 'Inscripcion' },
    { area: 'public', slug: 'presencial',      name: 'Inscripcion Presencial', description: 'Form para reservar sesion presencial.', tag: 'Inscripcion' },

    // Cliente (post-signup, panel cliente)
    { area: 'client', slug: 'checkin',         name: 'Check-in semanal',     description: 'Wizard semanal de bienestar, entreno, nutricion y notas.', tag: 'Cliente' },
    { area: 'client', slug: 'metrics',         name: 'Metricas corporales',  description: 'Peso, % grasa, % musculo, medidas (chest, waist, hip).', tag: 'Cliente' },
    { area: 'client', slug: 'profile',         name: 'Editar perfil',        description: 'Datos personales: nombre, ciudad, fecha nacimiento, objetivo.', tag: 'Cliente' },
    { area: 'client', slug: 'settings',        name: 'Configuracion cuenta', description: 'Email, password, preferencias de notificacion.', tag: 'Cliente' },
    { area: 'client', slug: 'habits',          name: 'Habitos diarios',      description: 'Toggle diario de habitos asignados al plan.', tag: 'Cliente' },
    { area: 'client', slug: 'supplements',     name: 'Suplementos',          description: 'Registro diario de toma de suplementos asignados.', tag: 'Cliente' },
    { area: 'client', slug: 'photos',          name: 'Fotos de progreso',    description: 'Subida de fotos frente/perfil/espalda.', tag: 'Cliente' },
    { area: 'client', slug: 'video-checkin',   name: 'Video check-in',       description: 'Subida de video corto de tecnica para revision del coach.', tag: 'Cliente' },
    { area: 'client', slug: 'tickets',         name: 'Soporte / tickets',    description: 'Form para abrir un ticket de soporte tecnico o de plan.', tag: 'Cliente' },

    // RISE (post-enrollment, panel RISE)
    { area: 'rise',   slug: 'habits',          name: 'Habitos RISE',         description: 'Tracking diario de habitos del programa RISE.', tag: 'RISE' },
    { area: 'rise',   slug: 'measurements',    name: 'Mediciones RISE',      description: 'Mediciones corporales del programa RISE.', tag: 'RISE' },
    { area: 'rise',   slug: 'photos',          name: 'Fotos RISE',           description: 'Fotos de progreso del programa RISE.', tag: 'RISE' },
    { area: 'rise',   slug: 'tracking',        name: 'Tracking diario RISE', description: 'Form diario de agua, sueno, pasos, notas.', tag: 'RISE' },
    { area: 'rise',   slug: 'profile',         name: 'Perfil RISE',          description: 'Editar perfil del usuario RISE.', tag: 'RISE' },
];

const filterTag = ref('all'); // all | Inscripcion | Cliente | RISE
const searchQuery = ref('');

const tags = ['all', 'Inscripcion', 'Cliente', 'RISE'];

const filteredForms = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    return FORMS.filter(f => {
        if (filterTag.value !== 'all' && f.tag !== filterTag.value) return false;
        if (! q) return true;
        return f.name.toLowerCase().includes(q)
            || f.description.toLowerCase().includes(q)
            || f.slug.toLowerCase().includes(q);
    });
});

const tagBadgeColor = (tag) => ({
    'Inscripcion': 'bg-amber-500/15 text-amber-400 border border-amber-500/30',
    'Cliente':     'bg-sky-500/15 text-sky-400 border border-sky-500/30',
    'RISE':        'bg-violet-500/15 text-violet-400 border border-violet-500/30',
}[tag] || 'bg-wc-bg-tertiary text-wc-text-secondary');

// Selected form for preview
const selected = ref(null);

const previewUrl = computed(() => {
    if (! selected.value) return '';
    return `/admin/forms-preview/${selected.value.area}/${selected.value.slug}`;
});

const iframeKey = ref(0);
function reloadIframe() { iframeKey.value++; }

function openPreview(form) {
    selected.value = form;
    iframeKey.value++;
}

function closePreview() {
    selected.value = null;
}

function openInNewTab() {
    if (previewUrl.value) window.open(previewUrl.value, '_blank');
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">FORMULARIOS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">
          Vista previa visual de todos los formularios que llenan asesorados, clientes y usuarios RISE.
          Los datos no se envian — solo se renderiza el componente con su CSS para auditar la experiencia.
        </p>
      </div>

      <!-- Filters -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <!-- Tag filter pills -->
        <div class="flex flex-wrap gap-2">
          <button
            v-for="t in tags"
            :key="t"
            @click="filterTag = t"
            :class="[
              'rounded-full px-3 py-1 text-xs font-semibold transition-colors',
              filterTag === t
                ? 'bg-wc-accent text-white'
                : 'border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'
            ]"
          >{{ t === 'all' ? 'Todos' : t }}</button>
        </div>

        <!-- Search -->
        <div class="relative flex-1 sm:ml-auto sm:max-w-sm">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar formulario..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
          />
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="! filteredForms.length" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <p class="text-sm text-wc-text-secondary">Ningun formulario coincide con los filtros.</p>
      </div>

      <!-- Forms grid -->
      <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <button
          v-for="form in filteredForms"
          :key="`${form.area}/${form.slug}`"
          @click="openPreview(form)"
          class="group flex flex-col rounded-xl border border-wc-border bg-wc-bg-secondary p-5 text-left transition-all hover:-translate-y-0.5 hover:border-wc-accent/50 hover:shadow-lg"
        >
          <div class="mb-3 flex items-center justify-between">
            <span :class="['rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider', tagBadgeColor(form.tag)]">
              {{ form.tag }}
            </span>
            <svg class="h-4 w-4 text-wc-text-tertiary transition-transform group-hover:translate-x-0.5 group-hover:text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
          </div>

          <h3 class="font-display text-lg leading-tight tracking-wide text-wc-text">{{ form.name.toUpperCase() }}</h3>
          <p class="mt-2 text-xs leading-relaxed text-wc-text-secondary">{{ form.description }}</p>

          <div class="mt-4 flex items-center gap-2 text-[10px] text-wc-text-tertiary">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
            </svg>
            <code class="font-mono">/{{ form.area }}/{{ form.slug }}</code>
          </div>
        </button>
      </div>
    </div>

    <!-- Preview Modal — fullscreen iframe -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="selected" class="fixed inset-0 z-50 flex flex-col bg-black/90 backdrop-blur-sm">

        <!-- Toolbar -->
        <div class="flex items-center justify-between gap-3 border-b border-wc-border bg-wc-bg-secondary px-4 py-3">
          <div class="flex items-center gap-3 min-w-0">
            <span :class="['shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider', tagBadgeColor(selected.tag)]">
              {{ selected.tag }}
            </span>
            <div class="min-w-0">
              <h2 class="truncate font-display text-base tracking-wide text-wc-text">{{ selected.name.toUpperCase() }}</h2>
              <p class="truncate text-[11px] text-wc-text-tertiary">{{ selected.description }}</p>
            </div>
          </div>

          <div class="flex shrink-0 items-center gap-2">
            <button
              @click="reloadIframe"
              class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary transition-colors hover:text-wc-text"
              title="Recargar preview"
              aria-label="Recargar"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
              </svg>
            </button>

            <button
              @click="openInNewTab"
              class="flex h-9 items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 text-xs font-semibold text-wc-text-secondary transition-colors hover:text-wc-text"
              title="Abrir en pestana nueva"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
              </svg>
              Pestana nueva
            </button>

            <button
              @click="closePreview"
              class="flex h-9 w-9 items-center justify-center rounded-lg bg-wc-accent text-white transition-colors hover:bg-red-700"
              title="Cerrar"
              aria-label="Cerrar"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Iframe — sandbox prevents form submissions and top-frame nav -->
        <div class="relative flex-1 overflow-hidden bg-wc-bg">
          <iframe
            :key="iframeKey"
            :src="previewUrl"
            class="absolute inset-0 h-full w-full border-0"
            sandbox="allow-same-origin allow-scripts allow-forms"
            title="Vista previa del formulario"
          ></iframe>
        </div>
      </div>
    </Transition>
  </AdminLayout>
</template>
