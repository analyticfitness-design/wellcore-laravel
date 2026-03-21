<?php

use App\Livewire\Admin\AdminSettings;
use App\Livewire\Admin\AdminTools;
use App\Livewire\Admin\AIPlanGenerator;
use App\Livewire\Admin\ChatAnalytics;
use App\Livewire\Admin\ClientDetail;
use App\Livewire\Admin\ClientTable;
use App\Livewire\Admin\CoachManagement;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\InscriptionsList;
use App\Livewire\Admin\InvitationManager;
use App\Livewire\Admin\LiveFeed;
use App\Livewire\Admin\PaymentsDashboard;
use App\Livewire\Admin\PlanManagement;
use App\Livewire\Admin\RiseManagement;
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
use App\Livewire\Coach\Analytics;
use App\Livewire\Coach\BroadcastCenter;
use App\Livewire\Coach\CheckinReview;
use App\Livewire\Coach\ClientKanban;
use App\Livewire\Coach\ClientList as CoachClientList;
use App\Livewire\Coach\CoachProfilePage;
use App\Livewire\Coach\Dashboard as CoachDashboard;
use App\Livewire\Coach\CoachNotesPage;
use App\Livewire\Coach\MessageCenter;
use App\Livewire\Coach\MyBrand;
use App\Livewire\Coach\CoachFeatures;
use App\Livewire\Coach\PlansManager;
use App\Livewire\Coach\Resources as CoachResources;
use App\Livewire\Rise\DailyTracking;
use App\Livewire\Rise\Dashboard as RiseDashboard;
use App\Livewire\Rise\Measurements as RiseMeasurements;
use App\Livewire\Rise\ProgramView;
use App\Livewire\Shop\ProductCatalog;
use App\Livewire\Shop\ProductDetail;
use App\Livewire\Public\CoachApplication;
use App\Livewire\Public\PresencialForm;
use App\Livewire\Public\RiseEnrollment;
use App\Livewire\TestDashboard;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Chatbot API (public, no auth required)
Route::post('/api/chat', [ChatController::class, 'send'])->name('api.chat');

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
Route::get('/coaches', function () {
    return view('public.coaches');
})->name('coaches');
Route::get('/coaches/apply', CoachApplication::class)->name('coaches.apply');

// Legal pages
Route::get('/terminos', fn() => view('public.legal.terminos'))->name('terminos');
Route::get('/privacidad', fn() => view('public.legal.privacidad'))->name('privacidad');
Route::get('/politica-cookies', fn() => view('public.legal.cookies'))->name('cookies');
Route::get('/reembolsos', fn() => view('public.legal.reembolso'))->name('reembolsos');

// Coach Silvia landing
Route::get('/fit', fn() => view('public.fit'))->name('fit');

// Presencial
Route::get('/presencial', fn() => view('public.presencial'))->name('presencial');
Route::get('/presencial/inscripcion', PresencialForm::class)->name('presencial.form');

// RISE Enrollment
Route::get('/rise-enroll', RiseEnrollment::class)->name('rise.enroll');

Route::get('/inscripcion', \App\Livewire\InscriptionForm::class)->name('inscripcion');
Route::get('/pagar', \App\Livewire\Checkout::class)->name('pagar');
Route::get('/pago-exitoso', function () {
    return view('public.pago-exitoso');
})->name('pago-exitoso');
Route::get('/pago-confirmado', function () {
    return view('public.pago-exitoso');
})->name('pago-confirmado');

// Blog
Route::get('/blog', function () {
    return view('public.blog.index');
})->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Shop routes (public, no auth required)
Route::prefix('tienda')->name('shop.')->group(function () {
    Route::get('/', ProductCatalog::class)->name('catalog');
    Route::get('/{slug}', ProductDetail::class)->name('product');
});

// Newsletter API (public, no auth required)
Route::post('/api/newsletter', [NewsletterController::class, 'subscribe'])->name('api.newsletter');

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
        Route::get('/academia', \App\Livewire\Client\Academia::class)->name('academia')->lazy();
        Route::get('/timer', \App\Livewire\Client\WorkoutTimer::class)->name('timer');
        Route::get('/mindfulness', \App\Livewire\Client\Mindfulness::class)->name('mindfulness');
        Route::get('/videos', \App\Livewire\Client\VideoLibrary::class)->name('videos')->lazy();
        Route::get('/recetas', \App\Livewire\Client\RecipeDatabase::class)->name('recipes')->lazy();
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
        Route::get('/habits', \App\Livewire\Rise\Habits::class)->name('habits');
        Route::get('/photos', \App\Livewire\Rise\Photos::class)->name('photos');
        Route::get('/chat', \App\Livewire\Rise\Chat::class)->name('chat');
        Route::get('/profile', \App\Livewire\Rise\RiseProfile::class)->name('profile');
    });

    // Admin dashboard routes
    Route::prefix('admin')->name('admin.')->middleware('role:superadmin,admin,coach,jefe')->group(function () {
        Route::get('/', AdminDashboard::class)->name('dashboard');
        Route::get('/feed', LiveFeed::class)->name('feed');
        Route::get('/clients', ClientTable::class)->name('clients');
        Route::get('/clients/{clientId}', ClientDetail::class)->name('client-detail');
        Route::get('/payments', PaymentsDashboard::class)->name('payments');
        Route::get('/inscriptions', InscriptionsList::class)->name('inscriptions');
        Route::get('/invitations', InvitationManager::class)->name('invitations');
        Route::get('/coaches', CoachManagement::class)->name('coaches');
        Route::get('/plans', PlanManagement::class)->name('plans');
        Route::get('/ai-generator', AIPlanGenerator::class)->name('ai-generator')->lazy();
        Route::get('/rise', RiseManagement::class)->name('rise');
        Route::get('/tickets', \App\Livewire\Admin\TicketManager::class)->name('tickets');
        Route::get('/chat', ChatAnalytics::class)->name('chat');
        Route::get('/tools', AdminTools::class)->name('tools')->lazy();
        Route::get('/settings', AdminSettings::class)->name('settings');

        // CSV Export routes
        Route::get('/export/clients', [ExportController::class, 'clients'])->name('export.clients');
        Route::get('/export/payments', [ExportController::class, 'payments'])->name('export.payments');
        Route::get('/export/checkins', [ExportController::class, 'checkins'])->name('export.checkins');
    });

    // Coach portal routes
    Route::prefix('coach')->name('coach.')->middleware('role:superadmin,admin,coach,jefe')->group(function () {
        Route::get('/', CoachDashboard::class)->name('dashboard');
        Route::get('/clients', CoachClientList::class)->name('clients');
        Route::get('/kanban', ClientKanban::class)->name('kanban');
        Route::get('/checkins', CheckinReview::class)->name('checkins');
        Route::get('/messages', MessageCenter::class)->name('messages');
        Route::get('/broadcast', BroadcastCenter::class)->name('broadcast');
        Route::get('/notes', CoachNotesPage::class)->name('notes');
        Route::get('/plans', PlansManager::class)->name('plans');
        Route::get('/analytics', Analytics::class)->name('analytics')->lazy();
        Route::get('/profile', CoachProfilePage::class)->name('profile');
        Route::get('/brand', MyBrand::class)->name('brand');
        Route::get('/resources', CoachResources::class)->name('resources')->lazy();
        Route::get('/features', CoachFeatures::class)->name('features')->lazy();
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

// DEV ONLY routes — disabled in production
if (app()->environment('local', 'testing')) {
    Route::get('/test', TestDashboard::class);
    Route::get('/dev-login/{clientId}', function ($clientId) {
        $client = \App\Models\Client::find($clientId);
        if (!$client) return redirect('/login');
        $token = \App\Models\AuthToken::where('user_id', $clientId)->where('user_type', 'client')->where('expires_at', '>', now())->first();
        if (!$token) return redirect('/login');
        session(['wc_token' => $token->token, 'wc_user_type' => 'client', 'wc_user_id' => $clientId]);
        return redirect('/client');
    });
}
