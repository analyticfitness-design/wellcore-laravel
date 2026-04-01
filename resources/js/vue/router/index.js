import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Auth pages (eagerly loaded — most common entry point)
import Login from '../pages/Auth/Login.vue';
import ForgotPassword from '../pages/Auth/ForgotPassword.vue';
import ResetPassword from '../pages/Auth/ResetPassword.vue';

const routes = [
    // Auth (guest only)
    { path: '/login', name: 'login', component: Login, meta: { guest: true, title: 'Iniciar Sesion — WellCore' } },
    { path: '/forgot-password', name: 'forgot-password', component: ForgotPassword, meta: { guest: true, title: 'Recuperar Contrasena — WellCore' } },
    { path: '/reset-password/:token', name: 'reset-password', component: ResetPassword, meta: { guest: true, title: 'Nueva Contrasena — WellCore' } },

    // Public (lazy loaded)
    { path: '/inscripcion', name: 'inscripcion', component: () => import('../pages/Public/InscriptionForm.vue'), meta: { title: 'Inscripcion — WellCore' } },
    { path: '/coaches/apply', name: 'coach-apply', component: () => import('../pages/Public/CoachApplication.vue'), meta: { title: 'Aplicar como Coach — WellCore' } },
    { path: '/rise-enroll', name: 'rise-enroll', component: () => import('../pages/Public/RiseEnrollment.vue'), meta: { title: 'RISE Enrollment — WellCore' } },
    { path: '/presencial/inscripcion', name: 'presencial-form', component: () => import('../pages/Public/PresencialForm.vue'), meta: { title: 'Presencial — WellCore' } },

    // Shop (lazy loaded)
    { path: '/tienda', name: 'shop-catalog', component: () => import('../pages/Shop/ProductCatalog.vue'), meta: { title: 'Tienda — WellCore' } },
    { path: '/tienda/:slug', name: 'shop-product', component: () => import('../pages/Shop/ProductDetail.vue'), meta: { title: 'Producto — WellCore' } },

    // Client (lazy loaded, auth required)
    { path: '/client', name: 'client-dashboard', component: () => import('../pages/Client/Dashboard.vue'), meta: { auth: true, title: 'Dashboard — WellCore' } },
    { path: '/client/metrics', name: 'client-metrics', component: () => import('../pages/Client/MetricsTracker.vue'), meta: { auth: true, title: 'Metricas — WellCore' } },
    { path: '/client/profile', name: 'client-profile', component: () => import('../pages/Client/ProfileEditor.vue'), meta: { auth: true, title: 'Perfil — WellCore' } },
    { path: '/client/settings', name: 'client-settings', component: () => import('../pages/Client/ClientSettings.vue'), meta: { auth: true, title: 'Configuracion — WellCore' } },
    { path: '/client/plan', name: 'client-plan', component: () => import('../pages/Client/PlanViewer.vue'), meta: { auth: true, title: 'Mi Plan — WellCore' } },
    { path: '/client/workout/:day?', name: 'client-workout', component: () => import('../pages/Client/WorkoutPlayer.vue'), meta: { auth: true, title: 'Entrenamiento — WellCore' } },
    { path: '/client/timer', name: 'client-timer', component: () => import('../pages/Client/WorkoutTimer.vue'), meta: { auth: true, title: 'Timer — WellCore' } },
    { path: '/client/training', name: 'client-training', component: () => import('../pages/Client/TrainingView.vue'), meta: { auth: true, title: 'Calendario — WellCore' } },
    { path: '/client/workout-summary/:sessionId', name: 'client-workout-summary', component: () => import('../pages/Client/WorkoutSummary.vue'), meta: { auth: true, title: 'Resumen — WellCore' } },
    { path: '/client/checkin', name: 'client-checkin', component: () => import('../pages/Client/CheckinForm.vue'), meta: { auth: true, title: 'Check-in — WellCore' } },
    { path: '/client/community', name: 'client-community', component: () => import('../pages/Client/CommunityFeed.vue'), meta: { auth: true, title: 'Comunidad — WellCore' } },
    { path: '/client/challenges', name: 'client-challenges', component: () => import('../pages/Client/ChallengesView.vue'), meta: { auth: true, title: 'Retos — WellCore' } },
    { path: '/client/chat', name: 'client-chat', component: () => import('../pages/Client/ChatWidget.vue'), meta: { auth: true, title: 'Chat — WellCore' } },
    { path: '/client/nutrition', name: 'client-nutrition', component: () => import('../pages/Client/NutritionPlan.vue'), meta: { auth: true, title: 'Nutricion — WellCore' } },
    { path: '/client/habits', name: 'client-habits', component: () => import('../pages/Client/HabitTracker.vue'), meta: { auth: true, title: 'Habitos — WellCore' } },
    { path: '/client/referrals', name: 'client-referrals', component: () => import('../pages/Client/ReferralProgram.vue'), meta: { auth: true, title: 'Referidos — WellCore' } },
    { path: '/client/supplements', name: 'client-supplements', component: () => import('../pages/Client/SupplementTracker.vue'), meta: { auth: true, title: 'Suplementacion — WellCore' } },
    { path: '/client/photos', name: 'client-photos', component: () => import('../pages/Client/ProgressPhotos.vue'), meta: { auth: true, title: 'Fotos — WellCore' } },
    { path: '/client/records', name: 'client-records', component: () => import('../pages/Client/PersonalRecords.vue'), meta: { auth: true, title: 'Personal Records — WellCore' } },
    { path: '/client/ai-nutrition', name: 'client-ai-nutrition', component: () => import('../pages/Client/AINutrition.vue'), meta: { auth: true, title: 'Analisis IA — WellCore' } },
    { path: '/client/recipes', name: 'client-recipes', component: () => import('../pages/Client/RecipeDatabase.vue'), meta: { auth: true, title: 'Recetas — WellCore' } },
    { path: '/client/audio', name: 'client-audio', component: () => import('../pages/Client/AudioPlayer.vue'), meta: { auth: true, title: 'Audio Coaching — WellCore' } },
    { path: '/client/videos', name: 'client-videos', component: () => import('../pages/Client/VideoLibrary.vue'), meta: { auth: true, title: 'Videos — WellCore' } },
    { path: '/client/academia', name: 'client-academia', component: () => import('../pages/Client/Academia.vue'), meta: { auth: true, title: 'Academia — WellCore' } },
    { path: '/client/mindfulness', name: 'client-mindfulness', component: () => import('../pages/Client/Mindfulness.vue'), meta: { auth: true, title: 'Mindfulness — WellCore' } },
    { path: '/client/coach-feedback', name: 'client-coach-feedback', component: () => import('../pages/Client/CoachFeedback.vue'), meta: { auth: true, title: 'Coach Feedback — WellCore' } },
    { path: '/client/tickets', name: 'client-tickets', component: () => import('../pages/Client/TicketSupport.vue'), meta: { auth: true, title: 'Soporte — WellCore' } },
    { path: '/client/video-checkin', name: 'client-video-checkin', component: () => import('../pages/Client/VideoCheckinUpload.vue'), meta: { auth: true, title: 'Video Check-in — WellCore' } },
    { path: '/client/evidence-hacks', name: 'client-evidence-hacks', component: () => import('../pages/Client/EvidenceHacks.vue'), meta: { auth: true, title: 'Evidence Hacks — WellCore' } },

    // Rise (lazy loaded, auth required)
    { path: '/rise', name: 'rise-dashboard', component: () => import('../pages/Rise/Dashboard.vue'), meta: { auth: true, title: 'Dashboard RISE — WellCore' } },
    { path: '/rise/program', name: 'rise-program', component: () => import('../pages/Rise/ProgramView.vue'), meta: { auth: true, title: 'Mi Programa — WellCore RISE' } },
    { path: '/rise/tracking', name: 'rise-tracking', component: () => import('../pages/Rise/DailyTracking.vue'), meta: { auth: true, title: 'Tracking Diario — WellCore RISE' } },
    { path: '/rise/habits', name: 'rise-habits', component: () => import('../pages/Rise/Habits.vue'), meta: { auth: true, title: 'Habitos — WellCore RISE' } },
    { path: '/rise/measurements', name: 'rise-measurements', component: () => import('../pages/Rise/Measurements.vue'), meta: { auth: true, title: 'Mediciones — WellCore RISE' } },
    { path: '/rise/photos', name: 'rise-photos', component: () => import('../pages/Rise/Photos.vue'), meta: { auth: true, title: 'Fotos — WellCore RISE' } },
    { path: '/rise/chat', name: 'rise-chat', component: () => import('../pages/Rise/Chat.vue'), meta: { auth: true, title: 'Chat — WellCore RISE' } },
    { path: '/rise/workout/:day?', name: 'rise-workout', component: () => import('../pages/Rise/WorkoutPlayer.vue'), meta: { auth: true, title: 'Entrenamiento — WellCore RISE' } },
    { path: '/rise/workout-summary/:sessionId', name: 'rise-workout-summary', component: () => import('../pages/Rise/WorkoutSummary.vue'), meta: { auth: true, title: 'Resumen — WellCore RISE' } },
    { path: '/rise/profile', name: 'rise-profile', component: () => import('../pages/Rise/RiseProfile.vue'), meta: { auth: true, title: 'Perfil — WellCore RISE' } },

    // Coach (lazy loaded, auth required)
    { path: '/coach', name: 'coach-dashboard', component: () => import('../pages/Coach/Dashboard.vue'), meta: { auth: true, title: 'Dashboard Coach — WellCore' } },
    { path: '/coach/clients', name: 'coach-clients', component: () => import('../pages/Coach/ClientList.vue'), meta: { auth: true, title: 'Mis Clientes — WellCore' } },
    { path: '/coach/kanban', name: 'coach-kanban', component: () => import('../pages/Coach/ClientKanban.vue'), meta: { auth: true, title: 'Kanban Clientes — WellCore' } },
    { path: '/coach/checkins', name: 'coach-checkins', component: () => import('../pages/Coach/CheckinReview.vue'), meta: { auth: true, title: 'Check-ins — WellCore' } },
    { path: '/coach/messages', name: 'coach-messages', component: () => import('../pages/Coach/MessageCenter.vue'), meta: { auth: true, title: 'Mensajes — WellCore' } },
    { path: '/coach/broadcast', name: 'coach-broadcast', component: () => import('../pages/Coach/BroadcastCenter.vue'), meta: { auth: true, title: 'Broadcast — WellCore' } },
    { path: '/coach/plans', name: 'coach-plans', component: () => import('../pages/Coach/PlansManager.vue'), meta: { auth: true, title: 'Planes — WellCore' } },
    { path: '/coach/analytics', name: 'coach-analytics', component: () => import('../pages/Coach/Analytics.vue'), meta: { auth: true, title: 'Analytics — WellCore' } },
    { path: '/coach/profile', name: 'coach-profile', component: () => import('../pages/Coach/CoachProfile.vue'), meta: { auth: true, title: 'Perfil Coach — WellCore' } },
    { path: '/coach/notes', name: 'coach-notes', component: () => import('../pages/Coach/Notes.vue'), meta: { auth: true, title: 'Notas — WellCore' } },
    { path: '/coach/brand', name: 'coach-brand', component: () => import('../pages/Coach/MyBrand.vue'), meta: { auth: true, title: 'Mi Marca — WellCore' } },
    { path: '/coach/features', name: 'coach-features', component: () => import('../pages/Coach/Features.vue'), meta: { auth: true, title: 'Herramientas — WellCore' } },
    { path: '/coach/resources', name: 'coach-resources', component: () => import('../pages/Coach/Resources.vue'), meta: { auth: true, title: 'Recursos — WellCore' } },

    // Admin (lazy loaded, auth required)
    { path: '/admin', name: 'admin-dashboard', component: () => import('../pages/Admin/Dashboard.vue'), meta: { auth: true, title: 'Admin Dashboard — WellCore' } },
    { path: '/admin/feed', name: 'admin-feed', component: () => import('../pages/Admin/LiveFeed.vue'), meta: { auth: true, title: 'Live Feed — WellCore Admin' } },
    { path: '/admin/clients', name: 'admin-clients', component: () => import('../pages/Admin/ClientTable.vue'), meta: { auth: true, title: 'Clientes — WellCore Admin' } },
    { path: '/admin/clients/:id', name: 'admin-client-detail', component: () => import('../pages/Admin/ClientDetail.vue'), meta: { auth: true, title: 'Detalle Cliente — WellCore Admin' } },
    { path: '/admin/payments', name: 'admin-payments', component: () => import('../pages/Admin/PaymentsDashboard.vue'), meta: { auth: true, title: 'Pagos — WellCore Admin' } },
    { path: '/admin/inscriptions', name: 'admin-inscriptions', component: () => import('../pages/Admin/InscriptionsList.vue'), meta: { auth: true, title: 'Inscripciones — WellCore Admin' } },
    { path: '/admin/invitations', name: 'admin-invitations', component: () => import('../pages/Admin/InvitationManager.vue'), meta: { auth: true, title: 'Invitaciones — WellCore Admin' } },
    { path: '/admin/coaches', name: 'admin-coaches', component: () => import('../pages/Admin/CoachManagement.vue'), meta: { auth: true, title: 'Coaches — WellCore Admin' } },
    { path: '/admin/plans', name: 'admin-plans', component: () => import('../pages/Admin/PlanManagement.vue'), meta: { auth: true, title: 'Planes — WellCore Admin' } },
    { path: '/admin/ai-generator', name: 'admin-ai-generator', component: () => import('../pages/Admin/AIPlanGenerator.vue'), meta: { auth: true, title: 'Generador IA — WellCore Admin' } },
    { path: '/admin/rise', name: 'admin-rise', component: () => import('../pages/Admin/RiseManagement.vue'), meta: { auth: true, title: 'RISE — WellCore Admin' } },
    { path: '/admin/settings', name: 'admin-settings', component: () => import('../pages/Admin/AdminSettings.vue'), meta: { auth: true, title: 'Configuracion — WellCore Admin' } },
    { path: '/admin/tools', name: 'admin-tools', component: () => import('../pages/Admin/AdminTools.vue'), meta: { auth: true, title: 'Herramientas — WellCore Admin' } },
    { path: '/admin/chat-analytics', name: 'admin-chat-analytics', component: () => import('../pages/Admin/ChatAnalytics.vue'), meta: { auth: true, title: 'Chat Analytics — WellCore Admin' } },
    { path: '/admin/tickets', name: 'admin-tickets', component: () => import('../pages/Admin/TicketManager.vue'), meta: { auth: true, title: 'Tickets — WellCore Admin' } },
    { path: '/admin/referrals', name: 'admin-referrals', component: () => import('../pages/Admin/ReferralRewards.vue'), meta: { auth: true, title: 'Referidos — WellCore Admin' } },
    { path: '/admin/campaigns', name: 'admin-campaigns', component: () => import('../pages/Admin/CampaignTracker.vue'), meta: { auth: true, title: 'Campanas — WellCore Admin' } },
    { path: '/admin/send-invitation', name: 'admin-send-invitation', component: () => import('../pages/Admin/SendPlanInvitation.vue'), meta: { auth: true, title: 'Enviar Invitacion — WellCore Admin' } },

    // Catch-all: redirect unknown routes to login
    { path: '/:pathMatch(.*)*', redirect: '/login' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guards
router.beforeEach((to, from, next) => {
    // Set page title
    document.title = to.meta.title || 'WellCore Fitness';

    const authStore = useAuthStore();

    // Guest-only routes: redirect to the correct dashboard if already logged in
    if (to.meta.guest && authStore.isAuthenticated) {
        const dashboards = { admin: '/admin', coach: '/coach', rise: '/rise', client: '/client' };
        return next(dashboards[authStore.userType] ?? '/client');
    }

    // Auth-required routes: redirect to login if not logged in
    if (to.meta.auth && !authStore.isAuthenticated) {
        return next('/login');
    }

    next();
});

export default router;
