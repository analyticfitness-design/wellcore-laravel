{{--
    <x-public.compare-table> — Tabla comparativa Bloomberg-style (WellCore vs Otros).

    Spec: prompt-implementacion-blade.md §10 (Cap02 tabla comparativa)
    CSS:  resources/css/v2-public.css (.compare-table-v2)

    Props:
        $cols      (array of strings)         — headers (ej: ['Característica', 'WellCore', 'App Genérica', 'Gym PT']).
        $rows      (array of array of cells)  — cada row: [['text' => '...', 'good' => true|false|null, 'highlight' => bool], ...].
                                                Compatible legacy: array de strings (interpretados como text).
        $wcColIdx  (int)   — índice (1-based) de la columna WellCore para destacarla. Default 1 (segunda col).
        $sourceNote (string|null)  — nota inferior tipo "Comparativa basada en oferta estándar de mercado".

    Cell schema:
        ['text' => 'Sí, 40+ variables', 'good' => true]   → tick verde + texto
        ['text' => 'No', 'good' => false]                  → cruz roja + texto
        ['text' => 'Parcial']                              → solo texto neutro

    Ejemplo (i18n driven en metodo.blade.php):
        <x-public.compare-table
            :cols="['Característica', 'WellCore', 'App Genérica', 'Gym PT']"
            :rows="$comparisonRows"
            :wc-col-idx="1"
            source-note="Comparativa basada en oferta estándar de mercado."
        />
--}}
@props([
    'cols' => [],
    'rows' => [],
    'wcColIdx' => 1,
    'sourceNote' => null,
])

<div {{ $attributes->class(['compare-wrap-v2']) }} data-animate="fadeInUp">
    <table class="compare-table-v2">
        <thead>
            <tr>
                @foreach($cols as $idx => $col)
                    <th class="{{ $idx === $wcColIdx ? 'wc-col' : '' }}">{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $idx => $cell)
                        @php
                            // Compat: cell puede ser string o array.
                            if (is_array($cell)) {
                                $text = $cell['text'] ?? '';
                                $good = $cell['good'] ?? null;
                                $highlight = $cell['highlight'] ?? false;
                            } else {
                                $text = (string) $cell;
                                $good = null;
                                $highlight = false;
                            }
                            $isWcCol = $idx === $wcColIdx;
                            $tdClasses = [];
                            if ($isWcCol) $tdClasses[] = 'wc-col';
                            if ($highlight) $tdClasses[] = 'is-highlight';
                        @endphp
                        <td class="{{ implode(' ', $tdClasses) }}">
                            @if($good === true)
                                <span class="tick" aria-hidden="true">&#10003;</span>
                            @elseif($good === false)
                                <span class="cross" aria-hidden="true">&#10007;</span>
                            @endif
                            {{ $text }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if($sourceNote)
    <p class="source-note-v2" data-animate="fadeInUp">{{ $sourceNote }}</p>
@endif
