<?php

namespace App\Livewire\Client;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
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

        $user = auth('wellcore')->user();

        Ticket::create([
            'id'          => uniqid('tkt_', true),
            'client_name' => $user->name,
            'coach_id'    => $user->coach_id ?? '',
            'ticket_type' => $this->ticketType,
            'description' => $this->description,
            'priority'    => $this->priority,
            'status'      => 'open',
            'deadline'    => now()->addHours(48),
        ]);

        $this->resetForm();
        $this->showForm = false;
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

    public function render()
    {
        $clientName = auth('wellcore')->user()->name;

        $query = Ticket::where('client_name', $clientName)
            ->orderByDesc('created_at');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $tickets = $query->get();

        $stats = [
            'open'        => Ticket::where('client_name', $clientName)->where('status', 'open')->count(),
            'in_progress' => Ticket::where('client_name', $clientName)->where('status', 'in_progress')->count(),
            'closed'      => Ticket::where('client_name', $clientName)->whereIn('status', ['closed', 'resolved'])->count(),
            'total'       => Ticket::where('client_name', $clientName)->count(),
        ];

        return view('livewire.client.ticket-support', [
            'tickets' => $tickets,
            'stats'   => $stats,
        ]);
    }
}
