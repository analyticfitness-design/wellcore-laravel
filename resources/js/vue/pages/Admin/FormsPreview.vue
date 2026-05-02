<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminGreeting from '@/components/admin/dashboard/AdminGreeting.vue';
import AdminFormsList from '@/components/admin/forms/AdminFormsList.vue';
import AdminFormStats from '@/components/admin/forms/AdminFormStats.vue';
import AdminFormResponsesTable from '@/components/admin/forms/AdminFormResponsesTable.vue';
import AdminFormPreviewModal from '@/components/admin/forms/AdminFormPreviewModal.vue';
import { useAdminFormsStore } from '@/stores/adminForms';

const store = useAdminFormsStore();

const filterTag = ref('all');
const searchQuery = ref('');
const TAGS = ['all', 'Inscripcion', 'Cliente', 'RISE'];

const filteredForms = computed(() => store.filteredForms(filterTag.value, searchQuery.value));

const selectedKey = computed(() =>
    store.selectedForm ? `${store.selectedForm.area}/${store.selectedForm.slug}` : null
);

const hasSelectedWithSubmissions = computed(() =>
    !!store.selectedForm?.has_submissions
);

function selectForm(form) {
    store.selectForm(form);
}

function openPreview(form) {
    store.openPreview(form);
}

onMounted(() => {
    store.fetchCatalog();
});
</script>

<template>
  <AdminLayout>
    <div class="forms-page">

      <!-- Header -->
      <AdminGreeting
        greeting="Formularios"
        :critical-alerts="0"
        :pending-tickets="0"
        :review-tickets="0"
      />

      <!-- Filter bar -->
      <div class="forms-filter-bar">
        <div class="forms-filter-pills" role="group" aria-label="Filtrar por categoría">
          <button
            v-for="tag in TAGS"
            :key="tag"
            class="filter-pill"
            :class="{ 'filter-pill--active': filterTag === tag }"
            :aria-pressed="filterTag === tag"
            @click="filterTag = tag"
          >
            {{ tag === 'all' ? 'TODOS' : tag.toUpperCase() }}
          </button>
        </div>

        <div class="forms-search-wrap">
          <svg aria-hidden="true" class="forms-search-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Buscar formulario..."
            class="forms-search"
            aria-label="Buscar formulario"
          />
        </div>
      </div>

      <!-- Error state -->
      <div v-if="store.error" class="forms-error" role="alert">
        <span>{{ store.error }}</span>
        <button class="forms-error__retry" @click="store.fetchCatalog">Reintentar</button>
      </div>

      <!-- Main content grid -->
      <div class="forms-secondary" :class="{ 'has-panel': store.selectedForm }">

        <!-- Left: cards list -->
        <div class="forms-main">
          <AdminFormsList
            :forms="filteredForms"
            :selected-slug="selectedKey"
            :loading="store.loading"
            @select="selectForm"
            @preview="openPreview"
          />
        </div>

        <!-- Right: stats + responses (visible when a form is selected) -->
        <Transition name="panel">
          <div v-if="store.selectedForm" class="forms-panel">
            <AdminFormStats :form="store.selectedForm" />
            <AdminFormResponsesTable v-if="hasSelectedWithSubmissions" />
          </div>
        </Transition>
      </div>

    </div>

    <!-- Preview modal (Teleport to body inside component) -->
    <AdminFormPreviewModal
      :form="store.previewModal"
      @close="store.closePreview()"
    />
  </AdminLayout>
</template>

<style scoped>
.forms-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding-bottom: 40px;
}

/* Filter bar */
.forms-filter-bar {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
@media (min-width: 640px) {
    .forms-filter-bar { flex-direction: row; align-items: center; }
}

.forms-filter-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.filter-pill {
    display: inline-flex;
    align-items: center;
    height: 28px;
    padding: 0 10px;
    border-radius: var(--r-pill, 999px);
    border: 1px solid var(--c-border);
    background: transparent;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-2);
    cursor: pointer;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out), background 0.15s var(--ease-out);
}
.filter-pill:hover {
    color: var(--c-text);
    border-color: rgba(255,255,255,0.12);
}
.filter-pill--active {
    background: var(--c-accent-dim);
    border-color: var(--c-accent);
    color: var(--c-text);
}

.forms-search-wrap {
    position: relative;
    flex: 1;
    max-width: 300px;
}
@media (max-width: 639px) { .forms-search-wrap { max-width: 100%; } }

.forms-search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--c-text-3);
    pointer-events: none;
}
.forms-search {
    width: 100%;
    height: 36px;
    padding: 0 12px 0 32px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.03);
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text);
    outline: none;
    transition: border-color 0.15s var(--ease-out);
}
.forms-search::placeholder { color: var(--c-text-3); }
.forms-search:focus { border-color: rgba(255,255,255,0.12); }

/* Error */
.forms-error {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    border: 1px solid var(--c-accent);
    background: var(--c-accent-dim);
    font-family: var(--font-sans);
    font-size: 12px;
    color: #F87171;
}
.forms-error__retry {
    margin-left: auto;
    padding: 4px 10px;
    border-radius: var(--r-pill, 999px);
    border: 1px solid var(--c-accent);
    background: transparent;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: #F87171;
    cursor: pointer;
}

/* Secondary layout */
.forms-secondary {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}
@media (min-width: 1024px) {
    .forms-secondary.has-panel {
        grid-template-columns: 3fr 2fr;
    }
}

.forms-main { min-width: 0; }

.forms-panel {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 0;
}

/* Panel slide transition */
.panel-enter-active {
    transition: opacity 0.2s var(--ease-out), transform 0.2s var(--ease-out);
}
.panel-leave-active {
    transition: opacity 0.15s var(--ease-out);
}
.panel-enter-from {
    opacity: 0;
    transform: translateY(8px);
}
@media (min-width: 1024px) {
    .panel-enter-from { transform: translateX(8px); }
}
.panel-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .panel-enter-active, .panel-leave-active { transition: none; }
    .filter-pill, .forms-search { transition: none; }
}
</style>
