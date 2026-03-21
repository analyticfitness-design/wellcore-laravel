<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CommissionService
{
    // Commission rates by plan (coach gets this percentage)
    private const RATES = [
        'esencial' => 0.35,     // 35%
        'metodo' => 0.40,       // 40%
        'elite' => 0.45,        // 45%
        'presencial' => 0.50,   // 50%
    ];

    // Seniority bonuses (added to base rate)
    private const SENIORITY_BONUS = [
        0 => 0.00,    // 0-6 months
        6 => 0.02,    // 6-12 months: +2%
        12 => 0.05,   // 12-24 months: +5%
        24 => 0.08,   // 24+ months: +8%
    ];

    public static function calculateCommission(float $paymentAmount, string $planSlug, int $coachMonths = 0): array
    {
        $baseRate = self::RATES[$planSlug] ?? 0.35;
        $seniorityBonus = self::getSeniorityBonus($coachMonths);
        $totalRate = min($baseRate + $seniorityBonus, 0.60); // Cap at 60%

        $commission = round($paymentAmount * $totalRate, 2);
        $platform = round($paymentAmount - $commission, 2);

        return [
            'payment_amount' => $paymentAmount,
            'base_rate' => $baseRate,
            'seniority_bonus' => $seniorityBonus,
            'total_rate' => $totalRate,
            'commission' => $commission,
            'platform_revenue' => $platform,
            'plan' => $planSlug,
            'coach_months' => $coachMonths,
        ];
    }

    public static function getCoachEarnings(int $coachId, ?string $month = null): array
    {
        $query = DB::table('payments')
            ->where('coach_id', $coachId)
            ->where('status', 'approved');

        if ($month) {
            $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]);
        }

        $payments = $query->get();
        $totalEarnings = 0;
        $details = [];

        foreach ($payments as $payment) {
            $calc = self::calculateCommission(
                $payment->amount,
                $payment->plan_slug ?? 'esencial',
                0 // simplified — would need coach join date
            );
            $totalEarnings += $calc['commission'];
            $details[] = $calc;
        }

        return [
            'total_earnings' => $totalEarnings,
            'payment_count' => count($payments),
            'details' => $details,
        ];
    }

    private static function getSeniorityBonus(int $months): float
    {
        $bonus = 0;
        foreach (self::SENIORITY_BONUS as $threshold => $rate) {
            if ($months >= $threshold) $bonus = $rate;
        }
        return $bonus;
    }
}
