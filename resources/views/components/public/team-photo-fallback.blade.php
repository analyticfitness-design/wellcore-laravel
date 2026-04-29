{{--
    <x-public.team-photo-fallback> — SVG placeholder con 4 círculos overlapping + iniciales
    hasta que la foto del equipo sea autorizada y optimizada.

    TODO: Reemplazar por <picture> con foto real del equipo (requiere autorización de Daniel).

    Props: ninguno (siempre muestra DE/CR/MV/LM)
--}}
<div class="team-photo-fallback" aria-label="Equipo de coaches WellCore">
    <svg width="200" height="64" viewBox="0 0 200 64" fill="none" xmlns="http://www.w3.org/2000/svg"
         aria-hidden="true">
        {{-- Círculo 1: DE --}}
        <circle cx="32" cy="32" r="30" fill="#1a1a1a" stroke="rgba(255,255,255,0.12)" stroke-width="2"/>
        <text x="32" y="38" text-anchor="middle" fill="#DC2626"
              font-family="Oswald,Impact,sans-serif" font-size="14" font-weight="600"
              letter-spacing="0.04em">DE</text>

        {{-- Círculo 2: CR --}}
        <circle cx="82" cy="32" r="30" fill="#1a1a1a" stroke="rgba(255,255,255,0.12)" stroke-width="2"/>
        <text x="82" y="38" text-anchor="middle" fill="#DC2626"
              font-family="Oswald,Impact,sans-serif" font-size="14" font-weight="600"
              letter-spacing="0.04em">CR</text>

        {{-- Círculo 3: MV --}}
        <circle cx="132" cy="32" r="30" fill="#1a1a1a" stroke="rgba(255,255,255,0.12)" stroke-width="2"/>
        <text x="132" y="38" text-anchor="middle" fill="#DC2626"
              font-family="Oswald,Impact,sans-serif" font-size="14" font-weight="600"
              letter-spacing="0.04em">MV</text>

        {{-- Círculo 4: LM --}}
        <circle cx="182" cy="32" r="30" fill="#1a1a1a" stroke="rgba(255,255,255,0.12)" stroke-width="2"/>
        <text x="182" y="38" text-anchor="middle" fill="#DC2626"
              font-family="Oswald,Impact,sans-serif" font-size="14" font-weight="600"
              letter-spacing="0.04em">LM</text>
    </svg>
    {{-- TODO: reemplazar por foto real del equipo cuando sea autorizada --}}
    <p class="team-photo-caption">foto · equipo wellcore</p>
</div>
