<div wire:poll.30s x-data="{ showNotifs: false }" class="relative">

    {{-- Bell button --}}
    <button
        x-on:click="showNotifs = !showNotifs"
        class="relative flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
        title="Notificaciones"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        {{-- Unread badge --}}
        @if($unreadCount > 0)
            <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-wc-accent px-1 text-[10px] font-bold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div
        x-show="showNotifs"
        x-on:click.outside="showNotifs = false"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute right-0 top-full mt-2 w-80 rounded-xl border border-wc-border bg-wc-bg-secondary shadow-xl z-50"
    >
        {{-- Dropdown header --}}
        <div class="flex items-center justify-between border-b border-wc-border px-4 py-3">
            <h3 class="text-sm font-semibold text-wc-text font-data tracking-wide">Notificaciones</h3>
            @if($unreadCount > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-xs text-wc-accent hover:text-wc-accent/80 font-medium transition-colors"
                >
                    Marcar todas como leidas
                </button>
            @endif
        </div>

        {{-- Notification list --}}
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div
                    wire:key="notif-{{ $notification->id }}"
                    wire:click="markAsRead({{ $notification->id }})"
                    @if($notification->link)
                        x-on:click.once="setTimeout(() => window.location.href = '{{ $notification->link }}', 100)"
                    @endif
                    class="flex items-start gap-3 border-b border-wc-border px-4 py-3 hover:bg-wc-bg-tertiary cursor-pointer transition-colors last:border-b-0"
                >
                    {{-- Unread indicator dot --}}
                    <div class="mt-1.5 shrink-0">
                        @if(is_null($notification->read_at))
                            <span class="block h-2 w-2 rounded-full bg-wc-accent"></span>
                        @else
                            <span class="block h-2 w-2 rounded-full bg-wc-border"></span>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-wc-text leading-snug {{ is_null($notification->read_at) ? '' : 'opacity-60' }}">
                            {{ $notification->title }}
                        </p>
                        @if($notification->body)
                            <p class="mt-0.5 text-xs text-wc-text-secondary line-clamp-2 leading-relaxed">
                                {{ $notification->body }}
                            </p>
                        @endif
                        @if($notification->created_at)
                            <p class="mt-1 text-[10px] text-wc-text-tertiary font-data">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                    <svg class="h-10 w-10 text-wc-border mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    <p class="text-sm text-wc-text-secondary">No tienes notificaciones</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
