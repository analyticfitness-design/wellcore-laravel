<?php

namespace App\Console\Commands;

use App\Enums\PaymentProofStatus;
use App\Models\PaymentProof;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePaymentProofsCommand extends Command
{
    protected $signature = 'wellcore:expire-payment-proofs';

    protected $description = 'Marks payment_proofs older than 7 days without review as expirado';

    public function handle(): int
    {
        $count = PaymentProof::where('status', PaymentProofStatus::Pendiente)
            ->where('expires_at', '<', now())
            ->update([
                'status' => PaymentProofStatus::Expirado->value,
            ]);

        if ($count > 0) {
            AuditService::logAction(
                'payment_proof_expired',
                "{$count} comprobantes expirados automáticamente"
            );

            Log::info("ExpirePaymentProofs: {$count} proofs marked as expired");
        }

        $this->info("{$count} comprobante(s) expirado(s).");

        return self::SUCCESS;
    }
}
