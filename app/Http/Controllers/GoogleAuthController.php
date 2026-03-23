<?php

namespace App\Http\Controllers;

use App\Enums\PlanType;
use App\Enums\UserType;
use App\Models\AuthToken;
use App\Models\Client;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth consent screen.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google OAuth.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('google_error', 'Error al autenticar con Google. Intenta de nuevo.');
        }

        // 1. Try to find by google_id first, then by email
        $client = Client::where('google_id', $googleUser->getId())->first()
            ?? Client::where('email', $googleUser->getEmail())->first();

        if (! $client) {
            // No existing account — redirect to inscription page with Google info pre-filled.
            // We do NOT auto-create a free account; the user must complete registration and pay.
            return redirect()->route('inscripcion', [
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'source'    => 'google',
            ]);
        }

        // 2. Link Google ID if not yet linked
        if (empty($client->google_id)) {
            $client->update(['google_id' => $googleUser->getId()]);
        }

        // 3. Update avatar from Google if client doesn't have one
        if (empty($client->avatar_url) && $googleUser->getAvatar()) {
            $client->update(['avatar_url' => $googleUser->getAvatar()]);
        }

        // 4. Create auth token (64-char hex, matching the vanilla PHP app)
        $token = bin2hex(random_bytes(32));

        AuthToken::create([
            'user_type' => UserType::Client->value,
            'user_id' => $client->id,
            'token' => $token,
            'ip_address' => request()->ip(),
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
        ]);

        // 5. Store token in session (matching Login.php mechanism)
        session()->put('wc_token', $token);
        session()->put('wc_user_type', UserType::Client->value);
        session()->put('wc_user_id', $client->id);

        // 6. Redirect based on plan type (matching Login.php logic)
        $redirectUrl = match ($client->plan) {
            PlanType::Rise => route('rise.dashboard'),
            default => route('client.dashboard'),
        };

        return redirect($redirectUrl);
    }
}
