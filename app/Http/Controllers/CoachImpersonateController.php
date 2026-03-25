<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\Admin;
use App\Models\AuthToken;
use Illuminate\Http\RedirectResponse;

class CoachImpersonateController extends Controller
{
    /**
     * POST /admin/coach-impersonate/{adminId}
     *
     * Permite a un superadmin ver el portal de cualquier coach/admin.
     * El token del superadmin se guarda como 'wc_super_token' para restaurarlo.
     */
    public function start(int $adminId): RedirectResponse
    {
        $superadmin = auth('wellcore')->user();
        if (! $superadmin instanceof Admin) {
            abort(403, 'Solo admins pueden usar la impersonación de coach.');
        }

        $coach = Admin::find($adminId);
        if (! $coach) {
            abort(404, 'Coach no encontrado.');
        }

        $superToken = session('wc_token');
        if (! $superToken) {
            abort(403, 'No se encontró una sesión activa de admin.');
        }

        // Guardar el token del superadmin y el nombre del coach para el banner
        session([
            'wc_super_token' => $superToken,
            'wc_coach_name'  => $coach->name ?? $coach->username,
        ]);

        // Buscar o crear un token de admin para el coach
        $coachToken = AuthToken::where('user_id', $adminId)
            ->where('user_type', UserType::Admin->value)
            ->where('expires_at', '>', now())
            ->first();

        if (! $coachToken) {
            $coachToken = AuthToken::create([
                'user_type'  => UserType::Admin,
                'user_id'    => $adminId,
                'token'      => bin2hex(random_bytes(32)),
                'expires_at' => now()->addHours(2),
            ]);
        }

        // Cambiar la sesión al token del coach
        session([
            'wc_token'     => $coachToken->token,
            'wc_user_type' => 'admin',
            'wc_user_id'   => $adminId,
        ]);

        return redirect('/coach');
    }

    /**
     * POST /admin/coach-impersonate/stop
     *
     * Restaura la sesión original del superadmin.
     */
    public function stop(): RedirectResponse
    {
        $superToken = session('wc_super_token');

        if (! $superToken) {
            return redirect('/admin');
        }

        session(['wc_token' => $superToken]);
        session()->forget(['wc_super_token', 'wc_coach_name', 'wc_user_type', 'wc_user_id']);

        return redirect('/admin/coaches');
    }
}
