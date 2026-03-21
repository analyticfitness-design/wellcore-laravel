<div class="space-y-6" x-data="{ savedAnim: false }" x-on:food-saved.window="savedAnim = true; setTimeout(() => savedAnim = false, 2500)">

    {{-- Header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">REGISTRO NUTRICIONAL</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Registra tus comidas y lleva el control de tus macros diarios.</p>
    </div>

    {{-- Daily Summary Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {{-- Calories --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Calorias</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ number_format($dailySummary['calories']) }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">kcal hoy</p>
        </div>

        {{-- Protein --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Proteina</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-400/10">
                    <svg class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $dailySummary['protein'] }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">g proteina</p>
        </div>

        {{-- Carbs --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Carbohidratos</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-yellow-400/10">
                    <svg class="h-4 w-4 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $dailySummary['carbs'] }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">g carbohidratos</p>
        </div>

        {{-- Fat --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Grasa</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-400/10">
                    <svg class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $dailySummary['fat'] }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">g grasa</p>
        </div>
    </div>

    {{-- Success animation --}}
    <div x-show="savedAnim"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-center"
         x-cloak>
        <svg class="mx-auto h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <p class="mt-1 text-sm font-semibold text-emerald-400">Comida registrada!</p>
    </div>

    {{-- Tab navigation --}}
    <div class="flex gap-2">
        <button wire:click="setTab('manual')"
                class="rounded-lg px-4 py-2.5 text-sm font-semibold transition-colors {{ $activeTab === 'manual' ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text' }}">
            Registro Manual
        </button>
        <button wire:click="setTab('ai')"
                class="rounded-lg px-4 py-2.5 text-sm font-semibold transition-colors opacity-50 cursor-not-allowed bg-wc-bg-tertiary text-wc-text-secondary"
                {{ $aiAvailable ? '' : 'disabled' }}
                title="{{ $aiAvailable ? '' : 'Proximamente disponible' }}">
            <span class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                </svg>
                Analisis por Foto
            </span>
        </button>
    </div>

    {{-- Manual Tab Content --}}
    @if($activeTab === 'manual')
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <form wire:submit="saveManual" class="space-y-4">
                {{-- Food name --}}
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary mb-1">Nombre de la comida</label>
                    <input type="text"
                           wire:model="food_name"
                           placeholder="Ej: Pechuga de pollo con arroz"
                           class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                    @error('food_name') <span class="mt-1 text-xs text-wc-accent">{{ $message }}</span> @enderror
                </div>

                {{-- Macros grid 2x2 --}}
                <div class="grid grid-cols-2 gap-3">
                    {{-- Calories --}}
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-wc-accent"></span>
                                Calorias (kcal)
                            </span>
                        </label>
                        <input type="number"
                               wire:model="calories"
                               placeholder="0"
                               min="0"
                               max="9999"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        @error('calories') <span class="mt-1 text-xs text-wc-accent">{{ $message }}</span> @enderror
                    </div>

                    {{-- Protein --}}
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                Proteina (g)
                            </span>
                        </label>
                        <input type="number"
                               wire:model="protein"
                               placeholder="0"
                               min="0"
                               max="999"
                               step="0.1"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        @error('protein') <span class="mt-1 text-xs text-wc-accent">{{ $message }}</span> @enderror
                    </div>

                    {{-- Carbs --}}
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-yellow-400"></span>
                                Carbohidratos (g)
                            </span>
                        </label>
                        <input type="number"
                               wire:model="carbs"
                               placeholder="0"
                               min="0"
                               max="999"
                               step="0.1"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        @error('carbs') <span class="mt-1 text-xs text-wc-accent">{{ $message }}</span> @enderror
                    </div>

                    {{-- Fat --}}
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-red-400"></span>
                                Grasa (g)
                            </span>
                        </label>
                        <input type="number"
                               wire:model="fat"
                               placeholder="0"
                               min="0"
                               max="999"
                               step="0.1"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        @error('fat') <span class="mt-1 text-xs text-wc-accent">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Save button --}}
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="w-full rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="saveManual">Guardar Comida</span>
                    <span wire:loading wire:target="saveManual">Guardando...</span>
                </button>
            </form>
        </div>
    @endif

    {{-- AI Tab Content --}}
    @if($activeTab === 'ai' && $aiAvailable)
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 space-y-4">
            {{-- Upload zone --}}
            <div x-data="{ dragging: false }"
                 x-on:dragover.prevent="dragging = true"
                 x-on:dragleave.prevent="dragging = false"
                 x-on:drop.prevent="dragging = false"
                 class="relative rounded-lg border-2 border-dashed transition-colors p-8 text-center"
                 :class="dragging ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border'">

                @if($photo)
                    <div class="space-y-3">
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="mx-auto max-h-48 rounded-lg object-cover" loading="lazy" decoding="async" />
                        <button wire:click="removePhoto" class="text-xs text-wc-accent hover:underline">Quitar foto</button>
                    </div>
                @else
                    <div class="space-y-2">
                        <svg class="mx-auto h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
                        <p class="text-sm text-wc-text-secondary">Arrastra una foto o haz clic para subir</p>
                        <p class="text-xs text-wc-text-tertiary">JPG, PNG, WEBP &mdash; Max 5MB</p>
                    </div>
                    <input type="file"
                           wire:model="photo"
                           accept="image/jpeg,image/png,image/webp"
                           class="absolute inset-0 cursor-pointer opacity-0" />
                @endif

                @error('photo') <p class="mt-2 text-xs text-wc-accent">{{ $message }}</p> @enderror
            </div>

            {{-- Analyze button (disabled) --}}
            <button disabled
                    class="w-full rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white opacity-50 cursor-not-allowed"
                    title="Proximamente disponible">
                <span class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                    Analizar foto
                </span>
            </button>

            <p class="text-xs text-wc-text-tertiary text-center">Sube una foto de tu comida y tu coach analizara los macronutrientes de tu plato. Disponible proximamente.</p>
        </div>
    @endif

    {{-- Photo analysis coming soon (shown when not available) --}}
    @if(!$aiAvailable)
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-wc-text">Analisis por foto</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Proximamente podras subir una foto de tu comida para que tu coach analice los macronutrientes de tu plato. Mientras tanto, registra tus comidas manualmente.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- History --}}
    <div>
        <h2 class="font-display text-xl tracking-wide text-wc-text mb-3">HISTORIAL</h2>

        @if($history->count() > 0)
            <div class="space-y-3">
                @foreach($history as $entry)
                    @php
                        $totalMacros = (float)$entry->protein + (float)$entry->carbs + (float)$entry->fat;
                        $pPct = $totalMacros > 0 ? round(((float)$entry->protein / $totalMacros) * 100) : 0;
                        $cPct = $totalMacros > 0 ? round(((float)$entry->carbs / $totalMacros) * 100) : 0;
                        $fPct = $totalMacros > 0 ? 100 - $pPct - $cPct : 0;
                    @endphp
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all hover:border-wc-accent/30">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-wc-text truncate">{{ $entry->food_name }}</p>
                                    <span class="shrink-0 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ $entry->source === 'ai' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-wc-bg-secondary text-wc-text-tertiary' }}">
                                        {{ $entry->source === 'ai' ? 'Foto' : 'Manual' }}
                                    </span>
                                </div>

                                {{-- Macro numbers --}}
                                <div class="mt-2 flex items-center gap-4 text-xs text-wc-text-secondary">
                                    <span class="flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-wc-accent"></span>
                                        {{ $entry->calories }} kcal
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-400"></span>
                                        {{ $entry->protein }}g P
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-yellow-400"></span>
                                        {{ $entry->carbs }}g C
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                        {{ $entry->fat }}g F
                                    </span>
                                </div>

                                {{-- Macro bar --}}
                                @if($totalMacros > 0)
                                    <div class="mt-2 flex h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                                        <div class="bg-blue-400" style="width: {{ $pPct }}%"></div>
                                        <div class="bg-yellow-400" style="width: {{ $cPct }}%"></div>
                                        <div class="bg-red-400" style="width: {{ $fPct }}%"></div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <p class="text-[10px] text-wc-text-tertiary">{{ $entry->created_at->diffForHumans() }}</p>
                                <button wire:click="deleteEntry({{ $entry->id }})"
                                        wire:confirm="Eliminar esta entrada?"
                                        class="text-wc-text-tertiary hover:text-wc-accent transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                </svg>
                <p class="mt-3 text-sm text-wc-text-secondary">No tienes comidas registradas</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">Usa el formulario de arriba para registrar tu primera comida.</p>
            </div>
        @endif
    </div>
</div>
