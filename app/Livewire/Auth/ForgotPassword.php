<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Recuperar Contrasena — WellCore'])]
class ForgotPassword extends Component
{
    public string $email = '';
    public bool $sent = false;
    public string $errorMsg = '';

    public function sendReset(): void
    {
        $this->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Ingresa tu email.',
            'email.email' => 'Ingresa un email valido.',
        ]);

        // Rate limit: max 3 reset requests per email per hour
        $rateLimitKey = 'password-reset:' . Str::lower($this->email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = (int) ceil($seconds / 60);
            $this->errorMsg = "Has solicitado demasiados enlaces. Intenta de nuevo en {$minutes} minuto" . ($minutes > 1 ? 's' : '') . '.';
            return;
        }

        RateLimiter::hit($rateLimitKey, 3600); // 1 hour decay

        $client = DB::table('clients')->where('email', $this->email)->first();

        if (! $client) {
            // Don't reveal if email exists — still show success
            $this->sent = true;
            return;
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $this->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // Send reset email
        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($this->email));
        $clientName = $client->name;

        try {
            Mail::send('emails.password-reset', [
                'token' => $token,
                'name' => $clientName,
                'resetUrl' => $resetUrl,
            ], function ($message) use ($clientName) {
                $message->to($this->email, $clientName)
                    ->subject('Restablecer Contrasena — WellCore Fitness');
            });
        } catch (\Exception $e) {
            \Log::error('Password reset email failed', [
                'email' => $this->email,
                'error' => $e->getMessage(),
            ]);
            $this->errorMsg = 'No pudimos enviar el email. Intenta de nuevo en unos minutos.';
            return;
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
