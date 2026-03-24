<div class="space-y-6">

    {{-- Page header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">FOTOS DE PROGRESO</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Registra tu transformacion visual semana a semana.</p>
    </div>

    {{-- Comparison section (if we have photos) --}}
    @if($firstDate && $latestDate && $firstDate !== $latestDate)
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Comparacion de progreso</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Semana 1 vs Actual</p>

            <div class="mt-4 grid grid-cols-2 gap-4">
                {{-- First photo set --}}
                <div class="space-y-2">
                    <p class="text-center text-xs font-medium text-wc-text-secondary">{{ \Carbon\Carbon::parse($firstDate)->translatedFormat('d M Y') }}</p>
                    @php
                        $firstPhotos = collect($photosByDate)->firstWhere('date', $firstDate);
                    @endphp
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

                {{-- Latest photo set --}}
                <div class="space-y-2">
                    <p class="text-center text-xs font-medium text-wc-text-secondary">{{ \Carbon\Carbon::parse($latestDate)->translatedFormat('d M Y') }}</p>
                    @php
                        $latestPhotos = collect($photosByDate)->firstWhere('date', $latestDate);
                    @endphp
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
                {{-- Frente --}}
                <div class="space-y-1.5">
                    <p class="text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Frente</p>
                    <div class="aspect-[3/4] overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary">
                        @if($group['frente'])
                            <img src="/uploads/photos/{{ $group['frente'] }}" alt="Frente" class="h-full w-full object-cover" loading="lazy" decoding="async">
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

                {{-- Perfil --}}
                <div class="space-y-1.5">
                    <p class="text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Perfil</p>
                    <div class="aspect-[3/4] overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary">
                        @if($group['perfil'])
                            <img src="/uploads/photos/{{ $group['perfil'] }}" alt="Perfil" class="h-full w-full object-cover" loading="lazy" decoding="async">
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

                {{-- Espalda --}}
                <div class="space-y-1.5">
                    <p class="text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Espalda</p>
                    <div class="aspect-[3/4] overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary">
                        @if($group['espalda'])
                            <img src="/uploads/photos/{{ $group['espalda'] }}" alt="Espalda" class="h-full w-full object-cover" loading="lazy" decoding="async">
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
            </div>
        </div>
    @empty
        {{-- Empty state --}}
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

    {{-- Upload area (visual placeholder) --}}
    <div class="rounded-xl border-2 border-dashed border-wc-border bg-wc-bg-tertiary p-8 text-center transition-colors hover:border-wc-accent/30">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent/10">
            <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
            </svg>
        </div>
        <h3 class="mt-3 font-display text-base text-wc-text">Subir fotos nuevas</h3>
        <p class="mt-1 text-sm text-wc-text-tertiary">Arrastra tus fotos aqui o haz clic para seleccionar</p>
        <p class="mt-1 text-xs text-wc-text-tertiary">Frente, perfil y espalda — JPG o PNG, max 5MB</p>
        <label class="mt-4 inline-flex cursor-pointer items-center gap-2 rounded-full bg-wc-accent px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover transition-all">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
            </svg>
            Seleccionar fotos
            <input type="file" class="hidden" accept="image/*" multiple disabled>
        </label>
        <p class="mt-2 text-[11px] text-wc-accent/70">Proximamente: subida de fotos desde el navegador</p>
    </div>
</div>
