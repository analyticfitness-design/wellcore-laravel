<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import PublicLayout from '../../layouts/PublicLayout.vue';

const route = useRoute();
const router = useRouter();

// --- State ---
const product = ref(null);
const relatedProducts = ref([]);
const isLoading = ref(true);
const hasError = ref(false);
const selectedImageIndex = ref(0);

// --- Price formatter ---
const formatPrice = (value) => {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(value);
};

// --- Computed ---
const discount = computed(() => {
  if (!product.value) return 0;
  const p = product.value;
  if (p.compare_price && p.compare_price > p.price_cop) {
    return Math.round((1 - p.price_cop / p.compare_price) * 100);
  }
  return 0;
});

const isInStock = computed(() => {
  if (!product.value) return false;
  const p = product.value;
  return p.stock_status === 'in_stock' || (p.stock !== null && p.stock > 0);
});

const images = computed(() => {
  if (!product.value) return [];
  const p = product.value;
  // Combine main image + gallery
  const imgs = [];
  if (p.image_url) {
    imgs.push({ url: p.image_url, alt: p.image_alt || p.name });
  }
  if (p.gallery && Array.isArray(p.gallery)) {
    p.gallery.forEach((img) => {
      imgs.push({
        url: typeof img === 'string' ? img : img.url,
        alt: typeof img === 'string' ? p.name : (img.alt || p.name),
      });
    });
  }
  return imgs;
});

const selectedImage = computed(() => {
  if (images.value.length === 0) return null;
  return images.value[selectedImageIndex.value] || images.value[0];
});

// --- Fetch product ---
async function fetchProduct() {
  isLoading.value = true;
  hasError.value = false;
  try {
    const slug = route.params.slug;
    const { data } = await axios.get(`/api/v/shop/products/${slug}`);
    product.value = data.data ?? data.product ?? data;
    relatedProducts.value = data.related ?? [];
  } catch (err) {
    console.error('Error fetching product:', err);
    hasError.value = true;
  } finally {
    isLoading.value = false;
  }
}

// --- Add to cart (placeholder for future integration) ---
function addToCart() {
  // Future: integrate with cart store / API
  alert('Producto agregado al carrito (funcionalidad en desarrollo)');
}

// --- Navigate ---
function goToProduct(slug) {
  router.push({ name: 'shop-product', params: { slug } });
}

function goBack() {
  router.push({ name: 'shop-catalog' });
}

// --- Init ---
onMounted(() => {
  fetchProduct();
});
</script>

<template>
  <PublicLayout>
    <!-- Loading Skeleton -->
    <template v-if="isLoading">
      <section class="border-b border-wc-border bg-wc-bg-secondary">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
          <div class="flex items-center gap-2">
            <div class="h-4 w-16 animate-pulse rounded bg-wc-bg-tertiary"></div>
            <div class="h-4 w-4 animate-pulse rounded bg-wc-bg-tertiary"></div>
            <div class="h-4 w-32 animate-pulse rounded bg-wc-bg-tertiary"></div>
          </div>
        </div>
      </section>
      <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" aria-busy="true">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
          <div class="animate-pulse overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
            <div class="aspect-square bg-wc-bg-tertiary"></div>
          </div>
          <div class="space-y-4">
            <div class="h-4 w-24 animate-pulse rounded bg-wc-bg-tertiary"></div>
            <div class="h-10 w-3/4 animate-pulse rounded bg-wc-bg-tertiary"></div>
            <div class="h-8 w-1/3 animate-pulse rounded bg-wc-bg-tertiary"></div>
            <div class="h-6 w-28 animate-pulse rounded bg-wc-bg-tertiary"></div>
            <div class="flex gap-4">
              <div class="h-16 w-24 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
              <div class="h-16 w-24 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
            </div>
            <div class="space-y-2 pt-4">
              <div class="h-3 w-full animate-pulse rounded bg-wc-bg-tertiary"></div>
              <div class="h-3 w-5/6 animate-pulse rounded bg-wc-bg-tertiary"></div>
              <div class="h-3 w-4/6 animate-pulse rounded bg-wc-bg-tertiary"></div>
            </div>
            <div class="h-12 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
          </div>
        </div>
      </section>
    </template>

    <!-- Error State -->
    <template v-else-if="hasError">
      <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-center rounded-xl border border-wc-border bg-wc-bg-secondary py-16">
          <svg class="h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
          <p class="mt-4 text-sm text-wc-text-secondary">No se pudo cargar el producto.</p>
          <div class="mt-4 flex gap-3">
            <button @click="fetchProduct" class="text-sm font-medium text-wc-accent hover:underline">
              Reintentar
            </button>
            <button @click="goBack" class="text-sm font-medium text-wc-text-secondary hover:underline">
              Volver a la tienda
            </button>
          </div>
        </div>
      </section>
    </template>

    <!-- Product Detail -->
    <template v-else-if="product">
      <!-- Breadcrumbs -->
      <section class="border-b border-wc-border bg-wc-bg-secondary">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
          <nav class="flex items-center gap-2 text-sm text-wc-text-secondary" aria-label="Navegacion">
            <router-link
              :to="{ name: 'shop-catalog' }"
              class="transition-colors hover:text-wc-text"
            >
              Tienda
            </router-link>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <template v-if="product.category_name">
              <router-link
                :to="{ name: 'shop-catalog', query: { category: product.category_slug } }"
                class="transition-colors hover:text-wc-text"
              >
                {{ product.category_name }}
              </router-link>
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
              </svg>
            </template>
            <span class="text-wc-text">{{ product.name }}</span>
          </nav>
        </div>
      </section>

      <!-- Main Product Section -->
      <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">

          <!-- Product Image / Gallery -->
          <div>
            <!-- Main Image -->
            <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
              <div class="aspect-square">
                <img
                  v-if="selectedImage"
                  :src="selectedImage.url"
                  :alt="selectedImage.alt"
                  class="h-full w-full object-cover"
                >
                <div v-else class="flex h-full w-full items-center justify-center bg-wc-bg-tertiary">
                  <svg class="h-24 w-24 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                  </svg>
                </div>
              </div>
            </div>

            <!-- Thumbnail Gallery -->
            <div v-if="images.length > 1" class="mt-4 flex gap-3 overflow-x-auto">
              <button
                v-for="(img, idx) in images"
                :key="idx"
                @click="selectedImageIndex = idx"
                :class="[
                  'h-20 w-20 shrink-0 overflow-hidden rounded-lg border-2 transition-all focus:outline-none focus:ring-2 focus:ring-wc-accent',
                  idx === selectedImageIndex
                    ? 'border-wc-accent'
                    : 'border-wc-border hover:border-wc-accent/40'
                ]"
                :aria-label="'Ver imagen ' + (idx + 1)"
              >
                <img :src="img.url" :alt="img.alt" class="h-full w-full object-cover">
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="flex flex-col">
            <!-- Brand -->
            <span v-if="product.brand_name" class="text-sm font-medium text-wc-accent">
              {{ product.brand_name }}
            </span>

            <!-- Name -->
            <h1 class="mt-2 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">
              {{ product.name }}
            </h1>

            <!-- Price -->
            <div class="mt-4 flex items-baseline gap-3">
              <span class="font-mono text-3xl font-bold text-wc-text">
                {{ formatPrice(product.price_cop) }}
              </span>
              <span class="text-sm text-wc-text-tertiary">COP</span>
              <template v-if="product.compare_price && product.compare_price > product.price_cop">
                <span class="font-mono text-lg text-wc-text-tertiary line-through">
                  {{ formatPrice(product.compare_price) }}
                </span>
                <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-500">
                  -{{ discount }}%
                </span>
              </template>
            </div>

            <!-- Stock Status -->
            <div class="mt-4">
              <span
                v-if="isInStock"
                class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-sm font-medium text-emerald-500"
              >
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Disponible
                <span v-if="product.stock !== null && product.stock <= 5" class="text-xs">
                  ({{ product.stock }} restantes)
                </span>
              </span>
              <span
                v-else
                class="inline-flex items-center gap-2 rounded-full bg-red-400/10 px-3 py-1 text-sm font-medium text-red-400"
              >
                <span class="h-2 w-2 rounded-full bg-red-400"></span>
                Agotado
              </span>
            </div>

            <!-- Product Details (servings, weight) -->
            <div v-if="product.servings || product.weight" class="mt-6 flex gap-4">
              <div v-if="product.servings" class="rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-center">
                <p class="font-mono text-lg font-bold text-wc-text">{{ product.servings }}</p>
                <p class="text-xs text-wc-text-tertiary">Porciones</p>
              </div>
              <div v-if="product.weight" class="rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-center">
                <p class="font-mono text-lg font-bold text-wc-text">{{ product.weight }}</p>
                <p class="text-xs text-wc-text-tertiary">Peso</p>
              </div>
            </div>

            <!-- Flavors -->
            <div v-if="product.flavors && product.flavors.length > 0" class="mt-6">
              <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Sabores disponibles
              </h3>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="flavor in product.flavors"
                  :key="flavor"
                  class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-sm text-wc-text"
                >
                  {{ flavor }}
                </span>
              </div>
            </div>

            <!-- Description -->
            <div v-if="product.description" class="mt-6">
              <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Descripcion
              </h3>
              <div
                class="prose prose-sm max-w-none text-wc-text-secondary"
                v-html="product.description_html || product.description.replace(/\n/g, '<br>')"
              ></div>
            </div>

            <!-- Add to Cart Button -->
            <div class="mt-8">
              <button
                v-if="isInStock"
                @click="addToCart"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-wc-accent px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 sm:w-auto"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
                Agregar al carrito
              </button>
              <button
                v-else
                disabled
                class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg bg-wc-bg-tertiary px-6 py-3 text-base font-semibold text-wc-text-tertiary sm:w-auto"
              >
                Producto agotado
              </button>
            </div>

            <!-- Tags -->
            <div v-if="product.tags && product.tags.length > 0" class="mt-6 flex flex-wrap gap-2">
              <span
                v-for="tag in product.tags"
                :key="tag"
                class="rounded-full bg-wc-accent/10 px-2.5 py-0.5 text-xs font-medium text-wc-accent"
              >
                {{ tag }}
              </span>
            </div>
          </div>
        </div>
      </section>

      <!-- Reviews Section -->
      <section v-if="product.reviews && product.reviews.length > 0" class="border-t border-wc-border">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
          <h2 class="font-display text-2xl tracking-wide text-wc-text">RESENAS</h2>

          <!-- Average Rating -->
          <div v-if="product.avg_rating" class="mt-4 flex items-center gap-3">
            <div class="flex items-center gap-1">
              <svg
                v-for="star in 5"
                :key="'star-' + star"
                class="h-5 w-5"
                :class="star <= Math.round(product.avg_rating) ? 'text-yellow-400' : 'text-wc-text-tertiary'"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
            <span class="text-sm text-wc-text-secondary">
              {{ product.avg_rating.toFixed(1) }} de 5 ({{ product.reviews.length }} {{ product.reviews.length === 1 ? 'resena' : 'resenas' }})
            </span>
          </div>

          <!-- Review Cards -->
          <div class="mt-8 space-y-6">
            <article
              v-for="review in product.reviews"
              :key="review.id"
              class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-bg-tertiary text-sm font-semibold text-wc-text">
                    {{ (review.author || 'A').charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-wc-text">{{ review.author || 'Anonimo' }}</p>
                    <p v-if="review.date" class="text-xs text-wc-text-tertiary">{{ review.date }}</p>
                  </div>
                </div>
                <div v-if="review.rating" class="flex items-center gap-0.5">
                  <svg
                    v-for="star in 5"
                    :key="'review-star-' + star"
                    class="h-4 w-4"
                    :class="star <= review.rating ? 'text-yellow-400' : 'text-wc-text-tertiary'"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
              </div>
              <p class="mt-3 text-sm text-wc-text-secondary">{{ review.content }}</p>
            </article>
          </div>
        </div>
      </section>

      <!-- Related Products -->
      <section v-if="relatedProducts.length > 0" class="border-t border-wc-border bg-wc-bg-secondary">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
          <h2 class="font-display text-2xl tracking-wide text-wc-text">PRODUCTOS RELACIONADOS</h2>
          <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <a
              v-for="related in relatedProducts"
              :key="related.slug"
              @click.prevent="goToProduct(related.slug)"
              href="#"
              class="group cursor-pointer overflow-hidden rounded-xl border border-wc-border bg-wc-bg transition-all hover:border-wc-accent/40 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-wc-accent"
            >
              <div class="aspect-square overflow-hidden bg-wc-bg-tertiary">
                <img
                  v-if="related.image_url"
                  :src="related.image_url"
                  :alt="related.image_alt || related.name"
                  class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                  loading="lazy"
                  decoding="async"
                >
                <div v-else class="flex h-full w-full items-center justify-center">
                  <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                  </svg>
                </div>
              </div>
              <div class="p-4">
                <h3 class="line-clamp-2 text-sm font-semibold text-wc-text transition-colors group-hover:text-wc-accent">
                  {{ related.name }}
                </h3>
                <div class="mt-2 flex items-baseline gap-2">
                  <span class="font-mono text-lg font-bold text-wc-text">
                    {{ formatPrice(related.price_cop) }}
                  </span>
                  <span class="text-xs text-wc-text-tertiary">COP</span>
                </div>
              </div>
            </a>
          </div>
        </div>
      </section>
    </template>
  </PublicLayout>
</template>
