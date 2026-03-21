<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LoadPlanCommand extends Command
{
    protected $signature = 'wellcore:load-plan
        {--client= : Client ID or email}
        {--type= : Plan type (entrenamiento, nutricion, habitos, suplementacion, ciclo_hormonal, bloodwork, rise)}
        {--file= : Path to JSON file}
        {--list : List all plans for the client}
        {--coach= : Coach ID (optional, defaults to 1)}
        {--plan-version= : Plan version (optional, auto-increments)}';

    protected $description = 'Load a training/nutrition plan JSON into assigned_plans (replaces manual-load.php)';

    private const VALID_TYPES = [
        'entrenamiento', 'nutricion', 'habitos', 'suplementacion',
        'ciclo_hormonal', 'bloodwork', 'rise',
    ];

    public function handle(): int
    {
        // Find client
        $clientIdentifier = $this->option('client');
        if (!$clientIdentifier) {
            $this->error('--client is required. Use client ID or email.');
            return 1;
        }

        $client = $this->findClient($clientIdentifier);
        if (!$client) {
            $this->error("Client not found: {$clientIdentifier}");
            return 1;
        }

        $this->info("Client: {$client->name} (ID: {$client->id}, Email: {$client->email})");

        // List mode
        if ($this->option('list')) {
            return $this->listPlans($client->id);
        }

        // Load mode
        $type = $this->option('type');
        $file = $this->option('file');

        if (!$type || !$file) {
            $this->error('--type and --file are required for loading a plan.');
            $this->line('Valid types: ' . implode(', ', self::VALID_TYPES));
            return 1;
        }

        if (!in_array($type, self::VALID_TYPES)) {
            $this->error("Invalid type: {$type}");
            $this->line('Valid types: ' . implode(', ', self::VALID_TYPES));
            return 1;
        }

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $json = file_get_contents($file);
        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON: ' . json_last_error_msg());
            return 1;
        }

        $this->info("JSON valid. Size: " . strlen($json) . " bytes");

        // Get version
        $version = $this->option('plan-version');
        if (!$version) {
            $lastVersion = DB::table('assigned_plans')
                ->where('client_id', $client->id)
                ->where('plan_type', $type)
                ->max('version') ?? 0;
            $version = $lastVersion + 1;
        }

        // Deactivate previous plans of this type
        $deactivated = DB::table('assigned_plans')
            ->where('client_id', $client->id)
            ->where('plan_type', $type)
            ->where('active', 1)
            ->update(['active' => 0]);

        if ($deactivated > 0) {
            $this->warn("Deactivated {$deactivated} previous {$type} plan(s).");
        }

        // Insert new plan
        $coachId = $this->option('coach') ?? 1;

        $planId = DB::table('assigned_plans')->insertGetId([
            'client_id' => $client->id,
            'assigned_by' => $coachId,
            'plan_type' => $type,
            'content' => $json,
            'version' => $version,
            'active' => 1,
            'valid_from' => now(),
            'created_at' => now(),
        ]);

        $this->newLine();
        $this->info("Plan loaded successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['Plan ID', $planId],
                ['Client', "{$client->name} (#{$client->id})"],
                ['Type', $type],
                ['Version', $version],
                ['Size', number_format(strlen($json)) . ' bytes'],
                ['Active', 'Yes'],
                ['Coach ID', $coachId],
            ]
        );

        $this->newLine();
        $this->info("The plan is now visible in the client's dashboard via PlanViewer.");
        $this->line("Verify: php artisan wellcore:load-plan --client={$client->id} --list");

        return 0;
    }

    private function findClient(string $identifier): ?object
    {
        if (is_numeric($identifier)) {
            return DB::table('clients')->where('id', $identifier)->first();
        }

        return DB::table('clients')->where('email', $identifier)->first();
    }

    private function listPlans(int $clientId): int
    {
        $plans = DB::table('assigned_plans')
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->get(['id', 'plan_type', 'version', 'active', 'valid_from', 'created_at',
                    DB::raw('LENGTH(content) as content_bytes')]);

        if ($plans->isEmpty()) {
            $this->warn('No plans found for this client.');
            return 0;
        }

        $this->table(
            ['ID', 'Type', 'Ver', 'Active', 'Size', 'Valid From', 'Created'],
            $plans->map(fn ($p) => [
                $p->id,
                $p->plan_type,
                'v' . $p->version,
                $p->active ? 'YES' : 'no',
                number_format($p->content_bytes) . ' B',
                $p->valid_from ? substr($p->valid_from, 0, 10) : '-',
                substr($p->created_at, 0, 16),
            ])->toArray()
        );

        return 0;
    }
}
