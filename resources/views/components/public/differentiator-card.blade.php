@props([
    'icon' => '',
    'title' => '',
    'body' => '',
    'badge' => null,
    'featured' => false,
])

{{--
  Card de diferenciador transversal (lo que TODOS los planes incluyen).
  Usado en /planes sección "EN TODOS LOS PLANES" entre comparador y testimonios.
  Spec: MASTER §5 + RULES-RESPONSIVE §3
  CSS: .differentiator-card en v2-public.css

  Props:
    - icon (string): nombre del icono. Soportados: mic | plate | shuffle | video |
                     chat | users | medal | wifi-off | target. Phosphor outline 24px stroke 1.5.
    - title (string): título de la feature (Oswald uppercase).
    - body (string): descripción corta (Raleway 14px).
    - badge (string|null): badge opcional, solo visible si featured=true.
    - featured (bool): si true → glass-strong + border accent + badge visible.
                       Reservado para Voice Logger ("Primero en LATAM").

  Ejemplo:
    <x-public.differentiator-card
        icon="mic"
        title="Voice Logger"
        body="Anota tu entrenamiento por voz."
        badge="Primero en LATAM"
        :featured="true" />
--}}
<article @class([
    'differentiator-card',
    'differentiator-card--featured' => $featured,
])>
    @if($featured && filled($badge))
        <span class="differentiator-badge">{{ $badge }}</span>
    @endif

    <div class="differentiator-icon" aria-hidden="true">
        @switch($icon)
            @case('mic')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="9" y="2" width="6" height="13" rx="3"/>
                    <path d="M5 10v1a7 7 0 0 0 14 0v-1"/>
                    <line x1="12" y1="18" x2="12" y2="22"/>
                    <line x1="8" y1="22" x2="16" y2="22"/>
                </svg>
                @break
            @case('plate')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <circle cx="12" cy="12" r="5"/>
                    <path d="M12 7v10"/>
                    <path d="M7 12h10"/>
                </svg>
                @break
            @case('shuffle')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="16 3 21 3 21 8"/>
                    <line x1="4" y1="20" x2="21" y2="3"/>
                    <polyline points="21 16 21 21 16 21"/>
                    <line x1="15" y1="15" x2="21" y2="21"/>
                    <line x1="4" y1="4" x2="9" y2="9"/>
                </svg>
                @break
            @case('video')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="23 7 16 12 23 17 23 7"/>
                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                </svg>
                @break
            @case('chat')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                @break
            @case('users')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                @break
            @case('medal')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="15" r="6"/>
                    <path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.12"/>
                    <path d="M7 9V2h10v7"/>
                </svg>
                @break
            @case('wifi-off')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="1" y1="1" x2="23" y2="23"/>
                    <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"/>
                    <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/>
                    <path d="M10.71 5.05A16 16 0 0 1 22.58 9"/>
                    <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"/>
                    <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                    <line x1="12" y1="20" x2="12.01" y2="20"/>
                </svg>
                @break
            @case('target')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <circle cx="12" cy="12" r="6"/>
                    <circle cx="12" cy="12" r="2"/>
                </svg>
                @break
        @endswitch
    </div>

    <h3 class="differentiator-title">{{ $title }}</h3>
    <p class="differentiator-body">{{ $body }}</p>
</article>
