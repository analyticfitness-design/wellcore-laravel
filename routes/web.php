<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Client\ChallengesView;
use App\Livewire\Client\ChatWidget;
use App\Livewire\Client\CheckinForm;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Client\HabitTracker;
use App\Livewire\Client\MetricsTracker;
use App\Livewire\Client\NutritionPlan;
use App\Livewire\Client\PlanViewer;
use App\Livewire\Client\ProfileEditor;
use App\Livewire\Client\ProgressPhotos;
use App\Livewire\Client\TrainingView;
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
    // Client dashboard routes
    Route::prefix('client')->name('client.')->group(function () {
        Route::get('/', ClientDashboard::class)->name('dashboard');
        Route::get('/plan', PlanViewer::class)->name('plan');
        Route::get('/checkin', CheckinForm::class)->name('checkin');
        Route::get('/training', TrainingView::class)->name('training');
        Route::get('/metrics', MetricsTracker::class)->name('metrics');
        Route::get('/nutrition', NutritionPlan::class)->name('nutrition');
        Route::get('/photos', ProgressPhotos::class)->name('photos');
        Route::get('/challenges', ChallengesView::class)->name('challenges');
        Route::get('/chat', ChatWidget::class)->name('chat');
        Route::get('/profile', ProfileEditor::class)->name('profile');
        Route::get('/habits', HabitTracker::class)->name('habits');
    });

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
