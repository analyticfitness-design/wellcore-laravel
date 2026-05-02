<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminCampaignsKPIs from '../../components/admin/campaigns/AdminCampaignsKPIs.vue';
import AdminCampaignsTable from '../../components/admin/campaigns/AdminCampaignsTable.vue';
import AdminCampaignCard from '../../components/admin/campaigns/AdminCampaignCard.vue';
import AdminCampaignDetailDrawer from '../../components/admin/campaigns/AdminCampaignDetailDrawer.vue';
import { useAdminCampaignsStore } from '../../stores/adminCampaigns';

const store  = useAdminCampaignsStore();
const api    = useApi();

// Drawer
const drawerOpen      = ref(false);
const drawerCampaignId = ref(null);

// Import modal
const importOpen     = ref(false);
const importPlatform = ref('meta');
const importFile     = ref(null);
const importLoading  = ref(false);
const importResult   = ref(null);
const importFileRef  = ref(null);

// Filters
const searchVal = ref('');
let searchTimeout = null;

// Pagination
const currentPage = computed(() => store.filters.page);
const totalPages  = computed(() => store.totalPages);

const PLATFORM_OPTIONS = [
    { value: '',       label: 'Todas' },
    { value: 'meta',   label: 'Meta' },
    { value: 'google', label: 'Google' },
    { value: 'tiktok', label: 'TikTok' },
    { value: 'email',  label: 'Email' },
];

const STATUS_OPTIONS = [
    { value: '',       label: 'Todos' },
    { value: 'active', label: 'Activas' },
    { value: 'paused', label: 'Pausadas' },
    { value: 'ended',  label: 'Terminadas' },
];

function onSearchInput(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        store.setFilter('search', e.target.value.trim());
    }, 350);
}

// Drawer handlers
function openDetail(campaign) {
    drawerCampaignId.value = campaign.id;
    drawerOpen.value = true;
}

function closeDetail() {
    drawerOpen.value = false;
    drawerCampaignId.value = null;
}

// Actions
async function handlePause(campaign) {
    try {
        await api.post(`/api/v/admin/campaigns/${campaign.id}/pause`);
        store.fetchCampaigns({ silent: true });
    } catch {/* no-op */}
}

async function handleResume(campaign) {
    try {
        await api.post(`/api/v/admin/campaigns/${campaign.id}/resume`);
        store.fetchCampaigns({ silent: true });
    } catch {/* no-op */}
}

async function handleDuplicate(campaign) {
    try {
        await api.post(`/api/v/admin/campaigns/${campaign.id}/duplicate`);
        store.fetchCampaigns({ silent: true });
    } catch {/* no-op */}
}

// Import
function openImport() {
    importResult.value = null;
    importFile.value   = null;
    importPlatform.value = 'meta';
    importOpen.value   = true;
}

function closeImport() {
    importOpen.value = false;
}

function onFileChange(e) {
    importFile.value = e.target.files[0] ?? null;
}

async function submitImport() {
    if (!importFile.value) return;

    importLoading.value = true;
    importResult.value  = null;

    const form = new FormData();
    form.append('file', importFile.value);
    form.append('platform', importPlatform.value);

    try {
        const { data } = await api.post('/api/v/admin/campaigns/import', form, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        importResult.value = { ok: true, imported: data.imported, errors: data.errors };
        store.fetchCampaigns({ silent: true });
        if (importFileRef.value) importFileRef.value.value = '';
        importFile.value = null;
    } catch (e) {
        importResult.value = {
            ok:      false,
            message: e?.response?.data?.message ?? 'Error al importar el archivo.',
        };
    } finally {
        importLoading.value = false;
    }
}

onMounted(() => {
    store.fetchCampaigns();
    store.startPolling(300_000);
});

onBeforeUnmount(() => {
    store.stopPolling();
    clearTimeout(searchTimeout);
});
</script>

<template>
    <AdminLayout>
        <div class="campaigns-page">

            <!-- Header -->
            <AdminGreeting
                greeting="Campañas"
                :critical-alerts="0"
                :pending-tickets="0"
                :review-tickets="0"
            />

            <!-- KPIs -->
            <AdminCampaignsKPIs
                :spend-mes="store.spendMes"
                :conversiones-mes="store.conversionesMes"
                :roas-promedio="store.roasPromedio"
                :cpl-promedio="store.cplPromedio"
                :loading="store.loading"
            />

            <!-- Toolbar: filtros + acciones -->
            <div class="campaigns-toolbar">
                <div class="toolbar-filters">
                    <!-- Search -->
                    <div class="search-wrap">
                        <svg class="search-icon" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input
                            class="search-input"
                            type="search"
                            placeholder="Buscar campaña..."
                            :value="store.filters.search"
                            @input="onSearchInput"
                            aria-label="Buscar campaña"
                        />
                    </div>

                    <!-- Platform filter -->
                    <div class="filter-chips" role="group" aria-label="Filtrar por plataforma">
                        <button
                            v-for="opt in PLATFORM_OPTIONS"
                            :key="opt.value"
                            class="filter-chip"
                            :class="{ 'filter-chip--active': store.filters.platform === opt.value }"
                            @click="store.setFilter('platform', opt.value)"
                        >{{ opt.label }}</button>
                    </div>

                    <!-- Status filter -->
                    <div class="filter-chips" role="group" aria-label="Filtrar por estado">
                        <button
                            v-for="opt in STATUS_OPTIONS"
                            :key="opt.value"
                            class="filter-chip"
                            :class="{ 'filter-chip--active': store.filters.status === opt.value }"
                            @click="store.setFilter('status', opt.value)"
                        >{{ opt.label }}</button>
                    </div>
                </div>

                <div class="toolbar-actions">
                    <!-- Conectar cuenta (deshabilitado) -->
                    <div class="tooltip-wrap">
                        <button class="btn-secondary" disabled aria-disabled="true">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                            </svg>
                            CONECTAR CUENTA
                        </button>
                        <span class="tooltip-text">Próximamente — usa import manual mientras tanto</span>
                    </div>

                    <!-- Importar CSV -->
                    <button class="btn-primary" @click="openImport">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        IMPORTAR DATA
                    </button>
                </div>
            </div>

            <!-- Tabla desktop (lg+) -->
            <div class="hidden lg:block">
                <AdminCampaignsTable
                    :campaigns="store.campaigns"
                    :loading="store.loading"
                    :sort-by="store.filters.sort_by"
                    :sort-dir="store.filters.sort_dir"
                    @open-detail="openDetail"
                    @pause="handlePause"
                    @resume="handleResume"
                    @duplicate="handleDuplicate"
                    @sort="store.setSort($event)"
                />
            </div>

            <!-- Cards mobile (< lg) -->
            <div class="lg:hidden campaigns-cards">
                <template v-if="store.loading && !store.campaigns.length">
                    <div v-for="i in 4" :key="i" class="card-skeleton"></div>
                </template>
                <template v-else-if="store.campaigns.length">
                    <AdminCampaignCard
                        v-for="c in store.campaigns"
                        :key="c.id"
                        :campaign="c"
                        @open-detail="openDetail"
                        @pause="handlePause"
                        @resume="handleResume"
                        @duplicate="handleDuplicate"
                    />
                </template>
                <div v-else class="mobile-empty">
                    <div class="empty-num">—</div>
                    <p class="empty-msg">"Sin campañas activas. Importá data desde Meta Business Manager o Google Ads para empezar a trackear."</p>
                    <button class="empty-cta" @click="openImport">IMPORTAR DATA →</button>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="totalPages > 1" class="pagination">
                <button
                    class="page-btn"
                    :disabled="currentPage <= 1"
                    @click="store.setPage(currentPage - 1)"
                    aria-label="Página anterior"
                >←</button>
                <span class="page-info">{{ currentPage }} / {{ totalPages }}</span>
                <button
                    class="page-btn"
                    :disabled="currentPage >= totalPages"
                    @click="store.setPage(currentPage + 1)"
                    aria-label="Página siguiente"
                >→</button>
            </div>

        </div>

        <!-- Detail drawer -->
        <AdminCampaignDetailDrawer
            :campaign-id="drawerCampaignId"
            :open="drawerOpen"
            @close="closeDetail"
            @pause="handlePause"
            @resume="handleResume"
            @duplicate="handleDuplicate"
        />

        <!-- Import modal -->
        <Teleport to="body">
            <Transition name="backdrop-fade">
                <div
                    v-if="importOpen"
                    class="import-backdrop"
                    @click="closeImport"
                    aria-hidden="true"
                ></div>
            </Transition>
            <Transition name="modal-pop">
                <div v-if="importOpen" class="import-modal" role="dialog" aria-label="Importar data de campañas">
                    <div class="modal-header">
                        <span class="modal-title">IMPORTAR DATA</span>
                        <button class="modal-close" @click="closeImport" aria-label="Cerrar">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- Platform selector -->
                        <div class="field">
                            <label class="field-label" for="import-platform">PLATAFORMA</label>
                            <select id="import-platform" class="field-select" v-model="importPlatform">
                                <option value="meta">Meta Ads</option>
                                <option value="google">Google Ads</option>
                                <option value="tiktok">TikTok Ads</option>
                                <option value="email">Email</option>
                            </select>
                        </div>

                        <!-- File input -->
                        <div class="field">
                            <label class="field-label">ARCHIVO CSV</label>
                            <div class="file-drop">
                                <input
                                    ref="importFileRef"
                                    type="file"
                                    accept=".csv,.txt"
                                    class="file-input"
                                    @change="onFileChange"
                                    aria-label="Seleccionar archivo CSV"
                                />
                                <div class="file-drop-ui">
                                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="file-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <span v-if="importFile">{{ importFile.name }}</span>
                                    <span v-else class="file-placeholder">Seleccionar archivo CSV exportado de Meta / Google</span>
                                </div>
                            </div>
                        </div>

                        <!-- Result -->
                        <div v-if="importResult" class="import-result" :class="{ 'import-result--ok': importResult.ok, 'import-result--err': !importResult.ok }">
                            <template v-if="importResult.ok">
                                {{ importResult.imported }} campañas importadas correctamente.
                                <span v-if="importResult.errors?.length">
                                    {{ importResult.errors.length }} filas con errores: {{ importResult.errors.join(', ') }}
                                </span>
                            </template>
                            <template v-else>{{ importResult.message }}</template>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn-secondary" @click="closeImport">CANCELAR</button>
                        <button
                            class="btn-primary"
                            :disabled="!importFile || importLoading"
                            @click="submitImport"
                        >
                            <template v-if="importLoading">IMPORTANDO...</template>
                            <template v-else>IMPORTAR →</template>
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
.campaigns-page {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

/* Toolbar */
.campaigns-toolbar {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

@media (min-width: 768px) {
    .campaigns-toolbar {
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }
}

.toolbar-filters {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

@media (min-width: 640px) {
    .toolbar-filters {
        flex-direction: row;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
}

.toolbar-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

/* Search */
.search-wrap {
    position: relative;
    flex-shrink: 0;
}

.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--c-text-3);
    pointer-events: none;
}

.search-input {
    height: 36px;
    padding: 0 12px 0 32px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm, 12px);
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text);
    outline: none;
    width: 200px;
    transition: border-color 0.15s var(--ease-out);
}

.search-input::placeholder { color: var(--c-text-3); }
.search-input:focus { border-color: rgba(255,255,255,0.12); }

/* Filter chips */
.filter-chips {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.filter-chip {
    height: 28px;
    padding: 0 11px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-2);
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--c-border);
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}

.filter-chip:hover {
    background: rgba(255,255,255,0.06);
    color: var(--c-text);
}

.filter-chip--active {
    background: var(--c-accent-dim);
    border-color: var(--c-accent);
    color: var(--c-text);
}

/* Buttons */
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    padding: 0 14px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    background: var(--c-accent);
    color: #fff;
    border: none;
    border-radius: var(--r-sm, 12px);
    cursor: pointer;
    white-space: nowrap;
    min-height: var(--tap-comfort, 48px);
    transition: opacity 0.15s var(--ease-out);
}

.btn-primary:hover { opacity: 0.85; }
.btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    padding: 0 12px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-2);
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm, 12px);
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out);
}

.btn-secondary:hover { background: rgba(255,255,255,0.07); color: var(--c-text); }
.btn-secondary:disabled { opacity: 0.35; cursor: not-allowed; }

/* Tooltip */
.tooltip-wrap {
    position: relative;
}

.tooltip-text {
    position: absolute;
    bottom: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: 6px;
    padding: 6px 10px;
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--c-text-2);
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.15s var(--ease-out);
    z-index: 10;
}

.tooltip-wrap:hover .tooltip-text { opacity: 1; }

/* Cards mobile */
.campaigns-cards {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.card-skeleton {
    height: 148px;
    background: var(--c-surface-2);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md, 16px);
    animation: page-pulse 1.5s ease-in-out infinite;
}

/* Mobile empty */
.mobile-empty {
    padding: 24px 8px 18px;
    text-align: center;
}

.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}

.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    line-height: 1.55;
    margin: 0 0 16px;
    text-wrap: balance;
}

.empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    color: var(--c-text-2);
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--c-border);
    padding-bottom: 4px;
    cursor: pointer;
    text-transform: uppercase;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}

.empty-cta:hover {
    color: var(--c-text);
    border-bottom-color: var(--c-accent);
}

/* Pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 8px 0;
}

.page-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--c-border);
    border-radius: 6px;
    font-family: var(--font-display);
    font-size: 12px;
    color: var(--c-text-2);
    cursor: pointer;
    transition: background 0.15s var(--ease-out);
}

.page-btn:hover:not(:disabled) { background: rgba(255,255,255,0.08); color: var(--c-text); }
.page-btn:disabled { opacity: 0.35; cursor: not-allowed; }

.page-info {
    font-family: var(--font-display);
    font-size: 12px;
    color: var(--c-text-2);
    font-feature-settings: 'tnum' 1;
}

/* Import modal */
.import-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 49;
}

.import-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: min(480px, calc(100vw - 32px));
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md, 16px);
    z-index: 50;
    overflow: hidden;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--c-border);
}

.modal-title {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-2);
}

.modal-close {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--c-border);
    border-radius: 6px;
    color: var(--c-text-2);
    cursor: pointer;
    transition: background 0.15s var(--ease-out);
}

.modal-close:hover { background: rgba(255,255,255,0.08); }

.modal-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.modal-footer {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    padding: 14px 20px;
    border-top: 1px solid var(--c-border);
}

.field { display: flex; flex-direction: column; gap: 6px; }

.field-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

.field-select {
    height: 36px;
    padding: 0 12px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm, 12px);
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--c-text);
    outline: none;
    cursor: pointer;
}

.file-drop {
    position: relative;
    height: 80px;
    border: 1px dashed var(--c-border);
    border-radius: 10px;
    background: rgba(255,255,255,0.02);
    transition: border-color 0.15s var(--ease-out);
    overflow: hidden;
}

.file-drop:hover { border-color: rgba(255,255,255,0.12); }

.file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
    z-index: 2;
}

.file-drop-ui {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 6px;
    pointer-events: none;
    z-index: 1;
}

.file-icon { color: var(--c-text-3); }

.file-placeholder {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--c-text-3);
    text-align: center;
    padding: 0 16px;
}

.file-drop-ui span:not(.file-placeholder) {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 0.8px;
    color: var(--c-text-2);
}

.import-result {
    padding: 10px 14px;
    border-radius: var(--r-sm, 12px);
    font-family: var(--font-sans);
    font-size: 12px;
    line-height: 1.5;
}

.import-result--ok {
    background: rgba(16,185,129,0.1);
    color: #34D399;
}

.import-result--err {
    background: var(--c-accent-dim);
    color: #F87171;
}

/* Transitions */
.backdrop-fade-enter-active,
.backdrop-fade-leave-active { transition: opacity 0.2s var(--ease-out); }
.backdrop-fade-enter-from,
.backdrop-fade-leave-to { opacity: 0; }

.modal-pop-enter-active,
.modal-pop-leave-active { transition: opacity 0.2s var(--ease-out), transform 0.2s var(--ease-out); }
.modal-pop-enter-from,
.modal-pop-leave-to { opacity: 0; transform: translate(-50%, calc(-50% + 12px)); }

/* Skeleton */
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

@media (prefers-reduced-motion: reduce) {
    .card-skeleton { animation: none !important; }
    .drawer-slide-enter-active, .drawer-slide-leave-active,
    .backdrop-fade-enter-active, .backdrop-fade-leave-active,
    .modal-pop-enter-active, .modal-pop-leave-active { transition: none !important; }
}
</style>
