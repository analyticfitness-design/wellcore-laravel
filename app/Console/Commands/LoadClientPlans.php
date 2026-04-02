<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RiseProgram;
use Carbon\Carbon;

class LoadClientPlans extends Command
{
    protected $signature = 'wellcore:load-plans';
    protected $description = 'Load RISE plans for Nelson, Leidy, Danna and fix Lina';

    private array $clients = [
        [
            'file'             => 'nelson_rise_plan.json',
            'client_id'        => 63,
            'rise_program_id'  => null,
            'experience_level' => 'avanzado',
            'training_location'=> 'gym',
            'gender'           => 'male',
        ],
        [
            'file'             => 'leidy_rise_plan.json',
            'client_id'        => 61,
            'rise_program_id'  => null,
            'experience_level' => 'intermedio',
            'training_location'=> 'home',
            'gender'           => 'female',
        ],
        [
            'file'             => 'danna_rise_plan.json',
            'client_id'        => 64,
            'rise_program_id'  => null,
            'experience_level' => 'intermedio',
            'training_location'=> 'gym',
            'gender'           => 'female',
        ],
        [
            'file'             => 'lina_fix_plan.json',
            'client_id'        => 28,
            'rise_program_id'  => 15,
            'experience_level' => 'principiante',
            'training_location'=> 'home',
            'gender'           => 'female',
        ],
    ];

    public function handle(): int
    {
        foreach ($this->clients as $cfg) {
            $path = base_path($cfg['file']);

            if (!file_exists($path)) {
                $this->error("File not found: {$cfg['file']}");
                continue;
            }

            $plan = json_decode(file_get_contents($path), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("Invalid JSON in {$cfg['file']}: " . json_last_error_msg());
                continue;
            }

            $data = [
                'start_date'           => Carbon::now(),
                'end_date'             => Carbon::now()->addWeeks(5),
                'experience_level'     => $cfg['experience_level'],
                'training_location'    => $cfg['training_location'],
                'gender'               => $cfg['gender'],
                'status'               => 'active',
                'personalized_program' => $plan,
            ];

            if ($cfg['rise_program_id']) {
                // Update existing record
                RiseProgram::where('id', $cfg['rise_program_id'])->update($data);
                $this->info("Updated rise_program #{$cfg['rise_program_id']} for client_id={$cfg['client_id']} ({$cfg['file']})");
            } else {
                // Create or update by client_id
                $rp = RiseProgram::updateOrCreate(
                    ['client_id' => $cfg['client_id']],
                    $data
                );
                $this->info("Upserted rise_program #{$rp->id} for client_id={$cfg['client_id']} ({$cfg['file']})");
            }
        }

        $this->info('All plans loaded successfully.');
        return 0;
    }
}
