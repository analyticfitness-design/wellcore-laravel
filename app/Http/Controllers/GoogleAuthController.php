<?php

namespace App\Http\Controllers;

use App\Models\AuthToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Error al autenticar con Google. Intenta de nuevo.');
        }

        $client = DB::table('clients')->where('email', $googleUser->getEmail())->first();

        if (!$client) {
            // Create new client from Google data
            $clientId = DB::table('clients')->insertGetId([
                'client_code' => 'WC-' . strtoupper(Str::random(6)),
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password_hash' => bcrypt(Str::random(32)),
                'plan' => 'esencial',
                'status' => 'pendiente',
                'avatar_url' => $googleUser->getAvatar(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $client = DB::table('clients')->find($clientId);
        }

        // Create auth token
        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'token' => $token,
            'user_id' => $client->id,
            'user_type' => 'client',
            'expires_at' => now()->addDays(30),
        ]);

        session(['wc_token' => $token]);

        return redirect('/client');
    }
}
