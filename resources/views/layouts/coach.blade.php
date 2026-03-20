<!DOCTYPE html>
<html lang="es"
      x-data="{ darkMode: localStorage.getItem('darkMode') !== 'false', sidebarOpen: false }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Coach Portal' }} — WellCore</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Bebas+Neue&family=JetBrains+Mono:wght@400;500&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-wc-bg text-wc-text">

    @php
        $coach = auth('wellcore')->user();
    @endphp

    {{-- Mobile sidebar overlay --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black/60 lg:hidden"
        x-on:click="sidebarOpen = false"
        x-cloak
    ></div>

    {{-- Sidebar --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col bg-wc-bg-secondary border-r border-wc-border transition-transform duration-300 ease-in-out lg:translate-x-0"
    >
        {{-- Logo --}}
        <div class="flex h-16 items-center gap-3 border-b border-wc-border px-5">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent">
                <span class="font-display text-base leading-none text-white">W</span>
            </div>
            <span class="font-display text-xl tracking-wider text-wc-text">WELLCORE</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
            {{-- Coach --}}
            <div>
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Coach</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('coach.dashboard') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                  {{ request()->routeIs('coach.dashboard') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('coach.clients') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                  {{ request()->routeIs('coach.clients') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            Mis Clientes
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Comunicacion --}}
            <div>
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Comunicacion</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('coach.messages') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                  {{ request()->routeIs('coach.messages') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                            </svg>
                            Mensajes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('coach.notes') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                  {{ request()->routeIs('coach.notes') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                            Notas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('coach.broadcast') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                  {{ request()->routeIs('coach.broadcast') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                            </svg>
                            Broadcast
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Seguimiento --}}
            <div>
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Seguimiento</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('coach.checkins') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                  {{ request()->routeIs('coach.checkins') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Check-ins
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('coach.plans') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                  {{ request()->routeIs('coach.plans') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                            </svg>
                            Planes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('coach.analytics') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                  {{ request()->routeIs('coach.analytics') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            Analytics
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Cuenta --}}
            <div>
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Cuenta</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('coach.profile') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                  {{ request()->routeIs('coach.profile') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            Mi Perfil
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        {{-- Sidebar footer / Logout --}}
        <div class="border-t border-wc-border px-3 py-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors">
                    <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Cerrar sesion
                </button>
            </form>
        </div>
    </aside>

    {{-- Main wrapper (offset by sidebar on lg+) --}}
    <div class="lg:pl-60">

        {{-- Top bar --}}
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-wc-border bg-wc-bg/80 backdrop-blur-xl px-4 sm:px-6">
            {{-- Left: hamburger (mobile) + page title --}}
            <div class="flex items-center gap-3">
                <button
                    x-on:click="sidebarOpen = !sidebarOpen"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text lg:hidden"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

            {{-- Right: coach info, dark mode, etc. --}}
            <div class="flex items-center gap-3">
                {{-- Dark Mode Toggle --}}
                <button
                    x-on:click="darkMode = !darkMode"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text"
                    title="Cambiar modo"
                >
                    <template x-if="!darkMode">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                    </template>
                    <template x-if="darkMode">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </template>
                </button>

                {{-- Coach badge --}}
                <span class="hidden sm:inline-flex rounded-full bg-wc-accent/10 px-2.5 py-0.5 text-xs font-semibold text-wc-accent">
                    Coach
                </span>

                {{-- User avatar + name --}}
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-wc-accent/20">
                        <span class="text-sm font-semibold text-wc-accent">{{ substr($coach->name ?? 'C', 0, 1) }}</span>
                    </div>
                    <span class="hidden text-sm font-medium text-wc-text sm:inline">{{ $coach->name ?? 'Coach' }}</span>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
