<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'WellCore Fitness' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Bebas+Neue&family=JetBrains+Mono:wght@400;500&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-wc-bg text-wc-text">

    {{-- Top Nav --}}
    <nav class="sticky top-0 z-50 border-b border-wc-border bg-wc-bg-tertiary/80 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-wc-accent">
                    <span class="font-display text-lg leading-none text-white">W</span>
                </div>
                <span class="font-display text-xl tracking-wide text-wc-text">WELLCORE</span>
                <span class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-xs font-medium text-wc-accent">Laravel</span>
            </div>

            {{-- Right side --}}
            <div class="flex items-center gap-4">
                {{-- Dark Mode Toggle --}}
                <button
                    x-on:click="darkMode = !darkMode"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text"
                    title="Toggle dark mode"
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

                @auth('wellcore')
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-full bg-wc-accent/20 flex items-center justify-center">
                            <span class="text-sm font-semibold text-wc-accent">{{ substr(auth('wellcore')->user()->name ?? 'U', 0, 1) }}</span>
                        </div>
                        <span class="text-sm font-medium">{{ auth('wellcore')->user()->name ?? 'Usuario' }}</span>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-wc-border py-6 text-center text-sm text-wc-text-tertiary">
        <p>WellCore Fitness &copy; {{ date('Y') }} &mdash; Laravel {{ app()->version() }} &middot; PHP {{ PHP_VERSION }}</p>
    </footer>

    @livewireScripts
</body>
</html>
