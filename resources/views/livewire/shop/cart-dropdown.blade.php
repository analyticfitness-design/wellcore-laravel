<div x-data="{ open: false }" x-on:keydown.escape.window="open = false" class="relative">
    {{-- Cart Toggle Button --}}
    <button
        x-on:click="open = !open"
        class="relative flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
        title="Carrito"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
        </svg>

        {{-- Badge --}}
        @if($cartCount > 0)
            <span class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-wc-accent text-[10px] font-bold text-white">
                {{ $cartCount > 99 ? '99+' : $cartCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown Panel --}}
    <div
        x-show="open"
        x-on:click.outside="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        x-cloak
        class="absolute right-0 top-full z-50 mt-2 w-80 overflow-hidden rounded-xl border border-wc-border bg-wc-bg shadow-xl sm:w-96"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-wc-border px-4 py-3">
            <h3 class="text-sm font-semibold text-wc-text">
                Carrito
                @if($cartCount > 0)
                    <span class="ml-1 text-wc-text-tertiary">({{ $cartCount }})</span>
                @endif
            </h3>
            @if(count($cart) > 0)
                <button
                    wire:click="clearCart"
                    class="text-xs font-medium text-wc-text-tertiary hover:text-wc-accent transition-colors"
                >
                    Vaciar
                </button>
            @endif
        </div>

        {{-- Cart Items --}}
        @if(count($cart) > 0)
            <div class="max-h-72 overflow-y-auto">
                @foreach($cart as $item)
                    <div wire:key="cart-item-{{ $item['product_id'] }}" class="flex items-start gap-3 border-b border-wc-border px-4 py-3 last:border-b-0">
                        {{-- Thumbnail --}}
                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border border-wc-border bg-wc-bg-tertiary">
                            @if($item['image'])
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-wc-text">{{ $item['name'] }}</p>
                            <p class="mt-0.5 font-mono text-sm font-semibold text-wc-text-secondary">
                                ${{ number_format($item['price'], 0, ',', '.') }}
                            </p>

                            {{-- Quantity Controls --}}
                            <div class="mt-1.5 flex items-center gap-2">
                                <button
                                    wire:click="updateQuantity({{ $item['product_id'] }}, {{ $item['quantity'] - 1 }})"
                                    class="flex h-6 w-6 items-center justify-center rounded-md border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
                                >
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                    </svg>
                                </button>
                                <span class="min-w-[1.25rem] text-center text-sm font-medium text-wc-text">{{ $item['quantity'] }}</span>
                                <button
                                    wire:click="updateQuantity({{ $item['product_id'] }}, {{ $item['quantity'] + 1 }})"
                                    class="flex h-6 w-6 items-center justify-center rounded-md border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
                                >
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Remove Button --}}
                        <button
                            wire:click="removeFromCart({{ $item['product_id'] }})"
                            class="shrink-0 text-wc-text-tertiary hover:text-wc-accent transition-colors"
                            title="Eliminar"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>

            {{-- Footer with Total --}}
            <div class="border-t border-wc-border bg-wc-bg-secondary px-4 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-wc-text-secondary">Total</span>
                    <span class="font-mono text-lg font-bold text-wc-text">
                        ${{ number_format($cartTotal, 0, ',', '.') }}
                        <span class="text-xs font-normal text-wc-text-tertiary">COP</span>
                    </span>
                </div>
                <p class="mt-2 text-center text-xs text-wc-text-tertiary">
                    Checkout proximamente
                </p>
            </div>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center px-4 py-10">
                <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
                <p class="mt-3 text-sm text-wc-text-secondary">Tu carrito esta vacio</p>
                <a href="{{ route('shop.catalog') }}" class="mt-2 text-sm font-medium text-wc-accent hover:underline">
                    Explorar productos
                </a>
            </div>
        @endif
    </div>
</div>
