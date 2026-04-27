<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Coach;

use App\Enums\Marketing\LastUpdatedBy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\StoreMarketingProfileRequest;
use App\Http\Requests\Coach\UpdateMarketingProfileDraftRequest;
use App\Http\Resources\Coach\Marketing\MarketingProfileResource;
use App\Models\CoachMarketingProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class MarketingProfileController extends Controller
{
    public function show(Request $request): MarketingProfileResource|JsonResponse
    {
        $coach = Auth::user();
        $profile = CoachMarketingProfile::where('coach_id', $coach->id)->first();

        if (! $profile) {
            return response()->json(['data' => null], 200);
        }

        return new MarketingProfileResource($profile);
    }

    public function store(StoreMarketingProfileRequest $request): JsonResponse
    {
        $coach = Auth::user();
        $data = $request->validated();

        $data['coach_id'] = $coach->id;
        $data['last_updated_by'] = LastUpdatedBy::Coach;
        $data['completed_at'] = now();

        $profile = CoachMarketingProfile::updateOrCreate(
            ['coach_id' => $coach->id],
            $data,
        );

        return (new MarketingProfileResource($profile))->response()->setStatusCode(200);
    }

    public function updateDraft(UpdateMarketingProfileDraftRequest $request): MarketingProfileResource
    {
        $coach = Auth::user();
        $data = $request->validated();

        $data['last_updated_by'] = LastUpdatedBy::Coach;

        $profile = CoachMarketingProfile::where('coach_id', $coach->id)->first();

        if ($profile) {
            $profile->fill($data)->save();
        } else {
            // No existing profile — return a transient model so the client
            // gets the draft state echoed back without hitting DB constraints.
            $profile = new CoachMarketingProfile(array_merge(['coach_id' => $coach->id], $data));
        }

        return new MarketingProfileResource($profile);
    }
}
