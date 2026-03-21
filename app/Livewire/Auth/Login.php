<?php

namespace App\Livewire\Auth;

use App\Enums\PlanType;
use App\Enums\UserRole;
use App\Enums\UserType;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Iniciar Sesión — WellCore'])]
class Login extends Component
{
    #[Validate('required|string|min:3')]
    public string $identity = '';

    #[Validate('required|string|min:1')]
    public string $password = '';

    public bool $rememberMe = false;

    public string $errorMessage = '';

    public bool $isLoading = false;

    public bool $loginSuccess = false;

    public function login(): void
    {
        $this->errorMessage = '';
        $this->isLoading = true;

        $this->validate();

        // Try to find the user: first check admins by username, then clients by email
        $user = Admin::where('username', $this->identity)->first();
        $userType = UserType::Admin;

        if (! $user) {
            $user = Client::where('email', $this->identity)->first();
            $userType = UserType::Client;
        }

        if (! $user) {
            $this->isLoading = false;
            $this->errorMessage = 'No encontramos una cuenta con esas credenciales.';
            return;
        }

        // Verify password against the stored hash
        if (! password_verify($this->password, $user->password_hash)) {
            $this->isLoading = false;
            $this->errorMessage = 'La contraseña es incorrecta.';
            return;
        }

        // Create auth token (64-char hex, matching the vanilla PHP app)
        $token = bin2hex(random_bytes(32));

        AuthToken::create([
            'user_type' => $userType->value,
            'user_id' => $user->id,
            'token' => $token,
            'ip_address' => request()->ip(),
            'expires_at' => now()->addDays(30),
        ]);

        // Store token in session for Laravel web auth
        session()->put('wc_token', $token);
        session()->put('wc_user_type', $userType->value);
        session()->put('wc_user_id', $user->id);

        // Determine redirect URL based on user type and role/plan
        $redirectUrl = $this->resolveRedirectUrl($user, $userType);

        $this->loginSuccess = true;
        $this->isLoading = false;

        // Dispatch browser event so Alpine.js can store token in localStorage
        // (for compatibility with the vanilla PHP app)
        $this->dispatch('login-success', token: $token, userType: $userType->value, redirectUrl: $redirectUrl);

        // Server-side redirect as fallback (if JS dispatch doesn't trigger)
        $this->redirect($redirectUrl);
    }

    protected function resolveRedirectUrl(Admin|Client $user, UserType $userType): string
    {
        if ($userType === UserType::Admin) {
            return match ($user->role) {
                UserRole::Coach => route('coach.dashboard'),
                default => route('admin.dashboard'), // superadmin, admin, jefe
            };
        }

        // Client routing based on plan
        if ($user->plan === PlanType::Rise) {
            return route('rise.dashboard');
        }

        return route('client.dashboard');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
