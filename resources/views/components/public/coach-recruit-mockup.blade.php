{{--
    <x-public.coach-recruit-mockup> — Dashboard glass mockup para sección Coach Recruit.
    Datos DEMO — marcados con disclaimer "VISTA DE EJEMPLO".
    TODO: confirmar números reales con Daniel.

    Props:
        $label (string) — label del mockup
        $status (string) — estado "EN VIVO"
        $rows (array) — filas de clientes: [initials, name, status, day]
        $clients_count (string)
        $monthly_total (string)
        $disclaimer (string)
--}}
@props([
    'label'         => 'DASHBOARD COACH · ESTA SEMANA',
    'status'        => 'EN VIVO',
    'rows'          => [],
    'clients_count' => '12',
    'monthly_total' => '$3.420 USD',
    'disclaimer'    => 'VISTA DE EJEMPLO',
])

<div {{ $attributes->class(['cr-mockup', 'wc-glass']) }} aria-hidden="true" role="img">
    {{-- Header --}}
    <div class="cr-mockup-header">
        <span class="cr-mockup-label">{{ $label }}</span>
        <span class="cr-mockup-status-pill">
            <span class="cr-mockup-dot"></span>{{ $status }}
        </span>
    </div>

    {{-- Disclaimer --}}
    <div class="cr-mockup-disclaimer">
        <!-- TODO: confirmar números reales con Daniel -->
        <span class="cr-disclaimer-pill">{{ $disclaimer }}</span>
    </div>

    {{-- Client rows --}}
    @foreach($rows as $row)
        <div class="cr-row">
            <div class="cr-row-avatar {{ $row['variant'] ?? 'default' }}">{{ $row['initials'] }}</div>
            <div class="cr-row-info">
                <div class="cr-row-name">{{ $row['name'] }}</div>
                <div class="cr-row-meta">{{ $row['status'] }}</div>
            </div>
            <div class="cr-row-day">{{ $row['day'] }}</div>
        </div>
    @endforeach

    {{-- Footer --}}
    <div class="cr-mockup-footer">
        <span class="cr-footer-clients">{{ $clients_count }} {{ __('home.coach_recruit_clients_active') }}</span>
        <span class="cr-footer-total">{{ $monthly_total }}</span>
    </div>
</div>
