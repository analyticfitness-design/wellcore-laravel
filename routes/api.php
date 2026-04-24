<?php

use App\Http\Controllers\Api\AdminAuditLogController;
use App\Http\Controllers\Api\AdminClientRequestController;
use App\Http\Controllers\Api\AdminCoachManagementController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminPlanTicketController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CoachBrandController;
use App\Http\Controllers\Api\CoachClientRequestController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\CoachPlanTicketController;
use App\Http\Controllers\Api\EjerciciosController;
use App\Http\Controllers\Api\MedalController;
use App\Http\Controllers\Api\NutritionController;
use App\Http\Controllers\Api\PublicFormController;
use App\Http\Controllers\Api\RiseController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\TrainingController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

// debug-gif endpoint removed — was public DoS vector (opcache_reset + hardcoded client_id)

// Ejercicios Fitcron (public — no auth required)
Route::prefix('ejercicios')->group(function () {
    Route::get('/', [EjerciciosController::class, 'index']);
    Route::get('/{slug}', [EjerciciosController::class, 'show']);
});

// Vue SPA Auth API — needs web session so login persists for impersonation/blade
Route::prefix('v/auth')
    ->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
    ])
    ->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:login');
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:change-password');
    });

// Vue SPA Public Forms API
Route::prefix('v/public')->group(function () {
    Route::post('/inscription', [PublicFormController::class, 'inscriptionSubmit']);
    Route::post('/coach-apply', [PublicFormController::class, 'coachApply']);
    Route::post('/rise-enroll', [PublicFormController::class, 'riseEnroll']);
    Route::post('/presencial', [PublicFormController::class, 'presencialSubmit']);
    Route::post('/trial', [PublicFormController::class, 'trialSignup']);
});

// Shop (public — no auth required)
Route::prefix('v/shop')->group(function () {
    Route::get('/products', [ShopController::class, 'index']);
    Route::get('/products/{slug}', [ShopController::class, 'show']);
});

// Client (authenticated — Bearer token required)
Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::get('/account-status', [ClientController::class, 'accountStatus']);
    Route::get('/plan-status', [ClientController::class, 'planStatus']);
    Route::get('/dashboard', [ClientController::class, 'dashboard']);
    Route::get('/metrics', [ClientController::class, 'metrics']);
    Route::post('/metrics', [ClientController::class, 'storeMetric']);
    Route::get('/profile', [ClientController::class, 'profile']);
    Route::put('/profile', [ClientController::class, 'updateProfile']);
    Route::get('/settings', [ClientController::class, 'settings']);
    Route::put('/settings', [ClientController::class, 'updateSettings']);
    Route::put('/settings/password', [ClientController::class, 'changePassword'])->middleware('throttle:change-password');
    Route::post('/onboarding/complete', [ClientController::class, 'completeOnboarding']);
    Route::get('/coach-feedback', [ClientController::class, 'coachFeedback']);
    Route::post('/coach-feedback', [ClientController::class, 'submitCoachFeedback']);
    Route::get('/notifications', [ClientController::class, 'notifications']);
    Route::post('/notifications/read-all', [ClientController::class, 'markAllRead']);
    Route::get('/tickets', [ClientController::class, 'tickets']);
    Route::post('/tickets', [ClientController::class, 'createTicket'])->middleware('throttle:ticket-create');
    Route::post('/notifications/{id}/read', [ClientController::class, 'markRead']);
    Route::get('/my-coach', [ClientController::class, 'myCoach']);
});

// Training (Phase 5)
Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::get('/plan', [TrainingController::class, 'plan']);
    Route::get('/training', [TrainingController::class, 'training']);
    Route::post('/training/toggle', [TrainingController::class, 'toggleTrainingDay']);
    Route::get('/workout/{day?}', [TrainingController::class, 'workout'])->where('day', '[0-9]+');
    Route::post('/workout/start', [TrainingController::class, 'startWorkout']);
    Route::post('/workout/complete-set', [TrainingController::class, 'completeSet']);
    Route::post('/workout/uncomplete-set', [TrainingController::class, 'uncompleteSet']);
    Route::post('/workout/abandon', [TrainingController::class, 'abandonWorkout']);
    Route::post('/workout/finish', [TrainingController::class, 'finishWorkout']);
    Route::get('/workout-summary/{sessionId}', [TrainingController::class, 'workoutSummary'])->where('sessionId', '[0-9]+|latest');
    Route::post('/workout-summary/{sessionId}/feeling', [TrainingController::class, 'saveWorkoutFeeling'])->where('sessionId', '[0-9]+|latest');
    Route::get('/checkin', [TrainingController::class, 'checkin']);
    Route::post('/checkin', [TrainingController::class, 'submitCheckin'])->middleware('throttle:checkin');

    // Nutrition — Recipe Meal Swaps & AI Estimation
    Route::get('/nutrition/macros-today', [NutritionController::class, 'macrosToday']);
    Route::post('/nutrition/swap', [NutritionController::class, 'createSwap']);
    Route::delete('/nutrition/swap/{id}', [NutritionController::class, 'deleteSwap'])->where('id', '[0-9]+');
    // AI food estimation — elite only
    Route::post('/ai-nutrition/estimate', [NutritionController::class, 'estimateFood'])->middleware('ensure.plan:elite');
});

// Social & Resources (Phase 6)
Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::get('/community', [SocialController::class, 'communityIndex']);
    Route::post('/community', [SocialController::class, 'communityCreate'])->middleware('throttle:community-write');
    Route::post('/community/{id}/react', [SocialController::class, 'communityReact'])->where('id', '[0-9]+')->middleware('throttle:community-write');
    Route::post('/community/{id}/comment', [SocialController::class, 'communityComment'])->where('id', '[0-9]+')->middleware('throttle:community-write');
    Route::delete('/community/{id}', [SocialController::class, 'communityDelete'])->where('id', '[0-9]+');
    Route::get('/challenges', [SocialController::class, 'challenges']);
    Route::post('/challenges/{id}/join', [SocialController::class, 'joinChallenge'])->where('id', '[0-9]+');
    Route::get('/chat', [SocialController::class, 'chatIndex']);
    Route::post('/chat', [SocialController::class, 'chatSend']);
    Route::get('/nutrition', [SocialController::class, 'nutrition']);
    Route::post('/nutrition/water', [SocialController::class, 'toggleWater']);
    Route::post('/nutrition/dismiss-tutorial', [SocialController::class, 'dismissNutritionTutorial']);
    Route::get('/habits', [SocialController::class, 'habits']);
    Route::post('/habits/toggle', [SocialController::class, 'toggleHabit']);
    Route::get('/referrals', [SocialController::class, 'referrals']);
    Route::post('/referrals/invite', [SocialController::class, 'sendReferralInvite'])->middleware('throttle:referrals');
    Route::get('/supplements', [SocialController::class, 'supplements']);
    Route::post('/supplements/toggle', [SocialController::class, 'toggleSupplement']);
    Route::get('/photos', [SocialController::class, 'photos']);
    Route::post('/photos', [SocialController::class, 'uploadPhoto']);
    Route::get('/photos/{id}/view', [SocialController::class, 'viewPhoto'])->where('id', '[0-9]+');
    Route::delete('/photos/{id}', [SocialController::class, 'deletePhoto'])->where('id', '[0-9]+');
    Route::get('/records', [SocialController::class, 'records']);
    // AI nutrition history + analysis — elite only
    Route::get('/ai-nutrition', [SocialController::class, 'aiNutritionHistory'])->middleware('ensure.plan:elite');
    Route::post('/ai-nutrition/analyze', [SocialController::class, 'aiNutritionAnalyze'])->middleware('ensure.plan:elite');
    Route::get('/videos', [SocialController::class, 'videos']);
    Route::get('/academia', [SocialController::class, 'academia']);
    Route::get('/video-checkins', [SocialController::class, 'videoCheckinHistory']);
    Route::get('/video-checkins/{id}/view', [SocialController::class, 'viewVideoCheckin'])->where('id', '[0-9]+');
    Route::post('/video-checkin', [SocialController::class, 'videoCheckinSubmit']);
});

// Medals & Achievements (Phase 1 — Backend)
Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::get('/medals', [MedalController::class, 'index']);
    Route::get('/medals/unlocked', [MedalController::class, 'unlocked']);
});

// Rise (Phase 7 — authenticated client, Bearer token)
// Plan gating: metodo, elite, rise, presencial can access RISE (not esencial/trial)
Route::prefix('v/rise')->middleware(['auth:wellcore', 'throttle:api', 'ensure.plan:metodo,elite,rise,presencial'])->group(function () {
    Route::get('/dashboard', [RiseController::class, 'dashboard']);
    Route::get('/program', [RiseController::class, 'program']);
    Route::get('/habits', [RiseController::class, 'habits']);
    Route::post('/habits/toggle', [RiseController::class, 'toggleHabit']);
    Route::get('/measurements', [RiseController::class, 'measurements']);
    Route::post('/measurements', [RiseController::class, 'storeMeasurement']);
    Route::get('/photos', [RiseController::class, 'photos']);
    Route::post('/photos', [RiseController::class, 'uploadPhoto']);
    Route::delete('/photos/{id}', [RiseController::class, 'deletePhoto'])->where('id', '[0-9]+');
    Route::get('/chat', [RiseController::class, 'chat']);
    Route::post('/chat', [RiseController::class, 'chatSend']);
    Route::get('/workout/{day?}', [RiseController::class, 'workout'])->where('day', '[0-9]+');
    Route::post('/workout/start', [RiseController::class, 'startWorkout']);
    Route::post('/workout/complete-set', [RiseController::class, 'completeSet']);
    Route::post('/workout/complete-cardio-set', [RiseController::class, 'completeSet']);
    Route::post('/workout/uncomplete-set', [RiseController::class, 'uncompleteSet']);
    Route::post('/workout/abandon', [RiseController::class, 'abandonWorkout']);
    Route::post('/workout/dismiss-tutorial', [RiseController::class, 'dismissWorkoutTutorial']);
    Route::post('/workout/finish', [RiseController::class, 'finishWorkout']);
    Route::get('/workout-summary/{sessionId}', [RiseController::class, 'workoutSummary'])->where('sessionId', '[0-9]+|latest');
    Route::post('/workout-summary/{sessionId}/feeling', [RiseController::class, 'saveWorkoutFeeling'])->where('sessionId', '[0-9]+|latest');
    Route::get('/profile', [RiseController::class, 'profile']);
});

// Coach (Phase 8 — authenticated admin with coach/admin/superadmin/jefe role)
Route::prefix('v/coach')->middleware(['auth:wellcore', 'throttle:api', 'role:coach,admin,superadmin,jefe'])->group(function () {
    Route::get('/dashboard', [CoachController::class, 'dashboard']);
    Route::get('/clients', [CoachController::class, 'clients']);
    Route::get('/kanban', [CoachController::class, 'kanban']);
    Route::post('/kanban/move', [CoachController::class, 'kanbanMove']);
    Route::get('/kanban/detail/{id}', [CoachController::class, 'kanbanClientDetail'])->where('id', '[0-9]+');
    Route::get('/checkins', [CoachController::class, 'checkins']);
    Route::post('/checkins/{id}/reply', [CoachController::class, 'checkinReply'])->where('id', '[0-9]+');
    Route::get('/messages', [CoachController::class, 'messages']);
    Route::post('/messages', [CoachController::class, 'sendMessage']);
    Route::post('/broadcast', [CoachController::class, 'broadcast']);
    Route::get('/plans', [CoachController::class, 'plans']);
    Route::get('/analytics', [CoachController::class, 'analytics']);
    Route::get('/profile', [CoachController::class, 'profile']);
    Route::put('/profile', [CoachController::class, 'updateProfile']);
    Route::get('/notes', [CoachController::class, 'notes']);
    Route::post('/notes', [CoachController::class, 'createNote']);
    Route::put('/notes/{id}', [CoachController::class, 'updateNote'])->where('id', '[0-9]+');
    Route::delete('/notes/{id}', [CoachController::class, 'deleteNote'])->where('id', '[0-9]+');
    // Coach branding (P4 Mi Marca) — owned by CoachBrandController
    Route::get('/brand', [CoachBrandController::class, 'show']);
    Route::put('/brand', [CoachBrandController::class, 'update']);
    Route::post('/brand/logo', [CoachBrandController::class, 'uploadLogo']);
    Route::delete('/brand/logo', [CoachBrandController::class, 'deleteLogo']);
    Route::get('/features', [CoachController::class, 'features']);
    Route::get('/resources', [CoachController::class, 'resources']);

    // Onboarding checklist persistente (P5.3)
    Route::get('/onboarding', [CoachController::class, 'onboardingGet']);
    Route::post('/onboarding', [CoachController::class, 'onboardingSet']);

    // Impersonation (coach → client)
    Route::post('/clients/{id}/impersonate', [CoachController::class, 'impersonate'])->middleware('throttle:impersonate')->whereNumber('id');
    Route::post('/impersonate/end', [CoachController::class, 'endImpersonation']);

    // Client action requests (delete/deactivate/edit)
    Route::post('/clients/{id}/requests', [CoachClientRequestController::class, 'store'])->whereNumber('id');
    Route::get('/clients/{id}/requests', [CoachClientRequestController::class, 'index'])->whereNumber('id');
    Route::delete('/client-requests/{id}', [CoachClientRequestController::class, 'cancel'])->whereNumber('id');

    // Plan Tickets (coach brief inbox)
    Route::get('/plan-tickets/autofill', [CoachPlanTicketController::class, 'autofill']);
    Route::get('/plan-tickets', [CoachPlanTicketController::class, 'index']);
    Route::post('/plan-tickets', [CoachPlanTicketController::class, 'store']);
    Route::get('/plan-tickets/{id}', [CoachPlanTicketController::class, 'show'])->whereNumber('id');
    Route::put('/plan-tickets/{id}', [CoachPlanTicketController::class, 'update'])->whereNumber('id');
    Route::post('/plan-tickets/{id}/submit', [CoachPlanTicketController::class, 'submit'])->whereNumber('id');
    Route::post('/plan-tickets/{id}/duplicate', [CoachPlanTicketController::class, 'duplicate'])->whereNumber('id');
    Route::delete('/plan-tickets/{id}', [CoachPlanTicketController::class, 'destroy'])->whereNumber('id');
    Route::get('/plan-tickets/{id}/comments', [CoachPlanTicketController::class, 'listComments'])->whereNumber('id');
    Route::post('/plan-tickets/{id}/comments', [CoachPlanTicketController::class, 'addComment'])->whereNumber('id');
    Route::post('/plan-tickets/{id}/attachments', [CoachPlanTicketController::class, 'uploadAttachment'])->whereNumber('id');
    Route::get('/plan-tickets/{id}/attachments', [CoachPlanTicketController::class, 'listAttachments'])->whereNumber('id');
    Route::delete('/plan-tickets/{id}/attachments/{attId}', [CoachPlanTicketController::class, 'deleteAttachment'])->whereNumber('id')->whereNumber('attId');

    // Coach notifications
    Route::get('/notifications', [CoachPlanTicketController::class, 'notifications']);
    Route::post('/notifications/read-all', [CoachPlanTicketController::class, 'markAllNotificationsRead']);
    Route::post('/notifications/{id}/read', [CoachPlanTicketController::class, 'markNotificationRead'])->whereNumber('id');
});

// Admin (Phase 9 — authenticated admin with admin/superadmin/jefe role)
Route::prefix('v/admin')->middleware(['auth:wellcore', 'throttle:api', 'role:admin,superadmin,jefe'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/feed', [AdminController::class, 'feed']);
    Route::get('/clients', [AdminController::class, 'clients']);
    Route::get('/clients/{id}', [AdminController::class, 'clientDetail'])->where('id', '[0-9]+');
    Route::get('/clients/{id}/intake', [AdminController::class, 'clientIntake'])->whereNumber('id');
    Route::get('/clients/{id}/activity', [AdminController::class, 'clientActivity'])->whereNumber('id');
    Route::put('/clients/{id}', [AdminController::class, 'updateClient'])->where('id', '[0-9]+');
    Route::delete('/clients/{id}', [AdminController::class, 'deleteClient'])->where('id', '[0-9]+');
    Route::get('/payments', [AdminController::class, 'payments']);
    Route::get('/coaches/stats', [AdminController::class, 'coachStats']);
    Route::get('/coaches', [AdminController::class, 'coaches']);
    Route::post('/coaches', [AdminController::class, 'addCoach']);
    Route::get('/coaches/{id}', [AdminController::class, 'getCoach'])->where('id', '[0-9]+');
    Route::put('/coaches/{id}', [AdminController::class, 'updateCoach'])->where('id', '[0-9]+');
    Route::delete('/coaches/{id}', [AdminController::class, 'deleteCoach'])->where('id', '[0-9]+');
    Route::patch('/coaches/{id}/visibility', [AdminController::class, 'toggleCoachVisibility'])->where('id', '[0-9]+');
    Route::get('/plans', [AdminController::class, 'plans']);
    Route::post('/plans', [AdminController::class, 'createPlan']);
    Route::post('/clients/{id}/plans', [AdminController::class, 'assignClientPlan'])->where('id', '[0-9]+');
    Route::get('/plans/{id}', [AdminController::class, 'viewPlan'])->where('id', '[0-9]+');
    Route::put('/plans/{id}', [AdminController::class, 'updatePlan'])->where('id', '[0-9]+');
    Route::delete('/plans/{id}', [AdminController::class, 'deletePlan'])->where('id', '[0-9]+');
    Route::get('/inscriptions', [AdminController::class, 'inscriptions']);
    Route::put('/inscriptions/{id}', [AdminController::class, 'updateInscription'])->where('id', '[0-9]+');
    Route::post('/inscriptions/{id}/convert', [AdminController::class, 'convertInscription']);
    Route::get('/invitations', [AdminController::class, 'invitations']);
    Route::post('/invitations', [AdminController::class, 'createInvitation']);
    Route::delete('/invitations/{id}', [AdminController::class, 'deleteInvitation'])->where('id', '[0-9]+');
    Route::get('/rise', [AdminController::class, 'rise']);
    Route::get('/settings', [AdminController::class, 'settings']);
    Route::put('/settings', [AdminController::class, 'updateSettings']);
    Route::get('/chat-analytics', [AdminController::class, 'chatAnalytics']);
    Route::post('/ai-generator', [AdminController::class, 'aiGenerator']);
    Route::get('/tickets', [AdminController::class, 'tickets']);
    Route::post('/tickets/{id}/reply', [AdminController::class, 'replyTicket']);
    Route::patch('/tickets/{id}/status', [AdminController::class, 'updateTicketStatus']);
    Route::post('/send-plan-invitation', [AdminController::class, 'sendPlanInvitation']);
    Route::post('/send-gift-invitation', [AdminController::class, 'sendGiftInvitation']);

    // Coach management (full CRUD + reset password)
    Route::get('/coaches/manage', [AdminCoachManagementController::class, 'index']);
    Route::post('/coaches/manage', [AdminCoachManagementController::class, 'store'])->middleware('throttle:coach-create');
    Route::get('/coaches/manage/{id}', [AdminCoachManagementController::class, 'show'])->whereNumber('id');
    Route::put('/coaches/manage/{id}', [AdminCoachManagementController::class, 'update'])->whereNumber('id');
    Route::post('/coaches/manage/{id}/reset-password', [AdminCoachManagementController::class, 'resetPassword'])->whereNumber('id');
    Route::delete('/coaches/manage/{id}', [AdminCoachManagementController::class, 'destroy'])->whereNumber('id');

    // Audit log (read-only, superadmin only)
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index']);

    // Client action requests (admin inbox)
    Route::get('/client-requests', [AdminClientRequestController::class, 'index']);
    Route::get('/client-requests/{id}', [AdminClientRequestController::class, 'show'])->whereNumber('id');
    Route::post('/client-requests/{id}/approve', [AdminClientRequestController::class, 'approve'])->whereNumber('id');
    Route::post('/client-requests/{id}/reject', [AdminClientRequestController::class, 'reject'])->whereNumber('id');

    // Plan Tickets (admin inbox — review & export briefs)
    Route::get('/plan-tickets/stats', [AdminPlanTicketController::class, 'stats']);
    Route::get('/plan-tickets', [AdminPlanTicketController::class, 'index']);
    Route::get('/plan-tickets/{id}', [AdminPlanTicketController::class, 'show'])->whereNumber('id');
    Route::get('/plan-tickets/{id}/print', [AdminPlanTicketController::class, 'printView'])->whereNumber('id');
    Route::get('/plan-tickets/{id}/attachments', [AdminPlanTicketController::class, 'listAttachments'])->whereNumber('id');
    Route::delete('/plan-tickets/{id}/attachments/{attId}', [AdminPlanTicketController::class, 'deleteAttachment'])->whereNumber('id')->whereNumber('attId');
    Route::get('/plan-tickets/{id}/export', [AdminPlanTicketController::class, 'exportJson'])->whereNumber('id');
    Route::get('/plan-tickets/{id}/export/{section}', [AdminPlanTicketController::class, 'exportSection'])
        ->whereNumber('id')
        ->where('section', 'full|entrenamiento|nutricion|habitos|suplementacion|ciclo');
    Route::post('/plan-tickets/{id}/status', [AdminPlanTicketController::class, 'updateStatus'])->whereNumber('id');
    Route::get('/plan-tickets/{id}/comments', [AdminPlanTicketController::class, 'listComments'])->whereNumber('id');
    Route::post('/plan-tickets/{id}/comments', [AdminPlanTicketController::class, 'addComment'])->whereNumber('id');

    // Admin notifications
    Route::get('/notifications', [AdminPlanTicketController::class, 'notifications']);
    Route::post('/notifications/read-all', [AdminPlanTicketController::class, 'markAllNotificationsRead']);
    Route::post('/notifications/{id}/read', [AdminPlanTicketController::class, 'markNotificationRead'])->whereNumber('id');
});
