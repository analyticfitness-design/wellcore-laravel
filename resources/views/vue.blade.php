<!DOCTYPE html>
<html lang="es" class="dark" style="background-color: #09090B;">
<script nonce="@cspNonce">
    // Ajusta dark mode + bg-color del <html> para que el área safe-area-inset
    // (ej. gesture bar de Android) no muestre el blanco default del navegador.
    if (localStorage.getItem('darkMode') !== 'false') {
        document.documentElement.classList.add('dark');
        document.documentElement.style.backgroundColor = '#09090B';
    } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.style.backgroundColor = '#F5F5F7';
    }
</script>
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

    {{-- PWA manifest --}}
    <link rel="manifest" href="/manifest.json">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Raleway:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Phosphor Icons (self-hosted) -->
    <link rel="stylesheet" href="/css/phosphor.css">

    @vite(['resources/css/app.css', 'resources/js/vue/app.js'])

    {{-- Light Theme FINAL · Hero Watermark + Dark Inverted Cards (solo light mode) --}}
    <link rel="stylesheet" href="{{ asset('css/light-theme-final.css') }}?v=10">
    <link rel="stylesheet" href="{{ asset('css/workout-player.css') }}?v=1">
</head>
<body class="min-h-screen bg-wc-bg text-wc-text">
    <!-- SVG sprite para WcIcon — debe estar al inicio del body -->
    <div aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">
        @include('partials.wc-sprite')
    </div>
    <div id="vue-app"></div>
    @php
        // V2 es la única versión (decisión Daniel 2026-05-07). Sin gate, sin fallback a V1.
        $pv2Pct = 100;
        $pv2Eligible = true;

        // Metrics v2 feature flag — controlado via ENV WC_METRICS_V2 / WC_METRICS_V2_PCT
        $metricsV2Eligible = false;
        if (class_exists(\App\Services\FeatureFlagService::class)) {
            try {
                $metricsV2Eligible = \App\Services\FeatureFlagService::isEnabledForUser(
                    'metrics_v2',
                    auth('wellcore')->id()
                );
            } catch (\Throwable $e) {
                $metricsV2Eligible = false;
            }
        }
    @endphp
    <script nonce="@cspNonce">
        window.__WC_FEATURES = window.__WC_FEATURES || {};
        window.__WC_FEATURES.plan_viewer_v2 = @json($pv2Eligible);
        window.__WC_FEATURES.plan_viewer_v2_pct = @json($pv2Pct);
        window.__WC_FEATURES.metrics_v2 = @json($metricsV2Eligible);
    </script>
    @if(session('wc_token'))
    <script nonce="@cspNonce">
        window.__WC_SESSION = {
            token: @json(session('wc_token')),
            userType: @json(session('wc_user_type', 'client')),
            userId: @json(session('wc_user_id')),
            userName: @json(session('wc_user_name')),
            portal: @json(session('wc_user_portal')),
            // Legacy flags (mantenido por compat con flujos viejos)
            impersonating: @json(session()->has('wc_admin_token')),
            adminToken: @json(session('wc_admin_token')),
            // Chain-aware impersonation slots
            rootToken: @json(session('wc_root_token')),
            rootUserId: @json(session('wc_root_user_id')),
            rootUserName: @json(session('wc_root_user_name')),
            impersonationChain: @json(session('wc_impersonation_chain', [])),
        };
    </script>
    @endif
</body>
</html>
