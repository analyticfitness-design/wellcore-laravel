<div>
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">FOTOS DE PROGRESO</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Registra tu progreso visual con fotos periodicas</p>
    </div>

    {{-- Upload Section --}}
    <div class="mb-8 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Subir Fotos</h2>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['frente' => 'Frente', 'lado' => 'Lado', 'espalda' => 'Espalda'] as $key => $label)
                <div class="flex flex-col items-center rounded-lg border-2 border-dashed border-wc-border p-8 text-center transition hover:border-wc-accent/50">
                    <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                    <p class="mt-2 text-sm font-medium text-wc-text">{{ $label }}</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Proximamente</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Photo Gallery --}}
    @if(isset($photos) && count($photos) > 0)
        <div class="space-y-6">
            @foreach($photos as $date => $group)
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-wc-text-secondary">
                        {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    </h3>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($group as $photo)
                            <div class="aspect-[3/4] rounded-lg border border-wc-border bg-wc-bg-secondary flex items-center justify-center">
                                <span class="text-xs text-wc-text-tertiary">{{ ucfirst($photo->tipo) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <x-empty-state
            title="SIN FOTOS AUN"
            message="Sube tu primera foto de progreso para comenzar a documentar tu transformacion."
        />
    @endif
</div>
