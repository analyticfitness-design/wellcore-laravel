<?php

namespace App\Livewire\Client;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Soporte — WellCore'])]
class TicketSupport extends Component
{
    // Form fields
    public bool $showForm = false;
    public string $ticketType = '';
    public string $description = '';
    public string $priority = 'normal';

    // Filter
    public string $statusFilter = 'all';

    // Expanded ticket
    public ?string $expandedId = null;

    // Success flag
    public bool $showSuccess = false;

    protected function rules(): array
    {
        return [
            'ticketType'  => 'required|in:rutina_nueva,cambio_rutina,nutricion,habitos,invitacion_cliente,otro',
            'description' => 'required|string|min:10|max:2000',
            'priority'    => 'required|in:normal,alta',
        ];
    }

    protected function messages(): array
    {
        return [
            'ticketType.required'  => 'Selecciona el tipo de solicitud.',
            'ticketType.in'        => 'Tipo de solicitud no válido.',
            'description.required' => 'La descripción es obligatoria.',
            'description.min'      => 'La descripción debe tener al menos 10 caracteres.',
            'priority.required'    => 'Selecciona la prioridad.',
        ];
    }

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function createTicket(): void
    {
        $this->validate();

        $user     = auth('wellcore')->user();
        $coachId  = $this->resolveCoachId($user->id);

        Ticket::create([
            'id'          => (string) Str::uuid(),
            'client_name' => $user->name,
            'coach_id'    => $coachId,
            'ticket_type' => $this->ticketType,
            'description' => $this->description,
            'priority'    => $this->priority,
            'status'      => 'open',
            'deadline'    => now()->addHours(48),
        ]);

        $this->resetForm();
        $this->showForm    = false;
        $this->showSuccess = true;
    }

    public function toggleExpand(string $id): void
    {
        $this->expandedId = ($this->expandedId === $id) ? null : $id;
    }

    public function dismissSuccess(): void
    {
        $this->showSuccess = false;
    }

    private function resetForm(): void
    {
        $this->ticketType  = '';
        $this->description = '';
        $this->priority    = 'normal';
    }

    /**
     * Resolve the admin/coach ID for this client by checking coach_messages.
     * The clients table has no coach_id column; the relationship is inferred
     * from the most recent coach_messages exchange.
     * coach_id in tickets is varchar(60) NOT NULL, so we return '' as fallback.
     */
    private function resolveCoachId(int $clientId): string
    {
        $coachId = DB::table('coach_messages')
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->value('coach_id');

        return $coachId !== null ? (string) $coachId : '';
    }

    public function render()
    {
        // tickets table has no client_id column; client_name is the only
        // client identifier. It is always read from the authenticated user,
        // so the filter is scoped to the session owner and is safe.
        $clientName = auth('wellcore')->user()->name;

        $query = Ticket::where('client_name', $clientName)
            ->orderByDesc('created_at');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $tickets = $query->get();

        // Single query replacing 4 separate COUNT queries.
        // status ENUM values: 'open', 'in_progress', 'closed'
        $statsRaw = Ticket::where('client_name', $clientName)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $stats = [
            'total'       => (int) array_sum($statsRaw),
            'open'        => (int) ($statsRaw['open'] ?? 0),
            'in_progress' => (int) ($statsRaw['in_progress'] ?? 0),
            'closed'      => (int) ($statsRaw['closed'] ?? 0),
        ];

        return view('livewire.client.ticket-support', [
            'tickets' => $tickets,
            'stats'   => $stats,
        ]);
    }
}
