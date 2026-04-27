<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const CURRENCIES = ['COP', 'USD', 'MXN', 'ARS', 'CLP'];

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

const offers = computed(() => Array.isArray(props.modelValue.active_offers) ? props.modelValue.active_offers : []);
const posts = computed(() => Array.isArray(props.modelValue.top_working_posts) ? props.modelValue.top_working_posts : []);

function addOffer() {
    if (offers.value.length >= 3) return;
    update('active_offers', [...offers.value, { name: '', price: 0, currency: 'COP', promo: null }]);
}

function removeOffer(idx) {
    const next = [...offers.value];
    next.splice(idx, 1);
    update('active_offers', next);
}

function updateOfferField(idx, field, value) {
    const next = offers.value.map((o, i) => (i === idx ? { ...o, [field]: value } : o));
    update('active_offers', next);
}

function addPost() {
    if (posts.value.length >= 3) return;
    update('top_working_posts', [...posts.value, { url: '', why_worked: '' }]);
}

function removePost(idx) {
    const next = [...posts.value];
    next.splice(idx, 1);
    update('top_working_posts', next);
}

function updatePostField(idx, field, value) {
    const next = posts.value.map((p, i) => (i === idx ? { ...p, [field]: value } : p));
    update('top_working_posts', next);
}
</script>

<template>
  <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-8 space-y-10">
    <header>
      <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-accent">06 / OFERTAS Y CONTENIDO TOP</p>
      <h2 class="mt-2 font-display text-3xl uppercase tracking-tight text-wc-text">Que vendes y que funciona</h2>
      <p class="mt-2 font-editorial italic text-base text-wc-text-secondary">Que vendes y que te ha funcionado.</p>
    </header>

    <div>
      <div class="flex items-baseline justify-between">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          Ofertas activas (1-3)
        </label>
        <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          {{ offers.length }}/3
        </span>
      </div>

      <div class="mt-3 space-y-4">
        <div
          v-for="(offer, idx) in offers"
          :key="idx"
          class="rounded-xl border border-wc-border bg-wc-bg p-4 space-y-3"
        >
          <div class="flex justify-between">
            <p class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
              Oferta {{ idx + 1 }}
            </p>
            <button
              type="button"
              class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary hover:text-wc-accent"
              @click="removeOffer(idx)"
            >
              Eliminar
            </button>
          </div>

          <input
            type="text"
            :value="offer.name"
            @input="updateOfferField(idx, 'name', $event.target.value)"
            maxlength="80"
            placeholder="Nombre de la oferta"
            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
          />

          <div class="grid grid-cols-3 gap-3">
            <input
              type="number"
              min="0"
              step="any"
              :value="offer.price"
              @input="updateOfferField(idx, 'price', Number($event.target.value) || 0)"
              placeholder="Precio"
              class="col-span-2 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
            />
            <select
              :value="offer.currency"
              @change="updateOfferField(idx, 'currency', $event.target.value)"
              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
            >
              <option v-for="c in CURRENCIES" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>

          <input
            type="text"
            :value="offer.promo ?? ''"
            @input="updateOfferField(idx, 'promo', $event.target.value || null)"
            maxlength="200"
            placeholder="Promo activa (opcional)"
            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
          />
        </div>
      </div>

      <button
        v-if="offers.length < 3"
        type="button"
        class="mt-3 w-full rounded-lg border border-dashed border-wc-border bg-wc-bg px-4 py-3 font-mono text-xs uppercase tracking-[0.15em] text-wc-text-secondary hover:border-wc-accent hover:text-wc-text"
        @click="addOffer"
      >
        + Agregar oferta
      </button>
    </div>

    <div>
      <div class="flex items-baseline justify-between">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          Posts que mas te han funcionado (opcional, max 3)
        </label>
        <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          {{ posts.length }}/3
        </span>
      </div>

      <div class="mt-3 space-y-4">
        <div
          v-for="(post, idx) in posts"
          :key="idx"
          class="rounded-xl border border-wc-border bg-wc-bg p-4 space-y-3"
        >
          <div class="flex justify-between">
            <p class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
              Post {{ idx + 1 }}
            </p>
            <button
              type="button"
              class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary hover:text-wc-accent"
              @click="removePost(idx)"
            >
              Eliminar
            </button>
          </div>

          <input
            type="url"
            :value="post.url"
            @input="updatePostField(idx, 'url', $event.target.value)"
            placeholder="URL del post"
            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
          />

          <textarea
            :value="post.why_worked"
            @input="updatePostField(idx, 'why_worked', $event.target.value)"
            rows="3"
            maxlength="300"
            placeholder="Por que crees que funciono?"
            class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
          ></textarea>
        </div>
      </div>

      <button
        v-if="posts.length < 3"
        type="button"
        class="mt-3 w-full rounded-lg border border-dashed border-wc-border bg-wc-bg px-4 py-3 font-mono text-xs uppercase tracking-[0.15em] text-wc-text-secondary hover:border-wc-accent hover:text-wc-text"
        @click="addPost"
      >
        + Agregar post
      </button>
    </div>
  </section>
</template>
