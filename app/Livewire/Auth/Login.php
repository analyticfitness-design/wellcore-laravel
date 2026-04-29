<?php

namespace App\Livewire\Auth;

use App\Enums\PlanType;
use App\Enums\UserRole;
use App\Enums\UserType;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use Illuminate\Support\Facades\RateLimiter;
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

        $rateLimitKey = 'wc-login:' . request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = (int) ceil($seconds / 60);
            $this->errorMessage = "Demasiados intentos. Intenta de nuevo en {$minutes} minuto" . ($minutes > 1 ? 's' : '') . '.';
            return;
        }

        $this->isLoading = true;
        $this->validate();

        $identity = trim($this->identity);

        $user = Admin::whereRaw('LOWER(username) = ?', [strtolower($identity)])->first();
        $userType = UserType::Admin;

        if (! $user) {
            $user = Client::whereRaw('LOWER(email) = ?', [strtolower($identity)])
                ->orWhere('client_code', $identity)
                ->first();
            $userType = UserType::Client;
        }

        if (! $user) {
            RateLimiter::hit($rateLimitKey, 60);
            $this->isLoading = false;
            $this->errorMessage = 'No encontramos una cuenta con esas credenciales.';
            return;
        }

        if (! password_verify($this->password, $user->password_hash)) {
            RateLimiter::hit($rateLimitKey, 60);
            $this->isLoading = false;
            $this->errorMessage = 'La contraseña es incorrecta.';
            return;
        }

        RateLimiter::clear($rateLimitKey);

        $token = bin2hex(random_bytes(32));

        AuthToken::create([
            'user_type'    => $userType->value,
            'user_id'      => $user->id,
            'token'        => $token,
            'ip_address'   => request()->ip(),
            'fingerprint'  => mb_substr((string) request()->userAgent(), 0, 64),
            'expires_at'   => now()->addDays($this->rememberMe ? 30 : 7),
            'created_at'   => now(),
            'last_used_at' => now(),
        ]);

        $redirectUrl = $this->resolveRedirectUrl($user, $userType);

        session()->put('wc_token', $token);
        session()->put('wc_user_type', $userType->value);
        session()->put('wc_user_id', $user->id);
        session()->put('wc_user_portal', $redirectUrl);

        $forcePasswordChange = $userType === UserType::Admin
            ? (bool) ($user->must_change_password ?? false)
            : false;

        $userName = $user->name ?? $user->username ?? 'Usuario';

        $this->loginSuccess = true;
        $this->isLoading = false;

        $this->dispatch(
            'login-success',
            token: $token,
            userType: $userType->value,
            userId: $user->id,
            userName: $userName,
            redirectUrl: $redirectUrl,
            userPortal: $redirectUrl,
            forcePasswordChange: $forcePasswordChange,
        );

        $this->redirect($redirectUrl);
    }

    protected function resolveRedirectUrl(Admin|Client $user, UserType $userType): string
    {
        if ($userType === UserType::Admin) {
            return match ($user->role) {
                UserRole::Coach => '/coach',
                default => '/admin',
            };
        }

        if ($user->plan === PlanType::Rise) {
            return '/rise';
        }

        return '/client';
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
