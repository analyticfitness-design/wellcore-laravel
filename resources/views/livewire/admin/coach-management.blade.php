<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text">GESTION DE COACHES</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Administra coaches, perfiles y asignaciones de clientes.</p>
        </div>
        <button wire:click="openCreate"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
            + Nuevo Coach
        </button>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="font-data text-2xl font-bold text-wc-text">{{ $stats['total'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
        </div>
        <div class="rounded-xl border border-sky-500/30 bg-sky-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-sky-400">{{ $stats['coaches'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Coaches</p>
        </div>
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-emerald-400">{{ $stats['with_profile'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Con Perfil</p>
        </div>
        <div class="rounded-xl border border-violet-500/30 bg-violet-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-violet-400">{{ $stats['clients'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Clientes Asignados</p>
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
                   placeholder="Buscar por nombre o usuario..."
                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
        </div>

        {{-- Role filter --}}
        <select wire:model.live="roleFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Todos los roles</option>
            <option value="superadmin">Superadmin</option>
            <option value="admin">Admin</option>
            <option value="coach">Coach</option>
            <option value="jefe">Jefe</option>
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
                                Coach
                                @if($sortBy === 'name')
                                    <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="hidden px-4 py-3 text-left sm:table-cell">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Rol</span>
                        </th>
                        <th class="hidden px-4 py-3 text-left md:table-cell">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Ciudad</span>
                        </th>
                        <th class="hidden px-4 py-3 text-center lg:table-cell">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Clientes</span>
                        </th>
                        <th class="hidden px-4 py-3 text-left lg:table-cell">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Especialidades</span>
                        </th>
                        <th class="hidden px-4 py-3 text-center sm:table-cell">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Publico</span>
                        </th>
                        <th class="px-4 py-3 text-right">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-wc-border">
                    @forelse($admins as $admin)
                        @php
                            $profile     = $admin->coachProfile;
                            $roleVal     = $admin->role instanceof \App\Enums\UserRole ? $admin->role->value : $admin->role;
                            $roleColors  = [
                                'superadmin' => 'bg-red-500/10 text-red-400',
                                'admin'      => 'bg-violet-500/10 text-violet-400',
                                'coach'      => 'bg-sky-500/10 text-sky-400',
                                'jefe'       => 'bg-amber-500/10 text-amber-400',
                            ];
                            $clientCount = $clientCounts[$admin->id] ?? 0;
                            $specs       = $profile && is_array($profile->specializations) ? $profile->specializations : [];
                        @endphp
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50" wire:key="coach-{{ $admin->id }}">
                            {{-- Avatar + Name --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-{{ $roleVal === 'coach' ? 'sky' : ($roleVal === 'superadmin' ? 'red' : 'violet') }}-500/20">
                                        <span class="text-sm font-semibold text-{{ $roleVal === 'coach' ? 'sky' : ($roleVal === 'superadmin' ? 'red' : 'violet') }}-400">
                                            {{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-wc-text">{{ $admin->name ?? '—' }}</div>
                                        <div class="text-xs text-wc-text-tertiary">{{ $admin->username }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Role badge --}}
                            <td class="hidden px-4 py-3 sm:table-cell">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $roleColors[$roleVal] ?? 'bg-wc-bg-secondary text-wc-text-secondary' }}">
                                    {{ $admin->role instanceof \App\Enums\UserRole ? $admin->role->label() : ucfirst($roleVal) }}
                                </span>
                            </td>

                            {{-- City --}}
                            <td class="hidden px-4 py-3 md:table-cell">
                                <span class="text-xs text-wc-text-secondary">{{ $profile->city ?? '—' }}</span>
                            </td>

                            {{-- Clients count --}}
                            <td class="hidden px-4 py-3 text-center lg:table-cell">
                                <span class="font-data text-sm font-semibold text-wc-text">{{ $clientCount }}</span>
                            </td>

                            {{-- Specializations --}}
                            <td class="hidden px-4 py-3 lg:table-cell">
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($specs, 0, 3) as $spec)
                                        <span class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] text-wc-text-secondary">{{ $spec }}</span>
                                    @endforeach
                                    @if(count($specs) > 3)
                                        <span class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] text-wc-text-tertiary">+{{ count($specs) - 3 }}</span>
                                    @endif
                                    @if(empty($specs))
                                        <span class="text-xs text-wc-text-tertiary">—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Public visible --}}
                            <td class="hidden px-4 py-3 text-center sm:table-cell">
                                @if($profile)
                                    <button wire:click="toggleVisibility({{ $admin->id }})"
                                            class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold transition-colors
                                                   {{ $profile->public_visible ? 'bg-emerald-500/10 text-emerald-400' : 'bg-wc-bg-secondary text-wc-text-tertiary' }}">
                                        {{ $profile->public_visible ? 'Visible' : 'Oculto' }}
                                    </button>
                                @else
                                    <span class="text-xs text-wc-text-tertiary">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button wire:click="openView({{ $admin->id }})"
                                            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary hover:border-sky-500 hover:text-sky-400 transition-colors"
                                            title="Ver detalle">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>
                                    @if($profile || $roleVal === 'coach')
                                        <button wire:click="openEdit({{ $admin->id }})"
                                                class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary hover:border-wc-accent hover:text-wc-accent transition-colors"
                                                title="Editar perfil">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-wc-text-tertiary">
                                No se encontraron coaches con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($admins->hasPages())
        <div class="flex justify-center">
            {{ $admins->links() }}
        </div>
    @endif

    {{-- ==================== CREATE MODAL ==================== --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeCreate"></div>
            <div class="relative z-10 w-full max-w-lg rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="mb-5 flex items-start justify-between">
                    <h2 class="font-display text-2xl tracking-wide text-wc-text">NUEVO COACH</h2>
                    <button wire:click="closeCreate" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="createCoach" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Nombre <span class="text-wc-accent">*</span></label>
                        <input type="text" wire:model="newName" placeholder="Nombre completo"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        @error('newName') <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Usuario <span class="text-wc-accent">*</span></label>
                        <input type="text" wire:model="newUsername" placeholder="usuario_login"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        @error('newUsername') <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Contrasena <span class="text-wc-accent">*</span></label>
                        <input type="password" wire:model="newPassword" placeholder="Minimo 8 caracteres"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        @error('newPassword') <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Rol</label>
                        <select wire:model="newRole"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                            <option value="coach">Coach</option>
                            <option value="admin">Admin</option>
                            <option value="jefe">Jefe</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="closeCreate"
                                class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="btn-press flex-1 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors"
                                wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-not-allowed">
                            <span wire:loading.remove wire:target="createCoach">Crear Coach</span>
                            <span wire:loading wire:target="createCoach" class="inline-flex items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Creando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ==================== EDIT PROFILE MODAL ==================== --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeEdit"></div>
            <div class="relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="mb-5 flex items-start justify-between">
                    <h2 class="font-display text-2xl tracking-wide text-wc-text">EDITAR PERFIL</h2>
                    <button wire:click="closeEdit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveProfile" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Ciudad</label>
                            <input type="text" wire:model="editCity" placeholder="Monterrey"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                            @error('editCity') <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Experiencia</label>
                            <input type="text" wire:model="editExperience" placeholder="5 anos, certificado NSCA"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Bio</label>
                        <textarea wire:model="editBio" rows="3" placeholder="Descripcion breve del coach..."
                                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"></textarea>
                        @error('editBio') <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Especialidades <span class="text-wc-text-tertiary text-[10px] normal-case">(separadas por coma)</span></label>
                        <input type="text" wire:model="editSpecializations" placeholder="Fuerza, Hipertrofia, Nutricion deportiva"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">WhatsApp</label>
                            <input type="text" wire:model="editWhatsapp" placeholder="+52 811 234 5678"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Instagram</label>
                            <input type="text" wire:model="editInstagram" placeholder="@coach_fitness"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Codigo Referido</label>
                            <input type="text" wire:model="editReferralCode" placeholder="COACH2026"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Comision Referido (%)</label>
                            <input type="number" step="0.01" min="0" max="100" wire:model="editReferralComm" placeholder="15.00"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        </div>
                    </div>

                    {{-- Public visible toggle --}}
                    <div class="flex items-center gap-3">
                        <button type="button"
                                wire:click="$toggle('editPublicVisible')"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200
                                       {{ $editPublicVisible ? 'bg-emerald-500' : 'bg-wc-bg-tertiary' }}"
                                role="switch"
                                aria-checked="{{ $editPublicVisible ? 'true' : 'false' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition-transform duration-200
                                         {{ $editPublicVisible ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                        <span class="text-sm text-wc-text-secondary">Perfil publico visible</span>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="closeEdit"
                                class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="btn-press flex-1 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors"
                                wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-not-allowed">
                            <span wire:loading.remove wire:target="saveProfile">Guardar Perfil</span>
                            <span wire:loading wire:target="saveProfile" class="inline-flex items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ==================== VIEW DETAIL MODAL ==================== --}}
    @if($showViewModal && $viewingAdmin)
        @php
            $vp       = $viewingAdmin->coachProfile;
            $vRoleVal = $viewingAdmin->role instanceof \App\Enums\UserRole ? $viewingAdmin->role->value : $viewingAdmin->role;
            $vRoleColors = [
                'superadmin' => 'bg-red-500/10 text-red-400',
                'admin'      => 'bg-violet-500/10 text-violet-400',
                'coach'      => 'bg-sky-500/10 text-sky-400',
                'jefe'       => 'bg-amber-500/10 text-amber-400',
            ];
            $vSpecs = $vp && is_array($vp->specializations) ? $vp->specializations : [];
            $vClients = $clientCounts[$viewingAdmin->id] ?? 0;
        @endphp
        <div class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeView"></div>
            <div class="relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="mb-5 flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-sky-500/20">
                            <span class="font-display text-2xl text-sky-400">{{ strtoupper(substr($viewingAdmin->name ?? 'A', 0, 1)) }}</span>
                        </div>
                        <div>
                            <h2 class="font-display text-2xl tracking-wide text-wc-text">{{ strtoupper($viewingAdmin->name ?? 'SIN NOMBRE') }}</h2>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-wc-text-tertiary">@{{ $viewingAdmin->username }}</span>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $vRoleColors[$vRoleVal] ?? '' }}">
                                    {{ $viewingAdmin->role instanceof \App\Enums\UserRole ? $viewingAdmin->role->label() : ucfirst($vRoleVal) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <button wire:click="closeView" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Stats mini --}}
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                        <p class="font-data text-lg font-bold text-wc-text">{{ $vClients }}</p>
                        <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Clientes</p>
                    </div>
                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                        <p class="font-data text-lg font-bold text-wc-text">{{ $vp ? ($vp->referral_commission ?? '0') . '%' : '—' }}</p>
                        <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Comision</p>
                    </div>
                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                        <p class="font-data text-lg font-bold {{ $vp && $vp->public_visible ? 'text-emerald-400' : 'text-wc-text-tertiary' }}">
                            {{ $vp && $vp->public_visible ? 'SI' : 'NO' }}
                        </p>
                        <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Publico</p>
                    </div>
                </div>

                @if($vp)
                    <div class="space-y-4">
                        @if($vp->bio)
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                                <h4 class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Bio</h4>
                                <p class="text-sm text-wc-text leading-relaxed">{{ $vp->bio }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                                <h4 class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Ciudad</h4>
                                <p class="text-sm text-wc-text">{{ $vp->city ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                                <h4 class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Experiencia</h4>
                                <p class="text-sm text-wc-text">{{ $vp->experience ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                                <h4 class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">WhatsApp</h4>
                                <p class="text-sm text-wc-text">{{ $vp->whatsapp ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                                <h4 class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Instagram</h4>
                                <p class="text-sm text-wc-text">{{ $vp->instagram ?? '—' }}</p>
                            </div>
                        </div>

                        @if(!empty($vSpecs))
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                                <h4 class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Especialidades</h4>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($vSpecs as $spec)
                                        <span class="rounded-full bg-sky-500/10 px-2.5 py-1 text-xs font-medium text-sky-400">{{ $spec }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($vp->referral_code)
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                                <h4 class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Codigo Referido</h4>
                                <p class="font-mono text-sm font-semibold text-wc-accent">{{ $vp->referral_code }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                        <p class="text-sm text-wc-text-tertiary">Este admin no tiene perfil de coach configurado.</p>
                        @if($vRoleVal === 'coach')
                            <button wire:click="closeView" x-on:click="$nextTick(() => $wire.openEdit({{ $viewingAdmin->id }}))"
                                    class="mt-3 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                                Crear Perfil
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
