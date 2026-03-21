<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Nueva Contrasena — WellCore'])]
class ResetPassword extends Component
{
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $reset = false;
    public bool $invalid = false;

    public function mount(string $token = ''): void
    {
        $this->token = $token;
        $this->email = request('email', '');

        // Validate token exists and is not expired (1 hour)
        if ($this->email && $this->token) {
            $record = DB::table('password_reset_tokens')
                ->where('email', $this->email)
                ->first();

            if (! $record || ! Hash::check($this->token, $record->token) ||
                now()->diffInMinutes($record->created_at) > 60) {
                $this->invalid = true;
            }
        } else {
            $this->invalid = true;
        }
    }

    public function resetPassword(): void
    {
        if ($this->invalid) {
            return;
        }

        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Ingresa tu email.',
            'password.required' => 'Ingresa tu nueva contrasena.',
            'password.min' => 'Minimo 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
        ]);

        // Re-verify token (defense in depth)
        $record = DB::table('password_reset_tokens')
            ->where('email', $this->email)
            ->first();

        if (! $record || ! Hash::check($this->token, $record->token) ||
            now()->diffInMinutes($record->created_at) > 60) {
            $this->invalid = true;
            return;
        }

        // Verify client exists
        $client = DB::table('clients')->where('email', $this->email)->first();
        if (! $client) {
            $this->invalid = true;
            return;
        }

        // Update password
        DB::table('clients')
            ->where('email', $this->email)
            ->update(['password_hash' => password_hash($this->password, PASSWORD_BCRYPT)]);

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $this->email)->delete();

        $this->reset = true;
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
