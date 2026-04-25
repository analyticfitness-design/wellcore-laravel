<!DOCTYPE html>
<script nonce="@cspNonce">
    // FOUC: apply dark class before Alpine loads, no flash
    if (localStorage.getItem('darkMode') === 'true') document.documentElement.classList.add('dark');
    document.addEventListener('alpine:init', () => {
        Alpine.store('darkMode', {
            on: localStorage.getItem('darkMode') === 'true',
            toggle() {
                this.on = !this.on;
                localStorage.setItem('darkMode', String(this.on));
                document.documentElement.classList.toggle('dark', this.on);
            }
        });
        Alpine.store('coachSidebar', {
            collapsed: localStorage.getItem('coachSidebarCollapsed') === 'true',
            toggle() {
                this.collapsed = !this.collapsed;
                localStorage.setItem('coachSidebarCollapsed', String(this.collapsed));
            }
        });
    });
    // Re-apply after each wire:navigate (morphdom removes the class from <html>)
    document.addEventListener('livewire:navigated', () => {
        document.documentElement.classList.toggle('dark', localStorage.getItem('darkMode') === 'true');
    });
</script>
<html lang="es"
      x-data="{ sidebarMobileOpen: false, fabOpen: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Coach Portal' }} – WellCore</title>

    <!-- Favicon & PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/icon-512x512.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="WellCore">
    <meta name="theme-color" content="#DC2626">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Raleway:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-wc-bg text-wc-text">

    @php
        $coachUser = auth('wellcore')->user();
        $sidebarPendingCheckins = $pendingCheckins ?? 0;
        $sidebarUnreadMessages  = $unreadMessages ?? 0;
        $sidebarActiveClients   = $activeClients ?? 0;
    @endphp

    {{-- Mobile sidebar overlay --}}
    <div
        x-show="sidebarMobileOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black/60 lg:hidden"
        @click="sidebarMobileOpen = false"
        x-cloak
    ></div>

    {{-- ═══ SIDEBAR ═══ --}}
    <aside
        :class="sidebarMobileOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex flex-col bg-gradient-to-b from-wc-bg-secondary to-wc-bg border-r border-wc-border/50 transition-all duration-300 ease-in-out lg:translate-x-0"
        :style="$store.coachSidebar?.collapsed ? 'width:4.5rem' : 'width:15rem'"
    >
        {{-- Logo / Collapse button row --}}
        <div class="flex h-16 items-center justify-between border-b border-wc-border/50 px-4 shrink-0">
            <a href="{{ route('coach.dashboard') }}" class="flex items-center gap-2 min-w-0">
                <img src="/images/logo-dark.png" alt="WellCore" class="h-7 shrink-0 dark:hidden">
                <img src="/images/logo-light.png" alt="WellCore" class="hidden h-7 shrink-0 dark:block">
                <span x-show="!$store.coachSidebar?.collapsed" x-cloak
                      class="font-display text-sm uppercase tracking-widest text-wc-text truncate">WellCore</span>
            </a>
            {{-- Desktop collapse toggle --}}
            <button @click="$store.coachSidebar.toggle()"
                    class="hidden lg:flex h-7 w-7 items-center justify-center rounded-lg hover:bg-wc-bg-tertiary text-wc-text-tertiary transition-colors shrink-0"
                    :title="$store.coachSidebar?.collapsed ? 'Expandir sidebar' : 'Colapsar sidebar'">
                <svg class="w-4 h-4 transition-transform" :class="$store.coachSidebar?.collapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto overflow-x-hidden px-2 py-3 space-y-4 no-scrollbar">

            {{-- ── Principal ── --}}
            <div>
                <p x-show="!$store.coachSidebar?.collapsed" x-cloak
                   class="mb-1.5 px-2 text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Principal</p>
                <ul class="space-y-0.5">
                    {{-- Inicio --}}
                    <li>
                        <a wire:navigate href="{{ route('coach.dashboard') }}"
                           class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-all duration-150
                                  {{ request()->routeIs('coach.dashboard') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                           :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                            <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="truncate">{{ __('coach_dashboard.sidebar_inicio') }}</span>
                        </a>
                    </li>
                    {{-- Clientes --}}
                    <li>
                        <a wire:navigate href="{{ route('coach.clients') }}"
                           class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-all duration-150
                                  {{ request()->routeIs('coach.clients') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                           :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                            <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="flex-1 truncate">{{ __('coach_dashboard.sidebar_clientes') }}</span>
                            @if($sidebarActiveClients > 0)
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak
                                  class="shrink-0 min-w-[18px] h-[18px] px-1 rounded-full bg-wc-bg-tertiary border border-wc-border text-[9px] font-bold text-wc-text-secondary flex items-center justify-center">{{ $sidebarActiveClients }}</span>
                            @endif
                        </a>
                    </li>
                    {{-- Check-ins --}}
                    <li>
                        <a wire:navigate href="{{ route('coach.checkins') }}"
                           class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-all duration-150
                                  {{ request()->routeIs('coach.checkins') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                           :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                            <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="flex-1 truncate">{{ __('coach_dashboard.sidebar_checkins') }}</span>
                            @if($sidebarPendingCheckins > 0)
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak
                                  class="shrink-0 min-w-[18px] h-[18px] px-1 rounded-full bg-wc-accent text-[9px] font-bold text-white flex items-center justify-center">{{ $sidebarPendingCheckins }}</span>
                            @endif
                        </a>
                    </li>
                    {{-- Mensajes --}}
                    <li>
                        <a wire:navigate href="{{ route('coach.messages') }}"
                           class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-all duration-150
                                  {{ request()->routeIs('coach.messages') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                           :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                            <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                            </svg>
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="flex-1 truncate">{{ __('coach_dashboard.sidebar_mensajes') }}</span>
                            @if($sidebarUnreadMessages > 0)
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak
                                  class="shrink-0 min-w-[18px] h-[18px] px-1 rounded-full bg-wc-accent animate-wc-breathe text-[9px] font-bold text-white flex items-center justify-center">{{ $sidebarUnreadMessages }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>

            {{-- ── Trabajo ── --}}
            <div>
                <p x-show="!$store.coachSidebar?.collapsed" x-cloak
                   class="mb-1.5 px-2 text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Trabajo</p>
                <ul class="space-y-0.5">
                    {{-- Tickets (via checkins route) --}}
                    <li>
                        <a wire:navigate href="{{ route('coach.checkins') }}"
                           class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-all duration-150 text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text"
                           :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                            <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                            </svg>
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="truncate">{{ __('coach_dashboard.stat_tickets') }}</span>
                        </a>
                    </li>
                    {{-- Planes --}}
                    <li>
                        <a wire:navigate href="{{ route('coach.plans') }}"
                           class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-all duration-150
                                  {{ request()->routeIs('coach.plans') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                           :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                            <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                            </svg>
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="truncate">{{ __('coach_dashboard.sidebar_planes') }}</span>
                        </a>
                    </li>
                    {{-- Kanban --}}
                    <li>
                        <a wire:navigate href="{{ route('coach.kanban') }}"
                           class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-all duration-150
                                  {{ request()->routeIs('coach.kanban') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                           :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                            <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="truncate">{{ __('coach_dashboard.sidebar_kanban') }}</span>
                        </a>
                    </li>
                    {{-- Broadcast --}}
                    <li>
                        <a wire:navigate href="{{ route('coach.broadcast') }}"
                           class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-all duration-150
                                  {{ request()->routeIs('coach.broadcast') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                           :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                            <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                            </svg>
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="truncate">{{ __('coach_dashboard.sidebar_broadcast') }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- ── Insights ── --}}
            <div>
                <p x-show="!$store.coachSidebar?.collapsed" x-cloak
                   class="mb-1.5 px-2 text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Insights</p>
                <ul class="space-y-0.5">
                    {{-- Analítica --}}
                    <li>
                        <a wire:navigate href="{{ route('coach.analytics') }}"
                           class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-all duration-150
                                  {{ request()->routeIs('coach.analytics') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                           :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                            <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="truncate">{{ __('coach_dashboard.sidebar_analitica') }}</span>
                        </a>
                    </li>
                    {{-- Notas --}}
                    <li>
                        <a wire:navigate href="{{ route('coach.notes') }}"
                           class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-all duration-150
                                  {{ request()->routeIs('coach.notes') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                           :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                            <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                            <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="truncate">{{ __('coach_dashboard.sidebar_notas') }}</span>
                        </a>
                    </li>
                </ul>
            </div>

        </nav>

        {{-- ── Sidebar Footer ── --}}
        <div class="mt-auto border-t border-wc-border/50 px-2 py-3 space-y-0.5 shrink-0">
            {{-- Mi Marca --}}
            <a wire:navigate href="{{ route('coach.brand') }}"
               class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors"
               :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                </svg>
                <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="truncate">{{ __('coach_dashboard.sidebar_marca') }}</span>
            </a>
            {{-- Perfil --}}
            <a wire:navigate href="{{ route('coach.profile') }}"
               class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors"
               :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="truncate">{{ __('coach_dashboard.sidebar_perfil') }}</span>
            </a>
            {{-- Cerrar sesión --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors"
                        :class="$store.coachSidebar?.collapsed ? 'justify-center' : ''">
                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    <span x-show="!$store.coachSidebar?.collapsed" x-cloak class="truncate">{{ __('dashboard.cerrar_sesion') }}</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══ MAIN WRAPPER (offset by sidebar on lg+) ═══ --}}
    <main class="min-h-screen transition-all duration-300 lg:pb-0 pb-24"
          :class="$store.coachSidebar?.collapsed ? 'lg:ml-[4.5rem]' : 'lg:ml-60'">

        {{-- Impersonation banner — preserved from original --}}
        @if(session('wc_super_token'))
        <div class="flex items-center justify-between bg-violet-600 px-4 py-2 text-sm font-medium text-white">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <span>Viendo portal de coach: <strong>{{ session('wc_coach_name', 'Coach') }}</strong></span>
            </div>
            <form method="POST" action="{{ route('coach.impersonate.stop') }}">
                @csrf
                <button type="submit"
                        class="rounded-lg border border-white/30 bg-white/10 px-3 py-1 text-xs font-medium text-white hover:bg-white/20 transition-colors">
                    ← Volver al Admin
                </button>
            </form>
        </div>
        @endif

        {{-- ─── DESKTOP TOP BAR ─── --}}
        <header class="hidden lg:flex sticky top-0 z-20 h-16 items-center justify-between px-6 border-b backdrop-blur-xl"
                style="background: color-mix(in srgb, var(--color-wc-bg) 85%, transparent); border-color: var(--color-wc-border)">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="text-sm" style="color: var(--color-wc-text-tertiary)">
                Coach <span class="mx-1 opacity-40">/</span>
                <span class="font-medium" style="color: var(--color-wc-text)">{{ $title ?? 'Dashboard' }}</span>
            </nav>

            {{-- Right side --}}
            <div class="flex items-center gap-3">
                {{-- Language switcher --}}
                <x-language-switcher />

                {{-- Bell with badge --}}
                <button class="relative p-2 rounded-lg hover:bg-wc-bg-tertiary transition-colors"
                        aria-label="Notificaciones">
                    <svg class="w-5 h-5 text-wc-text-secondary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    @if(isset($urgentClientsCount) && $urgentClientsCount > 0)
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-wc-accent animate-wc-breathe"></span>
                    @endif
                </button>

                {{-- Dark mode toggle --}}
                <button @click="$store.darkMode.toggle()"
                        class="p-2 rounded-lg hover:bg-wc-bg-tertiary transition-colors"
                        :title="$store.darkMode?.on ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                        aria-label="Alternar modo oscuro">
                    <svg class="w-5 h-5 text-wc-text-secondary dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    <svg class="hidden w-5 h-5 text-wc-text-secondary dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>

                {{-- Avatar dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-wc-bg-tertiary transition-colors"
                            aria-label="Menú de usuario">
                        <div class="w-8 h-8 rounded-full bg-wc-accent flex items-center justify-center text-white text-sm font-bold shrink-0">
                            {{ strtoupper(substr($coachUser->name ?? 'C', 0, 1)) }}
                        </div>
                        <span class="text-sm font-medium text-wc-text hidden xl:block">{{ $coachUser->name ?? 'Coach' }}</span>
                        <svg class="w-3.5 h-3.5 text-wc-text-tertiary transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 rounded-xl border bg-wc-bg-secondary shadow-xl z-50"
                         style="border-color: var(--color-wc-border)"
                         x-cloak>
                        <div class="px-4 py-3 border-b" style="border-color: var(--color-wc-border)">
                            <p class="text-xs font-medium text-wc-text truncate">{{ $coachUser->name ?? 'Coach' }}</p>
                            <p class="text-[11px] text-wc-text-tertiary truncate">{{ $coachUser->email ?? '' }}</p>
                        </div>
                        <div class="py-1">
                            <a wire:navigate href="{{ route('coach.profile') }}" @click="open = false"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                Mi perfil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-sm text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- ─── MOBILE TOP BAR ─── --}}
        <header class="lg:hidden sticky top-0 z-20 flex items-center justify-between px-4 h-14 backdrop-blur-xl border-b"
                style="background: color-mix(in srgb, var(--color-wc-bg) 85%, transparent); border-color: var(--color-wc-border)">
            {{-- Hamburger --}}
            <button @click="sidebarMobileOpen = true"
                    class="p-2 -ml-2 rounded-lg hover:bg-wc-bg-tertiary transition-colors"
                    aria-label="Abrir menú">
                <svg class="w-5 h-5 text-wc-text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            {{-- Coach name + date --}}
            <div class="text-center">
                <div class="font-display text-sm uppercase tracking-wider text-wc-text">{{ $coachUser->name ?? 'Coach' }}</div>
                <div class="font-mono text-[10px] text-wc-text-tertiary">{{ now()->locale('es')->isoFormat('D MMM') }}</div>
            </div>

            {{-- Bell + dark toggle --}}
            <div class="flex items-center gap-0.5">
                <button class="relative p-2 rounded-lg hover:bg-wc-bg-tertiary transition-colors"
                        aria-label="Notificaciones">
                    <svg class="w-5 h-5 text-wc-text-secondary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    @if(isset($urgentClientsCount) && $urgentClientsCount > 0)
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-wc-accent animate-wc-breathe"></span>
                    @endif
                </button>
                <button @click="$store.darkMode.toggle()"
                        class="p-2 rounded-lg hover:bg-wc-bg-tertiary transition-colors"
                        aria-label="Alternar modo oscuro">
                    <svg class="w-5 h-5 text-wc-text-secondary dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    <svg class="hidden w-5 h-5 text-wc-text-secondary dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>
            </div>
        </header>

        {{-- Page content --}}
        {{ $slot }}

    </main>

    {{-- ═══ MOBILE BOTTOM NAV ═══ --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 border-t pb-safe"
         style="background: var(--color-wc-bg-secondary); border-color: var(--color-wc-border)"
         aria-label="Navegación principal">
        <div class="flex items-center justify-around h-16 px-2 relative">
            {{-- Inicio --}}
            <a wire:navigate href="{{ route('coach.dashboard') }}"
               class="nav-tap flex flex-col items-center justify-center gap-0.5 min-w-[44px] min-h-[44px] px-3 py-2 rounded-lg transition-colors
                      {{ request()->routeIs('coach.dashboard') ? 'text-wc-accent' : 'text-wc-text-tertiary' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span class="text-[9px] font-medium">Inicio</span>
            </a>

            {{-- Clientes --}}
            <a wire:navigate href="{{ route('coach.clients') }}"
               class="nav-tap flex flex-col items-center justify-center gap-0.5 min-w-[44px] min-h-[44px] px-3 py-2 rounded-lg transition-colors
                      {{ request()->routeIs('coach.clients') ? 'text-wc-accent' : 'text-wc-text-tertiary' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span class="text-[9px] font-medium">Clientes</span>
            </a>

            {{-- FAB spacer --}}
            <div class="w-14 shrink-0"></div>

            {{-- Check-ins --}}
            <a wire:navigate href="{{ route('coach.checkins') }}"
               class="nav-tap relative flex flex-col items-center justify-center gap-0.5 min-w-[44px] min-h-[44px] px-3 py-2 rounded-lg transition-colors
                      {{ request()->routeIs('coach.checkins') ? 'text-wc-accent' : 'text-wc-text-tertiary' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-[9px] font-medium">Check-ins</span>
                @if($sidebarPendingCheckins > 0)
                <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-wc-accent"></span>
                @endif
            </a>

            {{-- Mensajes --}}
            <a wire:navigate href="{{ route('coach.messages') }}"
               class="nav-tap relative flex flex-col items-center justify-center gap-0.5 min-w-[44px] min-h-[44px] px-3 py-2 rounded-lg transition-colors
                      {{ request()->routeIs('coach.messages') ? 'text-wc-accent' : 'text-wc-text-tertiary' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <span class="text-[9px] font-medium">Mensajes</span>
                @if($sidebarUnreadMessages > 0)
                <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-wc-accent animate-wc-breathe"></span>
                @endif
            </a>
        </div>

        {{-- FAB button — floats 28px above the nav bar --}}
        <button @click="fabOpen = !fabOpen"
                class="absolute left-1/2 -translate-x-1/2 -top-7 w-14 h-14 rounded-full bg-wc-accent shadow-lg shadow-red-900/40 flex items-center justify-center transition-transform duration-200 active:scale-95"
                :class="fabOpen ? 'rotate-45' : ''"
                aria-label="{{ __('coach_dashboard.fab_add_client') }}"
                aria-expanded="fabOpen">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </button>
    </nav>

    {{-- FAB Bottom Sheet backdrop --}}
    <div x-show="fabOpen"
         x-cloak
         @click="fabOpen = false"
         class="lg:hidden fixed inset-0 z-40 bg-black/60 animate-fade-in"
         aria-hidden="true">
    </div>

    {{-- FAB Bottom Sheet --}}
    <div x-show="fabOpen"
         x-cloak
         class="lg:hidden fixed bottom-0 inset-x-0 z-50 animate-slide-up pb-safe rounded-t-2xl border-t"
         style="background: var(--color-wc-bg-secondary); border-color: var(--color-wc-border)"
         role="dialog"
         aria-modal="true"
         aria-label="Acciones rápidas">
        <div class="p-4 space-y-1">
            <div class="w-10 h-1 rounded-full bg-wc-border mx-auto mb-4"></div>
            {{-- Agregar cliente --}}
            <a wire:navigate href="{{ route('coach.clients') }}" @click="fabOpen = false"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-wc-bg-tertiary transition-colors">
                <div class="w-10 h-10 rounded-xl bg-wc-accent/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                </div>
                <div>
                    <div class="font-medium text-sm text-wc-text">{{ __('coach_dashboard.fab_add_client') }}</div>
                    <div class="text-xs text-wc-text-tertiary">Registrar nuevo cliente</div>
                </div>
            </a>
            {{-- Enviar mensaje --}}
            <a wire:navigate href="{{ route('coach.messages') }}" @click="fabOpen = false"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-wc-bg-tertiary transition-colors">
                <div class="w-10 h-10 rounded-xl bg-blue-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </div>
                <div>
                    <div class="font-medium text-sm text-wc-text">{{ __('coach_dashboard.fab_broadcast') }}</div>
                    <div class="text-xs text-wc-text-tertiary">Mensaje a todos los clientes</div>
                </div>
            </a>
            {{-- Revisar check-ins --}}
            <a wire:navigate href="{{ route('coach.checkins') }}" @click="fabOpen = false"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-wc-bg-tertiary transition-colors">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <div class="font-medium text-sm text-wc-text">{{ __('coach_dashboard.fab_checkins') }}</div>
                    <div class="text-xs text-wc-text-tertiary">Ver check-ins pendientes</div>
                </div>
            </a>
        </div>
    </div>

    <x-toast-notifications />
    <x-ga-tracking />
    @livewireScripts
</body>
</html>
