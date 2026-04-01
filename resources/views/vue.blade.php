<!DOCTYPE html>
<html lang="es" class="dark">
<script>if(localStorage.getItem('darkMode')!=='false')document.documentElement.classList.add('dark');else document.documentElement.classList.remove('dark')</script>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WellCore Fitness</title>
    <meta name="description" content="Coaching fitness basado en ciencia. Entrenamiento personalizado, nutricion y seguimiento para alcanzar tu mejor version.">

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
</head>
<body class="min-h-screen bg-wc-bg text-wc-text">
    <div id="vue-app"></div>
    @if(session('wc_token'))
    <script>
        window.__WC_SESSION = {
            token: @json(session('wc_token')),
            userType: @json(session('wc_user_type', 'client')),
            userId: @json(session('wc_user_id')),
            impersonating: @json(session()->has('wc_admin_token')),
        };
    </script>
    @endif
</body>
</html>
