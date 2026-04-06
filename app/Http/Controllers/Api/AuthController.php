<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanType;
use App\Enums\UserRole;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'identity' => 'required|string|min:3',
            'password' => 'required|string|min:1',
        ], [
            'identity.required' => 'Ingresa tu email o nombre de usuario.',
            'identity.min' => 'Minimo 3 caracteres.',
            'password.required' => 'Ingresa tu contrasena.',
        ]);

        $identity = trim($request->identity);

        // Try admin by username first, then client by email/client_code
        $user = Admin::whereRaw('LOWER(username) = ?', [strtolower($identity)])->first();
        $userType = UserType::Admin;

        if (! $user) {
            $user = Client::whereRaw('LOWER(email) = ?', [strtolower($identity)])
                ->orWhere('client_code', $identity)
                ->first();
            $userType = UserType::Client;
        }

        if (! $user) {
            return response()->json([
                'message' => 'No encontramos una cuenta con esas credenciales.',
            ], 422);
        }

        if (! password_verify($request->password, $user->password_hash)) {
            return response()->json([
                'message' => 'La contrasena es incorrecta.',
            ], 422);
        }

        // Create auth token (64-char hex, compatible with vanilla PHP app)
        $token = bin2hex(random_bytes(32));

        AuthToken::create([
            'user_type' => $userType->value,
            'user_id' => $user->id,
            'token' => $token,
            'ip_address' => $request->ip(),
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
        ]);

        // Store in session for Livewire compatibility
        session()->put('wc_token', $token);
        session()->put('wc_user_type', $userType->value);
        session()->put('wc_user_id', $user->id);

        $redirectUrl = $this->resolveRedirectUrl($user, $userType);
        session()->put('wc_user_portal', $redirectUrl);

        return response()->json([
            'token' => $token,
            'userType' => $userType->value,
            'userId' => $user->id,
            'name' => $user->name ?? $user->username ?? 'Usuario',
            'redirectUrl' => $redirectUrl,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? session('wc_token');

        if ($token) {
            AuthToken::where('token', $token)->delete();
        }

        session()->flush();

        return response()->json(['message' => 'Sesion cerrada.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Ingresa tu email.',
            'email.email' => 'Ingresa un email valido.',
        ]);

        $rateLimitKey = 'password-reset:' . Str::lower($request->email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = (int) ceil($seconds / 60);

            return response()->json([
                'message' => "Has solicitado demasiados enlaces. Intenta de nuevo en {$minutes} minuto" . ($minutes > 1 ? 's' : '') . '.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 3600);

        $client = DB::table('clients')->where('email', $request->email)->first();

        if (! $client) {
            // Don't reveal if email exists
            return response()->json(['message' => 'Si existe una cuenta, recibiras un email.']);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $resetUrl = url('/v/reset-password/' . $token . '?email=' . urlencode($request->email));

        try {
            Mail::send('emails.password-reset', [
                'token' => $token,
                'name' => $client->name,
                'resetUrl' => $resetUrl,
            ], function ($message) use ($request, $client) {
                $message->to($request->email, $client->name)
                    ->subject('Restablecer Contrasena — WellCore Fitness');
            });
        } catch (\Exception $e) {
            \Log::error('Password reset email failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'No pudimos enviar el email. Intenta de nuevo en unos minutos.',
            ], 500);
        }

        return response()->json(['message' => 'Si existe una cuenta, recibiras un email.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Ingresa tu email.',
            'password.required' => 'Ingresa tu nueva contrasena.',
            'password.min' => 'Minimo 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token) ||
            now()->diffInMinutes($record->created_at) > 60) {
            return response()->json([
                'message' => 'El enlace es invalido o ha expirado.',
            ], 422);
        }

        $client = DB::table('clients')->where('email', $request->email)->first();
        if (! $client) {
            return response()->json(['message' => 'El enlace es invalido.'], 422);
        }

        DB::table('clients')
            ->where('email', $request->email)
            ->update(['password_hash' => password_hash($request->password, PASSWORD_BCRYPT)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Contrasena actualizada exitosamente.']);
    }

    public function me(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? session('wc_token');

        if (! $token) {
            return response()->json(['authenticated' => false], 401);
        }

        $authToken = AuthToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $authToken) {
            return response()->json(['authenticated' => false], 401);
        }

        $userType = UserType::from($authToken->user_type);
        $user = $userType === UserType::Admin
            ? Admin::find($authToken->user_id)
            : Client::find($authToken->user_id);

        if (! $user) {
            return response()->json(['authenticated' => false], 401);
        }

        return response()->json([
            'authenticated' => true,
            'userType' => $authToken->user_type,
            'userId' => $authToken->user_id,
            'name' => $user->name ?? $user->username ?? 'Usuario',
        ]);
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
}
