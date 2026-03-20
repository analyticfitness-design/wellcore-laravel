<div>
    {{-- Breadcrumbs --}}
    <section class="border-b border-wc-border bg-wc-bg-secondary">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-wc-text-secondary">
                <a href="{{ route('shop.catalog') }}" class="hover:text-wc-text transition-colors">Tienda</a>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
                @if($product->category)
                    <a href="{{ route('shop.catalog', ['category' => $product->category->slug]) }}" class="hover:text-wc-text transition-colors">{{ $product->category->name }}</a>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                @endif
                <span class="text-wc-text">{{ $product->name }}</span>
            </nav>
        </div>
    </section>

    {{-- Product Detail --}}
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">

            {{-- Product Image --}}
            <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
                <div class="aspect-square">
                    @if($product->image_url)
                        <img
                            src="{{ $product->image_url }}"
                            alt="{{ $product->image_alt ?? $product->name }}"
                            class="h-full w-full object-cover"
                        >
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-wc-bg-tertiary">
                            <svg class="h-24 w-24 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Product Info --}}
            <div class="flex flex-col">
                {{-- Brand --}}
                @if($product->brand)
                    <span class="text-sm font-medium text-wc-accent">{{ $product->brand->name }}</span>
                @endif

                {{-- Name --}}
                <h1 class="mt-2 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">
                    {{ $product->name }}
                </h1>

                {{-- Price --}}
                <div class="mt-4 flex items-baseline gap-3">
                    <span class="font-mono text-3xl font-bold text-wc-text">
                        ${{ number_format($product->price_cop, 0, ',', '.') }}
                    </span>
                    <span class="text-sm text-wc-text-tertiary">COP</span>
                    @if($product->compare_price && $product->compare_price > $product->price_cop)
                        <span class="font-mono text-lg text-wc-text-tertiary line-through">
                            ${{ number_format($product->compare_price, 0, ',', '.') }}
                        </span>
                        @php
                            $discount = round((1 - $product->price_cop / $product->compare_price) * 100);
                        @endphp
                        <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-500">
                            -{{ $discount }}%
                        </span>
                    @endif
                </div>

                {{-- Stock Status --}}
                <div class="mt-4">
                    @if($product->stock_status === 'in_stock' || ($product->stock !== null && $product->stock > 0))
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-sm font-medium text-emerald-500">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Disponible
                            @if($product->stock !== null && $product->stock <= 5)
                                <span class="text-xs">({{ $product->stock }} restantes)</span>
                            @endif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full bg-red-400/10 px-3 py-1 text-sm font-medium text-red-400">
                            <span class="h-2 w-2 rounded-full bg-red-400"></span>
                            Agotado
                        </span>
                    @endif
                </div>

                {{-- Product details (servings, weight) --}}
                @if($product->servings || $product->weight)
                    <div class="mt-6 flex gap-4">
                        @if($product->servings)
                            <div class="rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-center">
                                <p class="font-mono text-lg font-bold text-wc-text">{{ $product->servings }}</p>
                                <p class="text-xs text-wc-text-tertiary">Porciones</p>
                            </div>
                        @endif
                        @if($product->weight)
                            <div class="rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-center">
                                <p class="font-mono text-lg font-bold text-wc-text">{{ $product->weight }}</p>
                                <p class="text-xs text-wc-text-tertiary">Peso</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Flavors --}}
                @if($product->flavors && count($product->flavors) > 0)
                    <div class="mt-6">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Sabores disponibles</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->flavors as $flavor)
                                <span class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-sm text-wc-text">
                                    {{ $flavor }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Description --}}
                @if($product->description)
                    <div class="mt-6">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Descripcion</h3>
                        <div class="prose prose-sm max-w-none text-wc-text-secondary">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Add to Cart Button --}}
                <div class="mt-8">
                    @if($product->stock_status === 'in_stock' || ($product->stock !== null && $product->stock > 0))
                        <button
                            wire:click="addToCart"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-wc-accent px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-wc-accent-hover sm:w-auto"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                            Agregar al carrito
                        </button>
                    @else
                        <button
                            disabled
                            class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg bg-wc-bg-tertiary px-6 py-3 text-base font-semibold text-wc-text-tertiary sm:w-auto"
                        >
                            Producto agotado
                        </button>
                    @endif
                </div>

                {{-- Tags --}}
                @if($product->tags && count($product->tags) > 0)
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach($product->tags as $tag)
                            <span class="rounded-full bg-wc-accent/10 px-2.5 py-0.5 text-xs font-medium text-wc-accent">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Related Products --}}
    @if(count($relatedProducts) > 0)
        <section class="border-t border-wc-border bg-wc-bg-secondary">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <h2 class="font-display text-2xl tracking-wide text-wc-text">PRODUCTOS RELACIONADOS</h2>
                <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($relatedProducts as $related)
                        <a
                            href="{{ route('shop.product', $related['slug']) }}"
                            class="group overflow-hidden rounded-xl border border-wc-border bg-wc-bg transition-all hover:border-wc-accent/40 hover:shadow-lg"
                        >
                            <div class="aspect-square overflow-hidden bg-wc-bg-tertiary">
                                @if($related['image_url'] ?? null)
                                    <img
                                        src="{{ $related['image_url'] }}"
                                        alt="{{ $related['image_alt'] ?? $related['name'] }}"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-wc-text group-hover:text-wc-accent transition-colors line-clamp-2">
                                    {{ $related['name'] }}
                                </h3>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="font-mono text-lg font-bold text-wc-text">
                                        ${{ number_format($related['price_cop'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-wc-text-tertiary">COP</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
