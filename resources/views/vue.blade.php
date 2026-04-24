<!DOCTYPE html>
<html lang="es" class="dark">
<script nonce="@cspNonce">if(localStorage.getItem('darkMode')!=='false')document.documentElement.classList.add('dark');else document.documentElement.classList.remove('dark')</script>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WellCore Fitness</title>
    <meta name="description" content="Coaching fitness basado en ciencia. Entrenamiento personalizado, nutricion y seguimiento para alcanzar tu mejor version.">

    {{-- PWA / app-shell metas --}}
    <meta name="theme-color" content="#09090B" id="wc-theme-color">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="WellCore">
    <meta name="application-name" content="WellCore">
    <script nonce="@cspNonce">
        // Sincroniza el theme-color con el modo actual (dark/light).
        // Se ejecuta inline para evitar flicker en el status bar mobile.
        (function () {
            var meta = document.getElementById('wc-theme-color');
            if (!meta) return;
            var isDark = document.documentElement.classList.contains('dark');
            meta.setAttribute('content', isDark ? '#09090B' : '#FAFAF8');
        })();
    </script>

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Raleway:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/vue/app.js'])

    {{-- Light Theme FINAL · Hero Watermark + Dark Inverted Cards (solo light mode) --}}
    <link rel="stylesheet" href="{{ asset('css/light-theme-final.css') }}?v=4">
</head>
<body class="min-h-screen bg-wc-bg text-wc-text">
    <!-- SVG sprite para WcIcon — debe estar al inicio del body -->
    <div aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">
        @include('partials.wc-sprite')
    </div>
    <div id="vue-app"></div>
    @if(session('wc_token'))
    <script nonce="@cspNonce">
        window.__WC_SESSION = {
            token: @json(session('wc_token')),
            userType: @json(session('wc_user_type', 'client')),
            userId: @json(session('wc_user_id')),
            userName: @json(session('wc_user_name')),
            portal: @json(session('wc_user_portal')),
            impersonating: @json(session()->has('wc_admin_token')),
            adminToken: @json(session('wc_admin_token')),
        };
    </script>
    @endif
</body>
</html>
