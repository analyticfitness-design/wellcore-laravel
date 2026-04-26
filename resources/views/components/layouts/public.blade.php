<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: localStorage.getItem('darkMode') !== 'false' }" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark') })" x-on:toggle-dark.window="darkMode = !darkMode" :class="{ 'dark': darkMode }">
<script>if(localStorage.getItem('darkMode')!=='false')document.documentElement.classList.add('dark')</script>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'WellCore Fitness' }}</title>
    <meta name="description" content="{{ $description ?? 'Coaching fitness basado en ciencia. Entrenamiento personalizado, nutricion y seguimiento para alcanzar tu mejor version.' }}">

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512x512.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

    {{-- ────────────────────────────────────────────────────────
         FUENTES — estrategia anti-render-blocking
         1. Inline @font-face críticos (4 fuentes above-fold latin)
         2. Preload solo de las 4 woff2 críticas (paralelo, no serie)
         3. CSS catálogo completo: media="print" onload (no bloquea render)
         ──────────────────────────────────────────────────────── --}}
    <style>
      @font-face{font-family:'Oswald';font-style:normal;font-weight:600;font-display:swap;src:url('/fonts/oswald-600-latin.woff2') format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}
      @font-face{font-family:'Raleway';font-style:normal;font-weight:500;font-display:swap;src:url('/fonts/raleway-500-latin.woff2') format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}
      @font-face{font-family:'Raleway';font-style:normal;font-weight:600;font-display:swap;src:url('/fonts/raleway-600-latin.woff2') format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}
      @font-face{font-family:'Raleway';font-style:normal;font-weight:700;font-display:swap;src:url('/fonts/raleway-700-latin.woff2') format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}
    </style>

    {{-- Preload paralelo de las 4 fuentes críticas (mismas que el inline arriba) --}}
    <link rel="preload" as="font" type="font/woff2" href="/fonts/oswald-600-latin.woff2" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-500-latin.woff2" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-600-latin.woff2" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-700-latin.woff2" crossorigin>

    {{-- CSS catálogo completo: NO bloquea render (truco media=print + onload) --}}
    <link rel="preload" href="/fonts/wellcore-fonts.css" as="style">
    <link rel="stylesheet" href="/fonts/wellcore-fonts.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="/fonts/wellcore-fonts.css"></noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Alpine: se carga en el footer condicionalmente, despues de saber si Livewire rendero (ver final de body).
         NUNCA cargar Alpine standalone + Livewire a la vez: duplica instancias y rompe el morph (wire:click deja de actualizar el DOM). --}}

    <x-seo-meta :title="$title ?? 'WellCore Fitness'" :description="$description ?? 'Coaching fitness basado en ciencia.'" />
    <x-hreflang />
    <x-ga-tracking />
    <x-pwa-meta />

    @if(config('app.meta_pixel_id'))
    {{-- Meta Pixel (lazy: load tras primer interaccion o scroll — ahorra 384 KB en initial load, mantiene tracking) --}}
    <script>
    (function(){
        var pxId='{{ config('app.meta_pixel_id') }}',loaded=false;
        function loadPixel(){
            if(loaded)return;loaded=true;
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
            (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init',pxId);fbq('track','PageView');
            ['scroll','mousedown','touchstart','keydown'].forEach(function(ev){
                window.removeEventListener(ev,loadPixel,{passive:true});
            });
        }
        // Trigger on first user signal OR 4s after load (whichever first)
        ['scroll','mousedown','touchstart','keydown'].forEach(function(ev){
            window.addEventListener(ev,loadPixel,{once:true,passive:true});
        });
        if(document.readyState==='complete')setTimeout(loadPixel,4000);
        else window.addEventListener('load',function(){setTimeout(loadPixel,4000);});
    })();
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ config('app.meta_pixel_id') }}&ev=PageView&noscript=1"/></noscript>
    @endif
</head>
<body class="min-h-screen bg-wc-bg text-wc-text">

    {{-- Navigation --}}
    <nav x-data="{ mobileMenu: false }" x-on:click.outside="mobileMenu = false" class="sticky top-0 z-50 border-b border-wc-border bg-wc-bg/80 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            {{-- Logo (switches between dark/light versions) --}}
            <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="Inicio WellCore Fitness">
                <picture class="dark:hidden">
                    <source srcset="/images/logo-dark-320.avif 320w, /images/logo-dark-640.avif 640w" sizes="158px" type="image/avif">
                    <source srcset="/images/logo-dark-320.webp 320w, /images/logo-dark-640.webp 640w" sizes="158px" type="image/webp">
                    <img src="/images/logo-dark-320.webp" alt="WellCore Fitness" width="158" height="40" class="h-10 w-auto" decoding="async">
                </picture>
                <picture class="hidden dark:block">
                    <source srcset="/images/logo-light-320.avif 320w, /images/logo-light-640.avif 640w" sizes="158px" type="image/avif">
                    <source srcset="/images/logo-light-320.webp 320w, /images/logo-light-640.webp 640w" sizes="158px" type="image/webp">
                    <img src="/images/logo-light-320.webp" alt="WellCore Fitness" width="158" height="40" class="h-10 w-auto" decoding="async">
                </picture>
            </a>

            {{-- Desktop Nav Links (8 links â€” need lg breakpoint) --}}
            <div class="hidden items-center gap-5 lg:flex xl:gap-7">
                <a href="{{ route('metodo') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('metodo') ? '!text-wc-text' : '' }}">{{ __('nav.metodo') }}</a>
                {{-- RISE cerrado: se oculta del nav público (legacy clientes siguen accediendo por /rise) --}}
                <a href="{{ route('nosotros') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('nosotros') ? '!text-wc-text' : '' }}">{{ __('nav.nosotros') }}</a>
                <a href="{{ route('proceso') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('proceso') ? '!text-wc-text' : '' }}">{{ __('nav.proceso') }}</a>
                <a href="{{ route('planes') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('planes') ? '!text-wc-text' : '' }}">{{ __('nav.planes') }}</a>
                <a href="{{ route('blog.index') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('blog.*') ? '!text-wc-text' : '' }}">{{ __('nav.blog') }}</a>
                <a href="{{ route('faq') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('faq') ? '!text-wc-text' : '' }}">{{ __('nav.faq') }}</a>
                <a href="{{ route('coaches') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('coaches*') ? '!text-wc-text' : '' }}">{{ __('nav.coaches') }}</a>
                <a href="{{ route('presencial') }}" class="text-sm font-medium text-wc-text-secondary hover:text-wc-text {{ request()->routeIs('presencial*') ? '!text-wc-text' : '' }}">{{ __('nav.presencial') }}</a>
            </div>

            {{-- Right side --}}
            <div class="flex items-center gap-2.5">
                {{-- Mi Cuenta --}}
                <a href="{{ route('login') }}" class="hidden items-center gap-1.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text lg:flex">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    {{ __('nav.mi_cuenta') }}
                </a>

                {{-- Language Switcher --}}
                <x-language-switcher />

                {{-- Dark Mode Toggle (CSS-based, no Alpine flash) --}}
                <button
                    x-on:click="$dispatch('toggle-dark')"
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text"
                    title="{{ __('nav.cambiar_modo') }}"
                    aria-label="{{ __('nav.cambiar_modo') }}"
                >
                    {{-- Moon: visible in light mode, hidden in dark mode --}}
                    <svg class="h-[18px] w-[18px] dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    {{-- Sun: hidden in light mode, visible in dark mode --}}
                    <svg class="hidden h-[18px] w-[18px] dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>

                {{-- Empezar CTA --}}
                <a href="{{ route('inscripcion') }}" class="hidden rounded-full bg-wc-accent px-5 py-2 text-sm font-semibold text-white hover:bg-wc-accent-hover sm:inline-flex" aria-label="{{ __('nav.empezar') }} — inscripción WellCore Fitness">
                    {{ __('nav.empezar') }}
                </a>

                {{-- Hamburger Menu Button --}}
                <button
                    x-on:click="mobileMenu = !mobileMenu"
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text lg:hidden"
                    :aria-label="mobileMenu ? 'Cerrar menu' : 'Abrir menu'"
                    :aria-expanded="mobileMenu"
                >
                    {{-- Hamburger icon --}}
                    <svg x-show="!mobileMenu" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    {{-- X close icon --}}
                    <svg x-show="mobileMenu" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenu"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             style="display: none;"
             class="border-t border-wc-border bg-wc-bg lg:hidden">
            <div class="space-y-1 px-4 py-4" x-on:click="mobileMenu = false">
                <a href="{{ route('metodo') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('metodo') ? 'bg-wc-bg-secondary text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text' }}">{{ __('nav.metodo') }}</a>
                {{-- RISE cerrado: oculto del menu movil --}}
                <a href="{{ route('nosotros') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('nosotros') ? 'bg-wc-bg-secondary text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text' }}">{{ __('nav.nosotros') }}</a>
                <a href="{{ route('proceso') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('proceso') ? 'bg-wc-bg-secondary text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text' }}">{{ __('nav.proceso') }}</a>
                <a href="{{ route('planes') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('planes') ? 'bg-wc-bg-secondary text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text' }}">{{ __('nav.planes') }}</a>
                <a href="{{ route('blog.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('blog.*') ? 'bg-wc-bg-secondary text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text' }}">{{ __('nav.blog') }}</a>
                <a href="{{ route('faq') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('faq') ? 'bg-wc-bg-secondary text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text' }}">{{ __('nav.faq') }}</a>
                <a href="{{ route('coaches') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('coaches*') ? 'bg-wc-bg-secondary text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text' }}">{{ __('nav.coaches') }}</a>
                <a href="{{ route('presencial') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('presencial*') ? 'bg-wc-bg-secondary text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text' }}">{{ __('nav.presencial') }}</a>
                <div class="flex gap-2 pt-3">
                    <a href="{{ route('login') }}" class="flex-1 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-center text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary">{{ __('nav.mi_cuenta') }}</a>
                    <a href="{{ route('inscripcion') }}" class="flex-1 rounded-lg bg-wc-accent px-3 py-2.5 text-center text-sm font-medium text-white hover:bg-wc-accent-hover" aria-label="{{ __('nav.empezar') }} — inscripción WellCore Fitness">{{ __('nav.empezar') }}</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <div class="section-divider"></div>
    <footer class="border-t border-wc-border bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Top: Brand statement + Newsletter --}}
            <div class="grid grid-cols-1 items-center gap-8 border-b border-wc-border py-12 lg:grid-cols-2">
                <div>
                    <picture class="dark:hidden">
                        <source srcset="/images/logo-dark-320.avif" type="image/avif">
                        <source srcset="/images/logo-dark-320.webp" type="image/webp">
                        <img src="/images/logo-dark-320.webp" alt="WellCore Fitness" width="158" height="40" class="h-10 w-auto" loading="lazy" decoding="async">
                    </picture>
                    <picture class="hidden dark:block">
                        <source srcset="/images/logo-light-320.avif" type="image/avif">
                        <source srcset="/images/logo-light-320.webp" type="image/webp">
                        <img src="/images/logo-light-320.webp" alt="WellCore Fitness" width="158" height="40" class="h-10 w-auto" loading="lazy" decoding="async">
                    </picture>
                    <p class="mt-4 max-w-md text-sm text-wc-text-secondary">
                        {{ __('footer.brand_desc') }}
                    </p>
                    <div class="mt-4 flex gap-3">
                        <a href="https://www.instagram.com/wellcore.fitness/" target="_blank" class="group flex h-10 w-10 items-center justify-center rounded-full bg-wc-bg-secondary text-wc-text-secondary transition-all duration-300 hover:bg-gradient-to-br hover:from-purple-500 hover:to-pink-500 hover:text-white hover:shadow-lg hover:shadow-purple-500/20" aria-label="Instagram">
                            <svg class="h-4 w-4 transition-transform group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/></svg>
                        </a>
                        <a href="https://www.youtube.com/@Wellcorefitness" target="_blank" class="group flex h-10 w-10 items-center justify-center rounded-full bg-wc-bg-secondary text-wc-text-secondary transition-all duration-300 hover:bg-red-600 hover:text-white hover:shadow-lg hover:shadow-red-600/20" aria-label="YouTube">
                            <svg class="h-4 w-4 transition-transform group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <a href="#" class="group flex h-10 w-10 items-center justify-center rounded-full bg-wc-bg-secondary text-wc-text-secondary transition-all duration-300 hover:bg-black hover:text-white hover:shadow-lg hover:shadow-black/20 dark:hover:bg-white dark:hover:text-black" aria-label="TikTok">
                            <svg class="h-4 w-4 transition-transform group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        </a>
                    </div>
                </div>
                <div class="lg:text-right">
                    <p class="text-sm font-semibold text-wc-text">{{ __('footer.newsletter_title') }}</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">{{ __('footer.newsletter_desc') }}</p>
                    <form class="mt-4 flex flex-wrap items-center gap-2 lg:justify-end"
                          x-data="{ email: '', success: false, error: '', submitting: false }"
                          x-on:submit.prevent="submitting=true; fetch('/api/newsletter', { method: 'POST', headers: {'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify({email}) }).then(r=>r.json()).then(d=>{ success=true; email=''; error=''; submitting=false; }).catch(e=>{ error='Error, intenta de nuevo'; submitting=false; })">
                        <input type="email" x-model="email" placeholder="tu@email.com" required class="w-full max-w-xs rounded-full border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                        <button type="submit" x-show="!success" :disabled="submitting" class="btn-press rounded-full bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover disabled:opacity-50">
                            <span x-show="!submitting">{{ __('footer.suscribirse') }}</span>
                            <span x-show="submitting" x-cloak class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                {{ __('footer.enviando') }}
                            </span>
                        </button>
                        {{-- Success animation --}}
                        <span x-show="success" x-cloak
                              x-transition:enter="transition ease-out duration-300"
                              x-transition:enter-start="scale-75 opacity-0"
                              x-transition:enter-end="scale-100 opacity-100"
                              class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ __('footer.suscrito') }}
                        </span>
                        <span x-show="error" x-cloak class="w-full text-xs text-red-500 lg:text-right" x-text="error"></span>
                    </form>
                </div>
            </div>

            {{-- Middle: Links grid --}}
            <div class="grid grid-cols-2 gap-8 py-10 sm:grid-cols-4">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-wc-text">{{ __('footer.programa') }}</h3>
                    <ul class="mt-4 space-y-2.5">
                        <li><a href="{{ route('metodo') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('footer.el_metodo') }}</a></li>
                        <li><a href="{{ route('proceso') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('nav.proceso') }}</a></li>
                        <li><a href="{{ route('planes') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('nav.planes') }}</a></li>
                        <li><a href="{{ route('coaches') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('nav.coaches') }}</a></li>
                        <li><a href="{{ route('presencial') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('nav.presencial') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-wc-text">{{ __('footer.recursos') }}</h3>
                    <ul class="mt-4 space-y-2.5">
                        <li><a href="{{ route('blog.index') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('nav.blog') }}</a></li>
                        <li><a href="{{ route('faq') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('nav.faq') }}</a></li>
                        <li><a href="{{ route('nosotros') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('nav.nosotros') }}</a></li>
                        {{-- RISE cerrado: oculto del footer --}}
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-wc-text">{{ __('footer.cuenta') }}</h3>
                    <ul class="mt-4 space-y-2.5">
                        <li><a href="{{ route('login') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('nav.mi_cuenta') }}</a></li>
                        <li><a href="{{ route('inscripcion') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text" aria-label="{{ __('nav.empezar') }} — inscripción WellCore Fitness">{{ __('nav.empezar') }}</a></li>
                        <li><a href="{{ route('privacidad') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('footer.privacidad') }}</a></li>
                        <li><a href="{{ route('terminos') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('footer.terminos') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-wc-text">{{ __('footer.contacto') }}</h3>
                    <ul class="mt-4 space-y-2.5">
                        <li><a href="mailto:info@wellcorefitness.com" class="text-sm text-wc-text-tertiary hover:text-wc-text">info@wellcorefitness.com</a></li>
                        <li><a href="{{ route('cookies') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('footer.cookies') }}</a></li>
                        <li><a href="{{ route('reembolsos') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">{{ __('footer.reembolsos') }}</a></li>
                    </ul>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="flex flex-col items-center justify-between gap-4 border-t border-wc-border py-6 sm:flex-row">
                <p class="text-xs text-wc-text-tertiary">&copy; {{ date('Y') }} WellCore Fitness. {{ __('footer.derechos') }}</p>
                <p class="text-xs text-wc-text-tertiary">{{ __('common.colombia') }} &middot; info@wellcorefitness.com</p>
            </div>
        </div>
    </footer>

    <x-whatsapp-button />
    <x-pwa-install-prompt />
    <x-cookie-consent />
    <x-toast-notifications />

    {{-- Sticky CTA mobile (solo en home y páginas públicas clave) --}}
    @if(request()->routeIs('home') || request()->routeIs('planes') || request()->routeIs('proceso'))
    <div class="hp-sticky-cta md:hidden" role="region" aria-label="Acción principal">
        <a href="{{ route('inscripcion') }}" class="hp-sticky-cta-btn" aria-label="Inscribirse en WellCore Fitness">
            <span class="hp-sticky-cta-label" aria-hidden="true">Empezar</span>
            <span class="hp-sticky-cta-price">desde $254k</span>
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
            </svg>
        </a>
    </div>
    @endif

    {{-- Alpine standalone SOLO si Livewire no rendero un componente (Livewire bundlea su propio Alpine).
         Cargar ambos duplica instancias → rompe wire:click y el morph del DOM (bug de forms de inscripcion). --}}
    @unless(\Livewire\Livewire::componentHasBeenRendered())
        <script defer src="/js/alpine.min.js"></script>
    @endunless
</body>
</html>
