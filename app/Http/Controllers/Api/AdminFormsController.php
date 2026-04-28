<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AdminFormsController extends Controller
{
    // Each entry maps form area/slug to its DB source.
    // Forms without 'table' key are edit-only (no trackable submissions).
    private static function formDefinitions(): array
    {
        return [
            'client/checkin' => [
                'area' => 'client', 'slug' => 'checkin', 'tag' => 'Cliente',
                'name' => 'Check-in semanal',
                'description' => 'Wizard semanal de bienestar, entreno, nutrición y notas.',
                'table' => 'checkins', 'date_col' => 'checkin_date', 'client_col' => 'client_id',
            ],
            'client/metrics' => [
                'area' => 'client', 'slug' => 'metrics', 'tag' => 'Cliente',
                'name' => 'Métricas corporales',
                'description' => 'Peso, % grasa, % músculo, medidas.',
                'table' => 'metrics', 'date_col' => 'log_date', 'client_col' => 'client_id',
            ],
            'client/habits' => [
                'area' => 'client', 'slug' => 'habits', 'tag' => 'Cliente',
                'name' => 'Hábitos diarios',
                'description' => 'Toggle diario de hábitos asignados al plan.',
                'table' => 'habit_logs', 'date_col' => 'log_date', 'client_col' => 'client_id',
                // Multiple rows per (client, day) — count unique sessions instead of raw rows
                'distinct_client_day' => true,
            ],
            'client/supplements' => [
                'area' => 'client', 'slug' => 'supplements', 'tag' => 'Cliente',
                'name' => 'Suplementos',
                'description' => 'Registro diario de toma de suplementos.',
                'table' => 'supplement_logs', 'date_col' => 'log_date', 'client_col' => 'client_id',
                'distinct_client_day' => true,
            ],
            'client/photos' => [
                'area' => 'client', 'slug' => 'photos', 'tag' => 'Cliente',
                'name' => 'Fotos de progreso',
                'description' => 'Subida de fotos frente/perfil/espalda.',
                'table' => 'progress_photos', 'date_col' => 'photo_date', 'client_col' => 'client_id',
            ],
            'client/video-checkin' => [
                'area' => 'client', 'slug' => 'video-checkin', 'tag' => 'Cliente',
                'name' => 'Video check-in',
                'description' => 'Subida de video corto de técnica para revisión.',
                'table' => 'video_checkins', 'date_col' => 'created_at', 'client_col' => 'client_id',
            ],
            'client/tickets' => [
                'area' => 'client', 'slug' => 'tickets', 'tag' => 'Cliente',
                'name' => 'Soporte / tickets',
                'description' => 'Form para abrir un ticket de soporte técnico o de plan.',
                'table' => 'tickets', 'date_col' => 'created_at', 'client_col' => 'client_id',
            ],
            'client/profile' => [
                'area' => 'client', 'slug' => 'profile', 'tag' => 'Cliente',
                'name' => 'Editar perfil',
                'description' => 'Datos personales: nombre, ciudad, fecha nacimiento, objetivo.',
            ],
            'client/settings' => [
                'area' => 'client', 'slug' => 'settings', 'tag' => 'Cliente',
                'name' => 'Configuración cuenta',
                'description' => 'Email, password, preferencias de notificación.',
            ],
            'public/inscripcion' => [
                'area' => 'public', 'slug' => 'inscripcion', 'tag' => 'Inscripcion',
                'name' => 'Inscripción',
                'description' => 'Form principal de signup del cliente nuevo.',
                'table' => 'inscriptions', 'date_col' => 'created_at', 'client_col' => null,
                'name_cols' => ['nombre', 'apellido'],
            ],
            'public/coach-apply' => [
                'area' => 'public', 'slug' => 'coach-apply', 'tag' => 'Inscripcion',
                'name' => 'Aplicar como Coach',
                'description' => 'Form público para coaches potenciales.',
                'table' => 'coach_applications', 'date_col' => 'created_at', 'client_col' => null,
                'name_cols' => ['name'],
            ],
            'public/presencial' => [
                'area' => 'public', 'slug' => 'presencial', 'tag' => 'Inscripcion',
                'name' => 'Inscripción Presencial',
                'description' => 'Form para reservar sesión presencial.',
                'table' => 'appointments', 'date_col' => 'created_at', 'client_col' => 'client_id',
            ],
            'public/rise-enroll' => [
                'area' => 'public', 'slug' => 'rise-enroll', 'tag' => 'Inscripcion',
                'name' => 'Inscripción RISE',
                'description' => 'Form de enrollment al programa RISE.',
            ],
            'rise/habits' => [
                'area' => 'rise', 'slug' => 'habits', 'tag' => 'RISE',
                'name' => 'Hábitos RISE',
                'description' => 'Tracking diario de hábitos del programa RISE.',
                'table' => 'rise_habits_logs', 'date_col' => 'log_date', 'client_col' => 'client_id',
                'distinct_client_day' => true,
            ],
            'rise/measurements' => [
                'area' => 'rise', 'slug' => 'measurements', 'tag' => 'RISE',
                'name' => 'Mediciones RISE',
                'description' => 'Mediciones corporales del programa RISE.',
                'table' => 'rise_measurements', 'date_col' => 'log_date', 'client_col' => 'client_id',
            ],
            'rise/photos' => [
                'area' => 'rise', 'slug' => 'photos', 'tag' => 'RISE',
                'name' => 'Fotos RISE',
                'description' => 'Fotos de progreso del programa RISE.',
            ],
            'rise/tracking' => [
                'area' => 'rise', 'slug' => 'tracking', 'tag' => 'RISE',
                'name' => 'Tracking diario RISE',
                'description' => 'Form diario de agua, sueño, pasos, notas.',
                'table' => 'rise_tracking', 'date_col' => 'log_date', 'client_col' => 'client_id',
            ],
            'rise/profile' => [
                'area' => 'rise', 'slug' => 'profile', 'tag' => 'RISE',
                'name' => 'Perfil RISE',
                'description' => 'Editar perfil del usuario RISE.',
            ],
        ];
    }

    public function catalog(): JsonResponse
    {
        $defs = self::formDefinitions();
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek(Carbon::MONDAY);
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);

        $forms = array_map(function (string $key, array $def) use ($weekStart, $lastWeekStart, $lastWeekEnd): array {
            $base = [
                'slug' => $def['slug'],
                'area' => $def['area'],
                'tag' => $def['tag'],
                'name' => $def['name'],
                'description' => $def['description'],
            ];

            if (! isset($def['table'])) {
                return array_merge($base, ['has_submissions' => false, 'metrics' => null]);
            }

            $table = $def['table'];
            $dc = $def['date_col'];
            $distinct = $def['distinct_client_day'] ?? false;

            [$total, $thisWeek, $lastWeek] = $distinct
                ? $this->countDistinctClientDay($table, $dc, $weekStart, $lastWeekStart, $lastWeekEnd)
                : $this->countRows($table, $dc, $weekStart, $lastWeekStart, $lastWeekEnd);

            return array_merge($base, [
                'has_submissions' => true,
                'metrics' => [
                    'total'     => $total,
                    'this_week' => $thisWeek,
                    'last_week' => $lastWeek,
                ],
            ]);
        }, array_keys($defs), $defs);

        return response()->json(['forms' => array_values($forms)]);
    }

    public function responses(Request $request, string $area, string $slug): JsonResponse
    {
        $key = "{$area}/{$slug}";
        $catalog = self::formDefinitions();

        if (! isset($catalog[$key]) || ! isset($catalog[$key]['table'])) {
            return response()->json(['error' => 'Este formulario no genera registros rastreables.'], 422);
        }

        $def = $catalog[$key];
        $perPage = min((int) $request->input('per_page', 20), 100);
        $search = trim((string) $request->input('search', ''));
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = $this->buildResponsesQuery($def, $search, $dateFrom, $dateTo);
        $paginated = $query->paginate($perPage);

        $items = array_map(
            fn($row) => $this->formatRow((array) $row, $def, $key),
            $paginated->items()
        );

        return response()->json([
            'data' => $items,
            'meta' => [
                'total'     => $paginated->total(),
                'page'      => $paginated->currentPage(),
                'per_page'  => $paginated->perPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    public function exportCsv(Request $request, string $area, string $slug): Response
    {
        $key = "{$area}/{$slug}";
        $catalog = self::formDefinitions();

        if (! isset($catalog[$key]) || ! isset($catalog[$key]['table'])) {
            abort(422, 'Este formulario no genera registros rastreables.');
        }

        $def = $catalog[$key];
        $search = trim((string) $request->input('search', ''));
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = $this->buildResponsesQuery($def, $search, $dateFrom, $dateTo);
        $formName = str_replace('/', '-', $key);
        $fileName = "formulario-{$formName}-" . now()->format('Ymd') . '.csv';

        return response()->stream(function () use ($query, $def, $key) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['ID', 'Cliente', 'Fecha', 'Resumen']);

            $query->chunk(200, function ($rows) use ($out, $def, $key) {
                foreach ($rows as $row) {
                    $r = (array) $row;
                    fputcsv($out, [
                        $r['id'] ?? '',
                        $this->resolveClientName($r, $def),
                        $r[$def['date_col']] ?? '',
                        $this->buildSummary($r, $key),
                    ]);
                }
            });

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function buildResponsesQuery(array $def, string $search, ?string $dateFrom, ?string $dateTo)
    {
        $table = $def['table'];
        $dc = $def['date_col'];
        $clientCol = $def['client_col'] ?? null;

        $query = DB::table("{$table} as f")
            ->orderBy("f.{$dc}", 'desc');

        if ($clientCol) {
            $query->leftJoin('clients as c', "f.{$clientCol}", '=', 'c.id')
                  ->select('f.*', 'c.name as _client_name');
        } else {
            $query->select('f.*');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search, $def, $clientCol) {
                if ($clientCol) {
                    $q->where('c.name', 'like', "%{$search}%");
                }
                foreach ($def['name_cols'] ?? [] as $col) {
                    $q->orWhere("f.{$col}", 'like', "%{$search}%");
                }
            });
        }

        if ($dateFrom) {
            $query->where("f.{$dc}", '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where("f.{$dc}", '<=', "{$dateTo} 23:59:59");
        }

        return $query;
    }

    private function formatRow(array $r, array $def, string $key): array
    {
        return [
            'id'          => $r['id'] ?? null,
            'client_name' => $this->resolveClientName($r, $def),
            'date'        => $r[$def['date_col']] ?? null,
            'summary'     => $this->buildSummary($r, $key),
        ];
    }

    private function resolveClientName(array $r, array $def): string
    {
        if (isset($r['_client_name'])) {
            return $r['_client_name'] ?? '—';
        }
        if (isset($def['name_cols'])) {
            return trim(implode(' ', array_map(fn($c) => $r[$c] ?? '', $def['name_cols']))) ?: '—';
        }
        return '—';
    }

    private function buildSummary(array $r, string $key): string
    {
        return match ($key) {
            'client/checkin'      => 'Bienestar ' . ($r['bienestar'] ?? '?') . '/10 · ' . ($r['dias_entrenados'] ?? '?') . ' días',
            'client/metrics'      => ($r['peso'] ?? '?') . ' kg · ' . ($r['porcentaje_grasa'] ?? '?') . '% grasa · ' . ($r['porcentaje_musculo'] ?? '?') . '% músculo',
            'client/habits'       => 'Hábito: ' . ($r['habit_type'] ?? '?'),
            'client/supplements'  => ($r['supplement_name'] ?? '?') . ' — ' . (($r['taken'] ?? false) ? 'tomado' : 'no tomado'),
            'client/photos'       => ucfirst($r['tipo'] ?? 'foto'),
            'client/video-checkin'=> ($r['exercise_name'] ?? 'Ejercicio') . ' · ' . ($r['status'] ?? 'pendiente'),
            'client/tickets'      => ($r['ticket_type'] ?? 'ticket') . ' · ' . ($r['status'] ?? '?'),
            'public/inscripcion'  => trim(($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? '')) . ' · ' . ($r['plan'] ?? '?') . ' · ' . ($r['ciudad'] ?? '?'),
            'public/coach-apply'  => ($r['name'] ?? '?') . ' · ' . ($r['city'] ?? '?') . ' · ' . ($r['status'] ?? '?'),
            'public/presencial'   => ($r['title'] ?? 'Sesión') . ' · ' . ($r['status'] ?? '?'),
            'rise/habits'         => 'Agua: ' . ($r['water_liters'] ?? '?') . ' L · Sueño: ' . ($r['sleep_hours'] ?? '?') . ' h',
            'rise/measurements'   => ($r['weight_kg'] ?? '?') . ' kg · ' . ($r['fat_pct'] ?? '?') . '% grasa',
            'rise/tracking'       => 'Entreno: ' . (($r['training_done'] ?? false) ? 'sí' : 'no') . ' · Nutri: ' . (($r['nutrition_done'] ?? false) ? 'sí' : 'no'),
            default               => '—',
        };
    }

    /** Plain COUNT(*) with week splits. */
    private function countRows(string $table, string $dc, Carbon $weekStart, Carbon $lwStart, Carbon $lwEnd): array
    {
        return [
            (int) DB::table($table)->count(),
            (int) DB::table($table)->where($dc, '>=', $weekStart)->count(),
            (int) DB::table($table)->whereBetween($dc, [$lwStart, $lwEnd])->count(),
        ];
    }

    /** COUNT DISTINCT (client_id, DATE(date_col)) — avoids inflating daily-log tables. */
    private function countDistinctClientDay(string $table, string $dc, Carbon $weekStart, Carbon $lwStart, Carbon $lwEnd): array
    {
        $expr = "COUNT(DISTINCT CONCAT(client_id, '_', DATE({$dc})))";
        return [
            (int) DB::table($table)->selectRaw("{$expr} as cnt")->value('cnt'),
            (int) DB::table($table)->where($dc, '>=', $weekStart)->selectRaw("{$expr} as cnt")->value('cnt'),
            (int) DB::table($table)->whereBetween($dc, [$lwStart, $lwEnd])->selectRaw("{$expr} as cnt")->value('cnt'),
        ];
    }
}
