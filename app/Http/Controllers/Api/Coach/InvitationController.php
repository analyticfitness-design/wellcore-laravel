<?php

namespace App\Http\Controllers\Api\Coach;

use App\Exceptions\CoachInvitationBlockedException;
use App\Exceptions\CoachInvitationCancelException;
use App\Exceptions\CoachInvitationRateLimitException;
use App\Exceptions\CoachInvitationResendException;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\PreviewInvitationRequest;
use App\Http\Requests\Coach\StoreInvitationRequest;
use App\Models\CoachInvitation;
use App\Services\CoachInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvitationController extends Controller
{
    use AuthenticatesVueRequests;

    public function __construct(
        private CoachInvitationService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);
        Gate::authorize('viewAny', CoachInvitation::class);

        $query = CoachInvitation::with(['coach'])
            ->when($coach->role->value === 'coach', fn ($q) => $q->where('coach_id', $coach->id));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        $perPage = min((int) ($request->per_page ?? 20), 100);
        $sort = in_array($request->sort, ['created_at', 'sent_at', 'paid_at', 'expires_at'])
            ? $request->sort
            : 'created_at';
        $order = $request->order === 'asc' ? 'asc' : 'desc';

        $paginated = $query->orderBy($sort, $order)->paginate($perPage);

        $statsQuery = CoachInvitation::when(
            $coach->role->value === 'coach',
            fn ($q) => $q->where('coach_id', $coach->id)
        );

        $stats = $statsQuery
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'currentPage' => $paginated->currentPage(),
                'perPage' => $paginated->perPage(),
                'total' => $paginated->total(),
                'lastPage' => $paginated->lastPage(),
            ],
            'stats' => [
                'sent' => $stats['sent'] ?? 0,
                'opened' => $stats['opened'] ?? 0,
                'linkClicked' => $stats['link_clicked'] ?? 0,
                'paid' => $stats['paid'] ?? 0,
                'expired' => $stats['expired'] ?? 0,
                'cancelled' => $stats['cancelled'] ?? 0,
                'failed' => $stats['failed'] ?? 0,
            ],
        ]);
    }

    public function store(StoreInvitationRequest $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);
        Gate::authorize('create', CoachInvitation::class);

        try {
            $invitation = $this->service->create($coach, $request->validated());

            return response()->json(['data' => $invitation->fresh()], 201);

        } catch (CoachInvitationRateLimitException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'RATE_LIMIT',
            ], 429);

        } catch (CoachInvitationBlockedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => $e->errorCode,
            ], 422);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'WOMPI_UNAVAILABLE',
            ], 503);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->resolveCoachOrFail($request);

        $invitation = CoachInvitation::findOrFail($id);
        Gate::authorize('view', $invitation);

        return response()->json(['data' => $invitation->load(['coach', 'client'])]);
    }

    public function resend(Request $request, int $id): JsonResponse
    {
        $this->resolveCoachOrFail($request);

        $invitation = CoachInvitation::findOrFail($id);
        Gate::authorize('resend', $invitation);

        try {
            $this->service->resend($invitation);

            return response()->json([
                'message' => 'Invitación reenviada.',
                'data' => $invitation->fresh()->only([
                    'resend_count',
                    'expires_at',
                    'wompi_payment_link_url',
                    'status',
                ]),
            ]);

        } catch (CoachInvitationResendException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'INVALID_STATUS_OR_MAX_RESENDS',
            ], 422);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'WOMPI_UNAVAILABLE',
            ], 503);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->resolveCoachOrFail($request);

        $invitation = CoachInvitation::findOrFail($id);
        Gate::authorize('cancel', $invitation);

        try {
            $this->service->cancel($invitation);

            return response()->json([
                'message' => 'Invitación cancelada.',
                'data' => $invitation->fresh()->only(['status', 'cancelled_at']),
            ]);

        } catch (CoachInvitationCancelException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'INVALID_STATUS',
            ], 422);
        }
    }

    public function preview(PreviewInvitationRequest $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $html = $this->service->renderPreview($coach, $request->validated());

        return response()->json(['html' => $html]);
    }
}
