<?php

namespace App\Livewire\Admin;

use App\Models\BiometricLog;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\CoachMessage;
use App\Models\CommunityPost;
use App\Models\HabitLog;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\ProgressPhoto;
use App\Models\TrainingLog;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'Live Feed'])]
class LiveFeed extends Component
{
    public string $typeFilter = 'all';
    public string $dateFilter = 'today';

    public array $feed = [];

    // Stats
    public int $eventsToday = 0;
    public int $inscriptionsToday = 0;
    public int $paymentsToday = 0;
    public int $activeConversations = 0;
    public int $actionsToday = 0;

    public function mount(): void
    {
        $this->loadFeed();
        $this->loadStats();
    }

    public function updatedTypeFilter(): void
    {
        $this->loadFeed();
    }

    public function updatedDateFilter(): void
    {
        $this->loadFeed();
        $this->loadStats();
    }

    public function loadFeed(): void
    {
        $dateFrom = $this->getDateFrom();
        $items = collect();

        // Inscriptions
        if ($this->typeFilter === 'all' || $this->typeFilter === 'inscriptions') {
            $inscriptions = Inscription::when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($i) => [
                    'type' => 'inscription',
                    'icon' => 'clipboard-document-check',
                    'color' => 'sky',
                    'title' => 'Nueva inscripcion',
                    'description' => trim(($i->nombre ?? '') . ' ' . ($i->apellido ?? '')) . ' — ' . ($i->plan?->label() ?? 'Sin plan') . ' — ' . ($i->email ?? ''),
                    'timestamp' => $i->created_at,
                    'time_ago' => $i->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($inscriptions);
        }

        // Payments
        if ($this->typeFilter === 'all' || $this->typeFilter === 'payments') {
            $payments = Payment::when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($p) => [
                    'type' => 'payment',
                    'icon' => 'banknotes',
                    'color' => 'emerald',
                    'title' => 'Pago recibido',
                    'description' => ($p->buyer_name ?? $p->email ?? 'Desconocido') . ' — $' . number_format((float) $p->amount, 0, ',', '.') . ' COP — ' . ($p->plan?->label() ?? '-') . ' — ' . ($p->status?->label() ?? $p->status ?? '-'),
                    'timestamp' => $p->created_at,
                    'time_ago' => $p->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($payments);
        }

        // Check-ins
        if ($this->typeFilter === 'all' || $this->typeFilter === 'checkins') {
            $checkins = Checkin::with('client')
                ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($c) => [
                    'type' => 'checkin',
                    'icon' => 'clipboard-document-list',
                    'color' => 'orange',
                    'title' => 'Check-in enviado',
                    'description' => ($c->client?->name ?? 'Cliente #' . $c->client_id) . ' — ' . ($c->week_label ?? '') . ' — Bienestar: ' . ($c->bienestar ?? '-') . '/10',
                    'timestamp' => $c->created_at,
                    'time_ago' => $c->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($checkins);
        }

        // Coach Messages
        if ($this->typeFilter === 'all' || $this->typeFilter === 'messages') {
            $messages = CoachMessage::with(['client', 'coach'])
                ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($m) => [
                    'type' => 'message',
                    'icon' => 'chat-bubble-left-right',
                    'color' => 'violet',
                    'title' => 'Nuevo mensaje',
                    'description' => ($m->direction === 'coach_to_client'
                        ? ('Coach ' . ($m->coach?->name ?? '#' . $m->coach_id) . ' → ' . ($m->client?->name ?? 'Cliente'))
                        : (($m->client?->name ?? 'Cliente') . ' → Coach')) . ' — ' . Str::limit($m->message ?? '', 60),
                    'timestamp' => $m->created_at,
                    'time_ago' => $m->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($messages);
        }

        // New Clients
        if ($this->typeFilter === 'all') {
            $newClients = Client::when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($c) => [
                    'type' => 'new_client',
                    'icon' => 'user-plus',
                    'color' => 'red',
                    'title' => 'Nuevo cliente',
                    'description' => ($c->name ?? 'Sin nombre') . ' — ' . ($c->plan?->label() ?? 'Sin plan'),
                    'timestamp' => $c->created_at,
                    'time_ago' => $c->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($newClients);
        }

        // Community Posts
        if ($this->typeFilter === 'all' || $this->typeFilter === 'community') {
            $posts = CommunityPost::with('client')
                ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($p) => [
                    'type' => 'community',
                    'icon' => 'chat-bubble-bottom-center-text',
                    'color' => 'pink',
                    'title' => 'Post comunidad',
                    'description' => ($p->client?->name ?? 'Cliente') . ' — ' . ($p->post_type ?? 'post') . ' — ' . Str::limit($p->content ?? '', 60),
                    'timestamp' => $p->created_at,
                    'time_ago' => $p->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($posts);
        }

        // Training Logs (uses log_date, no created_at)
        if ($this->typeFilter === 'all' || $this->typeFilter === 'training') {
            $training = TrainingLog::with('client')
                ->when($dateFrom, fn ($q) => $q->where('log_date', '>=', $dateFrom->toDateString()))
                ->latest('log_date')
                ->limit(50)
                ->get()
                ->map(fn ($t) => [
                    'type' => 'training',
                    'icon' => 'fire',
                    'color' => 'yellow',
                    'title' => 'Entrenamiento',
                    'description' => ($t->client?->name ?? 'Cliente #' . $t->client_id) . ' — ' . ($t->completed ? 'Completado' : 'Registrado') . ' — Semana ' . ($t->week_num ?? '-'),
                    'timestamp' => $t->log_date?->startOfDay(),
                    'time_ago' => $t->log_date?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($training);
        }

        // Progress Photos
        if ($this->typeFilter === 'all' || $this->typeFilter === 'photos') {
            $photos = ProgressPhoto::with('client')
                ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($p) => [
                    'type' => 'photo',
                    'icon' => 'camera',
                    'color' => 'cyan',
                    'title' => 'Foto progreso',
                    'description' => ($p->client?->name ?? 'Cliente #' . $p->client_id) . ' — ' . ucfirst($p->tipo ?? 'foto'),
                    'timestamp' => $p->created_at,
                    'time_ago' => $p->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($photos);
        }

        // Habit Logs
        if ($this->typeFilter === 'all' || $this->typeFilter === 'habits') {
            $habits = HabitLog::with('client')
                ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($h) => [
                    'type' => 'habit',
                    'icon' => 'check-circle',
                    'color' => 'amber',
                    'title' => 'Habito registrado',
                    'description' => ($h->client?->name ?? 'Cliente #' . $h->client_id) . ' — ' . ucfirst($h->habit_type ?? 'habito'),
                    'timestamp' => $h->created_at,
                    'time_ago' => $h->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($habits);
        }

        // Biometric Logs
        if ($this->typeFilter === 'all' || $this->typeFilter === 'biometrics') {
            $biometrics = BiometricLog::with('client')
                ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($b) => [
                    'type' => 'biometric',
                    'icon' => 'scale',
                    'color' => 'teal',
                    'title' => 'Metrica corporal',
                    'description' => ($b->client?->name ?? 'Cliente #' . $b->client_id)
                        . ($b->weight_kg ? ' — ' . $b->weight_kg . 'kg' : '')
                        . ($b->body_fat_pct ? ' — ' . $b->body_fat_pct . '% grasa' : '')
                        . ($b->sleep_hours ? ' — ' . $b->sleep_hours . 'h sueño' : ''),
                    'timestamp' => $b->created_at,
                    'time_ago' => $b->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($biometrics);
        }

        // Sort by timestamp descending and take 50
        $this->feed = $items
            ->sortByDesc('timestamp')
            ->take(50)
            ->values()
            ->toArray();
    }

    protected function loadStats(): void
    {
        $today = Carbon::today();

        $inscriptionsCount = Inscription::where('created_at', '>=', $today)->count();
        $paymentsCount = Payment::where('created_at', '>=', $today)->count();
        $checkinsCount = Checkin::where('created_at', '>=', $today)->count();
        $messagesCount = CoachMessage::where('created_at', '>=', $today)->count();
        $newClientsCount = Client::where('created_at', '>=', $today)->count();
        $communityCount = CommunityPost::where('created_at', '>=', $today)->count();
        $trainingCount = TrainingLog::where('log_date', '>=', $today)->count();
        $photosCount = ProgressPhoto::where('created_at', '>=', $today)->count();
        $habitsCount = HabitLog::where('created_at', '>=', $today)->count();
        $biometricsCount = BiometricLog::where('created_at', '>=', $today)->count();

        $this->eventsToday = $inscriptionsCount + $paymentsCount + $checkinsCount + $messagesCount + $newClientsCount + $communityCount + $trainingCount + $photosCount + $habitsCount + $biometricsCount;
        $this->inscriptionsToday = $inscriptionsCount;
        $this->paymentsToday = $paymentsCount;
        $this->actionsToday = $trainingCount + $checkinsCount + $habitsCount + $photosCount + $biometricsCount;
        $this->activeConversations = CoachMessage::where('created_at', '>=', $today)
            ->distinct('client_id')
            ->count('client_id');
    }

    protected function getDateFrom(): ?Carbon
    {
        // Use Colombia timezone (UTC-5) for date filters since clients are in LATAM
        $tz = 'America/Bogota';
        return match ($this->dateFilter) {
            'today' => Carbon::today($tz)->utc(),
            'week' => Carbon::now($tz)->subWeek()->startOfDay()->utc(),
            'month' => Carbon::now($tz)->subMonth()->startOfDay()->utc(),
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.admin.live-feed');
    }
}
