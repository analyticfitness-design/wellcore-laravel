<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Marketing;

use App\Enums\Marketing\LastUpdatedBy;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Marketing\AdminUpdateCoachProfileRequest;
use App\Http\Resources\Coach\Marketing\MarketingProfileResource;
use App\Models\Admin;
use App\Models\CoachMarketingProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class CoachProfileController extends Controller
{
    public function show(Admin $coach): MarketingProfileResource|JsonResponse
    {
        abort_unless(
            in_array(Auth::user()->role, [UserRole::Admin, UserRole::Superadmin], strict: true),
            403
        );
        abort_unless($coach->role === UserRole::Coach, 404);

        $profile = CoachMarketingProfile::where('coach_id', $coach->id)->first();

        if (! $profile) {
            return response()->json(['data' => null]);
        }

        return new MarketingProfileResource($profile);
    }

    public function update(AdminUpdateCoachProfileRequest $request, Admin $coach): \Illuminate\Http\JsonResponse
    {
        abort_unless(
            in_array(Auth::user()->role, [UserRole::Admin, UserRole::Superadmin], strict: true),
            403
        );
        abort_unless($coach->role === UserRole::Coach, 404);

        $admin = Auth::user();
        $data = $request->validated();
        $data['coach_id'] = $coach->id;
        $data['last_updated_by'] = LastUpdatedBy::Admin;
        $data['last_admin_editor_id'] = $admin->id;

        if (! isset($data['completed_at'])) {
            $data['completed_at'] = now();
        }

        $profile = CoachMarketingProfile::updateOrCreate(['coach_id' => $coach->id], $data);

        return (new MarketingProfileResource($profile))->response()->setStatusCode(200);
    }
}
