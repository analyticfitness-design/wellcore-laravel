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
<html lang="es"
      x-data="{ mobileMenu: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'WellCore Fitness' }}</title>
    <meta name="description" content="{{ $description ?? 'Coaching fitness basado en ciencia. Entrenamiento personalizado, nutricion y seguimiento para alcanzar tu mejor version.' }}">

    <!-- Favicon & PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/jpeg" sizes="512x512" href="{{ asset('images/favicon-wc.jpg') }}">
    <link rel="icon" type="image/jpeg" sizes="192x192" href="{{ asset('images/favicon-wc.jpg') }}">
    <link rel="icon" type="image/jpeg" sizes="32x32" href="{{ asset('images/favicon-wc.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon-wc.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon-wc-touch.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="WellCore">
    <meta name="theme-color" content="#DC2626">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Bebas+Neue&family=JetBrains+Mono:wght@400;500&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(config('app.meta_pixel_id'))
    <!-- Meta Pixel -->
    <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
    (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ config('app.meta_pixel_id') }}');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ config('app.meta_pixel_id') }}&ev=PageView&noscript=1"/></noscript>
    <!-- /Meta Pixel -->
    @endif
</head>
<body class="min-h-screen bg-wc-bg text-wc-text">

    {{-- Navigation --}}
    <nav class="sticky top-0 z-50 border-b border-wc-border bg-wc-bg/80 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-wc-accent">
                    <span class="font-display text-lg leading-none text-white">W</span>
                </div>
                <span class="font-display text-xl tracking-wider text-wc-text">WELLCORE</span>
            </a>

            {{-- Desktop Nav Links --}}
            <div class="hidden items-center gap-8 md:flex">
                <a href="{{ route('home') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('home') ? '!text-wc-text' : '' }}">
                    Inicio
                </a>
                <a href="{{ route('metodo') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('metodo') ? '!text-wc-text' : '' }}">
                    Metodo
                </a>
                <a href="{{ route('proceso') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('proceso') ? '!text-wc-text' : '' }}">
                    Proceso
                </a>
                <a href="{{ route('planes') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('planes') ? '!text-wc-text' : '' }}">
                    Planes
                </a>
                <a href="{{ route('nosotros') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('nosotros') ? '!text-wc-text' : '' }}">
                    Nosotros
                </a>
                <a href="{{ route('faq') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('faq') ? '!text-wc-text' : '' }}">
                    FAQ
                </a>
            </div>

            {{-- Right side --}}
            <div class="flex items-center gap-3">
                {{-- Dark Mode Toggle --}}
                <button
                    x-on:click="$store.darkMode.toggle()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text"
                    title="Toggle dark mode"
                >
                    <template x-if="!$store.darkMode?.on">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                    </template>
                    <template x-if="$store.darkMode?.on">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </template>
                </button>

                {{-- CTA --}}
                <a href="{{ route('login') }}" class="hidden rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover md:inline-flex">
                    Inicia Sesion
                </a>

                {{-- Mobile Menu Button --}}
                <button
                    x-on:click="mobileMenu = !mobileMenu"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text md:hidden"
                >
                    <template x-if="!mobileMenu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </template>
                    <template x-if="mobileMenu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </template>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" x-cloak class="border-t border-wc-border bg-wc-bg md:hidden">
            <div class="space-y-1 px-4 py-4">
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text">Inicio</a>
                <a href="{{ route('metodo') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text">Metodo</a>
                <a href="{{ route('proceso') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text">Proceso</a>
                <a href="{{ route('planes') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text">Planes</a>
                <a href="{{ route('nosotros') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text">Nosotros</a>
                <a href="{{ route('faq') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text">FAQ</a>
                <div class="pt-2">
                    <a href="{{ route('login') }}" class="block rounded-lg bg-wc-accent px-3 py-2 text-center text-sm font-medium text-white hover:bg-wc-accent-hover">Inicia Sesion</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-wc-border bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                {{-- Brand --}}
                <div class="md:col-span-1">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-wc-accent">
                            <span class="font-display text-lg leading-none text-white">W</span>
                        </div>
                        <span class="font-display text-xl tracking-wider text-wc-text">WELLCORE</span>
                    </div>
                    <p class="mt-4 text-sm text-wc-text-tertiary">Coaching fitness basado en ciencia, no en tendencias.</p>
                </div>

                {{-- Links --}}
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-wc-text">Navegacion</h4>
                    <ul class="mt-4 space-y-2">
                        <li><a href="{{ route('home') }}" class="text-sm text-wc-text-secondary hover:text-wc-text">Inicio</a></li>
                        <li><a href="{{ route('metodo') }}" class="text-sm text-wc-text-secondary hover:text-wc-text">El Metodo</a></li>
                        <li><a href="{{ route('proceso') }}" class="text-sm text-wc-text-secondary hover:text-wc-text">Proceso</a></li>
                        <li><a href="{{ route('planes') }}" class="text-sm text-wc-text-secondary hover:text-wc-text">Planes</a></li>
                        <li><a href="{{ route('nosotros') }}" class="text-sm text-wc-text-secondary hover:text-wc-text">Nosotros</a></li>
                        <li><a href="{{ route('faq') }}" class="text-sm text-wc-text-secondary hover:text-wc-text">FAQ</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-wc-text">Legal</h4>
                    <ul class="mt-4 space-y-2">
                        <li><a href="#" class="text-sm text-wc-text-secondary hover:text-wc-text">Terminos de servicio</a></li>
                        <li><a href="#" class="text-sm text-wc-text-secondary hover:text-wc-text">Politica de privacidad</a></li>
                        <li><a href="#" class="text-sm text-wc-text-secondary hover:text-wc-text">Politica de reembolso</a></li>
                    </ul>
                </div>

                {{-- Social --}}
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-wc-text">Redes</h4>
                    <div class="mt-4 flex gap-4">
                        <a href="#" class="text-wc-text-secondary hover:text-wc-text" aria-label="Instagram">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/></svg>
                        </a>
                        <a href="#" class="text-wc-text-secondary hover:text-wc-text" aria-label="YouTube">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <a href="#" class="text-wc-text-secondary hover:text-wc-text" aria-label="TikTok">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="mt-10 border-t border-wc-border pt-6 text-center text-sm text-wc-text-tertiary">
                <p>WellCore Fitness &copy; {{ date('Y') }}. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

</body>
</html>
