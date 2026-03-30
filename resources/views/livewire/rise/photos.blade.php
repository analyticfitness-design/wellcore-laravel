<div class="space-y-6">

    {{-- Page header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">FOTOS DE PROGRESO</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Registra tu transformacion visual semana a semana.</p>
    </div>

    {{-- Upload form --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Subir fotos nuevas</h2>

        {{-- Success message --}}
        @if($uploadSuccess)
            <div class="mt-3 flex items-center gap-2 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3">
                <svg class="h-4 w-4 shrink-0 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <p class="text-sm text-green-400">Fotos guardadas correctamente.</p>
            </div>
        @endif

        {{-- Error message --}}
        @error('upload')
            <div class="mt-3 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                {{ $message }}
            </div>
        @enderror

        {{-- Date picker --}}
        <div class="mt-4">
            <label class="block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Fecha de las fotos</label>
            <input type="date"
                   wire:model="uploadDate"
                   class="mt-1.5 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none [color-scheme:dark]">
        </div>

        {{-- Processing indicator (while Livewire uploads temp file) --}}
        <div wire:loading wire:target="photoFrente,photoPerfil,photoEspalda"
             class="mt-3 flex items-center gap-2 text-xs text-wc-text-tertiary">
            <div class="h-3 w-3 animate-spin rounded-full border-2 border-wc-accent/30 border-t-wc-accent"></div>
            <span>Procesando imagen...</span>
        </div>

        {{-- Three photo slots --}}
        <div class="mt-4 grid grid-cols-3 gap-3">

            {{-- Frente --}}
            <div>
                <p class="mb-1.5 text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Frente</p>
                <label class="group relative flex aspect-[3/4] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-wc-border bg-wc-bg-secondary transition-colors hover:border-wc-accent/50">
                    @if($photoFrente)
                        <img src="{{ $photoFrente->temporaryUrl() }}" alt="Frente" class="h-full w-full object-cover">
                        <div class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                            <span class="text-xs font-medium text-white">Cambiar</span>
                        </div>
                    @else
                        <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span class="mt-1 text-[10px] text-wc-text-tertiary">Agregar</span>
                    @endif
                    <input type="file" wire:model="photoFrente" class="hidden" accept="image/*">
                </label>
                @error('photoFrente')
                    <p class="mt-1 text-[10px] text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Perfil --}}
            <div>
                <p class="mb-1.5 text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Perfil</p>
                <label class="group relative flex aspect-[3/4] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-wc-border bg-wc-bg-secondary transition-colors hover:border-wc-accent/50">
                    @if($photoPerfil)
                        <img src="{{ $photoPerfil->temporaryUrl() }}" alt="Perfil" class="h-full w-full object-cover">
                        <div class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                            <span class="text-xs font-medium text-white">Cambiar</span>
                        </div>
                    @else
                        <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span class="mt-1 text-[10px] text-wc-text-tertiary">Agregar</span>
                    @endif
                    <input type="file" wire:model="photoPerfil" class="hidden" accept="image/*">
                </label>
                @error('photoPerfil')
                    <p class="mt-1 text-[10px] text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Espalda --}}
            <div>
                <p class="mb-1.5 text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Espalda</p>
                <label class="group relative flex aspect-[3/4] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-wc-border bg-wc-bg-secondary transition-colors hover:border-wc-accent/50">
                    @if($photoEspalda)
                        <img src="{{ $photoEspalda->temporaryUrl() }}" alt="Espalda" class="h-full w-full object-cover">
                        <div class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                            <span class="text-xs font-medium text-white">Cambiar</span>
                        </div>
                    @else
                        <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span class="mt-1 text-[10px] text-wc-text-tertiary">Agregar</span>
                    @endif
                    <input type="file" wire:model="photoEspalda" class="hidden" accept="image/*">
                </label>
                @error('photoEspalda')
                    <p class="mt-1 text-[10px] text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Save button --}}
        <button wire:click="uploadPhotos"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60 cursor-not-allowed"
                class="mt-5 w-full rounded-full bg-wc-accent px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 transition-all hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60">
            <span wire:loading.remove wire:target="uploadPhotos">Guardar fotos</span>
            <span wire:loading wire:target="uploadPhotos">Guardando...</span>
        </button>
    </div>

    {{-- Comparison section (if we have 2+ date groups) --}}
    @if($firstDate && $latestDate && $firstDate !== $latestDate)
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Comparacion de progreso</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Semana 1 vs Actual</p>

            <div class="mt-4 grid grid-cols-2 gap-4">
                @php
                    $firstPhotos  = collect($photosByDate)->firstWhere('date', $firstDate);
                    $latestPhotos = collect($photosByDate)->firstWhere('date', $latestDate);
                @endphp

                <div class="space-y-2">
                    <p class="text-center text-xs font-medium text-wc-text-secondary">{{ \Carbon\Carbon::parse($firstDate)->translatedFormat('d M Y') }}</p>
                    <div class="aspect-[3/4] overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary">
                        @if($firstPhotos && $firstPhotos['frente'])
                            <img src="/uploads/photos/{{ $firstPhotos['frente'] }}" alt="Foto inicial" class="h-full w-full object-cover" loading="lazy" decoding="async">
                        @else
                            <div class="flex h-full w-full flex-col items-center justify-center text-wc-text-tertiary">
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3v18m0-18h.257" />
                                </svg>
                                <span class="mt-1 text-xs">Sin foto</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-center text-xs font-medium text-wc-text-secondary">{{ \Carbon\Carbon::parse($latestDate)->translatedFormat('d M Y') }}</p>
                    <div class="aspect-[3/4] overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary">
                        @if($latestPhotos && $latestPhotos['frente'])
                            <img src="/uploads/photos/{{ $latestPhotos['frente'] }}" alt="Foto actual" class="h-full w-full object-cover" loading="lazy" decoding="async">
                        @else
                            <div class="flex h-full w-full flex-col items-center justify-center text-wc-text-tertiary">
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3v18m0-18h.257" />
                                </svg>
                                <span class="mt-1 text-xs">Sin foto</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Photo gallery by date --}}
    @forelse($photosByDate as $group)
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-base tracking-wide text-wc-text">{{ $group['formatted'] }}</h2>
                <span class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
                    {{ collect([$group['frente'], $group['perfil'], $group['espalda']])->filter()->count() }}/3 fotos
                </span>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3">
                @foreach(['frente' => 'Frente', 'perfil' => 'Perfil', 'espalda' => 'Espalda'] as $tipo => $label)
                    <div class="space-y-1.5">
                        <p class="text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">{{ $label }}</p>
                        <div class="aspect-[3/4] overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary">
                            @if($group[$tipo])
                                <img src="/uploads/photos/{{ $group[$tipo] }}" alt="{{ $label }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                            @else
                                <div class="flex h-full w-full flex-col items-center justify-center text-wc-text-tertiary">
                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    <span class="mt-1 text-[10px]">Pendiente</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-wc-bg-secondary">
                <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3v18m0-18h.257" />
                </svg>
            </div>
            <h3 class="mt-4 font-display text-lg text-wc-text">Sin fotos de progreso</h3>
            <p class="mt-2 text-sm text-wc-text-tertiary">Aun no has subido fotos. Las fotos te ayudan a ver tu transformacion.</p>
        </div>
    @endforelse

</div>
