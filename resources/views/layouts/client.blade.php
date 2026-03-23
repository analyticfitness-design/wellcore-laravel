<!DOCTYPE html>
<script>
    // FOUC: aplicar clase dark antes de que Alpine cargue
    if (localStorage.getItem('darkMode') === 'true') document.documentElement.classList.add('dark');
    // Alpine store — toggle controlado por usuario
    document.addEventListener('alpine:init', () => {
        Alpine.store('darkMode', {
            on: localStorage.getItem('darkMode') === 'true',
            toggle() {
                this.on = !this.on;
                localStorage.setItem('darkMode', String(this.on));
                document.documentElement.classList.toggle('dark', this.on);
            }
        });
    });
    // Re-aplicar después de cada wire:navigate (morphdom elimina la clase del <html>)
    document.addEventListener('livewire:navigated', () => {
        document.documentElement.classList.toggle('dark', localStorage.getItem('darkMode') === 'true');
    });
</script>
<html lang="{{ app()->getLocale() }}"
      x-data="{ sidebarOpen: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} — WellCore</title>

    <!-- Favicon & PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/jpeg" sizes="512x512" href="{{ asset('images/favicon-wc.jpg') }}">
    <link rel="icon" type="image/jpeg" sizes="192x192" href="{{ asset('images/favicon-wc.jpg') }}">
    <link rel="icon" type="image/jpeg" sizes="32x32" href="{{ asset('images/favicon-wc.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon-wc.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon-wc-touch.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="WellCore">
    <meta name="theme-color" content="#DC2626">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Bebas+Neue&family=JetBrains+Mono:wght@400;500&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-wc-bg text-wc-text">

    @php
        $client = auth('wellcore')->user();
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
        class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col bg-gradient-to-b from-wc-bg-secondary to-wc-bg border-r border-wc-border/50 transition-transform duration-300 ease-in-out lg:translate-x-0"
    >
        {{-- Logo --}}
        <div class="flex h-16 items-center border-b border-wc-border/50 px-5">
            <img src="/images/logo-dark.png" alt="WellCore" class="h-8 dark:hidden">
            <img src="/images/logo-light.png" alt="WellCore" class="hidden h-8 dark:block">
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
            {{-- Entrenamiento --}}
            <div>
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">{{ __('dashboard.client.sec_entrenamiento') }}</p>
                <ul class="space-y-0.5">
                    <li>
                        <a wire:navigate href="{{ route('client.dashboard') }}" data-nav-order="0"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.dashboard') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                            {{ __('dashboard.client.resumen') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.plan') }}" data-nav-order="1"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.plan') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                            </svg>
                            {{ __('dashboard.client.plan') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.workout') }}" data-nav-order="1.5"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.workout*') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                            </svg>
                            Entrenar
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.checkin') }}" data-nav-order="2"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.checkin') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            {{ __('dashboard.client.checkin') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Progreso --}}
            <div>
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">{{ __('dashboard.client.sec_progreso') }}</p>
                <ul class="space-y-0.5">
                    <li>
                        <a wire:navigate href="{{ route('client.photos') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.photos') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 0 3Z" />
                            </svg>
                            {{ __('dashboard.client.fotos') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.metrics') }}" data-nav-order="4"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.metrics') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                            </svg>
                            {{ __('dashboard.client.metricas') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.video-checkin') }}" data-nav-order="5"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.video-checkin') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            {{ __('dashboard.client.video_checkin') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Social --}}
            <div>
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">{{ __('dashboard.client.sec_comunidad') }}</p>
                <ul class="space-y-0.5">
                    <li>
                        <a wire:navigate href="{{ route('client.community') }}" data-nav-order="6"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.community') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                            {{ __('dashboard.client.comunidad') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.challenges') }}" data-nav-order="7"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.challenges') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" />
                            </svg>
                            {{ __('dashboard.client.retos') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.chat') }}" data-nav-order="8"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.chat') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                            </svg>
                            {{ __('dashboard.client.chat') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.referrals') }}" data-nav-order="9"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.referrals') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                            {{ __('dashboard.client.referidos') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Herramientas --}}
            <div>
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">{{ __('dashboard.client.sec_recursos') }}</p>
                <ul class="space-y-0.5">
                    <li>
                        <a wire:navigate href="{{ route('client.nutrition') }}" data-nav-order="11"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.nutrition') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                            </svg>
                            {{ __('dashboard.client.nutricion') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.habits') }}" data-nav-order="11.3"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.habits') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Hábitos
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.supplements') }}" data-nav-order="11.5"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.supplements') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 6 5.25 5.25M4.5 19.5l6.75-6.75m-3.75 3.75 9-9a3.182 3.182 0 0 0 0-4.5 3.182 3.182 0 0 0-4.5 0l-9 9a3.182 3.182 0 0 0 0 4.5 3.182 3.182 0 0 0 4.5 0Z" />
                            </svg>
                            Suplementos
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.ai-nutrition') }}" data-nav-order="12"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.ai-nutrition') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                            </svg>
                            {{ __('dashboard.client.hacks') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.settings') }}" data-nav-order="13"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.settings') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            {{ __('dashboard.configuracion') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('client.profile') }}" data-nav-order="14"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                                  {{ request()->routeIs('client.profile') ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            {{ __('dashboard.perfil') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- RISE (conditional) --}}
            @if($client && $client->plan === 'rise')
            <div>
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">RISE</p>
                <ul class="space-y-0.5">
                    <li>
                        <a wire:navigate href="{{ route('rise.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200 text-wc-accent hover:bg-wc-bg-tertiary">
                            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                            Dashboard RISE
                        </a>
                    </li>
                </ul>
            </div>
            @endif
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
                    {{ __('dashboard.cerrar_sesion') }}
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
                    class="btn-press flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text lg:hidden"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

            {{-- Right: user info, dark mode, etc. --}}
            <div class="flex items-center gap-3">
                <x-language-switcher />
                {{-- Dark Mode Toggle --}}
                <button
                    x-on:click="$store.darkMode.toggle()"
                    class="btn-press flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
                    title="{{ __('dashboard.cambiar_modo') }}"
                >
                    <template x-if="!$store.darkMode?.on">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                            <span class="hidden sm:inline">Oscuro</span>
                        </span>
                    </template>
                    <template x-if="$store.darkMode?.on">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                            <span class="hidden sm:inline">Claro</span>
                        </span>
                    </template>
                </button>

                {{-- Plan badge --}}
                @if($client && $client->plan)
                    <span class="hidden sm:inline-flex rounded-full bg-wc-accent/10 px-2.5 py-0.5 text-xs font-semibold text-wc-accent">
                        {{ $client->plan->label() }}
                    </span>
                @endif

                {{-- User avatar + name --}}
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-wc-accent/20">
                        <span class="text-sm font-semibold text-wc-accent">{{ substr($client->name ?? 'U', 0, 1) }}</span>
                    </div>
                    <span class="hidden text-sm font-medium text-wc-text sm:inline">{{ $client->name ?? 'Usuario' }}</span>
                </div>
            </div>
        </header>

        {{-- Pull to Refresh (Mobile) --}}
        <div x-data="{
            pulling: false,
            pullDistance: 0,
            refreshing: false,
            threshold: 60,
            maxPull: 100,
            startY: 0,
            handleTouchStart(e) {
                if (window.innerWidth >= 768 || window.scrollY > 0) return;
                this.startY = e.touches[0].clientY;
                this.pulling = true;
            },
            handleTouchMove(e) {
                if (!this.pulling || this.refreshing) return;
                const diff = e.touches[0].clientY - this.startY;
                if (diff > 0) {
                    this.pullDistance = Math.min(diff * 0.5, this.maxPull);
                    if (this.pullDistance > 10) {
                        e.preventDefault();
                    }
                } else {
                    this.pulling = false;
                    this.pullDistance = 0;
                }
            },
            handleTouchEnd() {
                if (!this.pulling) return;
                if (this.pullDistance >= this.threshold) {
                    this.refreshing = true;
                    this.pullDistance = this.threshold;
                    Livewire.dispatch('$refresh');
                    setTimeout(() => {
                        this.refreshing = false;
                        this.pullDistance = 0;
                        this.pulling = false;
                    }, 1500);
                } else {
                    this.pullDistance = 0;
                    this.pulling = false;
                }
            }
        }"
        @touchstart.passive="handleTouchStart($event)"
        @touchmove="handleTouchMove($event)"
        @touchend="handleTouchEnd($event)"
        class="md:hidden">

            {{-- Pull indicator --}}
            <div
                class="fixed top-0 left-0 right-0 flex justify-center z-50 transition-transform duration-200 pointer-events-none"
                :style="'transform: translateY(' + (pullDistance - 40) + 'px)'"
                x-show="pullDistance > 10"
                x-transition
            >
                <div class="mt-2 flex h-10 w-10 items-center justify-center rounded-full bg-wc-bg-tertiary border border-wc-border shadow-lg">
                    <svg
                        class="h-5 w-5 text-wc-accent transition-transform"
                        :class="{ 'animate-spin': refreshing }"
                        :style="!refreshing ? 'transform: rotate(' + (pullDistance / threshold * 360) + 'deg)' : ''"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Page content with swipe navigation (mobile) --}}
        <main class="px-4 py-6 pb-32 sm:px-6 lg:px-8 lg:pb-6" style="touch-action: pan-y;"
              x-data="{
                  touchStartX: 0,
                  touchStartY: 0,
                  swiping: false,
                  swipeHint: 0,
                  navLinks: [],
                  init() {
                      this.navLinks = [...document.querySelectorAll('[data-nav-order]')]
                          .sort((a, b) => a.dataset.navOrder - b.dataset.navOrder)
                          .map(a => a.getAttribute('href'))
                          .filter((v, i, arr) => arr.indexOf(v) === i);
                  },
                  handleTouchStart(e) {
                      if (window.innerWidth >= 768) return;
                      if (e.target.closest('[data-no-swipe]') || e.target.closest('.overflow-x-auto') || e.target.closest('.overflow-x-scroll') || e.target.closest('table') || e.target.closest('[x-data*=fabOpen]')) return;
                      this.touchStartX = e.touches[0].clientX;
                      this.touchStartY = e.touches[0].clientY;
                      this.swiping = true;
                      this.swipeHint = 0;
                  },
                  handleTouchMove(e) {
                      if (!this.swiping || window.innerWidth >= 768) return;
                      const diffX = e.touches[0].clientX - this.touchStartX;
                      const diffY = e.touches[0].clientY - this.touchStartY;
                      if (Math.abs(diffY) > Math.abs(diffX)) {
                          this.swiping = false;
                          this.swipeHint = 0;
                          return;
                      }
                      if (Math.abs(diffX) > 20) {
                          this.swipeHint = Math.max(-1, Math.min(1, diffX / 80));
                      }
                  },
                  handleTouchEnd(e) {
                      if (!this.swiping || window.innerWidth >= 768) { this.swipeHint = 0; return; }
                      this.swiping = false;
                      const diffX = e.changedTouches[0].clientX - this.touchStartX;
                      const diffY = e.changedTouches[0].clientY - this.touchStartY;
                      this.swipeHint = 0;
                      if (Math.abs(diffX) > 80 && Math.abs(diffX) > Math.abs(diffY) * 1.5) {
                          const currentPath = window.location.pathname;
                          const currentIdx = this.navLinks.indexOf(currentPath);
                          if (currentIdx === -1) return;
                          const nextIdx = diffX > 0 ? currentIdx - 1 : currentIdx + 1;
                          if (nextIdx >= 0 && nextIdx < this.navLinks.length) {
                              Livewire.navigate(this.navLinks[nextIdx]);
                          }
                      }
                  }
              }"
              @touchstart.passive="handleTouchStart($event)"
              @touchmove.passive="handleTouchMove($event)"
              @touchend="handleTouchEnd($event)"
        >
            <div class="swipe-indicator lg:hidden"></div>
            {{ $slot }}
        </main>

        {{-- Mobile swipe navigation indicator --}}
        @php
            $swipeNavSections = [
                ['route' => 'client.dashboard', 'path' => '/client'],
                ['route' => 'client.plan', 'path' => '/client/plan'],
                ['route' => 'client.checkin', 'path' => '/client/checkin'],
                ['route' => 'client.photos', 'path' => '/client/photos'],
                ['route' => 'client.metrics', 'path' => '/client/metrics'],
                ['route' => 'client.video-checkin', 'path' => '/client/video-checkin'],
                ['route' => 'client.community', 'path' => '/client/community'],
                ['route' => 'client.challenges', 'path' => '/client/challenges'],
                ['route' => 'client.chat', 'path' => '/client/chat'],
                ['route' => 'client.referrals', 'path' => '/client/referrals'],
                ['route' => 'client.nutrition', 'path' => '/client/nutrition'],
                ['route' => 'client.ai-nutrition', 'path' => '/client/ai-nutrition'],
                ['route' => 'client.settings', 'path' => '/client/settings'],
                ['route' => 'client.profile', 'path' => '/client/profile'],
            ];
        @endphp
        <div class="fixed bottom-16 left-0 right-0 flex justify-center gap-1.5 py-1.5 md:hidden pointer-events-none z-10"
             aria-label="Indicador de navegacion">
            @foreach($swipeNavSections as $idx => $section)
                <div class="h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs($section['route']) ? 'w-4 bg-wc-accent' : 'w-1.5 bg-wc-text-tertiary/30' }}"></div>
            @endforeach
        </div>
    </div>

    {{-- Premium Mobile Bottom Navigation --}}
    <x-mobile-bottom-nav variant="client" />

    {{-- Quick Actions FAB — hidden on form-heavy pages where it would overlap inputs --}}
    @unless(request()->routeIs('client.metrics', 'client.checkin', 'client.profile', 'client.settings', 'client.video-checkin'))
    <div x-data="{ fabOpen: false }"
         x-on:click.outside="fabOpen = false"
         x-on:keydown.escape.window="fabOpen = false"
         class="fixed bottom-[5.5rem] right-4 z-40 flex flex-col items-end gap-3 lg:hidden">

        {{-- Action buttons (shown when open) --}}
        <template x-if="fabOpen">
            <div class="flex flex-col items-end gap-2">
                {{-- 1. Log Peso --}}
                <a wire:navigate href="{{ route('client.metrics') }}"
                   class="fab-action-enter flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-tertiary px-4 py-2 shadow-lg hover:bg-wc-bg-secondary transition-colors"
                   style="animation-delay: 100ms">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.589-1.202L18.75 4.97Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.589-1.202L5.25 4.97Z" />
                    </svg>
                    <span class="text-sm font-medium text-wc-text">Log Peso</span>
                </a>

                {{-- 2. Check-in --}}
                <a wire:navigate href="{{ route('client.checkin') }}"
                   class="fab-action-enter flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-tertiary px-4 py-2 shadow-lg hover:bg-wc-bg-secondary transition-colors"
                   style="animation-delay: 200ms">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <span class="text-sm font-medium text-wc-text">Check-in</span>
                </a>

                {{-- 3. Entrenar --}}
                <a wire:navigate href="{{ route('client.workout') }}"
                   class="fab-action-enter flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-tertiary px-4 py-2 shadow-lg hover:bg-wc-bg-secondary transition-colors"
                   style="animation-delay: 300ms">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                    </svg>
                    <span class="text-sm font-medium text-wc-text">Entrenar</span>
                </a>

                {{-- 4. Foto --}}
                <a wire:navigate href="{{ route('client.photos') }}"
                   class="fab-action-enter flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-tertiary px-4 py-2 shadow-lg hover:bg-wc-bg-secondary transition-colors"
                   style="animation-delay: 400ms">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                    <span class="text-sm font-medium text-wc-text">Foto</span>
                </a>
            </div>
        </template>

        {{-- Main FAB button --}}
        <button x-on:click="fabOpen = !fabOpen"
                class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent shadow-lg shadow-wc-accent/30 hover:bg-wc-accent-hover transition-all duration-200"
                :class="{ 'rotate-45': fabOpen }"
                aria-label="Acciones rapidas">
            <svg class="h-6 w-6 text-white transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </button>
    </div>
    @endunless

    {{-- Rest Timer (global overlay) --}}
    <livewire:client.rest-timer />

    {{-- Training Completion Sound --}}
    <script>
        function playCompletionSound() {
            if (localStorage.getItem('wc_sound_enabled') === 'false') return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 880;
                gain.gain.value = 0.1;
                osc.start();
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                osc.stop(ctx.currentTime + 0.3);
            } catch (e) {
                // Web Audio API not supported or blocked
            }
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('training-completed', () => {
                playCompletionSound();
            });
        });
    </script>

    {{-- Keyboard shortcuts listener --}}
    <div x-data x-on:keydown.window="
        if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.tagName === 'SELECT') return;
        if (event.key === 'c' || event.key === 'C') window.location.href = '{{ route("client.checkin") }}';
        if (event.key === 't' || event.key === 'T') window.location.href = '{{ route("client.workout") }}';
        if (event.key === 'm' || event.key === 'M') window.location.href = '{{ route("client.metrics") }}';
        if (event.key === 'p' || event.key === 'P') window.location.href = '{{ route("client.plan") }}';
        if (event.key === 'h' || event.key === 'H') window.location.href = '{{ route("client.dashboard") }}';
        if (event.key === '?' && event.shiftKey) $dispatch('show-shortcuts');
    " style="display:none"></div>

    {{-- Keyboard shortcuts help modal --}}
    <div x-data="{ open: false }"
         x-on:show-shortcuts.window="open = true"
         x-on:keydown.escape.window="open = false"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center">
        {{-- Backdrop --}}
        <div x-show="open"
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-black/60"
             x-on:click="open = false"></div>
        {{-- Card --}}
        <div x-show="open"
             x-transition:enter="transition duration-200 ease-out"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition duration-150 ease-in"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-sm rounded-xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg tracking-wide text-wc-text">ATAJOS DE TECLADO</h3>
                <button x-on:click="open = false" class="rounded-lg p-1 text-wc-text-tertiary hover:bg-wc-bg-tertiary hover:text-wc-text">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="mt-4 space-y-2">
                <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-wc-bg-tertiary">
                    <span class="text-sm text-wc-text-secondary">Dashboard</span>
                    <kbd class="rounded border border-wc-border bg-wc-bg px-2 py-0.5 font-mono text-xs text-wc-text">H</kbd>
                </div>
                <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-wc-bg-tertiary">
                    <span class="text-sm text-wc-text-secondary">Mi Plan</span>
                    <kbd class="rounded border border-wc-border bg-wc-bg px-2 py-0.5 font-mono text-xs text-wc-text">P</kbd>
                </div>
                <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-wc-bg-tertiary">
                    <span class="text-sm text-wc-text-secondary">Check-in</span>
                    <kbd class="rounded border border-wc-border bg-wc-bg px-2 py-0.5 font-mono text-xs text-wc-text">C</kbd>
                </div>
                <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-wc-bg-tertiary">
                    <span class="text-sm text-wc-text-secondary">Entrenamiento</span>
                    <kbd class="rounded border border-wc-border bg-wc-bg px-2 py-0.5 font-mono text-xs text-wc-text">T</kbd>
                </div>
                <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-wc-bg-tertiary">
                    <span class="text-sm text-wc-text-secondary">Metricas</span>
                    <kbd class="rounded border border-wc-border bg-wc-bg px-2 py-0.5 font-mono text-xs text-wc-text">M</kbd>
                </div>
                <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-wc-bg-tertiary">
                    <span class="text-sm text-wc-text-secondary">Mostrar atajos</span>
                    <kbd class="rounded border border-wc-border bg-wc-bg px-2 py-0.5 font-mono text-xs text-wc-text">Shift + ?</kbd>
                </div>
            </div>
            <p class="mt-4 text-center text-xs text-wc-text-tertiary">Presiona <kbd class="rounded border border-wc-border bg-wc-bg px-1.5 py-0.5 font-mono text-[10px]">Esc</kbd> para cerrar</p>
        </div>
    </div>

    @livewireScripts
</body>
</html>
