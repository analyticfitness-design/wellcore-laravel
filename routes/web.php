<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\TestDashboard;
use Illuminate\Support\Facades\Route;

// Public marketing pages
Route::get('/', function () {
    return view('public.home');
})->name('home');
Route::get('/planes', function () {
    return view('public.planes');
})->name('planes');
Route::get('/nosotros', function () {
    return view('public.nosotros');
})->name('nosotros');
Route::get('/faq', function () {
    return view('public.faq');
})->name('faq');

// Auth routes (guest only — redirect if already logged in)
Route::middleware('guest:wellcore')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
});

// Authenticated routes
Route::middleware('auth:wellcore')->group(function () {
    // Client dashboards
    Route::get('/client', function () {
        return 'Client Dashboard — Coming in Phase 2';
    })->name('client.dashboard');

    Route::get('/rise', function () {
        return 'RISE Dashboard — Coming in Phase 2';
    })->name('rise.dashboard');

    // Admin dashboards
    Route::get('/admin', function () {
        return 'Admin Dashboard — Coming in Phase 3';
    })->name('admin.dashboard');

    Route::get('/coach', function () {
        return 'Coach Portal — Coming in Phase 3';
    })->name('coach.dashboard');

    // Logout
    Route::post('/logout', function () {
        $token = session('wc_token');
        if ($token) {
            \App\Models\AuthToken::where('token', $token)->delete();
        }
        session()->flush();

        return redirect('/login');
    })->name('logout');
});

// Test route (from Phase 0)
Route::get('/test', TestDashboard::class);
