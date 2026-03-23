<?php

namespace App\Livewire\Admin;

use App\Models\Checkin;
use App\Models\Client;
use App\Models\CoachMessage;
use App\Models\CommunityPost;
use App\Models\Inscription;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
                    'metadata' => [
                        'nombre' => trim(($i->nombre ?? '') . ' ' . ($i->apellido ?? '')),
                        'plan' => $i->plan?->label() ?? '-',
                        'email' => $i->email ?? '',
                        'status' => $i->status ?? '-',
                    ],
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
                    'metadata' => [
                        'buyer_name' => $p->buyer_name ?? $p->email ?? '-',
                        'amount' => number_format((float) $p->amount, 0, ',', '.'),
                        'plan' => $p->plan?->label() ?? '-',
                        'status' => $p->status?->label() ?? $p->status ?? '-',
                        'method' => $p->payment_method ?? '-',
                    ],
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
                    'metadata' => [
                        'client_name' => $c->client?->name ?? 'Cliente #' . $c->client_id,
                        'week_label' => $c->week_label ?? '-',
                        'bienestar' => $c->bienestar ?? '-',
                    ],
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
                        : (($m->client?->name ?? 'Cliente') . ' → Coach')) . ' — ' . \Illuminate\Support\Str::limit($m->message ?? '', 60),
                    'timestamp' => $m->created_at,
                    'time_ago' => $m->created_at?->diffForHumans() ?? '-',
                    'metadata' => [
                        'direction' => $m->direction ?? '-',
                        'client_name' => $m->client?->name ?? '-',
                        'coach_name' => $m->coach?->name ?? '-',
                        'preview' => \Illuminate\Support\Str::limit($m->message ?? '', 80),
                    ],
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
                    'metadata' => [
                        'name' => $c->name ?? '-',
                        'plan' => $c->plan?->label() ?? '-',
                        'email' => $c->email ?? '-',
                    ],
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
                    'description' => ($p->client?->name ?? 'Cliente') . ' — ' . ($p->post_type ?? 'post') . ' — ' . \Illuminate\Support\Str::limit($p->content ?? '', 60),
                    'timestamp' => $p->created_at,
                    'time_ago' => $p->created_at?->diffForHumans() ?? '-',
                    'metadata' => [
                        'client_name' => $p->client?->name ?? '-',
                        'post_type' => $p->post_type ?? '-',
                        'preview' => \Illuminate\Support\Str::limit($p->content ?? '', 80),
                    ],
                ]);
            $items = $items->merge($posts);
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

        $this->eventsToday = $inscriptionsCount + $paymentsCount + $checkinsCount + $messagesCount + $newClientsCount + $communityCount;
        $this->inscriptionsToday = $inscriptionsCount;
        $this->paymentsToday = $paymentsCount;
        $this->activeConversations = CoachMessage::where('created_at', '>=', $today)
            ->distinct('client_id')
            ->count('client_id');
    }

    protected function getDateFrom(): ?Carbon
    {
        return match ($this->dateFilter) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.admin.live-feed');
    }
}
