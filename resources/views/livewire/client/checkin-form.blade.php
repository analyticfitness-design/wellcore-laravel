<div class="space-y-6">
    {{-- Title --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">CHECK-IN SEMANAL</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">
            Semana {{ now()->isoFormat('W') }} &middot; {{ now()->locale('es')->isoFormat('D [de] MMMM, YYYY') }}
        </p>
    </div>

    {{-- Success Message --}}
    @if ($showSuccess)
        <div class="flex items-center justify-between rounded-[--radius-card] border border-emerald-500/30 bg-emerald-500/10 p-4">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-sm font-medium text-emerald-400">Check-in enviado correctamente.</span>
            </div>
            <button wire:click="dismissSuccess" class="text-wc-text-tertiary hover:text-wc-text">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Form --}}
    <form wire:submit="submit" class="space-y-6 rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6">

        {{-- Bienestar (1-5) --}}
        <div>
            <label class="mb-3 block text-sm font-medium text-wc-text">Bienestar general</label>
            <div class="flex flex-wrap gap-2">
                @php
                    $bienestarLabels = [1 => 'Muy mal', 2 => 'Mal', 3 => 'Normal', 4 => 'Bien', 5 => 'Muy bien'];
                @endphp
                @foreach ($bienestarLabels as $value => $label)
                    <button
                        type="button"
                        wire:click="setBienestar({{ $value }})"
                        class="flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition-all
                            {{ $bienestar === $value
                                ? 'border-wc-accent bg-wc-accent text-white'
                                : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:border-wc-text-tertiary' }}"
                    >
                        <span class="font-data">{{ $value }}</span>
                        <span>{{ $label }}</span>
                    </button>
                @endforeach
            </div>
            @error('bienestar')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Dias Entrenados --}}
        <div>
            <label for="diasEntrenados" class="mb-2 block text-sm font-medium text-wc-text">Dias entrenados esta semana</label>
            <div class="flex items-center gap-3">
                <input
                    type="number"
                    id="diasEntrenados"
                    wire:model="diasEntrenados"
                    min="0"
                    max="7"
                    class="w-24 rounded-[--radius-button] border border-wc-border bg-wc-bg-secondary px-4 py-2.5 font-data text-lg text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
                <span class="text-sm text-wc-text-tertiary">de 7 dias</span>
            </div>
            @error('diasEntrenados')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nutricion --}}
        <div>
            <label for="nutricion" class="mb-2 block text-sm font-medium text-wc-text">Nutrición</label>
            <select
                id="nutricion"
                wire:model="nutricion"
                class="w-full rounded-[--radius-button] border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
            >
                <option value="Si">Sí, la seguí bien</option>
                <option value="Parcial">Parcialmente</option>
                <option value="No">No la seguí</option>
            </select>
            @error('nutricion')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- RPE Slider --}}
        <div>
            <label for="rpe" class="mb-2 block text-sm font-medium text-wc-text">
                RPE promedio
                <span class="ml-2 font-data text-lg font-semibold text-wc-accent">{{ $rpe }}</span>
            </label>
            <input
                type="range"
                id="rpe"
                wire:model.live="rpe"
                min="1"
                max="10"
                class="w-full accent-wc-accent"
            >
            <div class="mt-1 flex justify-between text-xs text-wc-text-tertiary">
                <span>1 - Muy fácil</span>
                <span>10 - Maximo esfuerzo</span>
            </div>
            @error('rpe')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Comentario --}}
        <div>
            <label for="comentario" class="mb-2 block text-sm font-medium text-wc-text">Comentario para tu coach</label>
            <textarea
                id="comentario"
                wire:model="comentario"
                rows="3"
                placeholder="¿Cómo te sentiste esta semana? ¿Alguna molestia o logro?"
                class="w-full rounded-[--radius-button] border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
            ></textarea>
            @error('comentario')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Duplicate check-in error --}}
        @error('submit')
            <div class="flex items-center gap-3 rounded-[--radius-card] border border-wc-accent/30 bg-wc-accent/10 p-4">
                <svg class="h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <p class="text-sm font-medium text-wc-accent">{{ $message }}</p>
            </div>
        @enderror

        {{-- Submit --}}
        <button
            type="submit"
            class="btn-press w-full rounded-[--radius-button] bg-wc-accent px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg disabled:opacity-50"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="submit">Enviar Check-in</span>
            <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Enviando...
            </span>
        </button>
    </form>

    {{-- Recent Check-ins --}}
    @if ($recentCheckins->isNotEmpty())
        <div>
            <h2 class="mb-4 font-display text-xl tracking-wide text-wc-text">CHECK-INS ANTERIORES</h2>

            <div class="space-y-3">
                @foreach ($recentCheckins as $checkin)
                    <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
                        {{-- Header --}}
                        <div class="mb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-data text-sm font-semibold text-wc-text">{{ $checkin->week_label }}</span>
                                <span class="text-xs text-wc-text-tertiary">
                                    {{ $checkin->checkin_date?->locale('es')->isoFormat('D MMM YYYY') }}
                                </span>
                            </div>
                            @if ($checkin->coach_reply)
                                <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-500">Respondido</span>
                            @endif
                        </div>

                        {{-- Metrics Row --}}
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div>
                                <p class="text-xs text-wc-text-tertiary">Bienestar</p>
                                <p class="font-data text-sm font-semibold text-wc-text">{{ $checkin->bienestar }}/5</p>
                            </div>
                            <div>
                                <p class="text-xs text-wc-text-tertiary">Dias</p>
                                <p class="font-data text-sm font-semibold text-wc-text">{{ $checkin->dias_entrenados }}/7</p>
                            </div>
                            <div>
                                <p class="text-xs text-wc-text-tertiary">Nutrición</p>
                                <p class="text-sm font-medium capitalize text-wc-text">{{ $checkin->nutricion }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-wc-text-tertiary">RPE</p>
                                <p class="font-data text-sm font-semibold text-wc-text">{{ $checkin->rpe }}/10</p>
                            </div>
                        </div>

                        {{-- Comentario --}}
                        @if ($checkin->comentario)
                            <p class="mt-3 text-sm text-wc-text-secondary">{{ $checkin->comentario }}</p>
                        @endif

                        {{-- Coach Reply --}}
                        @if ($checkin->coach_reply)
                            <div class="mt-3 rounded-lg border border-wc-accent/20 bg-wc-accent/5 p-3">
                                <div class="mb-1 flex items-center gap-2">
                                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                    </svg>
                                    <span class="text-xs font-medium text-wc-accent">Respuesta del coach</span>
                                    @if ($checkin->replied_at)
                                        <span class="text-xs text-wc-text-tertiary">{{ $checkin->replied_at->locale('es')->diffForHumans() }}</span>
                                    @endif
                                </div>
                                <p class="text-sm text-wc-text">{{ $checkin->coach_reply }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===== ONBOARDING TUTORIAL: CHECK-IN ===== --}}
    @if($showTutorial)
    <div
        x-data="{ step: 1, total: 3 }"
        class="fixed inset-0 z-[80] flex items-end justify-center bg-black/70 px-4 pb-6"
        @keydown.escape.window="$wire.dismissTutorial()"
    >
        <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">

            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-lg tracking-widest text-wc-text">CHECK-IN SEMANAL</h3>
                <button @click="$wire.dismissTutorial()" class="text-wc-text-tertiary hover:text-wc-text transition-colors" aria-label="Cerrar" type="button">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-show="step === 1">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">1</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">¿Qué es el check-in?</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Es tu reporte semanal al coach. Con esta información tu coach ajusta tu plan de entrenamiento y nutrición para maximizar tus resultados semana a semana.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 2">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">2</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Sé honesto</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">No hay respuestas malas. Si tuviste una semana difícil, dilo. Tu coach solo puede ayudarte si conoce tu realidad — no la versión perfecta.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 3">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">3</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Hazlo cada semana</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Los clientes que completan su check-in semanalmente progresan 3x más rápido. El seguimiento constante es lo que diferencia los resultados promedio de los extraordinarios.</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-center gap-1.5">
                <template x-for="i in total" :key="i">
                    <div class="h-1.5 rounded-full transition-all" :class="i === step ? 'bg-wc-accent w-4' : 'bg-wc-bg-tertiary w-1.5'"></div>
                </template>
            </div>

            <div class="mt-5 flex gap-3">
                <button x-show="step > 1" @click="step--" class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors" type="button">Atrás</button>
                <button x-show="step < total" @click="step++" class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors" type="button">Siguiente</button>
                <button x-show="step === total" @click="$wire.dismissTutorial()" class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors" type="button">¡Listo, comenzar!</button>
            </div>
        </div>
    </div>
    @endif
    {{-- ===== /ONBOARDING TUTORIAL: CHECK-IN ===== --}}
</div>
