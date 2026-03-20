<?php

namespace App\Livewire\Admin;

use App\Models\Checkin;
use App\Models\Inscription;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'Herramientas'])]
class AdminTools extends Component
{
    #[Url]
    public string $tab = 'revenue';

    /* ───── Revenue data ───── */
    public string $mrr = '0';
    public string $monthTotal = '0';
    public string $yearTotal = '0';
    public int $approvedCount = 0;
    public array $monthlyRevenue = [];
    public array $planBreakdown = [];
    public array $methodBreakdown = [];
    public array $statusDistribution = [];

    /* ───── Logs data ───── */
    public string $logFilter = 'all';
    public array $logEntries = [];
    public int $logLineCount = 0;

    /* ───── Health data ───── */
    public array $healthChecks = [];

    public function mount(): void
    {
        $this->loadRevenue();
        $this->loadLogs();
        $this->loadHealth();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    /* ================================================================
     *  REVENUE
     * ================================================================ */

    protected function loadRevenue(): void
    {
        $now = Carbon::now();

        // MRR: approved payments in last 30 days
        $mrrValue = (float) Payment::where('status', 'approved')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->sum('amount');
        $this->mrr = number_format($mrrValue, 0, ',', '.');

        // This month total
        $monthValue = (float) Payment::where('status', 'approved')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('amount');
        $this->monthTotal = number_format($monthValue, 0, ',', '.');

        // This year total
        $yearValue = (float) Payment::where('status', 'approved')
            ->whereYear('created_at', $now->year)
            ->sum('amount');
        $this->yearTotal = number_format($yearValue, 0, ',', '.');

        // Approved count this month
        $this->approvedCount = Payment::where('status', 'approved')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Monthly revenue for last 12 months
        $this->monthlyRevenue = Payment::where('status', 'approved')
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total, COUNT(*) as count")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => $row->month,
                'label' => Carbon::createFromFormat('Y-m', $row->month)->translatedFormat('M Y'),
                'total' => round((float) $row->total, 2),
                'count' => (int) $row->count,
            ])
            ->toArray();

        // Revenue by plan
        $this->planBreakdown = Payment::where('status', 'approved')
            ->selectRaw('plan, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('plan')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'plan' => $row->plan?->label() ?? ucfirst($row->getRawOriginal('plan') ?? 'Otro'),
                'total' => round((float) $row->total, 2),
                'count' => (int) $row->count,
            ])
            ->toArray();

        // Revenue by payment method
        $this->methodBreakdown = Payment::where('status', 'approved')
            ->selectRaw('COALESCE(payment_method, "Otro") as method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'method' => ucfirst($row->method ?? 'Otro'),
                'total' => round((float) $row->total, 2),
                'count' => (int) $row->count,
            ])
            ->toArray();

        // Payment status distribution
        $this->statusDistribution = Payment::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status?->label() ?? ucfirst($row->getRawOriginal('status') ?? 'Otro'),
                'count' => (int) $row->count,
            ])
            ->toArray();
    }

    /* ================================================================
     *  LOGS
     * ================================================================ */

    public function setLogFilter(string $level): void
    {
        $this->logFilter = $level;
        $this->loadLogs();
    }

    public function refreshLogs(): void
    {
        $this->loadLogs();
    }

    protected function loadLogs(): void
    {
        $logPath = storage_path('logs/laravel.log');

        if (! file_exists($logPath)) {
            $this->logEntries = [];
            $this->logLineCount = 0;
            return;
        }

        // Read last 200 lines from the log file
        $lines = $this->tailFile($logPath, 200);
        $this->logLineCount = count($lines);

        $entries = [];
        $currentEntry = null;

        foreach ($lines as $line) {
            // Match Laravel log format: [2026-03-20 10:30:00] environment.LEVEL: message
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\]\s\w+\.(\w+):\s(.*)$/', $line, $matches)) {
                if ($currentEntry) {
                    $entries[] = $currentEntry;
                }
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'level' => strtoupper($matches[2]),
                    'message' => $matches[3],
                ];
            } elseif ($currentEntry) {
                // Continuation of previous entry (stack trace, etc.)
                $currentEntry['message'] .= "\n" . $line;
            }
        }

        if ($currentEntry) {
            $entries[] = $currentEntry;
        }

        // Reverse so newest first
        $entries = array_reverse($entries);

        // Filter by level
        if ($this->logFilter !== 'all') {
            $entries = array_values(array_filter(
                $entries,
                fn ($e) => $e['level'] === strtoupper($this->logFilter)
            ));
        }

        // Limit to 100 entries
        $this->logEntries = array_slice($entries, 0, 100);
    }

    /**
     * Read the last N lines of a file efficiently.
     */
    protected function tailFile(string $path, int $lines): array
    {
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $start = max(0, $totalLines - $lines);
        $result = [];

        $file->seek($start);
        while (! $file->eof()) {
            $line = rtrim($file->current(), "\r\n");
            if ($line !== '') {
                $result[] = $line;
            }
            $file->next();
        }

        return $result;
    }

    /* ================================================================
     *  HEALTH CHECK
     * ================================================================ */

    public function refreshHealth(): void
    {
        $this->loadHealth();
    }

    protected function loadHealth(): void
    {
        $checks = [];

        // 1. Database connection
        try {
            DB::select('SELECT 1');
            $checks[] = [
                'label' => 'Base de Datos',
                'status' => 'ok',
                'value' => 'MySQL conectado',
                'icon' => 'database',
            ];
        } catch (\Throwable $e) {
            $checks[] = [
                'label' => 'Base de Datos',
                'status' => 'error',
                'value' => 'Sin conexion: ' . $e->getMessage(),
                'icon' => 'database',
            ];
        }

        // 2. Cache
        try {
            Cache::put('health_check_test', 'ok', 10);
            $cached = Cache::get('health_check_test');
            Cache::forget('health_check_test');
            $checks[] = [
                'label' => 'Cache',
                'status' => $cached === 'ok' ? 'ok' : 'warning',
                'value' => $cached === 'ok' ? 'Funcionando' : 'Lectura fallida',
                'icon' => 'cache',
            ];
        } catch (\Throwable $e) {
            $checks[] = [
                'label' => 'Cache',
                'status' => 'error',
                'value' => 'Error: ' . $e->getMessage(),
                'icon' => 'cache',
            ];
        }

        // 3. Disk space
        $freeBytes = @disk_free_space(storage_path());
        $totalBytes = @disk_total_space(storage_path());
        if ($freeBytes !== false && $totalBytes !== false) {
            $freeGb = round($freeBytes / (1024 ** 3), 1);
            $totalGb = round($totalBytes / (1024 ** 3), 1);
            $usedPercent = round((1 - $freeBytes / $totalBytes) * 100, 1);
            $diskStatus = $usedPercent > 90 ? 'error' : ($usedPercent > 75 ? 'warning' : 'ok');
            $checks[] = [
                'label' => 'Almacenamiento',
                'status' => $diskStatus,
                'value' => "{$freeGb} GB libres de {$totalGb} GB ({$usedPercent}% usado)",
                'icon' => 'disk',
            ];
        }

        // 4. PHP version
        $checks[] = [
            'label' => 'PHP Version',
            'status' => 'ok',
            'value' => PHP_VERSION,
            'icon' => 'code',
        ];

        // 5. Laravel version
        $checks[] = [
            'label' => 'Laravel Version',
            'status' => 'ok',
            'value' => app()->version(),
            'icon' => 'framework',
        ];

        // 6. Queue (check jobs table if it exists)
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            $queueStatus = $failedJobs > 0 ? 'warning' : 'ok';
            $checks[] = [
                'label' => 'Cola de Trabajo',
                'status' => $queueStatus,
                'value' => "{$pendingJobs} pendientes, {$failedJobs} fallidos",
                'icon' => 'queue',
            ];
        } catch (\Throwable) {
            $checks[] = [
                'label' => 'Cola de Trabajo',
                'status' => 'warning',
                'value' => 'Tabla jobs no encontrada',
                'icon' => 'queue',
            ];
        }

        // 7. Last payment
        $lastPayment = Payment::where('status', 'approved')->latest('created_at')->first();
        $checks[] = [
            'label' => 'Ultimo Pago',
            'status' => $lastPayment ? 'ok' : 'warning',
            'value' => $lastPayment?->created_at?->diffForHumans() ?? 'Sin pagos',
            'icon' => 'payment',
        ];

        // 8. Last inscription
        $lastInscription = Inscription::latest('created_at')->first();
        $checks[] = [
            'label' => 'Ultima Inscripcion',
            'status' => $lastInscription ? 'ok' : 'warning',
            'value' => $lastInscription?->created_at?->diffForHumans() ?? 'Sin inscripciones',
            'icon' => 'inscription',
        ];

        // 9. Last check-in
        $lastCheckin = Checkin::latest('created_at')->first();
        $checks[] = [
            'label' => 'Ultimo Check-in',
            'status' => $lastCheckin ? 'ok' : 'warning',
            'value' => $lastCheckin?->created_at?->diffForHumans() ?? 'Sin check-ins',
            'icon' => 'checkin',
        ];

        // 10. Memory usage
        $peakMemory = round(memory_get_peak_usage(true) / (1024 * 1024), 1);
        $currentMemory = round(memory_get_usage(true) / (1024 * 1024), 1);
        $memoryLimit = ini_get('memory_limit');
        $checks[] = [
            'label' => 'Memoria',
            'status' => $peakMemory > 128 ? 'warning' : 'ok',
            'value' => "Actual: {$currentMemory}MB / Pico: {$peakMemory}MB (Limite: {$memoryLimit})",
            'icon' => 'memory',
        ];

        $this->healthChecks = $checks;
    }

    /* ================================================================
     *  RENDER
     * ================================================================ */

    public function render()
    {
        return view('livewire.admin.admin-tools');
    }
}
