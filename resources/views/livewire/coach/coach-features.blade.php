<div class="space-y-6" x-data="{ tab: @entangle('activeTab') }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl sm:text-3xl tracking-wide text-wc-text">HERRAMIENTAS DEL COACH</h1>
            <p class="text-sm text-wc-text-secondary mt-1">Pods, disponibilidad, audios y revision de video check-ins</p>
        </div>
    </div>

    {{-- Success toast --}}
    @if ($successMessage)
        <div class="flex items-center gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false; $wire.dismissSuccess() }, 4000)"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ $successMessage }}</span>
        </div>
    @endif

    {{-- Tab bar --}}
    <div class="flex gap-1 rounded-xl bg-wc-bg-secondary border border-wc-border p-1 overflow-x-auto">
        @foreach ([
            'pods' => 'Pods',
            'availability' => 'Disponibilidad',
            'audio' => 'Audios',
            'video_checkins' => 'Video Check-ins',
        ] as $key => $label)
            <button wire:click="switchTab('{{ $key }}')"
                    class="flex-1 min-w-[120px] rounded-lg px-4 py-2.5 text-sm font-medium transition-all whitespace-nowrap
                           {{ $activeTab === $key ? 'bg-wc-accent text-white shadow-lg shadow-wc-accent/25' : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{--  PODS TAB                                              --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'pods')
        <div class="space-y-6">
            @if ($viewingPodId && $viewingPod)
                {{-- Pod Detail View --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button wire:click="closePodView" class="flex h-8 w-8 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                            </button>
                            <div>
                                <h3 class="font-display text-xl tracking-wide text-wc-text">{{ $viewingPod['name'] }}</h3>
                                <p class="text-xs text-wc-text-secondary">{{ $viewingPod['member_count'] }}/{{ $viewingPod['max_members'] }} miembros</p>
                            </div>
                        </div>
                        <button wire:click="openAddMember" class="flex items-center gap-2 rounded-lg bg-wc-accent/10 px-3 py-2 text-sm font-medium text-wc-accent hover:bg-wc-accent/20 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                            Agregar
                        </button>
                    </div>

                    @if ($viewingPod['description'])
                        <p class="text-sm text-wc-text-secondary">{{ $viewingPod['description'] }}</p>
                    @endif

                    {{-- Members list --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary mb-3">Miembros</h4>
                        @if (count($viewingPod['members']) === 0)
                            <p class="text-sm text-wc-text-secondary">Sin miembros aun. Agrega clientes al pod.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($viewingPod['members'] as $member)
                                    <div class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-wc-accent/20 text-xs font-semibold text-wc-accent">{{ $member['initial'] }}</div>
                                            <span class="text-sm text-wc-text">{{ $member['name'] }}</span>
                                        </div>
                                        <button wire:click="removeMember({{ $member['id'] }})" wire:confirm="Remover a {{ $member['name'] }} del pod?" class="text-wc-text-tertiary hover:text-red-400 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Messages feed --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary mb-3">Mensajes del Pod</h4>
                        <div class="rounded-xl border border-wc-border bg-wc-bg max-h-80 overflow-y-auto p-4 space-y-3">
                            @forelse ($podMessages as $msg)
                                <div class="flex items-start gap-2 {{ $msg['is_coach'] ? 'flex-row-reverse' : '' }}">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $msg['is_coach'] ? 'bg-wc-accent/20 text-wc-accent' : 'bg-zinc-600/30 text-zinc-400' }} text-xs font-semibold">
                                        {{ $msg['sender_initial'] }}
                                    </div>
                                    <div class="{{ $msg['is_coach'] ? 'text-right' : '' }} max-w-[80%]">
                                        <p class="text-[10px] font-semibold text-wc-text-tertiary">{{ $msg['sender_name'] }}</p>
                                        <div class="mt-0.5 rounded-lg px-3 py-2 text-sm {{ $msg['is_coach'] ? 'bg-wc-accent/10 text-wc-text' : 'bg-wc-bg-tertiary text-wc-text' }}">
                                            {{ $msg['message'] }}
                                        </div>
                                        <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ $msg['created_at'] }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-sm text-wc-text-secondary py-8">Sin mensajes aun.</p>
                            @endforelse
                        </div>

                        {{-- Send message --}}
                        <div class="mt-3 flex gap-2">
                            <input type="text" wire:model="podMessageText" wire:keydown.enter="sendPodMessage"
                                   placeholder="Escribe un mensaje al pod..."
                                   class="flex-1 rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                            <button wire:click="sendPodMessage"
                                    class="flex items-center gap-1 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                                Enviar
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Add Member Modal --}}
                @if ($showAddMemberModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="closeAddMember">
                        <div class="w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="font-display text-lg tracking-wide text-wc-text">AGREGAR MIEMBRO</h3>
                                <button wire:click="closeAddMember" class="text-wc-text-secondary hover:text-wc-text">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <input type="text" wire:model.live.debounce.300ms="memberSearch" placeholder="Buscar cliente..."
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">

                            <div class="max-h-60 overflow-y-auto space-y-1">
                                @forelse ($availableClients as $client)
                                    <button wire:click="addMemberToPod({{ $client['id'] }})"
                                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-wc-accent/20 text-xs font-semibold text-wc-accent">{{ $client['initial'] }}</div>
                                        <span>{{ $client['name'] }}</span>
                                    </button>
                                @empty
                                    <p class="text-center text-sm text-wc-text-secondary py-4">No hay clientes disponibles.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
            @else
                {{-- Pods List --}}
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-wc-text">Accountability Pods</h2>
                    <button wire:click="openCreatePod"
                            class="flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors shadow-lg shadow-wc-accent/25">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Crear Pod
                    </button>
                </div>

                @if ($pods->isEmpty())
                    <div class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-secondary p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                        <p class="mt-4 text-sm text-wc-text-secondary">No tienes pods creados aun.</p>
                        <p class="text-xs text-wc-text-tertiary mt-1">Crea un pod para agrupar clientes y motivar la responsabilidad.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($pods as $pod)
                            <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5 space-y-4 hover:border-wc-accent/30 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-semibold text-wc-text">{{ $pod['name'] }}</h3>
                                        @if ($pod['description'])
                                            <p class="mt-1 text-xs text-wc-text-secondary line-clamp-2">{{ $pod['description'] }}</p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $pod['is_active'] ? 'bg-emerald-500/10 text-emerald-400' : 'bg-zinc-500/10 text-zinc-400' }}">
                                        {{ $pod['is_active'] ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-4 text-xs text-wc-text-secondary">
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                                        {{ $pod['member_count'] }}/{{ $pod['max_members'] }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        {{ $pod['last_activity'] }}
                                    </span>
                                </div>

                                {{-- Member avatars --}}
                                @if ($pod['member_count'] > 0)
                                    <div class="flex -space-x-2">
                                        @foreach (array_slice($pod['members']->toArray(), 0, 5) as $m)
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-wc-bg-secondary bg-wc-accent/20 text-[10px] font-semibold text-wc-accent" title="{{ $m['name'] }}">{{ $m['initial'] }}</div>
                                        @endforeach
                                        @if ($pod['member_count'] > 5)
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-wc-bg-secondary bg-wc-bg-tertiary text-[10px] font-semibold text-wc-text-secondary">+{{ $pod['member_count'] - 5 }}</div>
                                        @endif
                                    </div>
                                @endif

                                <div class="flex gap-2 pt-1">
                                    <button wire:click="viewPod({{ $pod['id'] }})"
                                            class="flex-1 flex items-center justify-center gap-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-xs font-medium text-wc-text hover:bg-wc-bg transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        Ver
                                    </button>
                                    <button wire:click="openEditPod({{ $pod['id'] }})"
                                            class="flex items-center justify-center gap-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-xs font-medium text-wc-text hover:bg-wc-bg transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Create Pod Modal --}}
                @if ($showCreatePodModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="closeCreatePod">
                        <div class="w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 space-y-5">
                            <div class="flex items-center justify-between">
                                <h3 class="font-display text-lg tracking-wide text-wc-text">CREAR POD</h3>
                                <button wire:click="closeCreatePod" class="text-wc-text-secondary hover:text-wc-text"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Nombre del Pod *</label>
                                    <input type="text" wire:model="podName" placeholder="Ej: Grupo Elite Lunes"
                                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                    @error('podName') <span class="text-xs text-red-400 mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Descripcion</label>
                                    <textarea wire:model="podDescription" rows="3" placeholder="Descripcion del pod..."
                                              class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none resize-none"></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Max. Miembros</label>
                                    <input type="number" wire:model="podMaxMembers" min="2" max="50"
                                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                    @error('podMaxMembers') <span class="text-xs text-red-400 mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button wire:click="closeCreatePod" class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm font-medium text-wc-text hover:bg-wc-bg transition-colors">Cancelar</button>
                                <button wire:click="savePod" class="flex-1 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors">Crear Pod</button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Edit Pod Modal --}}
                @if ($showEditPodModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="closeEditPod">
                        <div class="w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 space-y-5">
                            <div class="flex items-center justify-between">
                                <h3 class="font-display text-lg tracking-wide text-wc-text">EDITAR POD</h3>
                                <button wire:click="closeEditPod" class="text-wc-text-secondary hover:text-wc-text"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Nombre del Pod *</label>
                                    <input type="text" wire:model="editPodName"
                                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Descripcion</label>
                                    <textarea wire:model="editPodDescription" rows="3"
                                              class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none resize-none"></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Max. Miembros</label>
                                    <input type="number" wire:model="editPodMaxMembers" min="2" max="50"
                                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                </div>

                                <div class="flex items-center gap-3">
                                    <label class="text-xs font-semibold text-wc-text-secondary">Activo</label>
                                    <button wire:click="$toggle('editPodIsActive')"
                                            class="relative h-6 w-11 rounded-full transition-colors {{ $editPodIsActive ? 'bg-emerald-500' : 'bg-zinc-600' }}">
                                        <span class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white transition-transform {{ $editPodIsActive ? 'translate-x-5' : '' }}"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button wire:click="closeEditPod" class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm font-medium text-wc-text hover:bg-wc-bg transition-colors">Cancelar</button>
                                <button wire:click="updatePod" class="flex-1 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors">Guardar</button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{--  AVAILABILITY TAB                                      --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'availability')
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-wc-text">Disponibilidad Semanal</h2>
                <button wire:click="openAddSlot"
                        class="flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors shadow-lg shadow-wc-accent/25">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Agregar Horario
                </button>
            </div>

            {{-- Weekly grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3">
                @foreach ($weeklySlots as $day)
                    <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-4 min-h-[180px]">
                        <div class="text-center mb-3">
                            <p class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">{{ $day['short'] }}</p>
                            <p class="text-sm font-medium text-wc-text">{{ $day['name'] }}</p>
                        </div>

                        <div class="space-y-2">
                            @if (empty($day['slots']))
                                <p class="text-center text-xs text-wc-text-tertiary py-4">Sin horarios</p>
                            @else
                                @foreach ($day['slots'] as $slot)
                                    <div class="group relative rounded-lg px-2.5 py-2 text-xs {{ $slot['is_active'] ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-zinc-500/10 border border-zinc-500/20 text-zinc-400' }}">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono font-medium">{{ $slot['time_start'] }} - {{ $slot['time_end'] }}</span>
                                            <div class="hidden group-hover:flex items-center gap-1">
                                                <button wire:click="toggleSlotActive({{ $slot['id'] }})" class="hover:text-wc-text transition-colors" title="{{ $slot['is_active'] ? 'Desactivar' : 'Activar' }}">
                                                    @if ($slot['is_active'])
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" /></svg>
                                                    @else
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                                                    @endif
                                                </button>
                                                <button wire:click="openEditSlot({{ $slot['id'] }})" class="hover:text-wc-text transition-colors">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                                </button>
                                                <button wire:click="confirmDeleteSlot({{ $slot['id'] }})" class="hover:text-red-400 transition-colors">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Slot summary --}}
            <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-4">
                <div class="flex items-center gap-6 text-sm">
                    <span class="text-wc-text-secondary">Total: <span class="font-semibold text-wc-text">{{ $slots->count() }}</span> horarios</span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-wc-text-secondary">Activo: <span class="font-semibold text-emerald-400">{{ $slots->where('is_active', true)->count() }}</span></span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-zinc-500"></span>
                        <span class="text-wc-text-secondary">Inactivo: <span class="font-semibold text-zinc-400">{{ $slots->where('is_active', false)->count() }}</span></span>
                    </span>
                </div>
            </div>

            {{-- Delete confirmation --}}
            @if ($deletingSlotId)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="cancelDeleteSlot">
                    <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 text-center space-y-4">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </div>
                        <p class="text-sm text-wc-text">Eliminar este horario?</p>
                        <div class="flex gap-3">
                            <button wire:click="cancelDeleteSlot" class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm font-medium text-wc-text hover:bg-wc-bg transition-colors">Cancelar</button>
                            <button wire:click="deleteSlot" class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 transition-colors">Eliminar</button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Add/Edit Slot Modal --}}
            @if ($showSlotModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="closeSlotModal">
                    <div class="w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 space-y-5">
                        <div class="flex items-center justify-between">
                            <h3 class="font-display text-lg tracking-wide text-wc-text">{{ $editingSlotId ? 'EDITAR HORARIO' : 'AGREGAR HORARIO' }}</h3>
                            <button wire:click="closeSlotModal" class="text-wc-text-secondary hover:text-wc-text"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg></button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Dia de la Semana</label>
                                <select wire:model="slotDay"
                                        class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                    <option value="1">Lunes</option>
                                    <option value="2">Martes</option>
                                    <option value="3">Miercoles</option>
                                    <option value="4">Jueves</option>
                                    <option value="5">Viernes</option>
                                    <option value="6">Sabado</option>
                                    <option value="7">Domingo</option>
                                </select>
                                @error('slotDay') <span class="text-xs text-red-400 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Hora Inicio</label>
                                    <input type="time" wire:model="slotStart"
                                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                    @error('slotStart') <span class="text-xs text-red-400 mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Hora Fin</label>
                                    <input type="time" wire:model="slotEnd"
                                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                    @error('slotEnd') <span class="text-xs text-red-400 mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button wire:click="closeSlotModal" class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm font-medium text-wc-text hover:bg-wc-bg transition-colors">Cancelar</button>
                            <button wire:click="saveSlot" class="flex-1 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors">{{ $editingSlotId ? 'Guardar' : 'Agregar' }}</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{--  AUDIO TAB                                             --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'audio')
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-wc-text">Audios del Coach</h2>
                <button wire:click="openCreateAudio"
                        class="flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors shadow-lg shadow-wc-accent/25">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nuevo Audio
                </button>
            </div>

            @if ($audios->isEmpty())
                <div class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-secondary p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" /></svg>
                    <p class="mt-4 text-sm text-wc-text-secondary">No tienes audios creados aun.</p>
                    <p class="text-xs text-wc-text-tertiary mt-1">Sube audios motivacionales, instrucciones de ejercicios o contenido para tus clientes.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($audios as $audio)
                        <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5 space-y-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-wc-text truncate">{{ $audio['title'] }}</h3>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">{{ $audio['category'] }}</span>
                                        <span class="font-mono text-xs text-wc-text-tertiary">{{ $audio['duration_fmt'] }}</span>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $audio['is_active'] ? 'bg-emerald-500/10 text-emerald-400' : 'bg-zinc-500/10 text-zinc-400' }}">
                                    {{ $audio['is_active'] ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            {{-- Audio player --}}
                            <div class="rounded-lg bg-wc-bg p-3">
                                <audio controls preload="none" class="w-full h-8" style="max-height: 32px;">
                                    <source src="{{ $audio['audio_url'] }}" type="audio/mpeg">
                                    Tu navegador no soporta el reproductor de audio.
                                </audio>
                            </div>

                            @if ($audio['created_at'])
                                <p class="text-[10px] text-wc-text-tertiary">Creado: {{ $audio['created_at'] }}</p>
                            @endif

                            <div class="flex gap-2">
                                <button wire:click="toggleAudioActive({{ $audio['id'] }})"
                                        class="flex items-center gap-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text hover:bg-wc-bg transition-colors"
                                        title="{{ $audio['is_active'] ? 'Desactivar' : 'Activar' }}">
                                    @if ($audio['is_active'])
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                    @else
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    @endif
                                </button>
                                <button wire:click="openEditAudio({{ $audio['id'] }})"
                                        class="flex items-center gap-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text hover:bg-wc-bg transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                    Editar
                                </button>
                                <button wire:click="confirmDeleteAudio({{ $audio['id'] }})"
                                        class="flex items-center gap-1 rounded-lg border border-red-500/20 bg-red-500/5 px-3 py-1.5 text-xs font-medium text-red-400 hover:bg-red-500/10 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Delete Audio Confirm --}}
            @if ($deletingAudioId)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="cancelDeleteAudio">
                    <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 text-center space-y-4">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </div>
                        <p class="text-sm text-wc-text">Eliminar este audio?</p>
                        <div class="flex gap-3">
                            <button wire:click="cancelDeleteAudio" class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm font-medium text-wc-text hover:bg-wc-bg transition-colors">Cancelar</button>
                            <button wire:click="deleteAudio" class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 transition-colors">Eliminar</button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Create/Edit Audio Modal --}}
            @if ($showAudioModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" wire:click.self="closeAudioModal">
                    <div class="w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 space-y-5">
                        <div class="flex items-center justify-between">
                            <h3 class="font-display text-lg tracking-wide text-wc-text">{{ $editingAudioId ? 'EDITAR AUDIO' : 'NUEVO AUDIO' }}</h3>
                            <button wire:click="closeAudioModal" class="text-wc-text-secondary hover:text-wc-text"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg></button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Titulo *</label>
                                <input type="text" wire:model="audioTitle" placeholder="Ej: Motivacion lunes"
                                       class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                @error('audioTitle') <span class="text-xs text-red-400 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-wc-text-secondary mb-1">URL del Audio *</label>
                                <input type="url" wire:model="audioUrl" placeholder="https://..."
                                       class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                @error('audioUrl') <span class="text-xs text-red-400 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Duracion (seg)</label>
                                    <input type="number" wire:model="audioDuration" min="0"
                                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-wc-text-secondary mb-1">Categoria *</label>
                                    <select wire:model="audioCategory"
                                            class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none">
                                        <option value="general">General</option>
                                        <option value="motivacion">Motivacion</option>
                                        <option value="instrucciones">Instrucciones</option>
                                        <option value="meditacion">Meditacion</option>
                                        <option value="nutricion">Nutricion</option>
                                        <option value="recuperacion">Recuperacion</option>
                                    </select>
                                    @error('audioCategory') <span class="text-xs text-red-400 mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button wire:click="closeAudioModal" class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm font-medium text-wc-text hover:bg-wc-bg transition-colors">Cancelar</button>
                            <button wire:click="saveAudio" class="flex-1 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors">{{ $editingAudioId ? 'Guardar' : 'Crear Audio' }}</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{--  VIDEO CHECK-INS TAB                                   --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'video_checkins')
        <div class="space-y-6">
            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-4 text-center">
                    <p class="font-display text-2xl text-wc-text">{{ $checkinStats['total'] }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary mt-1">Total</p>
                </div>
                <div class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 text-center">
                    <p class="font-display text-2xl text-amber-400">{{ $checkinStats['pending'] }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-amber-400/60 mt-1">Pendientes</p>
                </div>
                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-4 text-center">
                    <p class="font-display text-2xl text-emerald-400">{{ $checkinStats['reviewed'] }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-emerald-400/60 mt-1">Revisados</p>
                </div>
                <div class="rounded-2xl border border-blue-500/20 bg-blue-500/5 p-4 text-center">
                    <p class="font-display text-2xl text-blue-400">{{ $checkinStats['ai_reviewed'] }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-blue-400/60 mt-1">AI Review</p>
                </div>
            </div>

            {{-- Filter --}}
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold text-wc-text-secondary">Filtrar:</span>
                @foreach ([
                    'all' => 'Todos',
                    'pending' => 'Pendientes',
                    'coach_reviewed' => 'Revisados',
                    'ai_reviewed' => 'AI Review',
                ] as $key => $label)
                    <button wire:click="$set('videoStatusFilter', '{{ $key }}')"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors
                                   {{ $videoStatusFilter === $key ? 'bg-wc-accent text-white' : 'bg-wc-bg-secondary border border-wc-border text-wc-text-secondary hover:text-wc-text' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Check-ins list --}}
            @if ($checkins->isEmpty())
                <div class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-secondary p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                    <p class="mt-4 text-sm text-wc-text-secondary">Sin video check-ins{{ $videoStatusFilter !== 'all' ? ' con este filtro' : '' }}.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($checkins as $ci)
                        <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary overflow-hidden transition-all">
                            {{-- Card header --}}
                            <button wire:click="toggleCheckin({{ $ci['id'] }})"
                                    class="w-full flex items-center gap-4 px-5 py-4 text-left hover:bg-wc-bg-tertiary/50 transition-colors">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/20 text-sm font-semibold text-wc-accent">{{ $ci['client_initial'] }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm text-wc-text">{{ $ci['client_name'] }}</span>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold
                                              {{ $ci['status'] === 'pending' ? 'bg-amber-500/10 text-amber-400' : '' }}
                                              {{ $ci['status'] === 'coach_reviewed' ? 'bg-emerald-500/10 text-emerald-400' : '' }}
                                              {{ $ci['status'] === 'ai_reviewed' ? 'bg-blue-500/10 text-blue-400' : '' }}">
                                            {{ $ci['status'] === 'pending' ? 'Pendiente' : ($ci['status'] === 'coach_reviewed' ? 'Revisado' : 'AI Review') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-wc-text-secondary truncate">{{ $ci['exercise_name'] ?: 'Sin ejercicio' }} &middot; {{ $ci['created_at_ago'] }}</p>
                                </div>
                                <div class="shrink-0 flex items-center gap-2">
                                    <span class="rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[10px] font-medium text-wc-text-tertiary uppercase">{{ $ci['media_type'] }}</span>
                                    <svg class="h-4 w-4 text-wc-text-tertiary transition-transform {{ $expandedCheckinId === $ci['id'] ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                </div>
                            </button>

                            {{-- Expanded content --}}
                            @if ($expandedCheckinId === $ci['id'])
                                <div class="border-t border-wc-border px-5 py-5 space-y-5">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        {{-- Media --}}
                                        <div>
                                            <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary mb-2">Media</p>
                                            @if ($ci['media_type'] === 'video')
                                                <video controls preload="metadata" class="w-full rounded-lg bg-black max-h-64">
                                                    <source src="{{ $ci['media_url'] }}" type="video/mp4">
                                                    Tu navegador no soporta video.
                                                </video>
                                            @else
                                                <img src="{{ $ci['media_url'] }}" alt="Check-in" class="w-full rounded-lg object-cover max-h-64" loading="lazy" decoding="async">
                                            @endif
                                        </div>

                                        {{-- Info --}}
                                        <div class="space-y-4">
                                            <div>
                                                <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary mb-1">Ejercicio</p>
                                                <p class="text-sm text-wc-text">{{ $ci['exercise_name'] ?: 'No especificado' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary mb-1">Notas del Cliente</p>
                                                <p class="text-sm text-wc-text-secondary">{{ $ci['notes'] ?: 'Sin notas' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary mb-1">Enviado</p>
                                                <p class="text-sm text-wc-text-secondary">{{ $ci['created_at'] }}</p>
                                            </div>
                                            @if ($ci['ai_response'])
                                                <div>
                                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-blue-400/60 mb-1">Respuesta AI</p>
                                                    <p class="text-sm text-blue-300 bg-blue-500/5 rounded-lg p-3 border border-blue-500/10">{{ $ci['ai_response'] }}</p>
                                                </div>
                                            @endif
                                            @if ($ci['coach_response'] && $ci['status'] === 'coach_reviewed')
                                                <div>
                                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-emerald-400/60 mb-1">Tu Respuesta</p>
                                                    <p class="text-sm text-emerald-300 bg-emerald-500/5 rounded-lg p-3 border border-emerald-500/10">{{ $ci['coach_response'] }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Response form (only if pending or ai_reviewed) --}}
                                    @if ($ci['status'] !== 'coach_reviewed')
                                        <div class="border-t border-wc-border pt-4 space-y-3">
                                            <label class="text-xs font-semibold text-wc-text-secondary">Tu Respuesta al Cliente</label>
                                            <textarea wire:model="coachResponse" rows="3"
                                                      placeholder="Escribe tu feedback sobre la ejecucion..."
                                                      class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50 outline-none resize-none"></textarea>
                                            <button wire:click="submitReview({{ $ci['id'] }})"
                                                    class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                                Marcar Revisado
                                            </button>
                                        </div>
                                    @endif

                                    @if ($ci['responded_at'])
                                        <p class="text-[10px] text-wc-text-tertiary">Respondido: {{ $ci['responded_at'] }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
