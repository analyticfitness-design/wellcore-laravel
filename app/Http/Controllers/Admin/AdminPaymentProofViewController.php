<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentProof;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPaymentProofViewController extends Controller
{
    /**
     * GET /admin/payment-proofs/{id}/view?token=...
     *
     * Streams the proof file after validating the cache-backed token.
     * Token is single-use: consumed on first access to prevent sharing.
     */
    public function view(Request $request, int $id): Response|StreamedResponse
    {
        $token = $request->string('token')->toString();

        if (! $token) {
            abort(403, 'Token requerido.');
        }

        $cacheKey = "proof_view_{$token}";
        $cachedId = Cache::get($cacheKey);

        if ($cachedId === null) {
            abort(403, 'Token inválido o expirado.');
        }

        if ((int) $cachedId !== $id) {
            abort(403, 'Token no corresponde al comprobante solicitado.');
        }

        // Consume token — single use
        Cache::forget($cacheKey);

        $proof = PaymentProof::findOrFail($id);

        if (! Storage::disk('payment_proofs')->exists($proof->file_path)) {
            abort(404, 'Archivo no encontrado.');
        }

        $mime = $proof->file_mime ?? 'application/octet-stream';
        $content = Storage::disk('payment_proofs')->get($proof->file_path);

        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
