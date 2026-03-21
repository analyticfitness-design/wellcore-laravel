<div class="space-y-6" x-data="{ newPrAnimation: false }" x-on:pr-saved.window="newPrAnimation = true; setTimeout(() => newPrAnimation = false, 2000)">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text">PERSONAL RECORDS</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Registra y compite contra ti mismo. Cada PR es una victoria.</p>
        </div>
        <button wire:click="openForm" class="rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors">
            + Nuevo PR
        </button>
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="font-data text-2xl font-bold text-wc-accent">{{ $totalPrs }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">PRs Actuales</p>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="font-data text-2xl font-bold text-wc-text">{{ $totalExercises }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Ejercicios</p>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="font-data text-2xl font-bold text-yellow-400">{{ $thisMonth }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Este Mes</p>
        </div>
    </div>

    {{-- PR saved animation --}}
    <div x-show="newPrAnimation" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="rounded-xl border border-yellow-500/30 bg-yellow-500/10 p-4 text-center" x-cloak>
        <span class="text-3xl">🏆</span>
        <p class="mt-1 text-sm font-semibold text-yellow-400">PR Registrado!</p>
    </div>

    {{-- Search + category filter --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar ejercicio..." class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
        </div>
        <div class="flex gap-2">
            @foreach(['all' => 'Todos', 'fuerza' => 'Fuerza', 'cardio' => 'Cardio', 'calistenia' => 'Calistenia', 'flexibilidad' => 'Flexibilidad'] as $key => $label)
                <button wire:click="$set('category', '{{ $key }}')" class="rounded-lg border border-wc-border px-3 py-2 text-sm font-medium transition-colors {{ $category === $key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Records list --}}
    @if($records->count() > 0)
        <div class="space-y-3">
            @foreach($records as $pr)
                <div class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all hover:border-wc-accent/30">
                    {{-- Trophy icon --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $pr->is_current ? 'bg-yellow-500/10' : 'bg-wc-bg-secondary' }}">
                        @if($pr->is_current)
                            <span class="text-lg">🏆</span>
                        @else
                            <span class="text-lg opacity-40">🥈</span>
                        @endif
                    </div>

                    {{-- Exercise info --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-wc-text">{{ $pr->exercise }}</h3>
                            @if($pr->is_current)
                                <span class="rounded-full bg-yellow-500/10 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-yellow-400">PR</span>
                            @endif
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-wc-text-tertiary">
                            <span class="rounded bg-wc-bg-secondary px-1.5 py-0.5 text-[10px] font-medium text-wc-text-secondary">{{ ucfirst($pr->category) }}</span>
                            <span>{{ $pr->achieved_at->format('d M Y') }}</span>
                            @if($pr->notes)
                                <span class="truncate max-w-[200px]">{{ $pr->notes }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Values --}}
                    <div class="flex items-center gap-4 text-right">
                        @if($pr->weight)
                            <div>
                                <p class="font-data text-lg font-bold text-wc-text">{{ rtrim(rtrim(number_format($pr->weight, 2), '0'), '.') }}</p>
                                <p class="text-[9px] text-wc-text-tertiary">kg</p>
                            </div>
                        @endif
                        @if($pr->reps)
                            <div>
                                <p class="font-data text-lg font-bold text-blue-400">{{ $pr->reps }}</p>
                                <p class="text-[9px] text-wc-text-tertiary">reps</p>
                            </div>
                        @endif
                        @if($pr->duration_sec)
                            <div>
                                <p class="font-data text-lg font-bold text-green-400">{{ gmdate($pr->duration_sec >= 3600 ? 'G:i:s' : 'i:s', $pr->duration_sec) }}</p>
                                <p class="text-[9px] text-wc-text-tertiary">tiempo</p>
                            </div>
                        @endif
                        @if($pr->distance_km)
                            <div>
                                <p class="font-data text-lg font-bold text-purple-400">{{ rtrim(rtrim(number_format($pr->distance_km, 2), '0'), '.') }}</p>
                                <p class="text-[9px] text-wc-text-tertiary">km</p>
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex shrink-0 gap-1">
                        <button wire:click="openForm({{ $pr->id }})" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-secondary hover:text-wc-text" title="Editar">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                        <button wire:click="confirmDelete({{ $pr->id }})" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-red-500/10 hover:text-red-400" title="Eliminar">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <span class="text-5xl">🏆</span>
            <p class="mt-3 text-sm font-semibold text-wc-text-secondary">Aun no tienes Personal Records</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Registra tu primer PR para empezar a trackear tu progreso.</p>
            <button wire:click="openForm" class="mt-4 rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover">
                Registrar Primer PR
            </button>
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" x-on:keydown.escape.window="$wire.closeForm()">
            <div class="w-full max-w-lg rounded-2xl border border-wc-border bg-wc-bg-secondary">
                <div class="flex items-center justify-between border-b border-wc-border px-6 py-4">
                    <h2 class="font-display text-xl tracking-wide text-wc-text">
                        {{ $editingId ? 'EDITAR PR' : 'NUEVO PR' }}
                    </h2>
                    <button wire:click="closeForm" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-secondary hover:text-wc-text">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4 px-6 py-5">
                    {{-- Exercise name --}}
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Ejercicio *</label>
                        <input type="text" wire:model="exercise" placeholder="Ej: Press de Banca, Sentadilla, 5K..." class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        @error('exercise') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Categoria *</label>
                        <div class="mt-1 flex gap-2">
                            @foreach(['fuerza' => 'Fuerza', 'cardio' => 'Cardio', 'calistenia' => 'Calistenia', 'flexibilidad' => 'Flexibilidad'] as $key => $label)
                                <button type="button" wire:click="$set('formCategory', '{{ $key }}')" class="rounded-lg border px-3 py-2 text-xs font-medium transition-colors {{ $formCategory === $key ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border text-wc-text-secondary hover:text-wc-text' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Values grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Peso (kg)</label>
                            <input type="number" wire:model="weight" step="0.5" min="0" max="9999" placeholder="0" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                            @error('weight') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Repeticiones</label>
                            <input type="number" wire:model="reps" min="0" max="9999" placeholder="0" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                            @error('reps') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Duracion (segundos)</label>
                            <input type="number" wire:model="durationSec" min="0" max="86400" placeholder="0" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                            @error('durationSec') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Distancia (km)</label>
                            <input type="number" wire:model="distanceKm" step="0.1" min="0" max="9999" placeholder="0" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                            @error('distanceKm') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Fecha *</label>
                        <input type="date" wire:model="achievedAt" max="{{ now()->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                        @error('achievedAt') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Notas (opcional)</label>
                        <textarea wire:model="notes" rows="2" placeholder="Ej: Primer intento sin spotter..." class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none resize-none"></textarea>
                    </div>

                    {{-- Submit --}}
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn-press flex-1 rounded-lg bg-wc-accent px-6 py-3 text-sm font-semibold text-white hover:bg-wc-accent-hover"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $editingId ? 'Guardar Cambios' : 'Registrar PR' }}</span>
                            <span wire:loading class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Guardando...
                            </span>
                        </button>
                        <button type="button" wire:click="closeForm" class="rounded-lg border border-wc-border px-6 py-3 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    @if($deletingId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
            <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10">
                    <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </div>
                <h3 class="mt-3 text-sm font-semibold text-wc-text">Eliminar este PR?</h3>
                <p class="mt-1 text-xs text-wc-text-tertiary">Esta accion no se puede deshacer.</p>
                <div class="mt-5 flex gap-3">
                    <button wire:click="delete" class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                        Eliminar
                    </button>
                    <button wire:click="cancelDelete" class="flex-1 rounded-lg border border-wc-border px-4 py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
