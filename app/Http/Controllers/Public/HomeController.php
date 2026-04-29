<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request, PricingService $pricing)
    {
        $latestPosts = collect([]);

        if (class_exists(\App\Models\BlogPost::class)) {
            try {
                $latestPosts = \App\Models\BlogPost::query()
                    ->where('status', 'published')
                    ->orderByDesc('published_at')
                    ->take(3)
                    ->get();
            } catch (\Throwable) {
                $latestPosts = collect([]);
            }
        }

        $liveCount = Cache::remember('home.live_count', 60, function () {
            if (class_exists(\App\Models\User::class)) {
                try {
                    return \App\Models\User::query()
                        ->where('status', 'active')
                        ->count();
                } catch (\Throwable) {}
            }
            return 47;
        });

        return view('public.home', [
            'latestPosts' => $latestPosts,
            'liveCount'   => $liveCount,
            'monthlyCop'  => [
                'esencial' => $pricing->priceCop('esencial'),
                'metodo'   => $pricing->priceCop('metodo'),
                'elite'    => $pricing->priceCop('elite'),
            ],
            'monthlyUsd'  => [
                'esencial' => $pricing->priceUsd('esencial'),
                'metodo'   => $pricing->priceUsd('metodo'),
                'elite'    => $pricing->priceUsd('elite'),
            ],
        ]);
    }
}
