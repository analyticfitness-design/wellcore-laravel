<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\DB;
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

        $client = DB::table('clients')->where('email', $this->email)->first();

        if (!$client) {
            // Don't reveal if email exists — still show success
            $this->sent = true;
            return;
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $this->email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        // TODO: Send email with reset link
        // Mail::to($this->email)->send(new PasswordResetMail($token));

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
