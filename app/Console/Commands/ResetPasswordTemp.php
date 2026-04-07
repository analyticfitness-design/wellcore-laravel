<?php
namespace App\Console\Commands;
use App\Models\Client;
use Illuminate\Console\Command;

class ResetPasswordTemp extends Command
{
    protected $signature = 'temp:reset-pw {email} {password}';
    protected $description = 'Temporary password reset';

    public function handle(): void
    {
        $client = Client::where('email', $this->argument('email'))->first();
        if (!$client) {
            $this->error('Client not found');
            return;
        }
        $client->password = bcrypt($this->argument('password'));
        $client->save();
        $this->info("Password updated for: {$client->name} ({$client->email})");
    }
}
