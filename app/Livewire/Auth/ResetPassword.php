<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\DB;
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
    }

    public function resetPassword(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Ingresa tu email.',
            'password.required' => 'Ingresa tu nueva contrasena.',
            'password.min' => 'Minimo 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $this->email)
            ->where('token', hash('sha256', $this->token))
            ->where('created_at', '>', now()->subHours(2))
            ->first();

        if (!$record) {
            $this->invalid = true;
            return;
        }

        DB::table('clients')
            ->where('email', $this->email)
            ->update(['password_hash' => password_hash($this->password, PASSWORD_BCRYPT)]);

        DB::table('password_reset_tokens')->where('email', $this->email)->delete();

        $this->reset = true;
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
