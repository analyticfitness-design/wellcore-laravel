<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentProofStatus;
use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\StorePaymentProofRequest;
use App\Mail\PaymentProofPending;
use App\Models\PaymentProof;
use App\Models\WellcoreNotification;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PaymentProofController extends Controller
{
    use AuthenticatesVueRequests;

    /**
     * POST /api/v/coach/payment-proofs
     *
     * Upload a manual payment proof for a prospective client.
     * One pending proof per (coach, client_email) is enforced.
     */
    public function store(StorePaymentProofRequest $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);
        Gate::authorize('create', PaymentProof::class);

        $email = $request->validated('client_email');

        $duplicate = PaymentProof::where('coach_id', $coach->id)
            ->where('client_email', $email)
            ->where('status', PaymentProofStatus::Pendiente)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'Ya existe un comprobante pendiente para este email.',
                'errorCode' => 'DUPLICATE_PENDING',
            ], 409);
        }

        $uploadedFile = $request->file('file');
        $fileHash = hash_file('sha256', $uploadedFile->getRealPath());

        $duplicateFile = PaymentProof::where('file_hash', $fileHash)
            ->where('status', PaymentProofStatus::Pendiente)
            ->exists();

        if ($duplicateFile) {
            return response()->json([
                'message' => 'Este comprobante ya fue subido y está pendiente de revisión.',
                'errorCode' => 'DUPLICATE_FILE',
            ], 409);
        }

        // Detect real MIME to decide storage strategy
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = $finfo ? (string) finfo_file($finfo, $uploadedFile->getRealPath()) : $uploadedFile->getMimeType();
        if ($finfo) {
            finfo_close($finfo);
        }

        $imageTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (in_array($realMime, $imageTypes, true)) {
            try {
                $pipelineResult = app(\App\Services\ImagePipelineService::class)->processUpload(
                    $uploadedFile,
                    'payment_proofs',
                    '',
                    maxWidth: 1600,
                    quality: 80,
                );
                // Delete fallback — private disk, only WebP needed
                Storage::disk('payment_proofs')->delete($pipelineResult['path_fallback']);
                $storedPath = $pipelineResult['path_webp'];
                $storedMime = 'image/webp';
                $storedSize = $pipelineResult['size_bytes_webp'];
            } catch (\Throwable $e) {
                // ImagePipeline failed — fall back to raw storage
                Log::warning('ImagePipelineService failed for payment proof: ' . $e->getMessage());
                $storedPath = Storage::disk('payment_proofs')->putFile('', $uploadedFile);
                $storedMime = $realMime;
                $storedSize = $uploadedFile->getSize();
            }
        } else {
            // PDF or other allowed type — store as-is
            $storedPath = Storage::disk('payment_proofs')->putFile('', $uploadedFile);
            $storedMime = $realMime;
            $storedSize = $uploadedFile->getSize();
        }

        $proof = PaymentProof::create([
            'coach_id' => $coach->id,
            'client_email' => $email,
            'client_name' => $request->validated('client_name'),
            'plan' => $request->validated('plan'),
            'amount' => $request->validated('amount'),
            'currency' => 'COP',
            'payment_method' => $request->validated('payment_method'),
            'coach_note' => $request->validated('coach_note'),
            'file_path' => $storedPath,
            'file_disk' => 'payment_proofs',
            'file_mime' => $storedMime,
            'file_size' => $storedSize,
            'file_hash' => $fileHash,
            'status' => PaymentProofStatus::Pendiente,
            'submitted_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        // Notify superadmin — silent on failure (must not block the upload)
        try {
            WellcoreNotification::create([
                'user_type' => UserType::Admin,
                'user_id' => 1,
                'type' => 'payment_proof_submitted',
                'title' => 'Nuevo comprobante pendiente',
                'body' => "Coach {$coach->name} subió comprobante para {$email}",
                'link' => '/admin/payment-proofs',
            ]);
        } catch (\Throwable $e) {
            Log::warning('PaymentProof notification failed: ' . $e->getMessage());
        }

        // Audit — silent on failure
        try {
            AuditService::logAction(
                'payment_proof_submitted',
                "Coach {$coach->id} subió comprobante para {$email} (proof_id: {$proof->id})"
            );
        } catch (\Throwable $e) {
            Log::warning('PaymentProof audit log failed: ' . $e->getMessage());
        }

        // Mail al superadmin (opcional — silencioso si falla)
        try {
            $superadminEmail = config('wellcore.superadmin_email', env('SUPERADMIN_EMAIL', 'daniel@wellcorefitness.com'));
            Mail::to($superadminEmail)->queue(new PaymentProofPending($proof));
        } catch (\Throwable $e) {
            Log::warning('PaymentProofPending mail failed: ' . $e->getMessage());
        }

        return response()->json([
            'id' => $proof->id,
            'status' => $proof->status->value,
            'submittedAt' => $proof->submitted_at->toIso8601String(),
            'expiresAt' => $proof->expires_at->toIso8601String(),
            'clientEmail' => $proof->client_email,
            'plan' => $proof->plan->value,
        ], 201);
    }

    /**
     * GET /api/v/coach/payment-proofs
     *
     * List proofs belonging to the authenticated coach.
     * Optional filters: status, from_date, to_date.
     */
    public function index(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);
        Gate::authorize('viewAny', PaymentProof::class);

        $query = PaymentProof::where('coach_id', $coach->id);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('submitted_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('submitted_at', '<=', $request->input('to_date'));
        }

        $paginated = $query->latest('submitted_at')->paginate(15);

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
            'submittedAt' => $proof->submitted_at?->toIso8601String(),
            'expiresAt' => $proof->expires_at?->toIso8601String(),
            'reviewedAt' => $proof->reviewed_at?->toIso8601String(),
            'reviewNote' => $proof->review_note,
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
     * GET /api/v/coach/payment-proofs/{id}/file
     *
     * Generate a short-lived (5 min) token so the coach can view their own file.
     * Uses the same cache-token mechanism as the admin endpoint.
     */
    public function file(Request $request, int $id): JsonResponse
    {
        $this->resolveCoachOrFail($request);

        $proof = PaymentProof::findOrFail($id);
        Gate::authorize('view', $proof);

        if (! Storage::disk('payment_proofs')->exists($proof->file_path)) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'FILE_NOT_FOUND', 'message' => 'El archivo no existe.'],
            ], 404);
        }

        $token = hash_hmac('sha256', $proof->id . '|' . time(), config('app.key'));
        $expiresAt = now()->addMinutes(5);
        Cache::put("proof_view_{$token}", $proof->id, $expiresAt);

        return response()->json([
            'url' => url("/coach/payment-proofs/{$id}/view?token={$token}"),
            'expiresAt' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * GET /api/v/coach/payment-proofs/{id}
     *
     * Return a single proof. Policy enforces coach ownership.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $this->resolveCoachOrFail($request);

        $proof = PaymentProof::findOrFail($id);
        Gate::authorize('view', $proof);

        return response()->json([
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
            'expiresAt' => $proof->expires_at?->toIso8601String(),
            'reviewedAt' => $proof->reviewed_at?->toIso8601String(),
            'reviewNote' => $proof->review_note,
        ]);
    }
}
