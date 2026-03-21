<?php

namespace App\Services;

use App\Models\Checkin;
use App\Models\Client;
use App\Models\CoachMessage;
use App\Models\Payment;
use App\Models\TrainingLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataExportService
{
    public function exportClientData(int $clientId): array
    {
        $client = Client::find($clientId);
        if (! $client) {
            return ['error' => 'Cliente no encontrado'];
        }

        $data = [
            'personal_info' => $client->only([
                'name', 'email', 'city', 'bio', 'birth_date',
                'plan', 'status', 'fecha_inicio', 'client_code',
            ]),
            'checkins' => Checkin::where('client_id', $clientId)
                ->select('bienestar', 'dias_entrenados', 'nutricion', 'rpe', 'comentario', 'checkin_date')
                ->get()->toArray(),
            'training_logs' => TrainingLog::where('client_id', $clientId)
                ->select('log_date', 'completed', 'year_num', 'week_num')
                ->get()->toArray(),
            'payments' => Payment::where('client_id', $clientId)
                ->select('amount', 'currency', 'status', 'payment_method', 'wompi_reference', 'payu_reference', 'created_at')
                ->get()->toArray(),
            'messages' => CoachMessage::where('client_id', $clientId)
                ->select('direction', 'message', 'created_at')
                ->get()->toArray(),
            'export_date' => now()->toISOString(),
            'platform'    => 'WellCore Fitness',
        ];

        AuditService::log('data_export', $client);

        return $data;
    }

    public function deleteClientData(int $clientId): array
    {
        $client = Client::find($clientId);
        if (! $client) {
            return ['error' => 'Cliente no encontrado'];
        }

        DB::beginTransaction();
        try {
            // Anonymize rather than hard delete (keep financial records intact)
            $client->update([
                'name'       => 'Usuario Eliminado',
                'email'      => 'deleted_' . $clientId . '@removed.wellcorefitness.com',
                'bio'        => null,
                'avatar_url' => null,
            ]);

            // Redact free-text personal content from check-ins
            Checkin::where('client_id', $clientId)->update(['comentario' => null]);

            // Remove direct coach–client messages (not financial, safe to delete)
            CoachMessage::where('client_id', $clientId)->delete();

            AuditService::log('data_deletion', $client);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Datos personales eliminados. Registros financieros anonimizados.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Data deletion failed', [
                'client_id' => $clientId,
                'error'     => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Error al eliminar datos.'];
        }
    }
}
