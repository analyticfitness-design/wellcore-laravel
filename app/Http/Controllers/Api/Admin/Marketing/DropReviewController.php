<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Marketing;

use App\Enums\Marketing\DropStatus;
use App\Exceptions\Marketing\InvalidDropSchema;
use App\Exceptions\Marketing\InvalidDropTransition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Marketing\ApproveDropRequest;
use App\Http\Requests\Admin\Marketing\RequestRegenerateRequest;
use App\Http\Requests\Admin\Marketing\UpdateDropContentRequest;
use App\Http\Resources\Admin\Marketing\AdminDropResource;
use App\Models\CoachContentDrop;
use App\Services\Marketing\DropDiffCalculator;
use App\Services\Marketing\DropSchemaValidator;
use App\Services\Marketing\DropStateMachine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class DropReviewController extends Controller
{
    public function __construct(
        private readonly DropSchemaValidator $validator,
        private readonly DropStateMachine $stateMachine,
        private readonly DropDiffCalculator $diff,
    ) {}

    public function show(CoachContentDrop $drop): AdminDropResource
    {
        Gate::authorize('admin.marketing.viewDrop', $drop);
        $drop->load('coach', 'pieceStates');

        return new AdminDropResource($drop);
    }

    public function updateContent(UpdateDropContentRequest $request, CoachContentDrop $drop): AdminDropResource
    {
        Gate::authorize('admin.marketing.updateDrop', $drop);

        $payload = $request->validated('content');

        try {
            $this->validator->validate($payload);
        } catch (InvalidDropSchema $e) {
            abort(422, 'El contenido no pasa la validacion del schema.');
        }

        $drop->content = $payload;
        $drop->save();
        $this->forgetCache($drop);

        return new AdminDropResource($drop->refresh());
    }

    public function approve(ApproveDropRequest $request, CoachContentDrop $drop): AdminDropResource
    {
        Gate::authorize('admin.marketing.approveDrop', $drop);

        $admin = Auth::user();

        $fresh = DB::transaction(function () use ($drop, $admin) {
            $locked = CoachContentDrop::lockForUpdate()->findOrFail($drop->id);

            if ($locked->status !== DropStatus::InReview) {
                abort(409, 'Drop ya fue aprobado por otro admin.');
            }

            $locked->admin_edits_diff = $this->diff->diff(
                $locked->original_content ?? $locked->content,
                $locked->content,
            );
            $locked->save();

            try {
                $this->stateMachine->transition($locked, DropStatus::Approved, $admin);
                $this->stateMachine->transition($locked->fresh(), DropStatus::Ready, $admin);
            } catch (InvalidDropTransition $e) {
                abort(409, 'Drop ya fue aprobado por otro admin.');
            }

            return $locked->fresh()->load('coach', 'pieceStates');
        });

        $this->forgetCache($fresh);

        return new AdminDropResource($fresh);
    }

    public function requestRegenerate(RequestRegenerateRequest $request, CoachContentDrop $drop): AdminDropResource
    {
        Gate::authorize('admin.marketing.requestRegenerate', $drop);

        $admin = Auth::user();

        $fresh = DB::transaction(function () use ($drop, $admin) {
            $locked = CoachContentDrop::lockForUpdate()->findOrFail($drop->id);

            try {
                $this->stateMachine->transition($locked, DropStatus::Pending, $admin);
            } catch (InvalidDropTransition $e) {
                abort(409, 'Drop ya cambio de estado por otro admin.');
            }

            return $locked->fresh();
        });

        $this->forgetCache($fresh);

        return new AdminDropResource($fresh);
    }

    private function forgetCache(CoachContentDrop $drop): void
    {
        Cache::forget("coach_drop_v3:{$drop->coach_id}:{$drop->iso_year}:{$drop->iso_week}");
    }
}
