<?php

namespace App\Livewire\Coach;

use App\Enums\ClientStatus;
use App\Enums\PlanType;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\CoachMessage;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Broadcast'])]
class BroadcastCenter extends Component
{
    // Tab state
    public string $activeTab = 'compose';

    // Compose state
    public string $recipientMode = 'all'; // all, plan, status, individual
    public array $selectedPlans = [];
    public string $selectedStatus = 'activo';
    public array $selectedClientIds = [];
    public string $clientSearch = '';
    public string $message = '';
    public bool $showPreview = false;

    // Send result
    public bool $sent = false;
    public int $sentCount = 0;

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->sent = false;
    }

    public function togglePlan(string $plan): void
    {
        if (in_array($plan, $this->selectedPlans)) {
            $this->selectedPlans = array_values(array_diff($this->selectedPlans, [$plan]));
        } else {
            $this->selectedPlans[] = $plan;
        }
    }

    public function toggleClient(int $clientId): void
    {
        if (in_array($clientId, $this->selectedClientIds)) {
            $this->selectedClientIds = array_values(array_diff($this->selectedClientIds, [$clientId]));
        } else {
            $this->selectedClientIds[] = $clientId;
        }
    }

    public function selectAllClients(): void
    {
        $coachId = auth('wellcore')->id();
        $clientIds = $this->getCoachClientIds($coachId);

        $this->selectedClientIds = Client::whereIn('id', $clientIds)
            ->where('status', 'activo')
            ->pluck('id')
            ->toArray();
    }

    public function deselectAllClients(): void
    {
        $this->selectedClientIds = [];
    }

    public function togglePreview(): void
    {
        $this->showPreview = !$this->showPreview;
    }

    public function useTemplate(string $message): void
    {
        $this->message = $message;
        $this->activeTab = 'compose';
    }

    public function sendBroadcast(): void
    {
        if (trim($this->message) === '') {
            return;
        }

        $coachId = auth('wellcore')->id();
        $recipientIds = $this->resolveRecipients($coachId);

        if (empty($recipientIds)) {
            return;
        }

        $messageText = trim($this->message);
        $count = 0;

        foreach ($recipientIds as $clientId) {
            CoachMessage::create([
                'coach_id' => $coachId,
                'client_id' => $clientId,
                'message' => $messageText,
                'direction' => 'coach_to_client',
            ]);
            $count++;
        }

        $this->sentCount = $count;
        $this->sent = true;
        $this->message = '';
        $this->showPreview = false;
        $this->selectedClientIds = [];
        $this->selectedPlans = [];
    }

    public function resetCompose(): void
    {
        $this->message = '';
        $this->recipientMode = 'all';
        $this->selectedPlans = [];
        $this->selectedStatus = 'activo';
        $this->selectedClientIds = [];
        $this->clientSearch = '';
        $this->showPreview = false;
        $this->sent = false;
        $this->sentCount = 0;
    }

    protected function resolveRecipients(int $coachId): array
    {
        $allClientIds = $this->getCoachClientIds($coachId);

        return match ($this->recipientMode) {
            'all' => Client::whereIn('id', $allClientIds)
                ->where('status', 'activo')
                ->pluck('id')
                ->toArray(),

            'plan' => !empty($this->selectedPlans)
                ? Client::whereIn('id', $allClientIds)
                    ->where('status', 'activo')
                    ->whereIn('plan', $this->selectedPlans)
                    ->pluck('id')
                    ->toArray()
                : [],

            'status' => Client::whereIn('id', $allClientIds)
                ->where('status', $this->selectedStatus)
                ->pluck('id')
                ->toArray(),

            'individual' => array_values(array_intersect(
                array_map('intval', $this->selectedClientIds),
                array_map('intval', $allClientIds->toArray())
            )),

            default => [],
        };
    }

    protected function getCoachClientIds(int $coachId)
    {
        return AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();
    }

    public function render()
    {
        $coachId = auth('wellcore')->id();
        $allClientIds = $this->getCoachClientIds($coachId);

        // All active clients for the individual selector
        $clientsQuery = Client::whereIn('id', $allClientIds)
            ->where('status', 'activo')
            ->orderBy('name');

        if ($this->clientSearch !== '') {
            $clientsQuery->where('name', 'like', '%' . $this->clientSearch . '%');
        }

        $allClients = $clientsQuery->get()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'initial' => substr($c->name ?? 'C', 0, 1),
            'plan' => $c->plan?->label() ?? 'Sin plan',
            'plan_value' => $c->plan?->value ?? '',
            'status' => $c->status?->label() ?? 'Desconocido',
        ]);

        // Recipient count for preview
        $recipientCount = count($this->resolveRecipients($coachId));

        // Plan types for filter
        $planTypes = array_map(fn ($p) => [
            'value' => $p->value,
            'label' => $p->label(),
        ], PlanType::cases());

        // Status types for filter
        $statusTypes = array_map(fn ($s) => [
            'value' => $s->value,
            'label' => $s->label(),
        ], ClientStatus::cases());

        // Broadcast history: group messages sent by coach at similar times
        $history = [];
        if ($this->activeTab === 'history') {
            $history = $this->loadHistory($coachId);
        }

        return view('livewire.coach.broadcast-center', [
            'allClients' => $allClients,
            'recipientCount' => $recipientCount,
            'planTypes' => $planTypes,
            'statusTypes' => $statusTypes,
            'history' => $history,
        ]);
    }

    protected function loadHistory(int $coachId): array
    {
        // Get recent coach-to-client messages, group by message text + time window
        $messages = CoachMessage::where('coach_id', $coachId)
            ->where('direction', 'coach_to_client')
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        // Group broadcasts: same message text within 2-minute window = one broadcast
        $broadcasts = [];
        $processed = [];

        foreach ($messages as $msg) {
            $key = md5($msg->message);
            if (isset($processed[$key])) {
                // Check if within 2 minutes of the first one in this group
                $firstTime = $processed[$key]['time'];
                $diff = abs(Carbon::parse($msg->created_at)->diffInSeconds(Carbon::parse($firstTime)));
                if ($diff <= 120) {
                    $broadcasts[$processed[$key]['index']]['recipient_count']++;
                    continue;
                }
            }

            $index = count($broadcasts);
            $broadcasts[] = [
                'message' => $msg->message,
                'preview' => str()->limit($msg->message, 100),
                'recipient_count' => 1,
                'sent_at' => Carbon::parse($msg->created_at)->format('d/m/Y H:i'),
                'sent_ago' => Carbon::parse($msg->created_at)->diffForHumans(),
            ];
            $processed[$key] = ['time' => $msg->created_at, 'index' => $index];
        }

        // Only show entries that were sent to multiple clients (broadcasts)
        // or all recent ones for single messages too
        return array_slice($broadcasts, 0, 20);
    }
}
