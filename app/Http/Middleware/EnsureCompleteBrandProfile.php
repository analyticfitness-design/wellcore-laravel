<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\CoachMarketingProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCompleteBrandProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('wellcore');

        if (! $user || $user->role !== UserRole::Coach) {
            return $next($request);
        }

        $isComplete = CoachMarketingProfile::where('coach_id', $user->id)
            ->whereNotNull('completed_at')
            ->exists();

        if (! $isComplete) {
            return response()->json([
                'message' => 'Debes completar tu perfil de marca primero.',
                'code' => 'PROFILE_INCOMPLETE',
            ], 403);
        }

        return $next($request);
    }
}
