<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminPlanTicketController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\CoachPlanTicketController;
use App\Http\Controllers\Api\EjerciciosController;
use App\Http\Controllers\Api\NutritionController;
use App\Http\Controllers\Api\PublicFormController;
use App\Http\Controllers\Api\RiseController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\TrainingController;
use App\Services\ExerciseMediaService;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Temp migrate
Route::get('/run-migrate-k7x9', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return response(\Illuminate\Support\Facades\Artisan::output())->header('Content-Type', 'text/plain');
});

// Temporary GIF debug route — remove after diagnosis
Route::get('/debug-gif', function () {
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    // Get Silvia's actual training plan (client_id=54)
    $plan = DB::table('assigned_plans')
        ->where('client_id', 54)->where('active', true)->where('plan_type', 'entrenamiento')
        ->first();
    if (! $plan) {
        return response()->json(['error' => 'no plan']);
    }
    $content = is_array($plan->content) ? $plan->content : json_decode($plan->content, true);

    // Simulate normalizeTrainingPlan (inline)
    $ctrl = app(TrainingController::class);
    $reflection = new ReflectionClass($ctrl);
    $method = $reflection->getMethod('normalizeTrainingPlan');
    $method->setAccessible(true);
    $trainingPlan = $method->invoke($ctrl, $content);

    $mediaService = app(ExerciseMediaService::class);
    $errors = [];
    $gifsBefore = 0;
    $gifsAfter = 0;

    if (isset($trainingPlan['semanas'])) {
        foreach ($trainingPlan['semanas'] as $sIdx => $semana) {
            foreach ($semana['dias'] as $dIdx => $dia) {
                $ejercicios = $dia['ejercicios'] ?? [];
                $gifsBefore += count($ejercicios);
                if (! empty($ejercicios)) {
                    try {
                        $mediaService->enrichWithMedia($ejercicios);
                        $trainingPlan['semanas'][$sIdx]['dias'][$dIdx]['ejercicios'] = $ejercicios;
                        $gifsAfter += count(array_filter($ejercicios, fn ($e) => ! empty($e['gif_url'])));
                    } catch (Throwable $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }
    }

    $firstEx = $trainingPlan['semanas'][0]['dias'][0]['ejercicios'][0] ?? null;

    return response()->json([
        'total_exercises' => $gifsBefore,
        'with_gif' => $gifsAfter,
        'errors' => $errors,
        'first_exercise' => $firstEx,
        'semanas_count' => count($trainingPlan['semanas'] ?? []),
    ]);
});

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
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
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
Route::prefix('v/client')->middleware('throttle:api')->group(function () {
    Route::get('/account-status', [ClientController::class, 'accountStatus']);
    Route::get('/dashboard', [ClientController::class, 'dashboard']);
    Route::get('/metrics', [ClientController::class, 'metrics']);
    Route::post('/metrics', [ClientController::class, 'storeMetric']);
    Route::get('/profile', [ClientController::class, 'profile']);
    Route::put('/profile', [ClientController::class, 'updateProfile']);
    Route::get('/settings', [ClientController::class, 'settings']);
    Route::put('/settings', [ClientController::class, 'updateSettings']);
    Route::put('/settings/password', [ClientController::class, 'changePassword']);
    Route::post('/onboarding/complete', [ClientController::class, 'completeOnboarding']);
    Route::get('/coach-feedback', [ClientController::class, 'coachFeedback']);
    Route::post('/coach-feedback', [ClientController::class, 'submitCoachFeedback']);
    Route::get('/notifications', [ClientController::class, 'notifications']);
    Route::post('/notifications/read-all', [ClientController::class, 'markAllRead']);
    Route::get('/tickets', [ClientController::class, 'tickets']);
    Route::post('/tickets', [ClientController::class, 'createTicket']);
    Route::post('/notifications/{id}/read', [ClientController::class, 'markRead']);
});

// Training (Phase 5)
Route::prefix('v/client')->middleware('throttle:api')->group(function () {
    Route::get('/plan', [TrainingController::class, 'plan']);
    Route::get('/training', [TrainingController::class, 'training']);
    Route::post('/training/toggle', [TrainingController::class, 'toggleTrainingDay']);
    Route::get('/workout/{day?}', [TrainingController::class, 'workout'])->where('day', '[0-9]+');
    Route::post('/workout/start', [TrainingController::class, 'startWorkout']);
    Route::post('/workout/complete-set', [TrainingController::class, 'completeSet']);
    Route::post('/workout/finish', [TrainingController::class, 'finishWorkout']);
    Route::get('/workout-summary/{sessionId}', [TrainingController::class, 'workoutSummary'])->where('sessionId', '[0-9]+|latest');
    Route::post('/workout-summary/{sessionId}/feeling', [TrainingController::class, 'saveWorkoutFeeling'])->where('sessionId', '[0-9]+|latest');
    Route::get('/checkin', [TrainingController::class, 'checkin']);
    Route::post('/checkin', [TrainingController::class, 'submitCheckin']);

    // Nutrition — Recipe Meal Swaps & AI Estimation
    Route::get('/nutrition/macros-today', [NutritionController::class, 'macrosToday']);
    Route::post('/nutrition/swap', [NutritionController::class, 'createSwap']);
    Route::delete('/nutrition/swap/{id}', [NutritionController::class, 'deleteSwap'])->where('id', '[0-9]+');
    Route::post('/ai-nutrition/estimate', [NutritionController::class, 'estimateFood']);
});

// Social & Resources (Phase 6)
Route::prefix('v/client')->middleware('throttle:api')->group(function () {
    Route::get('/community', [SocialController::class, 'communityIndex']);
    Route::post('/community', [SocialController::class, 'communityCreate']);
    Route::post('/community/{id}/react', [SocialController::class, 'communityReact'])->where('id', '[0-9]+');
    Route::post('/community/{id}/comment', [SocialController::class, 'communityComment'])->where('id', '[0-9]+');
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
    Route::post('/referrals/invite', [SocialController::class, 'sendReferralInvite']);
    Route::get('/supplements', [SocialController::class, 'supplements']);
    Route::post('/supplements/toggle', [SocialController::class, 'toggleSupplement']);
    Route::get('/photos', [SocialController::class, 'photos']);
    Route::post('/photos', [SocialController::class, 'uploadPhoto']);
    Route::delete('/photos/{id}', [SocialController::class, 'deletePhoto'])->where('id', '[0-9]+');
    Route::get('/records', [SocialController::class, 'records']);
    Route::get('/ai-nutrition', [SocialController::class, 'aiNutritionHistory']);
    Route::post('/ai-nutrition/analyze', [SocialController::class, 'aiNutritionAnalyze']);
    Route::get('/videos', [SocialController::class, 'videos']);
    Route::get('/academia', [SocialController::class, 'academia']);
    Route::get('/video-checkins', [SocialController::class, 'videoCheckinHistory']);
    Route::post('/video-checkin', [SocialController::class, 'videoCheckinSubmit']);
});

// Rise (Phase 7 — authenticated client, Bearer token)
Route::prefix('v/rise')->middleware('throttle:api')->group(function () {
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
Route::prefix('v/coach')->middleware('throttle:api')->group(function () {
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
    Route::post('/plans', [CoachController::class, 'createPlan']);
    Route::put('/plans/{id}', [CoachController::class, 'updatePlan'])->where('id', '[0-9]+');
    Route::post('/plans/generate', [CoachController::class, 'generatePlan']);
    Route::get('/analytics', [CoachController::class, 'analytics']);
    Route::get('/profile', [CoachController::class, 'profile']);
    Route::put('/profile', [CoachController::class, 'updateProfile']);
    Route::get('/notes', [CoachController::class, 'notes']);
    Route::post('/notes', [CoachController::class, 'createNote']);
    Route::put('/notes/{id}', [CoachController::class, 'updateNote'])->where('id', '[0-9]+');
    Route::delete('/notes/{id}', [CoachController::class, 'deleteNote'])->where('id', '[0-9]+');
    Route::get('/brand', [CoachController::class, 'brand']);
    Route::put('/brand', [CoachController::class, 'updateBrand']);
    Route::get('/features', [CoachController::class, 'features']);
    Route::get('/resources', [CoachController::class, 'resources']);

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
Route::prefix('v/admin')->middleware('throttle:api')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/feed', [AdminController::class, 'feed']);
    Route::get('/clients', [AdminController::class, 'clients']);
    Route::get('/clients/{id}', [AdminController::class, 'clientDetail'])->where('id', '[0-9]+');
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
