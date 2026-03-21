<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SchedulingService
{
    public static function getAvailableSlots(int $coachId, string $date): array
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek; // 0=Sunday

        // Get coach availability for this day
        $availability = DB::table('coach_availability')
            ->where('coach_id', $coachId)
            ->where('day_of_week', $dayOfWeek)
            ->where('active', true)
            ->get();

        if ($availability->isEmpty()) return [];

        // Get booked slots for this date
        $booked = DB::table('appointments')
            ->where('coach_id', $coachId)
            ->whereDate('scheduled_at', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('scheduled_at')
            ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
            ->toArray();

        $slots = [];
        foreach ($availability as $window) {
            $start = Carbon::parse($window->start_time);
            $end = Carbon::parse($window->end_time);
            $duration = $window->slot_duration_minutes ?? 30;

            while ($start->lt($end)) {
                $time = $start->format('H:i');
                $slots[] = [
                    'time' => $time,
                    'available' => !in_array($time, $booked),
                    'formatted' => $start->format('h:i A'),
                ];
                $start->addMinutes($duration);
            }
        }

        return $slots;
    }

    public static function bookAppointment(int $coachId, int $clientId, string $dateTime, string $type = 'checkin'): array
    {
        $scheduledAt = Carbon::parse($dateTime);

        // Check if slot is available
        $exists = DB::table('appointments')
            ->where('coach_id', $coachId)
            ->where('scheduled_at', $scheduledAt)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($exists) {
            return ['success' => false, 'error' => 'Este horario ya esta reservado.'];
        }

        try {
            $id = DB::table('appointments')->insertGetId([
                'coach_id' => $coachId,
                'client_id' => $clientId,
                'type' => $type,
                'scheduled_at' => $scheduledAt,
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['success' => true, 'appointment_id' => $id];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error al reservar. Intenta de nuevo.'];
        }
    }

    public static function cancelAppointment(int $appointmentId): bool
    {
        return DB::table('appointments')
            ->where('id', $appointmentId)
            ->update(['status' => 'cancelled', 'updated_at' => now()]) > 0;
    }

    public static function getUpcoming(int $userId, string $role = 'client', int $limit = 5): array
    {
        $column = $role === 'coach' ? 'coach_id' : 'client_id';

        return DB::table('appointments')
            ->where($column, $userId)
            ->where('scheduled_at', '>=', now())
            ->where('status', 'confirmed')
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
