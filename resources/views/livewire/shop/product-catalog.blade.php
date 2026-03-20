<div>
    {{-- Hero Banner --}}
    <section class="border-b border-wc-border bg-wc-bg-secondary">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl">TIENDA WELLCORE</h1>
            <p class="mt-3 max-w-xl text-lg text-wc-text-secondary">
                Suplementos deportivos y accesorios fitness seleccionados por nuestros coaches. Envio a toda Colombia.
            </p>
        </div>
    </section>

    {{-- Filters & Products --}}
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-8 lg:flex-row">

            {{-- Sidebar Filters --}}
            <aside class="w-full shrink-0 lg:w-64">
                <div class="sticky top-24 space-y-6">

                    {{-- Search --}}
                    <div>
                        <label for="search" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Buscar</label>
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            id="search"
                            placeholder="Nombre del producto..."
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                        >
                    </div>

                    {{-- Category Filter --}}
                    <div>
                        <label for="category" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Categoria</label>
                        <select
                            wire:model.live="category"
                            id="category"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                        >
                            <option value="">Todas</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Brand Filter --}}
                    <div>
                        <label for="brand" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Marca</label>
                        <select
                            wire:model.live="brand"
                            id="brand"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                        >
                            <option value="">Todas</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->slug }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Clear Filters --}}
                    @if($search !== '' || $category !== '' || $brand !== '')
                        <button
                            wire:click="clearFilters"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text transition-colors"
                        >
                            Limpiar filtros
                        </button>
                    @endif
                </div>
            </aside>

            {{-- Product Grid --}}
            <div class="flex-1">
                {{-- Results count --}}
                <div class="mb-6 flex items-center justify-between">
                    <p class="text-sm text-wc-text-secondary">
                        {{ $products->total() }} {{ $products->total() === 1 ? 'producto' : 'productos' }}
                    </p>
                </div>

                @if($products->isEmpty())
                    <div class="flex flex-col items-center justify-center rounded-xl border border-wc-border bg-wc-bg-secondary py-16">
                        <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <p class="mt-4 text-sm text-wc-text-secondary">No se encontraron productos con esos filtros.</p>
                        <button wire:click="clearFilters" class="mt-3 text-sm font-medium text-wc-accent hover:underline">
                            Limpiar filtros
                        </button>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($products as $product)
                            <a
                                href="{{ route('shop.product', $product->slug) }}"
                                wire:key="product-{{ $product->id }}"
                                class="group overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary transition-all hover:border-wc-accent/40 hover:shadow-lg"
                            >
                                {{-- Product Image --}}
                                <div class="aspect-square overflow-hidden bg-wc-bg-tertiary">
                                    @if($product->image_url)
                                        <img
                                            src="{{ $product->image_url }}"
                                            alt="{{ $product->image_alt ?? $product->name }}"
                                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <svg class="h-16 w-16 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Product Info --}}
                                <div class="p-4">
                                    {{-- Brand & Category --}}
                                    <div class="mb-2 flex items-center gap-2">
                                        @if($product->brand)
                                            <span class="text-xs font-medium text-wc-accent">{{ $product->brand->name }}</span>
                                        @endif
                                        @if($product->category)
                                            <span class="text-xs text-wc-text-tertiary">{{ $product->category->name }}</span>
                                        @endif
                                    </div>

                                    {{-- Name --}}
                                    <h3 class="text-sm font-semibold text-wc-text group-hover:text-wc-accent transition-colors line-clamp-2">
                                        {{ $product->name }}
                                    </h3>

                                    {{-- Price --}}
                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="font-mono text-lg font-bold text-wc-text">
                                            ${{ number_format($product->price_cop, 0, ',', '.') }}
                                        </span>
                                        <span class="text-xs text-wc-text-tertiary">COP</span>
                                        @if($product->compare_price && $product->compare_price > $product->price_cop)
                                            <span class="font-mono text-sm text-wc-text-tertiary line-through">
                                                ${{ number_format($product->compare_price, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Stock Status --}}
                                    <div class="mt-3">
                                        @if($product->stock_status === 'in_stock' || ($product->stock !== null && $product->stock > 0))
                                            <span class="inline-flex items-center gap-1 text-xs text-emerald-500">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Disponible
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs text-red-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                                Agotado
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
