<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plan Ticket #{{ $ticket->id }} — {{ $ticket->client_name }}</title>
    <style>
        @page { size: A4; margin: 18mm 16mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #1a1a1a;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 24px;
            background: #f5f5f5;
        }
        .page {
            background: #ffffff;
            max-width: 820px;
            margin: 0 auto;
            padding: 32px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        h1 { font-size: 22px; margin: 0 0 4px; color: #DC2626; letter-spacing: 0.5px; }
        h2 {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 24px 0 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #DC2626;
            color: #1a1a1a;
        }
        h3 { font-size: 12px; margin: 12px 0 6px; color: #555; text-transform: uppercase; letter-spacing: 0.5px; }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px 24px;
            margin: 16px 0 8px;
            font-size: 11px;
        }
        .meta-grid div { padding: 4px 0; }
        .label { color: #777; font-weight: 600; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; }
        .value { color: #1a1a1a; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            background: #eee;
            color: #333;
        }
        .badge.status { background: #DC2626; color: #fff; }
        pre {
            background: #fafafa;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            padding: 10px 12px;
            font-family: 'Menlo', 'Consolas', monospace;
            font-size: 10.5px;
            white-space: pre-wrap;
            word-break: break-word;
            margin: 6px 0;
        }
        .section-empty { color: #999; font-style: italic; padding: 6px 0; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 11px; vertical-align: top; }
        th { background: #fafafa; font-weight: 600; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; color: #555; }
        .no-print { text-align: right; margin-bottom: 16px; }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #DC2626;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
        }
        .btn:hover { background: #b91c1c; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e5e5e5; color: #999; font-size: 10px; text-align: center; }

        @media print {
            body { background: #fff; padding: 0; }
            .page { box-shadow: none; max-width: none; padding: 0; }
            .no-print { display: none !important; }
            pre { border: 1px solid #ddd; }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="no-print">
        <button type="button" class="btn" onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>

    <h1>Plan Ticket #{{ $ticket->id }}</h1>
    <div>
        <span class="badge status">{{ $ticket->status?->value ?? 'n/a' }}</span>
        <span class="badge">{{ $ticket->plan_type?->value ?? 'n/a' }}</span>
        <span class="badge">{{ $ticket->category ?? 'plan_nuevo' }}</span>
    </div>

    <div class="meta-grid">
        <div><span class="label">Cliente</span><br><span class="value">{{ $ticket->client_name }} (ID {{ $ticket->client_id }})</span></div>
        <div><span class="label">Coach</span><br><span class="value">{{ $ticket->coach_name }} (ID {{ $ticket->coach_id }})</span></div>
        <div><span class="label">Creado</span><br><span class="value">{{ $ticket->created_at?->format('Y-m-d H:i') }}</span></div>
        <div><span class="label">Enviado</span><br><span class="value">{{ $ticket->submitted_at?->format('Y-m-d H:i') ?? '—' }}</span></div>
        <div><span class="label">Revisado</span><br><span class="value">{{ $ticket->reviewed_at?->format('Y-m-d H:i') ?? '—' }}</span></div>
        <div><span class="label">Completado</span><br><span class="value">{{ $ticket->completed_at?->format('Y-m-d H:i') ?? '—' }}</span></div>
        <div><span class="label">Deadline</span><br><span class="value">{{ $ticket->deadline_at?->format('Y-m-d H:i') ?? '—' }}</span></div>
        <div><span class="label">Rechazado</span><br><span class="value">{{ $ticket->rejected_at?->format('Y-m-d H:i') ?? '—' }}{{ $ticket->rejection_code ? ' ('.$ticket->rejection_code.')' : '' }}</span></div>
    </div>

    <h2>Datos generales</h2>
    @if (!empty($ticket->datos_generales))
        <table>
            @foreach ($ticket->datos_generales as $k => $v)
                <tr>
                    <th style="width: 30%;">{{ $k }}</th>
                    <td>{{ is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <div class="section-empty">Sin datos generales.</div>
    @endif

    <h2>Plan de entrenamiento</h2>
    @if (!empty($ticket->plan_entrenamiento))
        <pre>{{ json_encode($ticket->plan_entrenamiento, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
    @else
        <div class="section-empty">Sin plan de entrenamiento.</div>
    @endif

    <h2>Plan nutricional</h2>
    @if (!empty($ticket->plan_nutricional))
        <pre>{{ json_encode($ticket->plan_nutricional, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
    @else
        <div class="section-empty">Sin plan nutricional.</div>
    @endif

    <h2>Plan de hábitos</h2>
    @if (!empty($ticket->plan_habitos))
        <pre>{{ json_encode($ticket->plan_habitos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
    @else
        <div class="section-empty">Sin plan de hábitos.</div>
    @endif

    <h2>Plan de suplementación</h2>
    @if (!empty($ticket->plan_suplementacion))
        <pre>{{ json_encode($ticket->plan_suplementacion, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
    @else
        <div class="section-empty">Sin plan de suplementación.</div>
    @endif

    @if ($ticket->plan_type?->value === 'elite')
        <h2>Plan de ciclo (Elite)</h2>
        @if (!empty($ticket->plan_ciclo))
            <pre>{{ json_encode($ticket->plan_ciclo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
        @else
            <div class="section-empty">Sin plan de ciclo.</div>
        @endif
    @endif

    @if (!empty($ticket->notas_coach))
        <h2>Notas del coach</h2>
        <div>{{ $ticket->notas_coach }}</div>
    @endif

    @if (!empty($ticket->admin_notas))
        <h2>Notas del equipo</h2>
        <div>{{ $ticket->admin_notas }}</div>
    @endif

    @if ($ticket->attachments->isNotEmpty())
        <h2>Adjuntos</h2>
        <table>
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Categoría</th>
                    <th>Subido por</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ticket->attachments as $att)
                    <tr>
                        <td>{{ $att->original_name }}<br><small style="color:#888;">{{ $att->mime }} · {{ number_format($att->size_bytes / 1024, 1) }} KB</small></td>
                        <td>{{ $att->category ?? '—' }}</td>
                        <td>{{ $att->uploaded_by_name }}</td>
                        <td>{{ $att->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        WellCore Fitness — Generado el {{ now()->format('Y-m-d H:i') }}
    </div>
</div>
</body>
</html>
