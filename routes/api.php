<?php

use App\Http\Controllers\Api\Admin\BroadcastController;
use App\Http\Controllers\Api\Admin\Marketing\CoachProfileController;
use App\Http\Controllers\Api\Admin\Marketing\DropAssetController;
use App\Http\Controllers\Api\Admin\Marketing\DropReviewController;
use App\Http\Controllers\Api\Admin\Marketing\QueueController;
use App\Http\Controllers\Api\Admin\ModerationQueueController;
use App\Http\Controllers\Api\Admin\PaymentProofReviewController;
use App\Http\Controllers\Api\AdminAIGeneratorController;
use App\Http\Controllers\Api\AdminAuditLogController;
use App\Http\Controllers\Api\AdminCampaignController;
use App\Http\Controllers\Api\AdminClientRequestController;
use App\Http\Controllers\Api\AdminCoachManagementController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminFormsController;
use App\Http\Controllers\Api\AdminImpersonateController;
use App\Http\Controllers\Api\AdminPlanTicketController;
use App\Http\Controllers\Api\AdminToolsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Client\FoodPhotoController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\Coach\CommunityController;
use App\Http\Controllers\Api\Coach\ContractController;
use App\Http\Controllers\Api\Coach\FoodPhotoReviewController;
use App\Http\Controllers\Api\Coach\InvitationController;
use App\Http\Controllers\Api\Coach\MarketingProfileController;
use App\Http\Controllers\Api\Coach\ModerationController;
use App\Http\Controllers\Api\Coach\PieceStateController;
use App\Http\Controllers\Api\Coach\StrategyAssetController;
use App\Http\Controllers\Api\Coach\StrategyController;
use App\Http\Controllers\Api\CoachBrandController;
use App\Http\Controllers\Api\CoachClientRequestController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\CoachPlanTicketController;
use App\Http\Controllers\Api\EjerciciosController;
use App\Http\Controllers\Api\GroupPulseController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\MedalController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NutritionController;
use App\Http\Controllers\Api\PaymentProofController;
use App\Http\Controllers\Api\PostReportController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PublicFormController;
use App\Http\Controllers\Api\PulsoController;
use App\Http\Controllers\Api\RiseController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\TrainingController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// debug-gif endpoint removed — was public DoS vector (opcache_reset + hardcoded client_id)

// Broadcasting channel authentication — required for private/presence channels via Reverb.
// Authenticated with the WellCore guard so both Admin and Client users can authorize.
Route::post('/v/broadcasting/auth', function (Request $request) {
    return Broadcast::auth($request);
})->middleware(['auth:wellcore']);

// Ejercicios Fitcron (public — no auth required)
Route::prefix('ejercicios')->middleware('throttle:api')->group(function () {
    Route::get('/', [EjerciciosController::class, 'index']);
    Route::get('/{slug}', [EjerciciosController::class, 'show'])->where('slug', '[\w\-]+');
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
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('throttle:api');
        Route::get('/me', [AuthController::class, 'me'])->middleware('throttle:api');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:change-password');
    });

// Vue SPA Public Forms API
Route::prefix('v/public')->group(function () {
    Route::post('/inscription', [PublicFormController::class, 'inscriptionSubmit']);
    Route::post('/coach-apply', [PublicFormController::class, 'coachApply']);
    Route::post('/rise-enroll', [PublicFormController::class, 'riseEnroll']);
    Route::post('/presencial', [PublicFormController::class, 'presencialSubmit']);
    Route::post('/trial', [PublicFormController::class, 'trialSignup']);
    Route::get('/invitations/{code}', [PublicFormController::class, 'resolveInvitation']);
    Route::post('/invitation-intake', [PublicFormController::class, 'invitationIntake']);
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
    Route::post('/avatar', [ClientController::class, 'uploadAvatar']);
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

    // Latido del Grupo — feed de actividad agregada del grupo del coach
    Route::get('/group-pulse', [GroupPulseController::class, 'index']);
});

// Training (Phase 5)
Route::prefix('v/client')->middleware(['auth:wellcore', 'plan.lock:strict', 'throttle:api'])->group(function () {
    Route::get('/plan', [TrainingController::class, 'plan']);
    Route::get('/training', [TrainingController::class, 'training']);
    Route::post('/training/toggle', [TrainingController::class, 'toggleTrainingDay']);
    Route::get('/workout/{day?}', [TrainingController::class, 'workout'])->where('day', '[0-9]+');
    Route::post('/workout/start', [TrainingController::class, 'startWorkout']);
    Route::post('/workout/complete-set', [TrainingController::class, 'completeSet']);
    Route::post('/workout/uncomplete-set', [TrainingController::class, 'uncompleteSet']);
    Route::post('/workout/abandon', [TrainingController::class, 'abandonWorkout']);
    Route::post('/workout/dismiss-tutorial', [TrainingController::class, 'dismissWorkoutTutorial']);
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

    // Food Tracking — fotos de comida revisadas por el coach
    Route::get('/food-photos', [FoodPhotoController::class, 'index']);
    Route::get('/food-photos/history', [FoodPhotoController::class, 'history']);
    Route::post('/food-photos', [FoodPhotoController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::patch('/food-photos/{id}/note', [FoodPhotoController::class, 'updateNote'])
        ->where('id', '[0-9]+');
    Route::delete('/food-photos/{id}', [FoodPhotoController::class, 'destroy'])
        ->where('id', '[0-9]+');
});

// Social & Resources (Phase 6)
Route::prefix('v/client')->middleware(['auth:wellcore', 'plan.lock:strict', 'throttle:api'])->group(function () {
    Route::get('/community', [SocialController::class, 'communityIndex']);
    Route::post('/community', [SocialController::class, 'communityCreate'])->middleware('throttle:community-write');
    Route::post('/community/{id}/react', [SocialController::class, 'communityReact'])->where('id', '[0-9]+')->middleware('throttle:community-write');
    Route::post('/community/{id}/comment', [SocialController::class, 'communityComment'])->where('id', '[0-9]+')->middleware('throttle:community-write');
    Route::delete('/community/{id}', [SocialController::class, 'communityDelete'])->where('id', '[0-9]+');

    // Pulsos WellCore
    Route::get('/pulsos', [PulsoController::class, 'index']);
    Route::post('/pulsos', [PulsoController::class, 'store']);
    Route::get('/pulsos/{id}', [PulsoController::class, 'show']);
    Route::get('/pulsos/{id}/media', [PulsoController::class, 'media']);
    Route::post('/pulsos/{id}/react', [PulsoController::class, 'react']);
    Route::delete('/pulsos/{id}', [PulsoController::class, 'destroy']);
    Route::get('/challenges', [SocialController::class, 'challenges']);
    Route::post('/challenges/{id}/join', [SocialController::class, 'joinChallenge'])->where('id', '[0-9]+');
    Route::get('/chat', [SocialController::class, 'chatIndex']);
    Route::post('/chat', [SocialController::class, 'chatSend']);
    Route::post('/chat/messages/{messageId}/react', [SocialController::class, 'toggleChatReaction'])->where('messageId', '[0-9]+');
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
Route::prefix('v/client')->middleware(['auth:wellcore', 'plan.lock:strict', 'throttle:api'])->group(function () {
    Route::get('/medals', [MedalController::class, 'index']);
    Route::get('/medals/unlocked', [MedalController::class, 'unlocked']);
});

// SP-4 — Community: Profiles, Follows, Community Notifications, Comments, Preferences
Route::prefix('v')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    // Client profiles & follow/unfollow
    Route::get('/profile/{clientId}', [ProfileController::class, 'show'])->whereNumber('clientId');
    Route::post('/profile/{clientId}/follow', [ProfileController::class, 'follow'])->whereNumber('clientId');
    Route::delete('/profile/{clientId}/follow', [ProfileController::class, 'unfollow'])->whereNumber('clientId');

    // Community notifications (separate from system notifications)
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Per-post comments
    Route::get('/community/posts/{postId}/comments', [SocialController::class, 'commentsList'])->whereNumber('postId');
    Route::post('/community/posts/{postId}/comments', [SocialController::class, 'commentCreate'])->whereNumber('postId')->middleware('throttle:community-write');

    // Client autoshare preferences
    Route::patch('/me/preferences', [MeController::class, 'updatePreferences']);
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
    // Contract endpoints — NOT behind the contract gate (coach must reach these to accept)
    Route::get('/contract/status', [ContractController::class, 'status']);
    Route::post('/contract/accept', [ContractController::class, 'accept']);
    Route::post('/contract/decline', [ContractController::class, 'decline']);

    // Everything else gated by contract acceptance
    Route::middleware('coach.contract')->group(function () {
        Route::get('/dashboard', [CoachController::class, 'dashboard']);
        Route::get('/clients', [CoachController::class, 'clients']);
        Route::get('/kanban', [CoachController::class, 'kanban']);
        Route::post('/kanban/move', [CoachController::class, 'kanbanMove']);
        Route::get('/kanban/detail/{id}', [CoachController::class, 'kanbanClientDetail'])->where('id', '[0-9]+');
        Route::get('/checkins', [CoachController::class, 'checkins']);
        Route::post('/checkins/{id}/reply', [CoachController::class, 'checkinReply'])->where('id', '[0-9]+');

        // Food Photo Review (coach)
        Route::get('/food-photos', [FoodPhotoReviewController::class, 'index']);
        Route::post('/food-photos/{id}/react', [FoodPhotoReviewController::class, 'react'])->where('id', '[0-9]+');
        Route::patch('/food-photos/{id}/note', [FoodPhotoReviewController::class, 'saveNote'])->where('id', '[0-9]+');
        Route::post('/food-photos/{id}/seen', [FoodPhotoReviewController::class, 'markSeen'])->where('id', '[0-9]+');
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

        // Payment Proofs (comprobantes de pago manual)
        Route::middleware('throttle:proof-upload')->group(function () {
            Route::post('/payment-proofs', [PaymentProofController::class, 'store']);
        });
        Route::get('/payment-proofs', [PaymentProofController::class, 'index']);
        Route::get('/payment-proofs/{id}/file', [PaymentProofController::class, 'file'])->whereNumber('id');
        Route::get('/payment-proofs/{id}', [PaymentProofController::class, 'show'])->whereNumber('id');

        // Coach Invitations
        Route::get('/invitations', [InvitationController::class, 'index']);
        Route::post('/invitations', [InvitationController::class, 'store'])->middleware('throttle:coach-inv-create');
        Route::post('/invitations/preview', [InvitationController::class, 'preview']);
        Route::get('/invitations/{id}', [InvitationController::class, 'show'])->whereNumber('id');
        Route::post('/invitations/{id}/resend', [InvitationController::class, 'resend'])->whereNumber('id');
        Route::delete('/invitations/{id}', [InvitationController::class, 'destroy'])->whereNumber('id');

        // Strategy Hub — Marketing Profile (M5)
        Route::get('/marketing-profile', [MarketingProfileController::class, 'show']);
        Route::put('/marketing-profile', [MarketingProfileController::class, 'store']);
        Route::patch('/marketing-profile/draft', [MarketingProfileController::class, 'updateDraft']);

        // Strategy drops — gated by complete brand profile
        Route::middleware('complete-brand-profile')->group(function () {
            Route::get('/strategy/current', [StrategyController::class, 'current']);
            Route::get('/strategy/history', [StrategyController::class, 'history']);
            Route::get('/strategy/drops/{drop}', [StrategyController::class, 'show']);
            Route::post('/strategy/drops/{drop}/pieces/{pieceKey}/publish', [PieceStateController::class, 'publish']);
            Route::post('/strategy/drops/{drop}/pieces/{pieceKey}/skip', [PieceStateController::class, 'skip']);
            Route::post('/strategy/drops/{drop}/pieces/{pieceKey}/in-progress', [PieceStateController::class, 'inProgress']);
            Route::get('/strategy/drops/{drop}/assets/{assetId}', [StrategyAssetController::class, 'show']);
            Route::get('/strategy/drops/{drop}/assets.zip', [StrategyAssetController::class, 'zip']);
        });
    });
});

// Coach impersonation END — accessible by ANY authenticated user (admin or client)
// because while impersonating, the active token's role may be coach/client.
// The controller itself checks for an active chain in session before doing anything.
Route::post('/v/admin/impersonate/end', [AdminImpersonateController::class, 'end'])
    ->middleware(['auth:wellcore', 'throttle:api']);

// Admin (Phase 9 — authenticated admin with admin/superadmin/jefe role)
Route::prefix('v/admin')->middleware(['auth:wellcore', 'throttle:api', 'role:admin,superadmin,jefe'])->group(function () {
    // Coach impersonation (superadmin only)
    Route::post('/coaches/{id}/impersonate', [AdminImpersonateController::class, 'start'])
        ->middleware(['role:superadmin', 'throttle:impersonate'])
        ->whereNumber('id');

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
    Route::post('/settings/test-smtp', [AdminController::class, 'testSmtp']);
    Route::post('/settings/verify-payment-gateway', [AdminController::class, 'verifyPaymentGateway']);
    Route::get('/chat-analytics', [AdminController::class, 'chatAnalytics']);
    Route::post('/ai-generator', [AdminController::class, 'aiGenerator']);
    Route::post('/ai-generator/save', [AdminController::class, 'aiGeneratorSave']);
    // Admin AI Generator v2 (streaming SSE + history + approve)
    Route::post('/ai-generator/stream', [AdminAIGeneratorController::class, 'stream']);
    Route::get('/ai-generator/history', [AdminAIGeneratorController::class, 'history']);
    Route::get('/ai-generator/history/{id}', [AdminAIGeneratorController::class, 'historyDetail'])->whereNumber('id');
    Route::post('/ai-generator/history/{id}/approve', [AdminAIGeneratorController::class, 'approve'])->whereNumber('id');
    Route::post('/ai-generator/history/{id}/discard', [AdminAIGeneratorController::class, 'discard'])->whereNumber('id');
    Route::get('/ai-generator/templates', [AdminAIGeneratorController::class, 'templates']);
    Route::get('/ai-generator/clients/search', [AdminAIGeneratorController::class, 'clientSearch']);
    Route::get('/tickets', [AdminController::class, 'tickets']);
    Route::post('/tickets/{id}/reply', [AdminController::class, 'replyTicket']);
    Route::patch('/tickets/{id}/status', [AdminController::class, 'updateTicketStatus']);
    Route::post('/send-plan-invitation', [AdminController::class, 'sendPlanInvitation']);
    Route::post('/send-gift-invitation', [AdminController::class, 'sendGiftInvitation']);

    // Coach management (full CRUD + reset password)
    Route::get('/coaches/manage', [AdminCoachManagementController::class, 'index']);
    Route::post('/coaches/manage', [AdminCoachManagementController::class, 'store'])->middleware('throttle:coach-create');
    Route::post('/coaches/manage/preview', [AdminCoachManagementController::class, 'preview']);
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

    // Payment Proof Review (admin inbox — Phase 2)
    Route::get('/payment-proofs', [PaymentProofReviewController::class, 'index']);
    Route::get('/payment-proofs/{id}/file', [PaymentProofReviewController::class, 'file'])->whereNumber('id');
    Route::post('/payment-proofs/{id}/approve', [PaymentProofReviewController::class, 'approve'])->whereNumber('id');
    Route::post('/payment-proofs/{id}/reject', [PaymentProofReviewController::class, 'reject'])->whereNumber('id');

    // Marketing drops — queue y review (admin/superadmin only)
    Route::get('/marketing/drops', [QueueController::class, 'index']);
    Route::get('/marketing/drops/{drop}', [DropReviewController::class, 'show']);
    Route::put('/marketing/drops/{drop}/content', [DropReviewController::class, 'updateContent']);
    Route::post('/marketing/drops/{drop}/approve', [DropReviewController::class, 'approve']);
    Route::post('/marketing/drops/{drop}/request-regenerate', [DropReviewController::class, 'requestRegenerate']);
    Route::post('/marketing/drops/{drop}/assets', [DropAssetController::class, 'store']);
    Route::delete('/marketing/drops/{drop}/assets/{assetId}', [DropAssetController::class, 'destroy']);
    Route::get('/coaches/{coach}/marketing-profile', [CoachProfileController::class, 'show'])->whereNumber('coach');
    Route::put('/coaches/{coach}/marketing-profile', [CoachProfileController::class, 'update'])->whereNumber('coach');

    // Forms catalog + responses (CMS read-only)
    Route::get('/forms', [AdminFormsController::class, 'catalog']);
    Route::get('/forms/{area}/{slug}/responses', [AdminFormsController::class, 'responses'])
        ->where(['area' => 'client|public|rise', 'slug' => '[a-z0-9-]+']);
    Route::get('/forms/{area}/{slug}/export', [AdminFormsController::class, 'exportCsv'])
        ->where(['area' => 'client|public|rise', 'slug' => '[a-z0-9-]+']);

    // Campaigns tracker
    Route::get('/campaigns', [AdminCampaignController::class, 'index']);
    Route::get('/campaigns/{id}', [AdminCampaignController::class, 'show'])->whereNumber('id');
    Route::post('/campaigns/{id}/pause', [AdminCampaignController::class, 'pause'])->whereNumber('id');
    Route::post('/campaigns/{id}/resume', [AdminCampaignController::class, 'resume'])->whereNumber('id');
    Route::post('/campaigns/{id}/duplicate', [AdminCampaignController::class, 'duplicate'])->whereNumber('id');
    Route::post('/campaigns/import', [AdminCampaignController::class, 'import']);

    // Referrals
    Route::get('/referrals', [AdminController::class, 'referrals']);
    Route::post('/referrals/{id}/mark-paid', [AdminController::class, 'markReferralPaid'])->whereNumber('id');
    Route::post('/referrals/{id}/expire', [AdminController::class, 'expireReferral'])->whereNumber('id');

    // Admin Tools — break-glass utilities
    Route::get('/tools', [AdminToolsController::class, 'catalog']);
    Route::get('/tools/history', [AdminToolsController::class, 'history']);
    Route::post('/tools/{id}/run', [AdminToolsController::class, 'run'])->where('id', '[\w\-]+');
});

// ───────────────────────────────────────────────────────────────────────────
// Community Cross-Role (Fase A) — Coach / Admin / Client community endpoints
// ───────────────────────────────────────────────────────────────────────────

// Coach community feed + pulse + announce (auth-only — role check in controllers)
Route::prefix('v/coach/community')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::get('pulse', [CommunityController::class, 'pulse']);
    Route::get('posts', [CommunityController::class, 'posts']);
    Route::get('pulsos', [CommunityController::class, 'pulsos']);
    Route::get('threads', [CommunityController::class, 'threads']);
    Route::get('achievements', [CommunityController::class, 'achievements']);
    Route::post('announce', [CommunityController::class, 'announce']);
});

// Coach Fase B — clients count + push subscriptions + notifications preferences
Route::prefix('v/coach')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::get('clients/count', [App\Http\Controllers\Api\Coach\ClientsController::class, 'count']);
    Route::post('push/subscribe', [App\Http\Controllers\Api\Coach\PushSubscriptionController::class, 'subscribe']);
    Route::delete('push/subscribe/{id}', [App\Http\Controllers\Api\Coach\PushSubscriptionController::class, 'unsubscribe'])->whereNumber('id');
    Route::get('notifications/preferences', [App\Http\Controllers\Api\Coach\PushSubscriptionController::class, 'preferences']);
    Route::patch('notifications/preferences', [App\Http\Controllers\Api\Coach\PushSubscriptionController::class, 'updatePreferences']);
});

// Coach moderation (pin/unpin/delete/make-official) — policy enforced in controller
Route::prefix('v/coach/posts')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::post('{post}/pin', [ModerationController::class, 'pin'])->whereNumber('post');
    Route::post('{post}/unpin', [ModerationController::class, 'unpin'])->whereNumber('post');
    Route::post('{post}/make-official', [ModerationController::class, 'makeOfficial'])->whereNumber('post');
    Route::delete('{post}', [ModerationController::class, 'delete'])->whereNumber('post');
});

// Admin community analytics + Fase C extensions
Route::prefix('v/admin/community')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::get('pulse-cross-coach', [App\Http\Controllers\Api\Admin\CommunityController::class, 'pulseCrossCoach']);
    Route::get('coaches/{coachId}/analytics', [App\Http\Controllers\Api\Admin\CoachAnalyticsController::class, 'show'])->whereNumber('coachId');
    Route::post('posts/{postId}/pin', [App\Http\Controllers\Api\Admin\CommunityController::class, 'pinAdminOverride'])->whereNumber('postId');
    Route::post('posts/{postId}/make-global', [App\Http\Controllers\Api\Admin\CommunityController::class, 'makeGlobal'])->whereNumber('postId');
});

// Admin notifications preferences
Route::prefix('v/admin')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::get('notifications/preferences', [App\Http\Controllers\Api\Admin\NotificationPreferencesController::class, 'show']);
    Route::patch('notifications/preferences', [App\Http\Controllers\Api\Admin\NotificationPreferencesController::class, 'update']);
});

// Admin moderation queue
Route::prefix('v/admin/community/moderation')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::get('queue', [ModerationQueueController::class, 'index']);
    Route::post('{report}/dismiss', [ModerationQueueController::class, 'dismiss'])->whereNumber('report');
    Route::post('{report}/action', [ModerationQueueController::class, 'action'])->whereNumber('report');
});

// Admin broadcast
Route::prefix('v/admin/broadcast')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::post('preview', [BroadcastController::class, 'preview']);
    Route::post('send', [BroadcastController::class, 'send']);
    Route::get('history', [BroadcastController::class, 'history']);
});

// Client post reports
Route::prefix('v/community')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
    Route::post('posts/{post}/report', [PostReportController::class, 'store'])->whereNumber('post');
});
