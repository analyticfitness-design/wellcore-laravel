<div class="space-y-6">

    {{-- Page header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">VIDEO CHECK-IN</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Sube un video o foto de tu ejercicio para recibir feedback de tu coach.</p>
    </div>

    {{-- Success notification --}}
    @if($showSuccess)
        <div class="flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4"
             x-data="{ show: true }"
             x-init="setTimeout(() => { show = false; $wire.dismissSuccess() }, 5000)"
             x-show="show"
             x-transition>
            <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            <p class="text-sm font-medium text-emerald-400">Check-in enviado exitosamente. Tu coach lo revisara pronto.</p>
            <button wire:click="dismissSuccess" class="ml-auto text-emerald-500/60 hover:text-emerald-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Upload form --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="text-sm font-semibold text-wc-text">Nuevo Check-in</h2>
        <p class="mt-1 text-xs text-wc-text-tertiary">Sube un video o imagen de tu ejercicio para revision.</p>

        <form wire:submit="submitCheckin" class="mt-5 space-y-5">

            {{-- File drop zone --}}
            <div>
                <label class="mb-1.5 block text-xs font-medium text-wc-text-secondary">Video o Imagen</label>
                <div
                    x-data="{ dragging: false }"
                    x-on:dragover.prevent="dragging = true"
                    x-on:dragleave.prevent="dragging = false"
                    x-on:drop.prevent="dragging = false"
                    class="relative rounded-xl border-2 border-dashed bg-wc-bg-secondary p-8 text-center transition-colors cursor-pointer"
                    :class="dragging ? 'border-wc-accent/60 bg-wc-accent/5' : 'border-wc-border hover:border-wc-accent/40'"
                >
                    <input
                        type="file"
                        wire:model="mediaFile"
                        accept="video/mp4,video/quicktime,video/webm,image/jpeg,image/png"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    />

                    @if($mediaFile)
                        {{-- File preview --}}
                        <div class="space-y-2">
                            @if($mediaType === 'image')
                                <img src="{{ $mediaFile->temporaryUrl() }}" alt="Preview" class="mx-auto max-h-48 rounded-lg object-contain" loading="lazy" decoding="async" />
                            @else
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-wc-accent/10">
                                    <svg class="h-10 w-10 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                </div>
                            @endif
                            <p class="text-sm font-medium text-wc-text">{{ $mediaFile->getClientOriginalName() }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ number_format($mediaFile->getSize() / 1024 / 1024, 1) }} MB &middot; {{ strtoupper($mediaType) }}</p>
                            <p class="text-xs text-wc-accent">Click o arrastra para cambiar archivo</p>
                        </div>
                    @else
                        {{-- Empty state --}}
                        <div class="space-y-3">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-tertiary">
                                <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-wc-text">Arrastra tu archivo aqui</p>
                                <p class="mt-1 text-xs text-wc-text-tertiary">o haz click para seleccionar</p>
                            </div>
                            <p class="text-[10px] text-wc-text-tertiary">MP4, MOV, WebM (max 100MB) &middot; JPG, PNG (max 10MB)</p>
                        </div>
                    @endif
                </div>

                {{-- Upload progress --}}
                <div wire:loading wire:target="mediaFile" class="mt-3">
                    <div class="flex items-center gap-3">
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div class="h-full w-full animate-pulse rounded-full bg-wc-accent/60"></div>
                        </div>
                        <span class="text-xs text-wc-text-tertiary">Subiendo...</span>
                    </div>
                </div>

                @error('mediaFile')
                    <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Exercise name --}}
            <div>
                <label for="exerciseName" class="mb-1.5 block text-xs font-medium text-wc-text-secondary">Nombre del ejercicio</label>
                <input
                    type="text"
                    id="exerciseName"
                    wire:model="exerciseName"
                    placeholder="Ej: Sentadilla, Press banca, Peso muerto..."
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                />
                @error('exerciseName')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="mb-1.5 block text-xs font-medium text-wc-text-secondary">Notas (opcional)</label>
                <textarea
                    id="notes"
                    wire:model="notes"
                    rows="3"
                    placeholder="Describe lo que quieres que tu coach revise, dudas de forma, peso utilizado..."
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none resize-none"
                ></textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
                    wire:loading.attr="disabled"
                    wire:target="submitCheckin"
                >
                    <svg wire:loading.remove wire:target="submitCheckin" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    <svg wire:loading wire:target="submitCheckin" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Enviar Check-in
                </button>
                <span wire:loading wire:target="submitCheckin" class="text-xs text-wc-text-tertiary">Procesando...</span>
            </div>
        </form>
    </div>

    {{-- History --}}
    <div>
        <h2 class="text-sm font-semibold text-wc-text">Historial de Check-ins</h2>
        <p class="mt-1 text-xs text-wc-text-tertiary">Tus envios anteriores y respuestas del coach.</p>

        @if($checkins->count() > 0)
            <div class="mt-4 space-y-3">
                @foreach($checkins as $checkin)
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden transition-colors
                                {{ $checkin->status === 'pending' ? 'border-l-2 border-l-yellow-500' : ($checkin->status === 'coach_reviewed' ? 'border-l-2 border-l-emerald-500' : 'border-l-2 border-l-blue-500') }}">

                        {{-- Card header — clickable to expand --}}
                        <button
                            wire:click="toggleExpand({{ $checkin->id }})"
                            class="flex w-full items-center gap-4 p-4 text-left"
                        >
                            {{-- Media type icon --}}
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary">
                                @if($checkin->media_type === 'video')
                                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 0 3Z" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-sm font-medium text-wc-text">{{ $checkin->exercise_name }}</p>
                                    {{-- Media type badge --}}
                                    <span class="inline-flex shrink-0 rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-semibold text-wc-text-secondary uppercase">
                                        {{ $checkin->media_type }}
                                    </span>
                                    {{-- Status badge --}}
                                    @if($checkin->status === 'pending')
                                        <span class="inline-flex shrink-0 rounded-full bg-yellow-500/10 px-2 py-0.5 text-[10px] font-semibold text-yellow-500">
                                            Pendiente
                                        </span>
                                    @elseif($checkin->status === 'coach_reviewed')
                                        <span class="inline-flex shrink-0 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-500">
                                            Revisado
                                        </span>
                                    @else
                                        <span class="inline-flex shrink-0 rounded-full bg-blue-500/10 px-2 py-0.5 text-[10px] font-semibold text-blue-500">
                                            IA Revisado
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-xs text-wc-text-tertiary">
                                    {{ $checkin->created_at->format('d M Y, H:i') }}
                                    &middot; {{ $checkin->created_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Expand chevron --}}
                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform {{ $expandedCheckin === $checkin->id ? 'rotate-180' : '' }}"
                                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        {{-- Expanded content --}}
                        @if($expandedCheckin === $checkin->id)
                            <div class="border-t border-wc-border bg-wc-bg-secondary/30 p-4 space-y-4">
                                {{-- Media player --}}
                                <div class="overflow-hidden rounded-lg bg-black/20">
                                    @if($checkin->media_type === 'video')
                                        <video
                                            controls
                                            preload="metadata"
                                            class="mx-auto max-h-80 w-full"
                                            src="{{ asset('storage/' . $checkin->media_url) }}"
                                        >
                                            Tu navegador no soporta el elemento video.
                                        </video>
                                    @else
                                        <img
                                            src="{{ asset('storage/' . $checkin->media_url) }}"
                                            alt="{{ $checkin->exercise_name }}"
                                            class="mx-auto max-h-80 object-contain"
                                        />
                                    @endif
                                </div>

                                {{-- Notes --}}
                                @if($checkin->notes)
                                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Tus notas</p>
                                        <p class="mt-1.5 text-sm text-wc-text-secondary leading-relaxed">{{ $checkin->notes }}</p>
                                    </div>
                                @endif

                                {{-- Coach response --}}
                                @if($checkin->coach_response)
                                    <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-500">Respuesta del coach</p>
                                            @if($checkin->responded_at)
                                                <span class="text-[10px] text-wc-text-tertiary">&middot; {{ $checkin->responded_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                        <p class="mt-2 text-sm text-wc-text-secondary leading-relaxed">{{ $checkin->coach_response }}</p>
                                    </div>
                                @endif

                                {{-- AI response --}}
                                @if($checkin->ai_response)
                                    <div class="rounded-lg border border-blue-500/20 bg-blue-500/5 p-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-3.5 w-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                            </svg>
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-blue-500">Analisis IA</p>
                                        </div>
                                        <p class="mt-2 text-sm text-wc-text-secondary leading-relaxed">{{ $checkin->ai_response }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty state --}}
            <div class="mt-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-secondary">
                    <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <p class="mt-3 text-sm font-medium text-wc-text">Sin check-ins aun</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">Sube tu primer video o foto para recibir feedback.</p>
            </div>
        @endif
    </div>

</div>
