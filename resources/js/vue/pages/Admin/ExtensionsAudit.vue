<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();
const rows = ref([]);
const loading = ref(false);
const error = ref('');
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 25 });

const filters = ref({
  actor_admin_id: '',
  date_from: '',
  date_to: '',
  page: 1,
});

async function load() {
  loading.value = true;
  error.value = '';
  try {
    const params = { ...filters.value, per_page: meta.value.per_page };
    Object.keys(params).forEach((k) => {
      if (params[k] === '' || params[k] === null) delete params[k];
    });
    const resp = await api.get('/api/v/admin/extensions', { params });
    rows.value = resp.data.data || [];
    meta.value = resp.data.meta || meta.value;
  } catch (e) {
    if (e?.response?.status === 403) {
      error.value = 'Solo superadmin/jefe pueden ver esta auditoría.';
    } else {
      error.value = e?.response?.data?.message || 'No se pudo cargar el historial.';
    }
  } finally {
    loading.value = false;
  }
}

function applyFilters() {
  filters.value.page = 1;
  load();
}

function resetFilters() {
  filters.value = { actor_admin_id: '', date_from: '', date_to: '', page: 1 };
  load();
}

function nextPage() {
  if (meta.value.current_page < meta.value.last_page) {
    filters.value.page = meta.value.current_page + 1;
    load();
  }
}
function prevPage() {
  if (meta.value.current_page > 1) {
    filters.value.page = meta.value.current_page - 1;
    load();
  }
}

function roleClass(role) {
  if (role === 'coach') return 'pill pill--warn';
  if (role === 'superadmin') return 'pill pill--success';
  return 'pill pill--neutral';
}

onMounted(load);
</script>

<template>
  <AdminLayout>
    <div class="p-4 md:p-6 space-y-6">
      <header class="space-y-1">
        <h1 class="text-2xl md:text-3xl font-display text-wc-text">Extensiones de Membresía</h1>
        <p class="text-sm text-wc-text-secondary">
          Historial completo de extensiones manuales hechas desde el panel (admin/coach). Solo superadmin/jefe.
        </p>
      </header>

      <section class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
          <input
            v-model="filters.actor_admin_id"
            type="number"
            placeholder="actor_admin_id"
            class="px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-sm text-wc-text"
          />
          <input
            v-model="filters.date_from"
            type="date"
            class="px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-sm text-wc-text"
          />
          <input
            v-model="filters.date_to"
            type="date"
            class="px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-sm text-wc-text"
          />
          <div class="flex gap-2">
            <button
              type="button"
              @click="applyFilters"
              class="flex-1 px-3 py-2 rounded-lg bg-wc-accent text-white text-sm font-display tracking-wider uppercase"
            >
              Filtrar
            </button>
            <button
              type="button"
              @click="resetFilters"
              class="px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-sm text-wc-text-secondary"
            >
              Reset
            </button>
          </div>
        </div>
      </section>

      <section v-if="error" class="rounded-xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-300">
        {{ error }}
      </section>

      <section v-if="loading" class="text-sm text-wc-text-secondary py-8 text-center">
        Cargando…
      </section>

      <section v-else-if="!rows.length" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-8 text-center">
        <p class="text-wc-text-secondary text-sm italic">"Aún no se ha registrado ninguna extensión manual."</p>
      </section>

      <section v-else class="rounded-xl border border-wc-border bg-wc-bg-secondary overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-xs uppercase tracking-wider text-wc-text-secondary border-b border-wc-border">
            <tr>
              <th class="text-left p-3">Fecha</th>
              <th class="text-left p-3">Operador</th>
              <th class="text-left p-3">Cliente</th>
              <th class="text-left p-3">Fecha previa</th>
              <th class="text-left p-3">Nueva fecha</th>
              <th class="text-left p-3">Notas</th>
              <th class="text-left p-3">Email</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in rows"
              :key="row.id"
              class="border-b border-wc-border/40 hover:bg-white/[0.02]"
            >
              <td class="p-3 text-wc-text-secondary whitespace-nowrap">{{ row.created_at }}</td>
              <td class="p-3">
                <div class="flex flex-col gap-1">
                  <span class="text-wc-text">{{ row.actor.name }}</span>
                  <span :class="roleClass(row.actor_role_snapshot)">
                    {{ (row.actor_role_snapshot || '—').toUpperCase() }}
                  </span>
                </div>
              </td>
              <td class="p-3">
                <div class="flex flex-col gap-0.5">
                  <span class="text-wc-text">{{ row.client.name }}</span>
                  <span class="text-xs text-wc-text-secondary">#{{ row.client.id }} · {{ row.client.email }}</span>
                </div>
              </td>
              <td class="p-3 text-wc-text-secondary">{{ row.previous_expires_at || '—' }}</td>
              <td class="p-3 text-wc-text font-medium">{{ row.new_expires_at }}</td>
              <td class="p-3 text-wc-text-secondary italic max-w-xs truncate" :title="row.notes || ''">
                {{ row.notes || '—' }}
              </td>
              <td class="p-3 text-xs text-wc-text-secondary">
                <span v-if="row.notification_sent_at" title="Email enviado al superadmin">✓</span>
                <span v-else class="opacity-40">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <footer v-if="rows.length" class="flex items-center justify-between text-sm text-wc-text-secondary">
        <span>{{ meta.total }} extensiones · página {{ meta.current_page }} de {{ meta.last_page }}</span>
        <div class="flex gap-2">
          <button
            type="button"
            @click="prevPage"
            :disabled="meta.current_page <= 1"
            class="px-3 py-1.5 rounded-lg bg-wc-bg border border-wc-border text-xs disabled:opacity-40"
          >
            ← Anterior
          </button>
          <button
            type="button"
            @click="nextPage"
            :disabled="meta.current_page >= meta.last_page"
            class="px-3 py-1.5 rounded-lg bg-wc-bg border border-wc-border text-xs disabled:opacity-40"
          >
            Siguiente →
          </button>
        </div>
      </footer>
    </div>
  </AdminLayout>
</template>

<style scoped>
.pill {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 999px;
    line-height: 1.4;
    align-self: flex-start;
}
.pill--success { background: rgba(16,185,129,0.1); color: #34D399; }
.pill--neutral { background: rgba(255, 255, 255, 0.04); color: var(--c-text-3); }
.pill--warn { background: rgba(251, 191, 36, 0.12); color: #FBBF24; }
</style>
