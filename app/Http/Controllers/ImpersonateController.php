<?php

namespace App\Http\Controllers;

use App\Enums\PlanType;
use App\Enums\UserType;
use App\Models\AuthToken;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;

class ImpersonateController extends Controller
{
    /**
     * POST /admin/impersonate/{clientId}
     *
     * Lets an admin/superadmin assume a client session.
     * The current admin token is stashed under 'wc_admin_token' so it can
     * be restored when impersonation ends.
     */
    public function start(\Illuminate\Http\Request $request, int $clientId): RedirectResponse
    {
        // 1. Verify the current user is a known admin type (middleware enforces
        //    role:superadmin,admin, but we re-check here for defence-in-depth).
        $admin = auth('wellcore')->user();
        if (!$admin instanceof \App\Models\Admin) {
            abort(403, 'Only admins can impersonate clients.');
        }

        // 2. Verify the target client exists.
        $client = Client::find($clientId);
        if (!$client) {
            abort(404, 'Client not found.');
        }

        // 3. Stash the current admin token so we can restore the session later.
        //    Vue SPA may not maintain the PHP session — accept token from POST body as fallback.
        $adminToken = session('wc_token');
        if (!$adminToken && $request->filled('admin_token')) {
            $candidate = $request->input('admin_token');
            $valid = \App\Models\AuthToken::where('token', $candidate)
                ->where('user_type', \App\Enums\UserType::Admin->value)
                ->where('expires_at', '>', now())
                ->exists();
            if ($valid) {
                $adminToken = $candidate;
            }
        }
        if (!$adminToken) {
            abort(403, 'No active admin session token found.');
        }
        session(['wc_admin_token' => $adminToken]);

        // 4. Find an existing, non-expired client token or create a short-lived one.
        $clientToken = AuthToken::where('user_id', $clientId)
            ->where('user_type', UserType::Client->value)
            ->where('expires_at', '>', now())
            ->first();

        if (!$clientToken) {
            $clientToken = AuthToken::create([
                'user_type'  => UserType::Client,
                'user_id'    => $clientId,
                'token'      => bin2hex(random_bytes(32)),
                'expires_at' => now()->addHours(2),
            ]);
        }

        // 5. Switch the active session to the client token.
        session([
            'wc_token'     => $clientToken->token,
            'wc_user_type' => 'client',
            'wc_user_id'   => $clientId,
            'wc_user_name' => $client->name,
        ]);

        // Redirect to the correct portal based on the client's plan type.
        $plan = $client->plan instanceof PlanType ? $client->plan : PlanType::tryFrom((string) $client->plan);
        $destination = $plan === PlanType::Rise ? '/rise' : '/client';

        return redirect($destination);
    }

    /**
     * POST /admin/impersonate/stop
     *
     * Restores the original admin session, ending impersonation.
     */
    public function stop(\Illuminate\Http\Request $request): RedirectResponse
    {
        $adminToken = session('wc_admin_token');

        // Fallback: Vue SPA may not maintain the PHP session reliably.
        // Accept the admin token from the POST body and validate it against the DB.
        if (!$adminToken && $request->filled('admin_token')) {
            $candidate = $request->input('admin_token');
            $valid = \App\Models\AuthToken::where('token', $candidate)
                ->where('user_type', \App\Enums\UserType::Admin->value)
                ->where('expires_at', '>', now())
                ->exists();
            if ($valid) {
                $adminToken = $candidate;
            }
        }

        // Guard: only proceed if there is a valid admin token.
        if (!$adminToken) {
            return redirect('/login');
        }

        // Look up admin user from the token to restore full session context.
        $authToken = \App\Models\AuthToken::where('token', $adminToken)
            ->where('user_type', \App\Enums\UserType::Admin->value)
            ->where('expires_at', '>', now())
            ->first();

        $adminUser = $authToken ? \App\Models\Admin::find($authToken->user_id) : null;

        // Restore the admin session.
        session([
            'wc_token'     => $adminToken,
            'wc_user_type' => 'admin',
            'wc_user_id'   => $adminUser?->id,
            'wc_user_name' => $adminUser?->name ?? $adminUser?->username ?? 'Admin',
        ]);

        // Remove impersonation state.
        session()->forget('wc_admin_token');

        return redirect('/admin/clients');
    }
}
