<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Mail\NewCoachCredentials;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\CoachProfile;
use App\Traits\Auditable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminCoachManagementController extends Controller
{
    use AuthenticatesVueRequests;
    use Auditable;

    /** Strong password policy (P2.4): min 10, upper, lower, digit, symbol. */
    protected const PASSWORD_POLICY_REGEX = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/';

    protected function resolveAdminOrFail(Request $request): Admin
    {
        $auth = $this->resolveAuthUser($request);

        if (! $auth || $auth['userType'] !== UserType::Admin) {
            abort(401, 'Token invalido o expirado.');
        }

        $admin = $auth['user'];
        $role = $admin->role instanceof UserRole ? $admin->role->value : $admin->role;

        if (! in_array($role, ['admin', 'superadmin', 'jefe'])) {
            abort(403, 'Solo administradores pueden gestionar coaches.');
        }

        return $admin;
    }

    public function index(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $query = Admin::where('role', 'coach');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('active', true);
        } elseif ($status === 'inactive') {
            $query->where('active', false);
        }

        $coaches = $query->orderBy('name')->get();

        $clientCountsByCoach = DB::table('clients')
            ->selectRaw('coach_id, COUNT(*) as total')
            ->whereIn('coach_id', $coaches->pluck('id'))
            ->whereNull('deleted_at')
            ->groupBy('coach_id')
            ->pluck('total', 'coach_id');

        $data = $coaches->map(fn (Admin $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'username' => $c->username,
            'email' => $c->email,
            'whatsapp' => $c->whatsapp,
            'active' => (bool) $c->active,
            'must_change_password' => (bool) $c->must_change_password,
            'created_at' => $c->created_at?->format('Y-m-d'),
            'last_login_at' => $c->last_login_at?->format('Y-m-d H:i'),
            'client_count' => (int) ($clientCountsByCoach[$c->id] ?? 0),
        ])->values();

        return response()->json(['coaches' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:admins,username',
            'email' => 'nullable|email|max:150',
            'whatsapp' => 'nullable|string|max:30',
            'password' => ['required', 'string', 'min:10', 'max:255', 'regex:'.self::PASSWORD_POLICY_REGEX],
        ], [
            'password.regex' => 'La contrasena debe incluir mayuscula, minuscula, numero y simbolo.',
        ]);

        $coach = DB::transaction(function () use ($validated) {
            $admin = Admin::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'password_hash' => Hash::make($validated['password']),
                'role' => 'coach',
                'active' => true,
                'must_change_password' => true,
            ]);

            CoachProfile::create([
                'admin_id' => $admin->id,
                'slug' => Str::slug($admin->name).'-'.Str::random(4),
                'referral_code' => strtoupper(Str::random(8)),
                'color_primary' => '#E31E24',
                'public_visible' => true,
            ]);

            return $admin;
        });

        $this->sendCredentialsEmail($coach, $validated['password'], isReset: false);

        $this->audit('coach.create', $coach, [
            'username' => $coach->username,
            'email' => $coach->email,
        ], $coach->name);

        return response()->json([
            'created' => true,
            'coach' => [
                'id' => $coach->id,
                'name' => $coach->name,
                'username' => $coach->username,
                'email' => $coach->email,
                'whatsapp' => $coach->whatsapp,
                'active' => true,
            ],
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $coach = Admin::where('role', 'coach')->findOrFail($id);

        $clientCount = Client::where('coach_id', $id)->count();

        return response()->json([
            'id' => $coach->id,
            'name' => $coach->name,
            'username' => $coach->username,
            'email' => $coach->email,
            'whatsapp' => $coach->whatsapp,
            'active' => (bool) $coach->active,
            'must_change_password' => (bool) $coach->must_change_password,
            'created_at' => $coach->created_at?->format('Y-m-d'),
            'last_login_at' => $coach->last_login_at?->format('Y-m-d H:i'),
            'client_count' => $clientCount,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $coach = Admin::where('role', 'coach')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'whatsapp' => 'nullable|string|max:30',
            'active' => 'nullable|boolean',
        ]);

        $before = $coach->only(['name', 'email', 'whatsapp', 'active']);
        $changes = array_filter($validated, fn ($v) => $v !== null);
        $coach->update($changes);

        $this->audit('coach.update', $coach, [
            'before' => $before,
            'after' => $changes,
        ], $coach->name);

        return response()->json(['updated' => true]);
    }

    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $coach = Admin::where('role', 'coach')->findOrFail($id);

        // Generate a password that satisfies PASSWORD_POLICY_REGEX.
        $newPassword = $this->generatePolicyCompliantPassword(12);

        $coach->update([
            'password_hash' => Hash::make($newPassword),
            'must_change_password' => true,
        ]);

        // Invalidate existing sessions
        AuthToken::where('user_id', $coach->id)
            ->where('user_type', 'admin')
            ->delete();

        $sent = $this->sendCredentialsEmail($coach, $newPassword, isReset: true);

        $this->audit('coach.reset_password', $coach, [
            'email_sent' => $sent,
        ], $coach->name);

        return response()->json([
            'ok' => true,
            'password_sent_to_email' => $sent,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $coach = Admin::where('role', 'coach')->findOrFail($id);

        $activeClientCount = Client::where('coach_id', $id)
            ->where('status', 'activo')
            ->count();

        if ($activeClientCount > 0) {
            return response()->json([
                'error' => 'Reasigna los clientes antes de desactivar',
                'active_clients' => $activeClientCount,
            ], 409);
        }

        $coach->update(['active' => false]);

        AuthToken::where('user_id', $coach->id)
            ->where('user_type', 'admin')
            ->delete();

        $this->audit('coach.delete', $coach, [], $coach->name);

        return response()->json(['deactivated' => true]);
    }

    /**
     * Build a random password that satisfies the policy regex
     * (>=10 chars + upper + lower + digit + symbol).
     */
    protected function generatePolicyCompliantPassword(int $length = 12): string
    {
        $length = max(10, $length);
        $upper = chr(random_int(65, 90));
        $lower = chr(random_int(97, 122));
        $digit = (string) random_int(0, 9);
        $symbols = '!@#$%&*?';
        $symbol = $symbols[random_int(0, strlen($symbols) - 1)];
        $rest = Str::password($length - 4, letters: true, numbers: true, symbols: false);
        $raw = str_split($upper.$lower.$digit.$symbol.$rest);
        shuffle($raw);

        return implode('', $raw);
    }

    protected function sendCredentialsEmail(Admin $coach, string $password, bool $isReset): bool
    {
        if (! $coach->email) {
            return false;
        }

        try {
            Mail::to($coach->email)->send(new NewCoachCredentials(
                coachName: $coach->name,
                username: $coach->username,
                temporaryPassword: $password,
                isReset: $isReset,
            ));

            return true;
        } catch (\Throwable $e) {
            Log::warning('NewCoachCredentials mail failed', [
                'coach_id' => $coach->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
