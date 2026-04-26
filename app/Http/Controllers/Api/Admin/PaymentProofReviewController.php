<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\ApprovePaymentProofAction;
use App\Actions\RejectPaymentProofAction;
use App\Enums\PaymentProofStatus;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PaymentProof;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PaymentProofReviewController extends Controller
{
    /**
     * GET /api/v/admin/payment-proofs
     *
     * List all payment proofs across all coaches.
     * Optional filters: status, coach_id, from_date, to_date.
     * Ordered by submitted_at DESC, paginated 20/page.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PaymentProof::query()
            ->with(['coach:id,name', 'reviewer:id,name'])
            ->latest('submitted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('coach_id')) {
            $query->where('coach_id', $request->integer('coach_id'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('submitted_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('submitted_at', '<=', $request->input('to_date'));
        }

        $paginated = $query->paginate(20);

        $items = $paginated->getCollection()->map(fn (PaymentProof $proof) => [
            'id' => $proof->id,
            'clientEmail' => $proof->client_email,
            'clientName' => $proof->client_name,
            'plan' => $proof->plan->value,
            'amount' => $proof->amount,
            'currency' => $proof->currency,
            'paymentMethod' => $proof->payment_method?->value,
            'coachNote' => $proof->coach_note,
            'status' => $proof->status->value,
            'fileMime' => $proof->file_mime,
            'fileSize' => $proof->file_size,
            'submittedAt' => $proof->submitted_at?->toIso8601String(),
            'reviewedAt' => $proof->reviewed_at?->toIso8601String(),
            'expiresAt' => $proof->expires_at?->toIso8601String(),
            'reviewNote' => $proof->review_note,
            'coach' => $proof->relationLoaded('coach') && $proof->coach
                ? ['id' => $proof->coach->id, 'name' => $proof->coach->name]
                : null,
            'reviewer' => $proof->relationLoaded('reviewer') && $proof->reviewer
                ? ['id' => $proof->reviewer->id, 'name' => $proof->reviewer->name]
                : null,
        ]);

        return response()->json([
            'data' => $items,
            'meta' => [
                'currentPage' => $paginated->currentPage(),
                'perPage' => $paginated->perPage(),
                'total' => $paginated->total(),
                'lastPage' => $paginated->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v/admin/payment-proofs/{id}/file
     *
     * Generate a short-lived token (5 min) for viewing the proof file.
     * Disk is local — no native signed URLs — so we use a Cache-backed token.
     */
    public function file(int $id): JsonResponse
    {
        $proof = PaymentProof::findOrFail($id);

        if (! Storage::disk('payment_proofs')->exists($proof->file_path)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FILE_NOT_FOUND',
                    'message' => 'El archivo del comprobante no existe en el disco.',
                ],
            ], 404);
        }

        $token = hash_hmac('sha256', $proof->id.'|'.time(), config('app.key'));
        $expiresAt = now()->addMinutes(5);

        Cache::put("proof_view_{$token}", $proof->id, $expiresAt);

        return response()->json([
            'url' => url("/admin/payment-proofs/{$id}/view?token={$token}"),
            'expiresAt' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * POST /api/v/admin/payment-proofs/{id}/approve
     */
    public function approve(int $id): JsonResponse
    {
        $proof = PaymentProof::where('status', PaymentProofStatus::Pendiente)->findOrFail($id);
        $reviewer = Admin::findOrFail(auth()->id());

        (new ApprovePaymentProofAction)->handle($proof, $reviewer);

        return response()->json(['message' => 'Comprobante aprobado.']);
    }

    /**
     * POST /api/v/admin/payment-proofs/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['review_note' => 'required|string|max:500']);

        $proof = PaymentProof::where('status', PaymentProofStatus::Pendiente)->findOrFail($id);
        $reviewer = Admin::findOrFail(auth()->id());

        (new RejectPaymentProofAction)->handle($proof, $reviewer, $request->input('review_note'));

        return response()->json(['message' => 'Comprobante rechazado.']);
    }
}
