<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminCampaignController extends Controller
{
    use AuthenticatesVueRequests;

    protected function resolveAdminOrFail(Request $request): Admin
    {
        $auth = $this->resolveAuthUser($request);

        if (! $auth) {
            abort(401, 'Token invalido o expirado.');
        }

        if ($auth['userType'] !== UserType::Admin) {
            abort(403, 'Acceso solo para administradores.');
        }

        $admin = $auth['user'];
        $role = $admin->role?->value ?? $admin->role ?? '';

        if (! in_array($role, ['admin', 'superadmin', 'jefe'])) {
            abort(403, 'No tienes permisos de administrador.');
        }

        return $admin;
    }

    public function index(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $platform = $request->query('platform');
        $status   = $request->query('status');
        $search   = trim((string) $request->query('search', ''));
        $sortBy   = $request->query('sort_by', 'created_at');
        $sortDir  = $request->query('sort_dir', 'desc');

        $allowedSorts = ['name', 'platform', 'status', 'budget_cop', 'spent_cop', 'impressions', 'clicks', 'leads', 'sales', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        $query = Campaign::query()->orderBy($sortBy, $sortDir);

        if ($platform && in_array($platform, ['meta', 'google', 'tiktok', 'email'])) {
            $query->where('platform', $platform);
        }

        if ($status && in_array($status, ['active', 'paused', 'ended'])) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $paginator = $query->paginate(30);

        $campaigns = $paginator->getCollection()->map(fn (Campaign $c) => $this->formatRow($c));

        // KPIs del mes en curso
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        $kpis = Campaign::query()
            ->where(function ($q) use ($monthStart, $now) {
                $q->whereBetween('start_date', [$monthStart, $now])
                  ->orWhereBetween('end_date', [$monthStart, $now])
                  ->orWhere(function ($q2) use ($monthStart, $now) {
                      $q2->where('start_date', '<=', $monthStart)
                         ->where(function ($q3) use ($now) {
                             $q3->whereNull('end_date')->orWhere('end_date', '>=', $now);
                         });
                  });
            })
            ->selectRaw('
                COALESCE(SUM(spent_cop), 0) as spend_mes,
                COALESCE(SUM(sales), 0) as conversiones_mes,
                COALESCE(SUM(leads), 0) as leads_mes
            ')
            ->first();

        $spendMes       = (int) ($kpis->spend_mes ?? 0);
        $conversionesMes = (int) ($kpis->conversiones_mes ?? 0);
        $leadsMes       = (int) ($kpis->leads_mes ?? 0);

        $revenueMes = Campaign::query()
            ->where(function ($q) use ($monthStart, $now) {
                $q->whereBetween('start_date', [$monthStart, $now])
                  ->orWhereBetween('end_date', [$monthStart, $now])
                  ->orWhere(function ($q2) use ($monthStart, $now) {
                      $q2->where('start_date', '<=', $monthStart)
                         ->where(function ($q3) use ($now) {
                             $q3->whereNull('end_date')->orWhere('end_date', '>=', $now);
                         });
                  });
            })
            ->sum('revenue_cop');

        $roasPromedio = $spendMes > 0 ? round($revenueMes / $spendMes, 2) : 0;
        $cplPromedio  = $leadsMes > 0 ? round($spendMes / $leadsMes) : 0;

        return response()->json([
            'data'       => $campaigns,
            'meta'       => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
            ],
            'kpis' => [
                'spend_mes'        => $spendMes,
                'conversiones_mes' => $conversionesMes,
                'roas_promedio'    => $roasPromedio,
                'cpl_promedio'     => $cplPromedio,
            ],
        ]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $campaign = Campaign::findOrFail($id);

        $funnel = [
            ['label' => 'Impresiones', 'value' => $campaign->impressions],
            ['label' => 'Clicks',      'value' => $campaign->clicks],
            ['label' => 'Leads',       'value' => $campaign->leads],
            ['label' => 'Ventas',      'value' => $campaign->sales],
        ];

        return response()->json([
            'campaign' => $this->formatRow($campaign),
            'funnel'   => $funnel,
            'timeline' => $campaign->daily_stats ?? [],
        ]);
    }

    public function pause(int $id, Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $campaign = Campaign::findOrFail($id);

        if ($campaign->status === 'ended') {
            return response()->json(['error' => 'Una campaña terminada no puede pausarse.'], 422);
        }

        $campaign->status = 'paused';
        $campaign->save();

        return response()->json(['ok' => true, 'campaign' => $this->formatRow($campaign)]);
    }

    public function resume(int $id, Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $campaign = Campaign::findOrFail($id);

        if ($campaign->status === 'ended') {
            return response()->json(['error' => 'Una campaña terminada no puede reactivarse.'], 422);
        }

        $campaign->status = 'active';
        $campaign->save();

        return response()->json(['ok' => true, 'campaign' => $this->formatRow($campaign)]);
    }

    public function duplicate(int $id, Request $request): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        $original = Campaign::findOrFail($id);

        $copy = $original->replicate();
        $copy->name       = $original->name . ' (copia)';
        $copy->status     = 'paused';
        $copy->created_by = $admin->id;
        $copy->save();

        return response()->json(['ok' => true, 'campaign' => $this->formatRow($copy)]);
    }

    public function import(Request $request): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        $request->validate([
            'file'     => 'required|file|mimes:csv,txt|max:5120',
            'platform' => 'required|in:meta,google,tiktok,email',
        ]);

        $platform = $request->input('platform');
        $file     = $request->file('file');

        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return response()->json(['error' => 'No se pudo leer el archivo.'], 422);
        }

        // Primera línea = headers
        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);
            return response()->json(['error' => 'El archivo CSV está vacío o malformado.'], 422);
        }

        $headers = array_map('strtolower', array_map('trim', $headers));

        $imported = 0;
        $errors   = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }

            $data = array_combine($headers, $row);

            $name = $data['campaign name'] ?? $data['name'] ?? $data['campaña'] ?? null;
            if (! $name) {
                continue;
            }

            try {
                Campaign::create([
                    'name'        => trim($name),
                    'platform'    => $platform,
                    'status'      => $this->parseStatus($data['status'] ?? 'active'),
                    'budget_cop'  => $this->parseCop($data['budget'] ?? $data['presupuesto'] ?? 0),
                    'spent_cop'   => $this->parseCop($data['amount spent'] ?? $data['spent'] ?? $data['gastado'] ?? 0),
                    'revenue_cop' => $this->parseCop($data['revenue'] ?? $data['purchase value'] ?? $data['ingresos'] ?? 0),
                    'impressions' => (int) ($data['impressions'] ?? $data['impresiones'] ?? 0),
                    'clicks'      => (int) ($data['link clicks'] ?? $data['clicks'] ?? 0),
                    'leads'       => (int) ($data['leads'] ?? 0),
                    'sales'       => (int) ($data['purchases'] ?? $data['sales'] ?? $data['ventas'] ?? 0),
                    'start_date'  => $this->parseDate($data['start date'] ?? $data['start'] ?? null),
                    'end_date'    => $this->parseDate($data['end date'] ?? $data['end'] ?? null),
                    'created_by'  => $admin->id,
                ]);
                $imported++;
            } catch (\Throwable $e) {
                Log::warning('campaign_import_row_error', ['row' => $data, 'error' => $e->getMessage()]);
                $errors[] = trim($name);
            }
        }

        fclose($handle);

        return response()->json([
            'ok'       => true,
            'imported' => $imported,
            'errors'   => $errors,
        ]);
    }

    private function formatRow(Campaign $c): array
    {
        return [
            'id'          => $c->id,
            'name'        => $c->name,
            'platform'    => $c->platform,
            'status'      => $c->status,
            'budget_cop'  => $c->budget_cop,
            'spent_cop'   => $c->spent_cop,
            'revenue_cop' => $c->revenue_cop,
            'impressions' => $c->impressions,
            'clicks'      => $c->clicks,
            'leads'       => $c->leads,
            'sales'       => $c->sales,
            'ctr'         => $c->ctr,
            'cr'          => $c->cr,
            'roas'        => $c->roas,
            'cpl'         => $c->cpl,
            'start_date'  => $c->start_date?->toDateString(),
            'end_date'    => $c->end_date?->toDateString(),
            'created_at'  => $c->created_at?->toDateTimeString(),
        ];
    }

    private function parseStatus(string $raw): string
    {
        $raw = strtolower(trim($raw));
        if (in_array($raw, ['active', 'activo', 'activa', 'enabled', 'running'])) {
            return 'active';
        }
        if (in_array($raw, ['paused', 'pausada', 'pausado', 'stopped'])) {
            return 'paused';
        }
        return 'ended';
    }

    private function parseCop(mixed $raw): int
    {
        if (is_null($raw) || $raw === '') {
            return 0;
        }
        // Remove currency symbols and formatting
        $clean = preg_replace('/[^0-9.]/', '', (string) $raw);
        return (int) round((float) $clean);
    }

    private function parseDate(mixed $raw): ?string
    {
        if (! $raw) {
            return null;
        }
        try {
            return Carbon::parse(trim((string) $raw))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
