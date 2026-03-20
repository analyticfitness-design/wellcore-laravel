<?php

use App\Livewire\Admin\ClientDetail;
use App\Livewire\Admin\ClientTable;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\InscriptionsList;
use App\Livewire\Admin\PaymentsDashboard;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Client\ChallengesView;
use App\Livewire\Client\ClientSettings;
use App\Livewire\Client\ChatWidget;
use App\Livewire\Client\CommunityFeed;
use App\Livewire\Client\CoachFeedback;
use App\Livewire\Client\ReferralProgram;
use App\Livewire\Client\TicketSupport;
use App\Livewire\Client\CheckinForm;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Client\VideoCheckinUpload;
use App\Livewire\Client\HabitTracker;
use App\Livewire\Client\MetricsTracker;
use App\Livewire\Client\AINutrition;
use App\Livewire\Client\NutritionPlan;
use App\Livewire\Client\PlanViewer;
use App\Livewire\Client\ProfileEditor;
use App\Livewire\Client\ProgressPhotos;
use App\Livewire\Client\TrainingView;
use App\Livewire\Coach\CheckinReview;
use App\Livewire\Coach\ClientList as CoachClientList;
use App\Livewire\Coach\Dashboard as CoachDashboard;
use App\Livewire\Coach\MessageCenter;
use App\Livewire\Rise\DailyTracking;
use App\Livewire\Rise\Dashboard as RiseDashboard;
use App\Livewire\Rise\Measurements as RiseMeasurements;
use App\Livewire\Rise\ProgramView;
use App\Livewire\Shop\ProductCatalog;
use App\Livewire\Shop\ProductDetail;
use App\Livewire\TestDashboard;
use App\Http\Controllers\WebhookController;
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
Route::get('/metodo', function () {
    return view('public.metodo');
})->name('metodo');
Route::get('/proceso', function () {
    return view('public.proceso');
})->name('proceso');
Route::get('/reto-rise', function () {
    return view('public.rise');
})->name('reto-rise');
Route::get('/inscripcion', \App\Livewire\InscriptionForm::class)->name('inscripcion');
Route::get('/pagar', \App\Livewire\Checkout::class)->name('pagar');
Route::get('/pago-exitoso', function () {
    return view('public.pago-exitoso');
})->name('pago-exitoso');
Route::get('/pago-confirmado', function () {
    return view('public.pago-exitoso');
})->name('pago-confirmado');

// Shop routes (public, no auth required)
Route::prefix('tienda')->name('shop.')->group(function () {
    Route::get('/', ProductCatalog::class)->name('catalog');
    Route::get('/{slug}', ProductDetail::class)->name('product');
});

// Webhook routes (excluded from CSRF via bootstrap/app.php)
Route::post('/webhooks/wompi', [WebhookController::class, 'wompi'])->name('webhooks.wompi');

// Auth routes (guest only — redirect if already logged in)
Route::middleware('guest:wellcore')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', \App\Livewire\Auth\ResetPassword::class)->name('password.reset');
    Route::get('/auth/google', [\App\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleAuthController::class, 'callback'])->name('auth.google.callback');
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
        Route::get('/ai-nutrition', AINutrition::class)->name('ai-nutrition');
        Route::get('/photos', ProgressPhotos::class)->name('photos');
        Route::get('/challenges', ChallengesView::class)->name('challenges');
        Route::get('/chat', ChatWidget::class)->name('chat');
        Route::get('/profile', ProfileEditor::class)->name('profile');
        Route::get('/habits', HabitTracker::class)->name('habits');
        Route::get('/academia', \App\Livewire\Client\Academia::class)->name('academia');
        Route::get('/timer', \App\Livewire\Client\WorkoutTimer::class)->name('timer');
        Route::get('/mindfulness', \App\Livewire\Client\Mindfulness::class)->name('mindfulness');
        Route::get('/videos', \App\Livewire\Client\VideoLibrary::class)->name('videos');
        Route::get('/recetas', \App\Livewire\Client\RecipeDatabase::class)->name('recipes');
        Route::get('/records', \App\Livewire\Client\PersonalRecords::class)->name('records');
        Route::get('/community', CommunityFeed::class)->name('community');
        Route::get('/video-checkin', VideoCheckinUpload::class)->name('video-checkin');
        Route::get('/audio', \App\Livewire\Client\AudioPlayer::class)->name('audio');
        Route::get('/hacks', \App\Livewire\Client\EvidenceHacks::class)->name('hacks');
        Route::get('/settings', ClientSettings::class)->name('settings');
        Route::get('/referrals', ReferralProgram::class)->name('referrals');
        Route::get('/coach-feedback', CoachFeedback::class)->name('coach.feedback');
        Route::get('/tickets', TicketSupport::class)->name('tickets');
    });

    // RISE program routes
    Route::prefix('rise')->name('rise.')->group(function () {
        Route::get('/', RiseDashboard::class)->name('dashboard');
        Route::get('/tracking', DailyTracking::class)->name('tracking');
        Route::get('/measurements', RiseMeasurements::class)->name('measurements');
        Route::get('/program', ProgramView::class)->name('program');
        Route::get('/habits', function () { return 'Coming soon'; })->name('habits');
        Route::get('/photos', function () { return 'Coming soon'; })->name('photos');
        Route::get('/chat', function () { return 'Coming soon'; })->name('chat');
        Route::get('/profile', function () { return 'Coming soon'; })->name('profile');
    });

    // Admin dashboard routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboard::class)->name('dashboard');
        Route::get('/clients', ClientTable::class)->name('clients');
        Route::get('/clients/{clientId}', ClientDetail::class)->name('client-detail');
        Route::get('/payments', PaymentsDashboard::class)->name('payments');
        Route::get('/inscriptions', InscriptionsList::class)->name('inscriptions');
        Route::get('/coaches', function () {
            return 'Coach Management — Coming soon';
        })->name('coaches');
        Route::get('/plans', function () {
            return 'Plan Management — Coming soon';
        })->name('plans');
        Route::get('/tickets', \App\Livewire\Admin\TicketManager::class)->name('tickets');
    });

    // Coach portal routes
    Route::prefix('coach')->name('coach.')->group(function () {
        Route::get('/', CoachDashboard::class)->name('dashboard');
        Route::get('/clients', CoachClientList::class)->name('clients');
        Route::get('/checkins', CheckinReview::class)->name('checkins');
        Route::get('/messages', MessageCenter::class)->name('messages');
        Route::get('/notes', function () {
            return 'Coming soon';
        })->name('notes');
        Route::get('/plans', function () {
            return 'Coming soon';
        })->name('plans');
    });

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
