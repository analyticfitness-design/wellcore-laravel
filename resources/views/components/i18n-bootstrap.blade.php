{{--
  i18n-bootstrap: inyecta el locale activo + lock state + namespaces críticos
  para que vue-i18n hidrate sin FOUC. Diseño: docs/adr/0004-i18n-en-us.md

  Usage:
    <x-i18n-bootstrap />
    o:
    <x-i18n-bootstrap :namespaces="['nav','dashboard','common','validation']" />
--}}
@props([
    'namespaces' => ['nav', 'dashboard', 'client_dashboard', 'client_nav', 'client_home', 'coach_nav', 'common', 'coaches', 'validation'],
])

@php
    $locale = app()->getLocale();
    $authUser = auth('wellcore')->user() ?? auth()->user();
    $localeLocked = $authUser?->locale_locked ?? false;
    $unitSystem = $authUser?->unit_system ?? 'metric';
    $currency = $authUser?->currency ?? null;

    $messages = [];
    $supported = ['es', 'en'];
    $resolvedLocale = in_array($locale, $supported, true) ? $locale : 'es';

    foreach ($namespaces as $ns) {
        $path = base_path("lang/{$resolvedLocale}/{$ns}.php");
        if (is_file($path)) {
            $payload = require $path;
            if (is_array($payload)) {
                $messages[$ns] = $payload;
            }
        }
    }
@endphp

<meta name="wc-locale" content="{{ $resolvedLocale }}">
@if($localeLocked)
    <meta name="wc-locale-locked" content="1">
@endif
<meta name="wc-unit-system" content="{{ $unitSystem }}">
@if($currency)
    <meta name="wc-currency" content="{{ $currency }}">
@endif

<script nonce="@cspNonce">
    window.__wcInitialLocale = @json($resolvedLocale);
    window.__wcLocaleLocked = @json((bool) $localeLocked);
    window.__wcUnitSystem = @json($unitSystem);
    window.__wcCurrency = @json($currency);
    window.__wcMessages = @json($messages);
</script>
