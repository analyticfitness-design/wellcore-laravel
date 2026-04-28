<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated Use App\Http\Controllers\Api\AdminImpersonateController via /api/v/admin/coaches/{id}/impersonate.
 *             This shim is kept temporarily so legacy bookmarks / form posts don't break.
 *             Remove after 2026-07-01.
 */
class CoachImpersonateController extends Controller
{
    public function start(Request $request, int $adminId): RedirectResponse
    {
        Log::channel('security')->warning('IMPERSONATE_LEGACY_CONTROLLER_HIT', [
            'route'    => 'start',
            'admin_id' => $adminId,
            'ip'       => $request->ip(),
        ]);
        return redirect('/admin/coaches')
            ->with('warning', 'El flujo antiguo de impersonificación de coach fue retirado. Usa el botón "Ver Portal" en la tabla de coaches.');
    }

    public function stop(Request $request): RedirectResponse
    {
        Log::channel('security')->warning('IMPERSONATE_LEGACY_CONTROLLER_HIT', [
            'route' => 'stop',
            'ip'    => $request->ip(),
        ]);
        return redirect('/admin');
    }
}
