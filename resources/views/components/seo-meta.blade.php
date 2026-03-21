@props([
    'title' => 'WellCore Fitness — Coaching 1:1 Basado en Ciencia',
    'description' => 'La primera plataforma de coaching fitness en Latinoamerica con nivel internacional. Entrenamiento y nutricion 1:1 basados en ciencia real.',
    'image' => '/images/og-default.png',
    'url' => request()->url(),
    'type' => 'website',
    'jsonLd' => null,
])

{{-- Open Graph --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ url($image) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="es_CO">
<meta property="og:site_name" content="WellCore Fitness">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ url($image) }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">

{{-- JSON-LD Structured Data --}}
@if($jsonLd)
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@else
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'WellCore Fitness',
    'url' => url('/'),
    'logo' => url('/images/logo-dark.png'),
    'description' => $description,
    'address' => [
        '@type' => 'PostalAddress',
        'addressLocality' => 'Bucaramanga',
        'addressRegion' => 'Santander',
        'addressCountry' => 'CO',
    ],
    'sameAs' => [
        'https://www.instagram.com/wellcore.fitness/',
        'https://www.youtube.com/@Wellcorefitness',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endif
