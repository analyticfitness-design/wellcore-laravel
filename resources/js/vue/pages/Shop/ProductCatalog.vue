<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import PublicLayout from '../../layouts/PublicLayout.vue';

const router = useRouter();

// --- State ---
const search = ref('');
const category = ref('');
const brand = ref('');
const sort = ref('newest');
const page = ref(1);

const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const totalProducts = ref(0);
const lastPage = ref(1);
const isLoading = ref(true);
const hasError = ref(false);

// --- Price formatter ---
const formatPrice = (value) => {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(value);
};

// --- Sort options ---
const sortOptions = [
  { value: 'newest', label: 'Mas recientes' },
  { value: 'price-asc', label: 'Precio: menor a mayor' },
  { value: 'price-desc', label: 'Precio: mayor a menor' },
  { value: 'popular', label: 'Populares' },
];

// --- Fetch products ---
async function fetchProducts() {
  isLoading.value = true;
  hasError.value = false;
  try {
    const params = {};
    if (search.value) params.search = search.value;
    if (category.value) params.category = category.value;
    if (brand.value) params.brand = brand.value;
    if (sort.value) params.sort = sort.value;
    if (page.value > 1) params.page = page.value;

    const { data } = await axios.get('/api/v/shop/products', { params });
    products.value = data.data ?? [];
    totalProducts.value = data.meta?.total ?? data.total ?? products.value.length;
    lastPage.value = data.meta?.last_page ?? data.last_page ?? 1;

    // Load categories & brands from sidecar if provided
    if (data.filters?.categories) categories.value = data.filters.categories;
    if (data.filters?.brands) brands.value = data.filters.brands;
  } catch (err) {
    console.error('Error fetching products:', err);
    hasError.value = true;
  } finally {
    isLoading.value = false;
  }
}

// --- Fetch filter options once ---
async function fetchFilters() {
  try {
    const { data } = await axios.get('/api/v/shop/filters');
    if (data.categories) categories.value = data.categories;
    if (data.brands) brands.value = data.brands;
  } catch {
    // Filters may come with the products response instead
  }
}

// --- Debounced search ---
let searchTimeout = null;
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    page.value = 1;
    fetchProducts();
  }, 300);
});

// --- Instant filter watchers ---
watch([category, brand, sort], () => {
  page.value = 1;
  fetchProducts();
});

// --- Pagination ---
function goToPage(p) {
  if (p < 1 || p > lastPage.value) return;
  page.value = p;
  fetchProducts();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

const paginationPages = computed(() => {
  const pages = [];
  const total = lastPage.value;
  const current = page.value;
  const delta = 2;
  const start = Math.max(1, current - delta);
  const end = Math.min(total, current + delta);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

// --- Clear filters ---
function clearFilters() {
  search.value = '';
  category.value = '';
  brand.value = '';
  sort.value = 'newest';
  page.value = 1;
  fetchProducts();
}

const hasActiveFilters = computed(() => {
  return search.value !== '' || category.value !== '' || brand.value !== '';
});

// --- Product count label ---
const productCountLabel = computed(() => {
  const count = totalProducts.value;
  return `${count} ${count === 1 ? 'producto' : 'productos'}`;
});

// --- Navigate to product ---
function goToProduct(slug) {
  router.push({ name: 'shop-product', params: { slug } });
}

// --- Init ---
onMounted(() => {
  fetchFilters();
  fetchProducts();
});
</script>

<template>
  <PublicLayout>
    <!-- Hero Banner -->
    <section class="border-b border-wc-border bg-wc-bg-secondary">
      <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl">
          TIENDA WELLCORE
        </h1>
        <p class="mt-3 max-w-xl text-lg text-wc-text-secondary">
          Suplementos deportivos y accesorios fitness seleccionados por nuestros coaches. Envio a toda Colombia.
        </p>
      </div>
    </section>

    <!-- Filters & Products -->
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="flex flex-col gap-8 lg:flex-row">

        <!-- Sidebar Filters -->
        <aside class="w-full shrink-0 lg:w-64">
          <div class="sticky top-24 space-y-6">

            <!-- Search -->
            <div>
              <label for="search" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Buscar
              </label>
              <input
                v-model="search"
                type="text"
                id="search"
                placeholder="Nombre del producto..."
                class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
            </div>

            <!-- Category Filter -->
            <div>
              <label for="category" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Categoria
              </label>
              <select
                v-model="category"
                id="category"
                class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
                <option value="">Todas</option>
                <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Brand Filter -->
            <div>
              <label for="brand" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Marca
              </label>
              <select
                v-model="brand"
                id="brand"
                class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
                <option value="">Todas</option>
                <option v-for="b in brands" :key="b.slug" :value="b.slug">
                  {{ b.name }}
                </option>
              </select>
            </div>

            <!-- Sort -->
            <div>
              <label for="sort" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Ordenar
              </label>
              <select
                v-model="sort"
                id="sort"
                class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>

            <!-- Clear Filters -->
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm font-medium text-wc-text-secondary transition-colors hover:bg-wc-bg-secondary hover:text-wc-text"
            >
              Limpiar filtros
            </button>
          </div>
        </aside>

        <!-- Product Grid -->
        <div class="flex-1">
          <!-- Results count & sort (desktop) -->
          <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-wc-text-secondary" :aria-busy="isLoading">
              <template v-if="!isLoading">{{ productCountLabel }}</template>
              <template v-else>Cargando productos...</template>
            </p>
          </div>

          <!-- Loading Skeleton -->
          <div v-if="isLoading" class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
            <div
              v-for="n in 6"
              :key="'skeleton-' + n"
              class="animate-pulse overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary"
            >
              <div class="aspect-square bg-wc-bg-tertiary"></div>
              <div class="space-y-3 p-4">
                <div class="flex gap-2">
                  <div class="h-3 w-16 rounded bg-wc-bg-tertiary"></div>
                  <div class="h-3 w-20 rounded bg-wc-bg-tertiary"></div>
                </div>
                <div class="h-4 w-3/4 rounded bg-wc-bg-tertiary"></div>
                <div class="h-5 w-1/2 rounded bg-wc-bg-tertiary"></div>
                <div class="h-3 w-20 rounded bg-wc-bg-tertiary"></div>
              </div>
            </div>
          </div>

          <!-- Error State -->
          <div
            v-else-if="hasError"
            class="flex flex-col items-center justify-center rounded-xl border border-wc-border bg-wc-bg-secondary py-16"
          >
            <svg class="h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <p class="mt-4 text-sm text-wc-text-secondary">Error al cargar los productos.</p>
            <button @click="fetchProducts" class="mt-3 text-sm font-medium text-wc-accent hover:underline">
              Reintentar
            </button>
          </div>

          <!-- Empty State -->
          <div
            v-else-if="products.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border border-wc-border bg-wc-bg-secondary py-16"
          >
            <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <p class="mt-4 text-sm text-wc-text-secondary">No se encontraron productos con esos filtros.</p>
            <button @click="clearFilters" class="mt-3 text-sm font-medium text-wc-accent hover:underline">
              Limpiar filtros
            </button>
          </div>

          <!-- Product Cards -->
          <div v-else>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
              <a
                v-for="product in products"
                :key="product.id"
                @click.prevent="goToProduct(product.slug)"
                href="#"
                class="group cursor-pointer overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary transition-all hover:border-wc-accent/40 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-wc-accent"
              >
                <!-- Product Image -->
                <div class="aspect-square overflow-hidden bg-wc-bg-tertiary">
                  <img
                    v-if="product.image_url"
                    :src="product.image_url"
                    :alt="product.image_alt || product.name"
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    loading="lazy"
                    decoding="async"
                  >
                  <div v-else class="flex h-full w-full items-center justify-center">
                    <svg class="h-16 w-16 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                    </svg>
                  </div>
                </div>

                <!-- Product Info -->
                <div class="p-4">
                  <!-- Brand & Category -->
                  <div class="mb-2 flex items-center gap-2">
                    <span v-if="product.brand_name" class="text-xs font-medium text-wc-accent">
                      {{ product.brand_name }}
                    </span>
                    <span v-if="product.category_name" class="text-xs text-wc-text-tertiary">
                      {{ product.category_name }}
                    </span>
                  </div>

                  <!-- Name -->
                  <h3 class="line-clamp-2 text-sm font-semibold text-wc-text transition-colors group-hover:text-wc-accent">
                    {{ product.name }}
                  </h3>

                  <!-- Price -->
                  <div class="mt-3 flex items-baseline gap-2">
                    <span class="font-mono text-lg font-bold text-wc-text">
                      {{ formatPrice(product.price_cop) }}
                    </span>
                    <span v-if="product.compare_price && product.compare_price > product.price_cop" class="font-mono text-sm text-wc-text-tertiary line-through">
                      {{ formatPrice(product.compare_price) }}
                    </span>
                  </div>

                  <!-- Stock Status -->
                  <div class="mt-3">
                    <span
                      v-if="product.stock_status === 'in_stock' || (product.stock !== null && product.stock > 0)"
                      class="inline-flex items-center gap-1 text-xs text-emerald-500"
                    >
                      <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                      Disponible
                    </span>
                    <span v-else class="inline-flex items-center gap-1 text-xs text-red-400">
                      <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                      Agotado
                    </span>
                  </div>
                </div>
              </a>
            </div>

            <!-- Pagination -->
            <nav v-if="lastPage > 1" class="mt-8 flex items-center justify-center gap-1" aria-label="Paginacion de productos">
              <!-- Previous -->
              <button
                @click="goToPage(page - 1)"
                :disabled="page <= 1"
                class="rounded-lg border border-wc-border px-3 py-2 text-sm text-wc-text-secondary transition-colors hover:bg-wc-bg-secondary disabled:cursor-not-allowed disabled:opacity-40"
                aria-label="Pagina anterior"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
              </button>

              <!-- Page numbers -->
              <button
                v-for="p in paginationPages"
                :key="p"
                @click="goToPage(p)"
                :class="[
                  'rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                  p === page
                    ? 'border-wc-accent bg-wc-accent text-white'
                    : 'border-wc-border text-wc-text-secondary hover:bg-wc-bg-secondary'
                ]"
                :aria-current="p === page ? 'page' : undefined"
              >
                {{ p }}
              </button>

              <!-- Next -->
              <button
                @click="goToPage(page + 1)"
                :disabled="page >= lastPage"
                class="rounded-lg border border-wc-border px-3 py-2 text-sm text-wc-text-secondary transition-colors hover:bg-wc-bg-secondary disabled:cursor-not-allowed disabled:opacity-40"
                aria-label="Pagina siguiente"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
              </button>
            </nav>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>
