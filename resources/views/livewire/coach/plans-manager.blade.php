<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Gestion de Planes</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">Crea, gestiona y asigna planes a tus clientes</p>
        </div>
    </div>

    {{-- Tab bar --}}
    <div class="flex items-center gap-1 rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
        @foreach (['my_templates' => 'Mis Templates', 'assigned' => 'Asignados', 'generate' => 'Generar con IA'] as $tab => $label)
            <button
                wire:click="switchTab('{{ $tab }}')"
                class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors
                       {{ $activeTab === $tab
                           ? 'bg-wc-accent text-white shadow-sm'
                           : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary' }}"
            >
                @if ($tab === 'generate')
                    <svg class="inline-block h-4 w-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                    </svg>
                @endif
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════ --}}
    {{--  MY TEMPLATES TAB                       --}}
    {{-- ═══════════════════════════════════════ --}}
    @if ($activeTab === 'my_templates')
        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
                <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ $templateStats['total'] }}</p>
            </div>
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Entrenamiento</p>
                <p class="mt-1 font-data text-2xl font-bold text-sky-500">{{ $templateStats['entrenamiento'] }}</p>
            </div>
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Nutricion</p>
                <p class="mt-1 font-data text-2xl font-bold text-emerald-500">{{ $templateStats['nutricion'] }}</p>
            </div>
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Habitos</p>
                <p class="mt-1 font-data text-2xl font-bold text-amber-500">{{ $templateStats['habitos'] }}</p>
            </div>
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">IA Generados</p>
                <p class="mt-1 font-data text-2xl font-bold text-purple-500">{{ $templateStats['ai_generated'] }}</p>
            </div>
        </div>

        {{-- Filters + CTA --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 items-center gap-3">
                <div class="relative flex-1 max-w-sm">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="templateSearch" type="text" placeholder="Buscar templates..."
                           class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                </div>
                <select wire:model.live="templateTypeFilter"
                        class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="">Todos los tipos</option>
                    <option value="entrenamiento">Entrenamiento</option>
                    <option value="nutricion">Nutricion</option>
                    <option value="habitos">Habitos</option>
                    <option value="suplementacion">Suplementacion</option>
                    <option value="ciclo">Ciclo</option>
                </select>
            </div>
            <button wire:click="openCreateTemplate"
                    class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo Template
            </button>
        </div>

        {{-- Templates list --}}
        @if ($templates->isEmpty())
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <p class="mt-4 text-sm text-wc-text-tertiary">No tienes templates aun. Crea uno o genera con IA.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($templates as $tpl)
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 hover:border-wc-accent/40 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-wc-text truncate">{{ $tpl->name }}</h3>
                                <div class="mt-1 flex items-center gap-2 flex-wrap">
                                    @php
                                        $typeColors = [
                                            'entrenamiento' => 'bg-sky-500/10 text-sky-500',
                                            'nutricion' => 'bg-emerald-500/10 text-emerald-500',
                                            'habitos' => 'bg-amber-500/10 text-amber-500',
                                            'suplementacion' => 'bg-purple-500/10 text-purple-500',
                                            'ciclo' => 'bg-pink-500/10 text-pink-500',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $typeColors[$tpl->plan_type] ?? 'bg-gray-500/10 text-gray-500' }}">
                                        {{ ucfirst($tpl->plan_type) }}
                                    </span>
                                    @if ($tpl->ai_generated)
                                        <span class="inline-flex items-center rounded-full bg-purple-500/10 px-2 py-0.5 text-xs font-medium text-purple-500">
                                            <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                                            </svg>
                                            IA
                                        </span>
                                    @endif
                                    @if ($tpl->is_public)
                                        <span class="inline-flex items-center rounded-full bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-500">Publico</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($tpl->methodology)
                            <p class="mt-2 text-xs text-wc-text-secondary">{{ $tpl->methodology }}</p>
                        @endif
                        @if ($tpl->description)
                            <p class="mt-1 text-xs text-wc-text-tertiary line-clamp-2">{{ $tpl->description }}</p>
                        @endif

                        <p class="mt-2 text-xs text-wc-text-tertiary">{{ $tpl->updated_at?->diffForHumans() ?? '-' }}</p>

                        {{-- Actions --}}
                        <div class="mt-3 flex items-center gap-1 border-t border-wc-border pt-3">
                            <button wire:click="previewTemplate({{ $tpl->id }})" class="rounded-md p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-secondary transition-colors" title="Vista previa">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                            <button wire:click="openEditTemplate({{ $tpl->id }})" class="rounded-md p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-secondary transition-colors" title="Editar">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button wire:click="duplicateTemplate({{ $tpl->id }})" class="rounded-md p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-secondary transition-colors" title="Duplicar">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.5a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.25a9.06 9.06 0 0 1 1.5-.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                </svg>
                            </button>
                            <button wire:click="togglePublic({{ $tpl->id }})" class="rounded-md p-1.5 transition-colors {{ $tpl->is_public ? 'text-green-500 hover:text-green-400' : 'text-wc-text-tertiary hover:text-wc-text' }} hover:bg-wc-bg-secondary" title="{{ $tpl->is_public ? 'Hacer privado' : 'Hacer publico' }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 3.03v.568c0 .334.148.65.405.864A6.75 6.75 0 0 1 18 10.5v.75M12.75 3.03c-.9 0-1.788.06-2.664.178m2.664-.178a48.693 48.693 0 0 1 4.06.371M7.5 3.208C4.153 4.127 1.5 6.986 1.5 10.5c0 .75.107 1.477.308 2.164m6.692-8.956a48.406 48.406 0 0 0-2.164.368M17.25 10.5V6.469a1.846 1.846 0 0 0-.556-1.323 48.258 48.258 0 0 0-5.444-4.649 1.845 1.845 0 0 0-2.25 0 48.267 48.267 0 0 0-5.444 4.649c-.372.356-.556.84-.556 1.323V10.5" />
                                </svg>
                            </button>
                            <div class="flex-1"></div>
                            <button wire:click="confirmDelete({{ $tpl->id }})" class="rounded-md p-1.5 text-wc-text-tertiary hover:text-red-500 hover:bg-red-500/10 transition-colors" title="Eliminar">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    {{-- ═══════════════════════════════════════ --}}
    {{--  ASSIGNED TAB                           --}}
    {{-- ═══════════════════════════════════════ --}}
    @elseif ($activeTab === 'assigned')
        {{-- Filters --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                {{-- Active filter --}}
                <div class="flex items-center gap-1 rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
                    @foreach (['active' => 'Activos', 'inactive' => 'Inactivos', 'all' => 'Todos'] as $val => $label)
                        <button
                            wire:click="$set('assignedActiveFilter', '{{ $val }}')"
                            class="rounded-md px-3 py-1 text-xs font-medium transition-colors
                                   {{ $assignedActiveFilter === $val
                                       ? 'bg-wc-accent text-white'
                                       : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary' }}"
                        >{{ $label }}</button>
                    @endforeach
                </div>
                <input wire:model.live.debounce.300ms="assignedClientFilter" type="text" placeholder="Filtrar por cliente..."
                       class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                <select wire:model.live="assignedTypeFilter"
                        class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="">Todos los tipos</option>
                    <option value="entrenamiento">Entrenamiento</option>
                    <option value="nutricion">Nutricion</option>
                    <option value="habitos">Habitos</option>
                </select>
            </div>
            <button wire:click="openAssignModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                Asignar Plan
            </button>
        </div>

        {{-- Assigned table --}}
        @if ($assignedPlans->isEmpty())
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                <p class="mt-4 text-sm text-wc-text-tertiary">No hay planes asignados. Asigna un plan desde un template.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-card border border-wc-border">
                <table class="w-full text-sm">
                    <thead class="border-b border-wc-border bg-wc-bg-secondary">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-wc-text-tertiary">Cliente</th>
                            <th class="px-4 py-3 text-left font-medium text-wc-text-tertiary">Tipo</th>
                            <th class="px-4 py-3 text-center font-medium text-wc-text-tertiary">Version</th>
                            <th class="px-4 py-3 text-center font-medium text-wc-text-tertiary">Estado</th>
                            <th class="px-4 py-3 text-left font-medium text-wc-text-tertiary">Desde</th>
                            <th class="px-4 py-3 text-left font-medium text-wc-text-tertiary">Creado</th>
                            <th class="px-4 py-3 text-center font-medium text-wc-text-tertiary">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        @foreach ($assignedPlans as $ap)
                            <tr class="hover:bg-wc-bg-secondary/50 transition-colors">
                                <td class="px-4 py-3 text-wc-text font-medium">{{ $ap->client?->name ?? 'Eliminado' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $apTypeColors = [
                                            'entrenamiento' => 'bg-sky-500/10 text-sky-500',
                                            'nutricion' => 'bg-emerald-500/10 text-emerald-500',
                                            'habitos' => 'bg-amber-500/10 text-amber-500',
                                        ];
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $apTypeColors[$ap->plan_type] ?? 'bg-gray-500/10 text-gray-500' }}">
                                        {{ ucfirst($ap->plan_type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-data text-wc-text">v{{ $ap->version }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="toggleAssignedActive({{ $ap->id }})"
                                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium transition-colors cursor-pointer
                                                   {{ $ap->active ? 'bg-green-500/10 text-green-500 hover:bg-green-500/20' : 'bg-gray-500/10 text-gray-400 hover:bg-gray-500/20' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $ap->active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                        {{ $ap->active ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-xs text-wc-text-secondary">{{ $ap->valid_from?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs text-wc-text-tertiary">{{ $ap->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="viewAssignedContent({{ $ap->id }})"
                                            class="rounded-md p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-secondary transition-colors" title="Ver contenido">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    {{-- ═══════════════════════════════════════ --}}
    {{--  GENERATE TAB                           --}}
    {{-- ═══════════════════════════════════════ --}}
    @elseif ($activeTab === 'generate')

        @if ($saved)
            {{-- Success state --}}
            <div class="rounded-card border border-green-500/30 bg-green-500/5 p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-500/10">
                    <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <h3 class="mt-4 font-display text-xl text-wc-text">Plan Guardado Exitosamente</h3>
                <p class="mt-2 text-sm text-wc-text-secondary">
                    Template #{{ $savedTemplateId }} creado
                    @if ($savedAssignedId)
                        y asignado al cliente (Plan #{{ $savedAssignedId }})
                    @endif
                </p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <button wire:click="startNewGeneration" class="rounded-lg bg-wc-accent px-5 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
                        Generar otro plan
                    </button>
                    <button wire:click="switchTab('my_templates')" class="rounded-lg border border-wc-border px-5 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                        Ver mis templates
                    </button>
                </div>
            </div>

        @elseif ($genStep === 1)
            {{-- STEP A: Configuration --}}
            <div class="space-y-6">
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <h3 class="font-display text-lg text-wc-text">Paso 1: Configurar el plan</h3>
                    <p class="mt-1 text-sm text-wc-text-tertiary">Selecciona tipo, metodologia y parametros basicos</p>
                </div>

                {{-- Plan type selector --}}
                <div>
                    <label class="text-sm font-medium text-wc-text">Tipo de plan</label>
                    <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @foreach ([
                            'entrenamiento' => ['label' => 'Entrenamiento', 'icon' => 'M15.75 6.75a3 3 0 1 1 0 7.5 3 3 0 0 1 0-7.5ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z', 'color' => 'sky'],
                            'nutricion' => ['label' => 'Nutricion', 'icon' => 'M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12', 'color' => 'emerald'],
                            'habitos' => ['label' => 'Habitos', 'icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z', 'color' => 'amber'],
                        ] as $typeKey => $typeData)
                            <button
                                wire:click="selectPlanType('{{ $typeKey }}')"
                                class="flex items-center gap-3 rounded-lg border-2 p-4 transition-all
                                       {{ $planType === $typeKey
                                           ? 'border-wc-accent bg-wc-accent/5'
                                           : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}"
                            >
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-{{ $typeData['color'] }}-500/10">
                                    <svg class="h-5 w-5 text-{{ $typeData['color'] }}-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $typeData['icon'] }}" />
                                    </svg>
                                </div>
                                <span class="font-medium text-wc-text">{{ $typeData['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Methodology grid (training/nutrition) --}}
                @if ($planType === 'entrenamiento' && !empty($this->methodologies['training']))
                    <div>
                        <label class="text-sm font-medium text-wc-text">Metodologia</label>
                        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($this->methodologies['training'] as $key => $meth)
                                <button
                                    wire:click="selectMethodology('{{ $key }}')"
                                    class="flex flex-col rounded-lg border-2 p-3 text-left transition-all
                                           {{ $methodology === $key
                                               ? 'border-wc-accent bg-wc-accent/5'
                                               : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}"
                                >
                                    <span class="text-sm font-medium text-wc-text">{{ $meth['name'] }}</span>
                                    <span class="mt-1 text-xs text-wc-text-tertiary line-clamp-2">{{ $meth['desc'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @elseif ($planType === 'nutricion' && !empty($this->methodologies['nutrition']))
                    <div>
                        <label class="text-sm font-medium text-wc-text">Enfoque nutricional</label>
                        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($this->methodologies['nutrition'] as $key => $meth)
                                <button
                                    wire:click="selectMethodology('{{ $key }}')"
                                    class="flex flex-col rounded-lg border-2 p-3 text-left transition-all
                                           {{ $methodology === $key
                                               ? 'border-wc-accent bg-wc-accent/5'
                                               : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}"
                                >
                                    <span class="text-sm font-medium text-wc-text">{{ $meth['name'] }}</span>
                                    <span class="mt-1 text-xs text-wc-text-tertiary line-clamp-2">{{ $meth['desc'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @elseif ($planType === 'habitos')
                    <div>
                        <label class="text-sm font-medium text-wc-text">Areas de enfoque</label>
                        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($this->habitAreas as $key => $area)
                                <button
                                    wire:click="toggleHabitArea('{{ $key }}')"
                                    class="flex items-center gap-3 rounded-lg border-2 p-3 text-left transition-all
                                           {{ in_array($key, $habitFocusAreas)
                                               ? 'border-wc-accent bg-wc-accent/5'
                                               : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}"
                                >
                                    <div>
                                        <span class="text-sm font-medium text-wc-text">{{ $area['name'] }}</span>
                                        <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ $area['desc'] }}</p>
                                    </div>
                                    @if (in_array($key, $habitFocusAreas))
                                        <svg class="ml-auto h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Client selector (optional) --}}
                @if ($planType)
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                        <label class="text-sm font-medium text-wc-text">Cliente objetivo (opcional)</label>
                        <p class="mt-0.5 text-xs text-wc-text-tertiary">Si seleccionas un cliente, el plan se personalizara con sus datos</p>
                        <div class="mt-2 relative">
                            <input wire:model.live.debounce.300ms="clientSearch" type="text" placeholder="Buscar cliente por nombre o email..."
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 px-3 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />

                            @if ($targetClientId)
                                @php $selClient = $clients->firstWhere('id', $targetClientId); @endphp
                                @if ($selClient)
                                    <div class="mt-2 flex items-center gap-2 rounded-lg border border-green-500/30 bg-green-500/5 px-3 py-2">
                                        <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span class="text-sm text-wc-text">{{ $selClient->name }}</span>
                                        <span class="text-xs text-wc-text-tertiary">{{ $selClient->email }}</span>
                                        <button wire:click="$set('targetClientId', null)" class="ml-auto text-wc-text-tertiary hover:text-red-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            @endif

                            @if (strlen($clientSearch) >= 2 && !$targetClientId && count($searchClients) > 0)
                                <div class="absolute z-10 mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary shadow-lg max-h-48 overflow-y-auto">
                                    @foreach ($searchClients as $sc)
                                        <button wire:click="$set('targetClientId', {{ $sc->id }}); $set('clientSearch', '')"
                                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-wc-bg-tertiary transition-colors">
                                            <span class="font-medium text-wc-text">{{ $sc->name }}</span>
                                            <span class="text-xs text-wc-text-tertiary">{{ $sc->email }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Basic parameters --}}
                @if ($planType)
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                        <h4 class="text-sm font-medium text-wc-text mb-3">Parametros</h4>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div>
                                <label class="text-xs text-wc-text-tertiary">Duracion (semanas)</label>
                                <input wire:model="durationWeeks" type="number" min="1" max="52"
                                       class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                            </div>
                            <div>
                                <label class="text-xs text-wc-text-tertiary">Frecuencia (dias/sem)</label>
                                <input wire:model="frequency" type="number" min="1" max="7"
                                       class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                            </div>

                            @if ($planType === 'entrenamiento')
                                <div>
                                    <label class="text-xs text-wc-text-tertiary">Nivel</label>
                                    <select wire:model="experienceLevel"
                                            class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                                        <option value="principiante">Principiante</option>
                                        <option value="intermedio">Intermedio</option>
                                        <option value="avanzado">Avanzado</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-wc-text-tertiary">Meta</label>
                                    <select wire:model="trainingGoal"
                                            class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                                        <option value="hipertrofia">Hipertrofia</option>
                                        <option value="fuerza">Fuerza</option>
                                        <option value="resistencia">Resistencia</option>
                                        <option value="recomposicion">Recomposicion</option>
                                        <option value="perdida_grasa">Perdida de grasa</option>
                                    </select>
                                </div>
                            @endif

                            @if ($planType === 'nutricion')
                                <div>
                                    <label class="text-xs text-wc-text-tertiary">Calorias</label>
                                    <input wire:model="calorieTarget" type="number" min="1000" max="6000"
                                           class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                                </div>
                                <div>
                                    <label class="text-xs text-wc-text-tertiary">Comidas/dia</label>
                                    <input wire:model="mealsPerDay" type="number" min="2" max="8"
                                           class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                                </div>
                            @endif
                        </div>

                        @if ($planType === 'entrenamiento')
                            <div class="mt-3">
                                <label class="text-xs text-wc-text-tertiary">Lesiones o limitaciones (opcional)</label>
                                <input wire:model="injuries" type="text" placeholder="Ej: dolor de rodilla derecha..."
                                       class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                            </div>
                        @endif

                        @if ($planType === 'nutricion')
                            <div class="mt-3 grid grid-cols-3 gap-3">
                                <div>
                                    <label class="text-xs text-wc-text-tertiary">Proteina %</label>
                                    <input wire:model="proteinPct" type="number" min="10" max="60"
                                           class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                                </div>
                                <div>
                                    <label class="text-xs text-wc-text-tertiary">Carbohidratos %</label>
                                    <input wire:model="carbsPct" type="number" min="5" max="65"
                                           class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                                </div>
                                <div>
                                    <label class="text-xs text-wc-text-tertiary">Grasas %</label>
                                    <input wire:model="fatPct" type="number" min="10" max="60"
                                           class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="text-xs text-wc-text-tertiary">Restricciones dietarias (opcional)</label>
                                <input wire:model="dietaryRestrictions" type="text" placeholder="Ej: sin lactosa, vegetariano..."
                                       class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Continue button --}}
                @if ($planType)
                    <div class="flex justify-end">
                        <button wire:click="goToGenerate"
                                class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
                            Continuar a generacion
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>

        @elseif ($genStep === 2)
            {{-- STEP B: Generate + Preview + Save --}}
            <div class="space-y-6">
                {{-- Back button --}}
                <button wire:click="backToConfig"
                        class="inline-flex items-center gap-1 text-sm text-wc-text-secondary hover:text-wc-text transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Volver a configuracion
                </button>

                {{-- Config summary --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                    <h3 class="font-display text-lg text-wc-text">Resumen de configuracion</h3>
                    <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-4">
                        <div>
                            <span class="text-wc-text-tertiary">Tipo:</span>
                            <span class="ml-1 font-medium text-wc-text">{{ ucfirst($planType) }}</span>
                        </div>
                        <div>
                            <span class="text-wc-text-tertiary">Duracion:</span>
                            <span class="ml-1 font-medium text-wc-text">{{ $durationWeeks }} semanas</span>
                        </div>
                        <div>
                            <span class="text-wc-text-tertiary">Frecuencia:</span>
                            <span class="ml-1 font-medium text-wc-text">{{ $frequency }} dias/sem</span>
                        </div>
                        @if ($targetClientId)
                            @php $tc = $clients->firstWhere('id', $targetClientId); @endphp
                            <div>
                                <span class="text-wc-text-tertiary">Cliente:</span>
                                <span class="ml-1 font-medium text-wc-text">{{ $tc?->name ?? '-' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Generate / Preview --}}
                @if (!$planGenerated)
                    <div class="flex flex-col items-center rounded-card border border-wc-border bg-wc-bg-tertiary p-12">
                        @if ($isGenerating)
                            <div class="flex flex-col items-center gap-4">
                                <div class="h-12 w-12 rounded-full border-4 border-wc-accent/30 border-t-wc-accent animate-spin"></div>
                                <p class="text-sm text-wc-text-secondary">Generando plan con IA...</p>
                                <p class="text-xs text-wc-text-tertiary">Esto puede tomar 15-30 segundos</p>
                            </div>
                        @else
                            <svg class="h-16 w-16 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                            </svg>
                            <p class="mt-4 text-sm text-wc-text-secondary">Listo para generar. Presiona el boton para crear el plan.</p>
                            <button wire:click="generatePlan"
                                    class="mt-6 inline-flex items-center gap-2 rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                                </svg>
                                Generar Plan con IA
                            </button>
                        @endif
                    </div>
                @else
                    {{-- Plan preview --}}
                    <div class="rounded-card border border-green-500/30 bg-wc-bg-tertiary p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="font-display text-lg text-wc-text">Plan Generado</h3>
                            <div class="flex items-center gap-2">
                                @if (isset($generatedPlan['generated_by']) && $generatedPlan['generated_by'] === 'template')
                                    <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs text-amber-500">Template</span>
                                @else
                                    <span class="rounded-full bg-purple-500/10 px-2 py-0.5 text-xs text-purple-500">IA</span>
                                @endif
                                <button wire:click="toggleRawJson" class="text-xs text-wc-text-tertiary hover:text-wc-text transition-colors">
                                    {{ $showRawJson ? 'Vista formateada' : 'Ver JSON' }}
                                </button>
                            </div>
                        </div>

                        @if ($generationError)
                            <div class="mt-3 rounded-lg bg-red-500/10 border border-red-500/30 p-3 text-sm text-red-400">
                                {{ $generationError }}
                            </div>
                        @endif

                        <div class="mt-4 max-h-96 overflow-y-auto rounded-lg bg-wc-bg-secondary p-4">
                            @if ($showRawJson)
                                <textarea wire:model.blur="generatedPlanJson" wire:change="updateGeneratedJson"
                                          rows="20"
                                          class="w-full rounded border-0 bg-transparent font-mono text-xs text-wc-text focus:outline-none resize-y"></textarea>
                            @else
                                @if ($generatedPlan)
                                    @include('livewire.coach._plan-preview', ['plan' => $generatedPlan, 'reorderable' => true, 'wirePrefix' => ''])
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Save options --}}
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <h4 class="text-sm font-medium text-wc-text mb-3">Guardar plan</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs text-wc-text-tertiary">Nombre del template</label>
                                <input wire:model="templateName" type="text" placeholder="Nombre descriptivo..."
                                       class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                                @error('templateName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input wire:model="saveAsPublic" type="checkbox" class="rounded border-wc-border text-wc-accent focus:ring-wc-accent">
                                    <span class="text-sm text-wc-text">Hacer publico</span>
                                </label>
                            </div>

                            <div>
                                <label class="text-xs text-wc-text-tertiary mb-2 block">Modo de guardado</label>
                                <div class="flex items-center gap-3">
                                    <label class="inline-flex items-center gap-2 cursor-pointer rounded-lg border-2 px-4 py-2 transition-all {{ $saveMode === 'template_only' ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border' }}">
                                        <input wire:model="saveMode" type="radio" value="template_only" class="text-wc-accent focus:ring-wc-accent">
                                        <span class="text-sm text-wc-text">Solo template</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer rounded-lg border-2 px-4 py-2 transition-all {{ $saveMode === 'template_and_assign' ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border' }} {{ !$targetClientId ? 'opacity-50 cursor-not-allowed' : '' }}">
                                        <input wire:model="saveMode" type="radio" value="template_and_assign" {{ !$targetClientId ? 'disabled' : '' }} class="text-wc-accent focus:ring-wc-accent">
                                        <span class="text-sm text-wc-text">Template + Asignar</span>
                                    </label>
                                </div>
                                @if (!$targetClientId && $saveMode !== 'template_only')
                                    <p class="mt-1 text-xs text-amber-500">Selecciona un cliente para poder asignar el plan</p>
                                @endif
                            </div>

                            <div class="flex justify-end gap-3">
                                <button wire:click="generatePlan"
                                        class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                                    Regenerar
                                </button>
                                <button wire:click="saveGeneratedPlan"
                                        class="rounded-lg bg-wc-accent px-6 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
                                    Guardar Plan
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif

    {{-- ═══════════════════════════════════════ --}}
    {{--  MODALS                                 --}}
    {{-- ═══════════════════════════════════════ --}}

    {{-- Template Create/Edit Modal --}}
    @if ($showTemplateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" wire:click.self="$set('showTemplateModal', false)">
            <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-xl text-wc-text">{{ $editingTemplate ? 'Editar Template' : 'Nuevo Template' }}</h3>
                    <button wire:click="$set('showTemplateModal', false)" class="rounded-md p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-tertiary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-wc-text">Nombre *</label>
                        <input wire:model="tplName" type="text" placeholder="Nombre del template..."
                               class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                        @error('tplName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-wc-text">Tipo *</label>
                            <select wire:model="tplPlanType"
                                    class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                                <option value="entrenamiento">Entrenamiento</option>
                                <option value="nutricion">Nutricion</option>
                                <option value="habitos">Habitos</option>
                                <option value="suplementacion">Suplementacion</option>
                                <option value="ciclo">Ciclo</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-wc-text">Metodologia</label>
                            <input wire:model="tplMethodology" type="text" placeholder="Ej: Progressive Overload"
                                   class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-wc-text">Descripcion</label>
                        <textarea wire:model="tplDescription" rows="2" placeholder="Descripcion breve..."
                                  class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-y"></textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-wc-text">Contenido JSON</label>
                        <textarea wire:model="tplContentJson" rows="10" placeholder='{"plan_type": "entrenamiento", ...}'
                                  class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 font-mono text-xs text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-y"></textarea>
                        @error('tplContentJson') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input wire:model="tplIsPublic" type="checkbox" class="rounded border-wc-border text-wc-accent focus:ring-wc-accent">
                        <span class="text-sm text-wc-text">Hacer publico (visible para otros coaches)</span>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showTemplateModal', false)"
                            class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="saveTemplate"
                            wire:loading.attr="disabled"
                            class="btn-press rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveTemplate">{{ $editingTemplate ? 'Actualizar' : 'Crear' }}</span>
                        <span wire:loading wire:target="saveTemplate" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Preview Modal --}}
    @if ($showPreviewModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" wire:click.self="$set('showPreviewModal', false)">
            <div class="w-full max-w-3xl max-h-[85vh] overflow-y-auto rounded-xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-xl text-wc-text">{{ $previewTitle }}</h3>
                    <button wire:click="$set('showPreviewModal', false)" class="rounded-md p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-tertiary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="rounded-lg bg-wc-bg-tertiary p-4 max-h-[70vh] overflow-y-auto">
                    @if ($previewContent)
                        @include('livewire.coach._plan-preview', ['plan' => $previewContent, 'reorderable' => true, 'wirePrefix' => 'preview'])
                    @else
                        <p class="text-sm text-wc-text-tertiary">Sin contenido</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Assigned Content Modal --}}
    @if ($showAssignedContentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" wire:click.self="$set('showAssignedContentModal', false)">
            <div class="w-full max-w-3xl max-h-[85vh] overflow-y-auto rounded-xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-xl text-wc-text">{{ $assignedContentTitle }}</h3>
                    <button wire:click="$set('showAssignedContentModal', false)" class="rounded-md p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-tertiary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="rounded-lg bg-wc-bg-tertiary p-4 max-h-[70vh] overflow-y-auto">
                    @if ($assignedContentPreview)
                        @include('livewire.coach._plan-preview', ['plan' => $assignedContentPreview, 'reorderable' => false, 'wirePrefix' => ''])
                    @else
                        <p class="text-sm text-wc-text-tertiary">Sin contenido</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" wire:click.self="$set('showDeleteConfirm', false)">
            <div class="w-full max-w-sm rounded-xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10">
                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </div>
                <h3 class="mt-4 font-display text-lg text-wc-text">Eliminar Template</h3>
                <p class="mt-2 text-sm text-wc-text-secondary">Esta accion no se puede deshacer. El template sera eliminado permanentemente.</p>
                <div class="mt-6 flex gap-3">
                    <button wire:click="$set('showDeleteConfirm', false)"
                            class="flex-1 rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="deleteTemplate"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Assign Plan Modal --}}
    @if ($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" wire:click.self="$set('showAssignModal', false)">
            <div class="w-full max-w-md rounded-xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-xl text-wc-text">Asignar Plan</h3>
                    <button wire:click="$set('showAssignModal', false)" class="rounded-md p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-tertiary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-wc-text">Cliente *</label>
                        <select wire:model="assignClientId"
                                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                            <option value="">Seleccionar cliente...</option>
                            @foreach ($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                            @endforeach
                        </select>
                        @error('assignClientId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-wc-text">Template *</label>
                        <select wire:model="assignTemplateId"
                                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                            <option value="">Seleccionar template...</option>
                            @foreach ($assignableTemplates as $at)
                                <option value="{{ $at->id }}">{{ $at->name }} ({{ ucfirst($at->plan_type) }})</option>
                            @endforeach
                        </select>
                        @error('assignTemplateId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showAssignModal', false)"
                            class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="assignPlan"
                            class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
                        Asignar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
