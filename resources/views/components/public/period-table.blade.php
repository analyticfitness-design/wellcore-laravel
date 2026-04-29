{{--
    <x-public.period-table> — Tabla de periodización (4 fases: ADAPT/HIPER/FUERZA/DESCARGA).

    Spec: prompt-implementacion-blade.md §10.6 (Cap04 El Plan)
    CSS:  resources/css/v2-public.css (.period-table-v2 + .phase-tag-*)

    Props:
        $headers (array of strings)  — header row labels (ej: ['Fase', 'Semanas', 'Objetivo', 'Intensidad relativa', 'Volumen']).
        $phases  (array of objects)  — items con keys:
                                         type      → 'adapt' | 'hyper' | 'fuerza' | 'desc' (color del phase-tag)
                                         tag       → texto del phase-tag (ej: 'ADAPTACIÓN')
                                         name      → nombre fase (ej: 'ADAPTACIÓN')  -- duplicado intencional para diseño editorial
                                         weeks     → '1–3'
                                         objective → texto largo
                                         intensity → 'Moderada–baja' (NUNCA "RIR" — Daniel decision)
                                         volume    → 'Moderado'
        $sourceNote (string|null)    — nota inferior tipo "Haff & Triplett 2016 ...".

    Ejemplo:
        <x-public.period-table
            :headers="['Fase', 'Semanas', 'Objetivo fisiológico', 'Intensidad relativa', 'Volumen']"
            :phases="$periodPhases"
            source-note="Haff & Triplett 2016 · Periodization Theory · adaptado al protocolo WellCore"
        />
--}}
@props([
    'headers' => [],
    'phases' => [],
    'sourceNote' => null,
])

<div {{ $attributes->class(['compare-wrap-v2']) }} data-animate="fadeInUp">
    <table class="period-table-v2">
        <thead>
            <tr>
                @foreach($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($phases as $phase)
                @php
                    $type = $phase['type'] ?? 'adapt';
                    $tag = $phase['tag'] ?? '';
                    $name = $phase['name'] ?? $tag;
                    $weeks = $phase['weeks'] ?? '';
                    $obj = $phase['objective'] ?? '';
                    $intensity = $phase['intensity'] ?? '';
                    $vol = $phase['volume'] ?? '';
                @endphp
                <tr>
                    <td class="period-phase-cell">
                        <span class="phase-tag phase-{{ $type }}">{{ $tag }}</span>
                        <span class="period-phase-name">{{ $name }}</span>
                    </td>
                    <td class="period-cell-muted">{{ $weeks }}</td>
                    <td>{{ $obj }}</td>
                    <td class="period-cell-mono">{{ $intensity }}</td>
                    <td class="period-cell-mono">{{ $vol }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if($sourceNote)
    <p class="source-note-v2" data-animate="fadeInUp">{{ $sourceNote }}</p>
@endif
