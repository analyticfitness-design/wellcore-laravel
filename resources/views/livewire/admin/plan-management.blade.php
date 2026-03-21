<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text">GESTION DE PLANES</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Templates de planes de entrenamiento, nutricion, habitos y mas.</p>
        </div>
        <button wire:click="openCreate"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
            + Nuevo Template
        </button>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="font-data text-2xl font-bold text-wc-text">{{ $stats['total'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
        </div>
        <div class="rounded-xl border border-sky-500/30 bg-sky-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-sky-400">{{ $stats['entrenamiento'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Entrena.</p>
        </div>
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-emerald-400">{{ $stats['nutricion'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Nutricion</p>
        </div>
        <div class="rounded-xl border border-violet-500/30 bg-violet-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-violet-400">{{ $stats['habitos'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Habitos</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-amber-400">{{ $stats['suplementacion'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Suplem.</p>
        </div>
        <div class="rounded-xl border border-pink-500/30 bg-pink-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-pink-400">{{ $stats['ciclo'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Ciclo</p>
        </div>
        <div class="rounded-xl border border-purple-500/30 bg-purple-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-purple-400">{{ $stats['ai_generated'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">AI Gen.</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        {{-- Search --}}
        <div class="relative flex-1 min-w-48">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por nombre, metodologia..."
                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
        </div>

        {{-- Type filter --}}
        <select wire:model.live="typeFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Todos los tipos</option>
            <option value="entrenamiento">Entrenamiento</option>
            <option value="nutricion">Nutricion</option>
            <option value="habitos">Habitos</option>
            <option value="suplementacion">Suplementacion</option>
            <option value="ciclo">Ciclo</option>
        </select>

        {{-- Coach filter --}}
        <select wire:model.live="coachFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Todos los coaches</option>
            @foreach($coaches as $coach)
                <option value="{{ $coach->id }}">{{ $coach->name ?? $coach->id }}</option>
            @endforeach
        </select>

        {{-- Public filter --}}
        <select wire:model.live="publicFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Visibilidad</option>
            <option value="yes">Publicos</option>
            <option value="no">Privados</option>
        </select>

        {{-- AI filter --}}
        <select wire:model.live="aiFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Origen</option>
            <option value="yes">AI Generado</option>
            <option value="no">Manual</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-wc-border">
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortByColumn('name')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                                Nombre
                                @if($sortBy === 'name')
                                    <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortByColumn('plan_type')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                                Tipo
                                @if($sortBy === 'plan_type')
                                    <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="hidden px-4 py-3 text-left sm:table-cell">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodologia</span>
                        </th>
                        <th class="hidden px-4 py-3 text-left md:table-cell">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Coach</span>
                        </th>
                        <th class="hidden px-4 py-3 text-center sm:table-cell">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Badges</span>
                        </th>
                        <th class="hidden px-4 py-3 text-left lg:table-cell">
                            <button wire:click="sortByColumn('created_at')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                                Fecha
                                @if($sortBy === 'created_at')
                                    <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-right">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-wc-border">
                    @forelse($plans as $plan)
                        @php
                            $typeColors = [
                                'entrenamiento'  => 'bg-sky-500/10 text-sky-400',
                                'nutricion'      => 'bg-emerald-500/10 text-emerald-400',
                                'habitos'        => 'bg-violet-500/10 text-violet-400',
                                'suplementacion' => 'bg-amber-500/10 text-amber-400',
                                'ciclo'          => 'bg-pink-500/10 text-pink-400',
                            ];
                            $typeLabels = [
                                'entrenamiento'  => 'Entrenamiento',
                                'nutricion'      => 'Nutricion',
                                'habitos'        => 'Habitos',
                                'suplementacion' => 'Suplementacion',
                                'ciclo'          => 'Ciclo',
                            ];
                        @endphp
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50" wire:key="plan-{{ $plan->id }}">
                            {{-- Name + description --}}
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-wc-text">{{ $plan->name }}</div>
                                @if($plan->description)
                                    <div class="mt-0.5 text-xs text-wc-text-tertiary line-clamp-1">{{ \Illuminate\Support\Str::limit($plan->description, 60) }}</div>
                                @endif
                            </td>

                            {{-- Type badge --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $typeColors[$plan->plan_type] ?? 'bg-wc-bg-secondary text-wc-text-secondary' }}">
                                    {{ $typeLabels[$plan->plan_type] ?? ucfirst($plan->plan_type) }}
                                </span>
                            </td>

                            {{-- Methodology --}}
                            <td class="hidden px-4 py-3 sm:table-cell">
                                <span class="text-xs text-wc-text-secondary">{{ $plan->methodology ?? '—' }}</span>
                            </td>

                            {{-- Coach --}}
                            <td class="hidden px-4 py-3 md:table-cell">
                                <span class="text-xs text-wc-text-secondary">{{ $plan->coach->name ?? '—' }}</span>
                            </td>

                            {{-- Badges: AI + Public --}}
                            <td class="hidden px-4 py-3 text-center sm:table-cell">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($plan->ai_generated)
                                        <span class="inline-flex rounded-full bg-purple-500/10 px-2 py-0.5 text-[10px] font-semibold text-purple-400">AI</span>
                                    @endif
                                    @if($plan->is_public)
                                        <span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Publico</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-semibold text-wc-text-tertiary">Privado</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Date --}}
                            <td class="hidden px-4 py-3 lg:table-cell">
                                <span class="text-xs text-wc-text-tertiary">{{ $plan->created_at?->format('d M Y') }}</span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- View --}}
                                    <button wire:click="openView({{ $plan->id }})"
                                            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary hover:border-sky-500 hover:text-sky-400 transition-colors"
                                            title="Ver contenido">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>
                                    {{-- Edit --}}
                                    <button wire:click="openEdit({{ $plan->id }})"
                                            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary hover:border-wc-accent hover:text-wc-accent transition-colors"
                                            title="Editar">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>
                                    {{-- Delete --}}
                                    <button wire:click="confirmDelete({{ $plan->id }})"
                                            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary hover:border-red-500 hover:text-red-400 transition-colors"
                                            title="Eliminar">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-wc-text-tertiary">
                                No se encontraron templates con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($plans->hasPages())
        <div class="flex justify-center">
            {{ $plans->links() }}
        </div>
    @endif

    {{-- ==================== CREATE / EDIT MODAL ==================== --}}
    @if($showFormModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="mb-5 flex items-start justify-between">
                    <h2 class="font-display text-2xl tracking-wide text-wc-text">
                        {{ $editingId ? 'EDITAR TEMPLATE' : 'NUEVO TEMPLATE' }}
                    </h2>
                    <button wire:click="closeForm" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="savePlan" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Nombre <span class="text-wc-accent">*</span></label>
                        <input type="text" wire:model="formName" placeholder="Nombre del template"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        @error('formName') <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Tipo <span class="text-wc-accent">*</span></label>
                            <select wire:model="formPlanType"
                                    class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="entrenamiento">Entrenamiento</option>
                                <option value="nutricion">Nutricion</option>
                                <option value="habitos">Habitos</option>
                                <option value="suplementacion">Suplementacion</option>
                                <option value="ciclo">Ciclo</option>
                            </select>
                            @error('formPlanType') <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Coach</label>
                            <select wire:model="formCoachId"
                                    class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Sin asignar</option>
                                @foreach($coaches as $coach)
                                    <option value="{{ $coach->id }}">{{ $coach->name ?? $coach->id }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Metodologia</label>
                        <input type="text" wire:model="formMethodology" placeholder="Push/Pull/Legs, Full Body, etc."
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Descripcion</label>
                        <textarea wire:model="formDescription" rows="2" placeholder="Breve descripcion del plan..."
                                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Contenido JSON <span class="text-wc-accent">*</span></label>
                        <textarea wire:model="formContentJson" rows="10" placeholder='{"weeks": [{"day": 1, "exercises": [...]}]}'
                                  class="w-full resize-y rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 font-mono text-xs text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"></textarea>
                        @error('formContentJson') <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p> @enderror
                    </div>

                    {{-- Public toggle --}}
                    <div class="flex items-center gap-3">
                        <button type="button"
                                wire:click="$toggle('formIsPublic')"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200
                                       {{ $formIsPublic ? 'bg-emerald-500' : 'bg-wc-bg-tertiary' }}"
                                role="switch"
                                aria-checked="{{ $formIsPublic ? 'true' : 'false' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition-transform duration-200
                                         {{ $formIsPublic ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                        <span class="text-sm text-wc-text-secondary">Template publico</span>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="closeForm"
                                class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="btn-press flex-1 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors"
                                wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-not-allowed">
                            <span wire:loading.remove wire:target="savePlan">{{ $editingId ? 'Actualizar' : 'Crear Template' }}</span>
                            <span wire:loading wire:target="savePlan" class="inline-flex items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ==================== VIEW CONTENT MODAL ==================== --}}
    @if($showViewModal && $viewingPlan)
        @php
            $vtypeColors = [
                'entrenamiento'  => 'bg-sky-500/10 text-sky-400',
                'nutricion'      => 'bg-emerald-500/10 text-emerald-400',
                'habitos'        => 'bg-violet-500/10 text-violet-400',
                'suplementacion' => 'bg-amber-500/10 text-amber-400',
                'ciclo'          => 'bg-pink-500/10 text-pink-400',
            ];
            $vtypeLabels = [
                'entrenamiento'  => 'Entrenamiento',
                'nutricion'      => 'Nutricion',
                'habitos'        => 'Habitos',
                'suplementacion' => 'Suplementacion',
                'ciclo'          => 'Ciclo',
            ];
        @endphp
        <div class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeView"></div>
            <div class="relative z-10 w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-2xl tracking-wide text-wc-text">{{ strtoupper($viewingPlan->name) }}</h2>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $vtypeColors[$viewingPlan->plan_type] ?? '' }}">
                                {{ $vtypeLabels[$viewingPlan->plan_type] ?? ucfirst($viewingPlan->plan_type) }}
                            </span>
                            @if($viewingPlan->ai_generated)
                                <span class="inline-flex rounded-full bg-purple-500/10 px-2 py-0.5 text-[10px] font-semibold text-purple-400">AI Generado</span>
                            @endif
                            @if($viewingPlan->is_public)
                                <span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Publico</span>
                            @endif
                            @if($viewingPlan->coach)
                                <span class="text-xs text-wc-text-tertiary">por {{ $viewingPlan->coach->name }}</span>
                            @endif
                        </div>
                    </div>
                    <button wire:click="closeView" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Metadata --}}
                <div class="grid grid-cols-2 gap-3 mb-5 sm:grid-cols-4">
                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodologia</p>
                        <p class="mt-1 text-sm text-wc-text">{{ $viewingPlan->methodology ?? '—' }}</p>
                    </div>
                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Coach</p>
                        <p class="mt-1 text-sm text-wc-text">{{ $viewingPlan->coach->name ?? '—' }}</p>
                    </div>
                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Creado</p>
                        <p class="mt-1 text-sm text-wc-text">{{ $viewingPlan->created_at?->format('d M Y') }}</p>
                    </div>
                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Actualizado</p>
                        <p class="mt-1 text-sm text-wc-text">{{ $viewingPlan->updated_at?->format('d M Y') }}</p>
                    </div>
                </div>

                @if($viewingPlan->description)
                    <div class="mb-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                        <h4 class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Descripcion</h4>
                        <p class="text-sm text-wc-text leading-relaxed">{{ $viewingPlan->description }}</p>
                    </div>
                @endif

                {{-- JSON Content --}}
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                    <h4 class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Contenido JSON</h4>
                    <pre class="max-h-96 overflow-auto rounded-lg bg-wc-bg p-4 font-mono text-xs text-wc-text leading-relaxed">{{ is_array($viewingPlan->content_json) ? json_encode($viewingPlan->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $viewingPlan->content_json }}</pre>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== DELETE CONFIRMATION MODAL ==================== --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeDelete"></div>
            <div class="relative z-10 w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10">
                    <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </div>
                <h3 class="font-display text-xl tracking-wide text-wc-text mb-2">ELIMINAR TEMPLATE</h3>
                <p class="text-sm text-wc-text-secondary mb-5">Esta accion no se puede deshacer. El template sera eliminado permanentemente.</p>
                <div class="flex gap-3">
                    <button wire:click="closeDelete"
                            class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="deletePlan"
                            class="flex-1 rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
                            wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-not-allowed">
                        <span wire:loading.remove wire:target="deletePlan">Eliminar</span>
                        <span wire:loading wire:target="deletePlan">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
