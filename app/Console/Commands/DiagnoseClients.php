<?php

namespace App\Console\Commands;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\HabitLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class DiagnoseClients extends Command
{
    protected $signature = 'wellcore:diagnose-clients';

    protected $description = 'Diagnose and report on client plans and habit logs for key clients';

    private array $targetEmails = [
        'l.gizethmm29@gmail.com',
        'Angiev.diaz20@gmail.com',
        'asarmientoslm@gmail.com',
        'vane08_26@hotmail.com',
        'nelsonroasotelo@gmail.com',
        'joselunagiron28@gmail.com',
    ];

    public function handle(): int
    {
        $clients = $this->resolveClients();

        if ($clients->isEmpty()) {
            $this->error('No clients found.');

            return self::FAILURE;
        }

        $referenceClientEmail = 'joselunagiron28@gmail.com';

        foreach ($clients as $client) {
            $this->diagnoseClient($client);
        }

        $this->outputReference($clients, $referenceClientEmail);

        return self::SUCCESS;
    }

    private function resolveClients(): Collection
    {
        $byEmail = Client::query()
            ->whereIn('email', $this->targetEmails)
            ->get();

        $dannaByName = Client::query()
            ->where('name', 'like', '%danna%')
            ->orWhere('name', 'like', '%Danna%')
            ->get();

        return $byEmail->merge($dannaByName)->unique('id');
    }

    private function diagnoseClient(Client $client): void
    {
        $this->newLine();
        $this->info(str_repeat('=', 80));
        $this->info("CLIENT: {$client->name} (ID: {$client->id})");
        $this->info("Email: {$client->email}");
        $this->info('Plan: '.($client->plan?->value ?? 'N/A').' | Status: '.($client->status?->value ?? 'N/A'));
        $this->info(str_repeat('-', 80));

        $this->diagnoseAssignedPlans($client);
        $this->diagnoseHabitLogs($client);
    }

    private function diagnoseAssignedPlans(Client $client): void
    {
        $plans = AssignedPlan::query()
            ->where('client_id', $client->id)
            ->where('active', true)
            ->get();

        if ($plans->isEmpty()) {
            $this->warn('  No active assigned_plans found.');

            return;
        }

        $this->info("  Active Assigned Plans: {$plans->count()}");
        $this->newLine();

        foreach ($plans as $plan) {
            $content = $plan->content;
            $jsonStr = is_array($content) ? json_encode($content, JSON_UNESCAPED_UNICODE) : '';
            $jsonLen = strlen($jsonStr);
            $preview = mb_substr($jsonStr, 0, 100);

            $this->line("  [{$plan->plan_type}] ID: {$plan->id} | JSON length: {$jsonLen} chars");
            $this->line("    Preview: {$preview}...");

            match ($plan->plan_type) {
                'nutricion' => $this->validateNutritionJson($content),
                'entrenamiento' => $this->validateTrainingJson($content),
                default => $this->line("    (No specific validation for '{$plan->plan_type}')"),
            };

            $this->newLine();
        }
    }

    private function validateNutritionJson(?array $content): void
    {
        if (! $content) {
            $this->error('    Nutrition JSON is empty/null');

            return;
        }

        $hasCalories = isset($content['calorias_diarias'])
            || isset($content['calorias'])
            || isset($content['kcal_diarias']);

        $hasProtein = isset($content['macros']['proteina_g'])
            || isset($content['macros']['proteinas_g'])
            || isset($content['macros']['proteina'])
            || isset($content['proteina_g']);

        $hasMeals = isset($content['comidas']) && is_array($content['comidas']);

        $status = fn (bool $ok): string => $ok ? '✓' : '✗';

        $this->line('    Nutrition validation:');
        $this->line("      {$status($hasCalories)} calorias_diarias");
        $this->line("      {$status($hasProtein)} macros.proteina_g");
        $this->line("      {$status($hasMeals)} comidas array".($hasMeals ? ' ('.count($content['comidas']).' items)' : ''));

        if (isset($content['macros']) && is_array($content['macros'])) {
            $this->line('      Macros keys: '.implode(', ', array_keys($content['macros'])));
        }
    }

    private function validateTrainingJson(?array $content): void
    {
        if (! $content) {
            $this->error('    Training JSON is empty/null');

            return;
        }

        $hasSemanas = isset($content['semanas']) && is_array($content['semanas']);

        $status = fn (bool $ok): string => $ok ? '✓' : '✗';

        $this->line('    Training validation:');
        $this->line("      {$status($hasSemanas)} semanas array".($hasSemanas ? ' ('.count($content['semanas']).' weeks)' : ''));

        if (! $hasSemanas) {
            $this->line('      Top-level keys: '.implode(', ', array_keys($content)));

            return;
        }

        $firstWeek = $content['semanas'][0] ?? null;
        if (! $firstWeek) {
            return;
        }

        $hasDias = isset($firstWeek['dias']) && is_array($firstWeek['dias']);
        $this->line("      {$status($hasDias)} semanas[0].dias".($hasDias ? ' ('.count($firstWeek['dias']).' days)' : ''));

        if (! $hasDias) {
            $this->line('      Week[0] keys: '.implode(', ', array_keys($firstWeek)));

            return;
        }

        $firstDay = $firstWeek['dias'][0] ?? null;
        if (! $firstDay) {
            return;
        }

        $hasEjercicios = isset($firstDay['ejercicios']) && is_array($firstDay['ejercicios']);
        $this->line("      {$status($hasEjercicios)} dias[0].ejercicios".($hasEjercicios ? ' ('.count($firstDay['ejercicios']).' exercises)' : ''));

        if (! $hasEjercicios) {
            $this->line('      Day[0] keys: '.implode(', ', array_keys($firstDay)));

            return;
        }

        $firstExercise = $firstDay['ejercicios'][0] ?? null;
        if (! $firstExercise) {
            return;
        }

        $hasSeries = isset($firstExercise['series']);
        $hasReps = isset($firstExercise['repeticiones']) || isset($firstExercise['reps']);

        $this->line("      {$status($hasSeries)} ejercicios[0].series");
        $this->line("      {$status($hasReps)} ejercicios[0].repeticiones");
        $this->line('      Exercise[0] keys: '.implode(', ', array_keys($firstExercise)));
    }

    private function diagnoseHabitLogs(Client $client): void
    {
        $since = Carbon::now()->subDays(30);

        $counts = HabitLog::query()
            ->where('client_id', $client->id)
            ->where('log_date', '>=', $since)
            ->selectRaw('habit_type, COUNT(*) as total')
            ->groupBy('habit_type')
            ->pluck('total', 'habit_type');

        $this->info('  Habit Logs (last 30 days):');

        if ($counts->isEmpty()) {
            $this->warn('    No habit logs found.');

            return;
        }

        foreach ($counts as $type => $total) {
            $this->line("    {$type}: {$total} entries");
        }
    }

    private function outputReference(Collection $clients, string $email): void
    {
        $cesar = $clients->firstWhere('email', $email);

        if (! $cesar) {
            $this->warn("Reference client ({$email}) not found.");

            return;
        }

        $this->newLine(2);
        $this->info(str_repeat('=', 80));
        $this->info("REFERENCE: {$cesar->name} — Full Plan JSON");
        $this->info(str_repeat('=', 80));

        $plans = AssignedPlan::query()
            ->where('client_id', $cesar->id)
            ->where('active', true)
            ->whereIn('plan_type', ['entrenamiento', 'nutricion'])
            ->get();

        foreach ($plans as $plan) {
            $this->newLine();
            $this->info("--- {$plan->plan_type} (ID: {$plan->id}) ---");
            $this->line(json_encode($plan->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}
