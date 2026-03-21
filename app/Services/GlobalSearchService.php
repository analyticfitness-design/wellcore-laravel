<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class GlobalSearchService
{
    public static function search(string $query, int $limit = 20): array
    {
        $query = trim($query);
        if (strlen($query) < 2) return [];

        $results = [];

        // Search clients
        $clients = DB::table('clients')
            ->select('id', 'name', 'email', DB::raw("'client' as type"))
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->name,
                'subtitle' => $r->email,
                'type' => 'client',
                'url' => "/admin/clients/{$r->id}",
                'icon' => 'user',
            ]);

        // Search coaches (admins with coach role)
        $coaches = DB::table('admins')
            ->select('id', 'name', 'email', DB::raw("'coach' as type"))
            ->where('role', 'coach')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->name,
                'subtitle' => "Coach · {$r->email}",
                'type' => 'coach',
                'url' => "/admin/coaches/{$r->id}",
                'icon' => 'badge',
            ]);

        // Search payments
        $payments = DB::table('payments')
            ->select('id', 'reference', 'amount', 'status', DB::raw("'payment' as type"))
            ->where('reference', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => "Pago #{$r->reference}",
                'subtitle' => '$' . number_format($r->amount, 0) . " · {$r->status}",
                'type' => 'payment',
                'url' => "/admin/payments",
                'icon' => 'banknotes',
            ]);

        // Search plans
        $plans = DB::table('plan_templates')
            ->select('id', 'name', 'type', DB::raw("'plan' as type_label"))
            ->where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->name,
                'subtitle' => "Plan · {$r->type}",
                'type' => 'plan',
                'url' => "/admin/plans",
                'icon' => 'document',
            ]);

        return [
            'clients' => $clients->toArray(),
            'coaches' => $coaches->toArray(),
            'payments' => $payments->toArray(),
            'plans' => $plans->toArray(),
            'total' => $clients->count() + $coaches->count() + $payments->count() + $plans->count(),
        ];
    }
}
