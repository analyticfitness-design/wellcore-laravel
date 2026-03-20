<div class="space-y-6">

    {{-- Page header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">COACH FEEDBACK</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Califica a tu coach y comparte tu experiencia</p>
    </div>

    @if(! $coachId)
        {{-- No coach assigned --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-10 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-wc-accent/10">
                <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <h2 class="mb-2 font-display text-xl tracking-wide text-wc-text">Sin coach asignado</h2>
            <p class="mb-6 text-sm text-wc-text-secondary">No tienes un coach asignado todavía. Escríbenos para que te conectemos con el coach perfecto para tu objetivo.</p>
            <a href="{{ route('client.chat') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                Ir al Chat
            </a>
        </div>

    @else
        {{-- Coach info card --}}
        @if($coach)
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-wc-accent/20">
                    @if($coach->photo_url)
                        <img src="{{ $coach->photo_url }}" alt="{{ $coach->name }}"
                             class="h-14 w-14 rounded-full object-cover">
                    @else
                        <span class="font-display text-2xl text-wc-accent">{{ substr($coach->name, 0, 1) }}</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Tu Coach</p>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">{{ $coach->name }}</h2>
                    @if($coach->city)
                        <p class="text-sm text-wc-text-secondary">{{ $coach->city }}</p>
                    @endif
                </div>
                <div class="ml-auto text-right">
                    @php
                        $avg = $ratings->avg('rating');
                        $count = $ratings->count();
                    @endphp
                    @if($count > 0)
                        <p class="font-display text-3xl tracking-wide text-yellow-400">{{ number_format($avg, 1) }}</p>
                        <p class="text-xs text-wc-text-secondary">{{ $count }} {{ $count === 1 ? 'valoración' : 'valoraciones' }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Success flash --}}
        @if($showSuccess)
        <div class="flex items-center justify-between rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3">
            <div class="flex items-center gap-2 text-sm text-green-400">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Valoración enviada. ¡Gracias por tu feedback!
            </div>
            <button wire:click="dismissSuccess" class="text-green-400 hover:text-green-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endif

        {{-- Rating form --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="mb-4 font-display text-lg tracking-wide text-wc-text">NUEVA VALORACIÓN</h2>

            <div class="space-y-5">
                {{-- Star selector --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-wc-text">Calificación <span class="text-wc-accent">*</span></label>
                    <div class="flex gap-1" x-data="{ hovered: 0 }">
                        @for($i = 1; $i <= 5; $i++)
                        <button
                            wire:click="$set('rating', {{ $i }})"
                            x-on:mouseenter="hovered = {{ $i }}"
                            x-on:mouseleave="hovered = 0"
                            type="button"
                            class="transition-transform hover:scale-110 focus:outline-none"
                            title="{{ $i }} {{ $i === 1 ? 'estrella' : 'estrellas' }}"
                        >
                            <svg class="h-9 w-9 transition-colors"
                                 :class="(hovered >= {{ $i }} || $wire.rating >= {{ $i }}) ? 'text-yellow-400 fill-yellow-400' : 'text-wc-border fill-transparent'"
                                 viewBox="0 0 24 24"
                                 stroke-width="1.5"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                            </svg>
                        </button>
                        @endfor

                        {{-- Rating label --}}
                        <span class="ml-3 self-center text-sm text-wc-text-secondary" x-show="$wire.rating > 0">
                            <span x-show="$wire.rating === 1">Malo</span>
                            <span x-show="$wire.rating === 2">Regular</span>
                            <span x-show="$wire.rating === 3">Bueno</span>
                            <span x-show="$wire.rating === 4">Muy bueno</span>
                            <span x-show="$wire.rating === 5">Excelente</span>
                        </span>
                    </div>
                    @error('rating')
                        <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Comment --}}
                <div>
                    <label for="comment" class="mb-2 block text-sm font-medium text-wc-text">Comentario <span class="text-wc-text-secondary">(opcional)</span></label>
                    <textarea
                        id="comment"
                        wire:model="comment"
                        rows="3"
                        placeholder="Comparte tu experiencia con tu coach..."
                        class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-secondary/50 focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"
                    ></textarea>
                    @error('comment')
                        <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-3">
                    <button
                        wire:click="submitRating"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="submitRating">Enviar Valoración</span>
                        <span wire:loading wire:target="submitRating">Enviando...</span>
                    </button>
                    @if($rating > 0)
                    <button
                        wire:click="$set('rating', 0)"
                        type="button"
                        class="text-sm text-wc-text-secondary hover:text-wc-text transition-colors"
                    >
                        Limpiar
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Rating history --}}
        @if($ratings->count() > 0)
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="mb-4 font-display text-lg tracking-wide text-wc-text">HISTORIAL DE VALORACIONES</h2>
            <div class="space-y-3">
                @foreach($ratings as $entry)
                <div class="rounded-lg border border-wc-border bg-wc-bg p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex gap-0.5">
                            @for($s = 1; $s <= 5; $s++)
                            <svg class="h-4 w-4 {{ $s <= $entry->rating ? 'text-yellow-400 fill-yellow-400' : 'text-wc-border fill-transparent' }}"
                                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                            </svg>
                            @endfor
                        </div>
                        <span class="text-xs text-wc-text-secondary">{{ $entry->created_at->format('d M Y') }}</span>
                    </div>
                    @if($entry->comment)
                    <p class="mt-2 text-sm text-wc-text-secondary leading-relaxed">{{ $entry->comment }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="rounded-xl border border-dashed border-wc-border p-6 text-center">
            <p class="text-sm text-wc-text-secondary">Aún no has enviado ninguna valoración. ¡Sé el primero en calificar a tu coach!</p>
        </div>
        @endif

    @endif

</div>
