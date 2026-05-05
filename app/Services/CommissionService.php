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
        // payments table has no coach_id; coach-client assignment lives in
        // assigned_plans.assigned_by. Join via client_id and de-duplicate so a
        // single payment is not counted twice when multiple plans exist.
        $query = DB::table('payments')
            ->join('assigned_plans', 'assigned_plans.client_id', '=', 'payments.client_id')
            ->where('assigned_plans.assigned_by', $coachId)
            ->where('payments.status', 'approved')
            ->select('payments.id', 'payments.amount', 'payments.plan', 'payments.created_at')
            ->distinct();

        if ($month) {
            $query->whereRaw("DATE_FORMAT(payments.created_at, '%Y-%m') = ?", [$month]);
        }

        $payments = $query->get();
        $totalEarnings = 0;
        $details = [];

        foreach ($payments as $payment) {
            // payments.plan is cast to PlanType enum on the model, but DB::table
            // returns raw strings — so just normalize here.
            $planSlug = is_string($payment->plan) && $payment->plan !== ''
                ? $payment->plan
                : 'esencial';

            $calc = self::calculateCommission(
                (float) $payment->amount,
                $planSlug,
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
            if ($months >= $threshold) {
                $bonus = $rate;
            }
        }

        return $bonus;
    }
}
