<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export all clients to CSV.
     */
    public static function exportClients(): StreamedResponse
    {
        return self::streamCsv('clientes-wellcore', function () {
            $clients = DB::table('clients')
                ->select([
                    'clients.client_code',
                    'clients.name',
                    'clients.email',
                    'clients.plan',
                    'clients.status',
                    'clients.city',
                    'clients.fecha_inicio',
                    'clients.created_at',
                ])
                ->orderByDesc('clients.created_at')
                ->get();

            yield ['Codigo', 'Nombre', 'Email', 'Plan', 'Estado', 'Ciudad', 'Fecha Inicio', 'Fecha Registro'];

            foreach ($clients as $client) {
                yield [
                    $client->client_code ?? '',
                    $client->name ?? '',
                    $client->email ?? '',
                    ucfirst($client->plan ?? ''),
                    ucfirst($client->status ?? ''),
                    $client->city ?? '',
                    $client->fecha_inicio ?? '',
                    $client->created_at ?? '',
                ];
            }
        });
    }

    /**
     * Export all payments to CSV.
     */
    public static function exportPayments(): StreamedResponse
    {
        return self::streamCsv('pagos-wellcore', function () {
            $payments = DB::table('payments')
                ->leftJoin('clients', 'payments.client_id', '=', 'clients.id')
                ->select([
                    'payments.id',
                    'payments.buyer_name',
                    'payments.email',
                    'clients.name as client_name',
                    'payments.plan',
                    'payments.amount',
                    'payments.currency',
                    'payments.status',
                    'payments.payment_method',
                    'payments.wompi_reference',
                    'payments.payu_reference',
                    'payments.created_at',
                ])
                ->orderByDesc('payments.created_at')
                ->get();

            yield [
                'ID', 'Comprador', 'Email', 'Cliente', 'Plan', 'Monto',
                'Moneda', 'Estado', 'Metodo de Pago', 'Ref. Wompi',
                'Ref. PayU', 'Fecha',
            ];

            foreach ($payments as $payment) {
                $statusLabel = match ($payment->status) {
                    'approved' => 'Aprobado',
                    'pending' => 'Pendiente',
                    'declined' => 'Rechazado',
                    'voided' => 'Anulado',
                    'error' => 'Error',
                    'cancelled' => 'Cancelado',
                    'rejected' => 'Rechazado',
                    default => ucfirst($payment->status ?? ''),
                };

                yield [
                    $payment->id,
                    $payment->buyer_name ?? '',
                    $payment->email ?? '',
                    $payment->client_name ?? '',
                    ucfirst($payment->plan ?? ''),
                    $payment->amount ?? '0',
                    $payment->currency ?? 'COP',
                    $statusLabel,
                    $payment->payment_method ?? '',
                    $payment->wompi_reference ?? '',
                    $payment->payu_reference ?? '',
                    $payment->created_at ?? '',
                ];
            }
        });
    }

    /**
     * Export all check-ins to CSV.
     */
    public static function exportCheckins(): StreamedResponse
    {
        return self::streamCsv('checkins-wellcore', function () {
            $checkins = DB::table('checkins')
                ->leftJoin('clients', 'checkins.client_id', '=', 'clients.id')
                ->select([
                    'checkins.id',
                    'clients.name as client_name',
                    'clients.email as client_email',
                    'checkins.week_label',
                    'checkins.checkin_date',
                    'checkins.bienestar',
                    'checkins.dias_entrenados',
                    'checkins.nutricion',
                    'checkins.rpe',
                    'checkins.comentario',
                    'checkins.coach_reply',
                    'checkins.replied_at',
                    'checkins.created_at',
                ])
                ->orderByDesc('checkins.created_at')
                ->get();

            yield [
                'ID', 'Cliente', 'Email', 'Semana', 'Fecha Check-in',
                'Bienestar (1-10)', 'Dias Entrenados', 'Nutricion',
                'RPE', 'Comentario', 'Respuesta Coach', 'Fecha Respuesta',
                'Fecha Registro',
            ];

            foreach ($checkins as $checkin) {
                yield [
                    $checkin->id,
                    $checkin->client_name ?? '',
                    $checkin->client_email ?? '',
                    $checkin->week_label ?? '',
                    $checkin->checkin_date ?? '',
                    $checkin->bienestar ?? '',
                    $checkin->dias_entrenados ?? '',
                    $checkin->nutricion ?? '',
                    $checkin->rpe ?? '',
                    $checkin->comentario ?? '',
                    $checkin->coach_reply ?? '',
                    $checkin->replied_at ?? '',
                    $checkin->created_at ?? '',
                ];
            }
        });
    }

    /**
     * Stream a CSV response from a generator callback.
     *
     * The generator must yield arrays of row values. The first yielded row
     * is used as the header row.
     *
     * A UTF-8 BOM is prepended so that Excel opens the file with the correct
     * encoding for Spanish characters (tildes, enes, etc.).
     */
    private static function streamCsv(string $filename, callable $generator): StreamedResponse
    {
        $date = date('Y-m-d');

        return new StreamedResponse(function () use ($generator) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility with Spanish chars
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($generator() as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}-{$date}.csv\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
