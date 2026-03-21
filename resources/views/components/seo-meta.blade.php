@props([
    'title' => 'WellCore Fitness — Coaching 1:1 Basado en Ciencia',
    'description' => 'La primera plataforma de coaching fitness en Latinoamerica con nivel internacional. Entrenamiento y nutricion 1:1 basados en ciencia real.',
    'image' => '/images/logo-dark.png',
    'url' => request()->url(),
])

<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ asset($image) }}">
<meta property="og:locale" content="es_CO">
<meta property="og:site_name" content="WellCore Fitness">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ asset($image) }}">
