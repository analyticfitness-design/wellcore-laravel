<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\RiseProgram;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateAdrianaPlan extends Command
{
    protected $signature = 'adriana:plan';
    protected $description = 'Create RISE plan for Adriana Sarmiento';

    public function handle()
    {
        // Load JSON
        $json = file_get_contents(base_path('adriana_rise_plan.json'));
        $plan = json_decode($json, true);

        // Find or create client (assuming email or name exists)
        $client = Client::where('name', 'like', '%ADRIANA%')
            ->orWhere('name', 'like', '%Adriana%')
            ->orWhere('email', 'like', '%adriana%')
            ->first();

        if (!$client) {
            $this->error('Client Adriana Sarmiento not found');
            return 1;
        }

        // Create/Update RISE program
        $riseProgram = RiseProgram::updateOrCreate(
            ['client_id' => $client->id],
            [
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(5),
                'experience_level' => 'intermediate_advanced',
                'training_location' => 'full_gym',
                'gender' => 'female',
                'status' => 'active',
                'personalized_program' => $plan,
            ]
        );

        $this->info("RISE plan created for Adriana Sarmiento (ID: {$client->id})");
        $this->info("Program ID: {$riseProgram->id}");
        $this->info("Duration: 5 weeks, Weekly Volume: 98 sets");

        return 0;
    }
}
