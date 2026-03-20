<?php

namespace App\Livewire\Admin;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Tickets — WellCore Admin'])]
class TicketManager extends Component
{
    use WithPagination;

    public string $statusFilter  = 'all';
    public string $priorityFilter = 'all';
    public string $search        = '';
    public string $sortBy        = 'created_at';
    public string $sortDir       = 'desc';

    // Response modal
    public ?string $respondingId  = null;
    public string $responseText   = '';
    public string $newStatus      = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'desc';
        }
    }

    public function openRespond(string $ticketId): void
    {
        $ticket = Ticket::findOrFail($ticketId);
        $this->respondingId  = $ticketId;
        $this->responseText  = $ticket->response ?? '';
        $this->newStatus     = $ticket->status instanceof TicketStatus
            ? $ticket->status->value
            : ($ticket->status ?? 'open');
    }

    public function closeRespond(): void
    {
        $this->respondingId = null;
        $this->responseText = '';
        $this->newStatus    = '';
    }

    public function respond(): void
    {
        $this->validate([
            'responseText' => 'required|string|min:5|max:5000',
            'newStatus'    => 'required|in:open,in_progress,resolved,closed',
        ], [
            'responseText.required' => 'La respuesta es obligatoria.',
            'responseText.min'      => 'La respuesta debe tener al menos 5 caracteres.',
            'newStatus.required'    => 'Selecciona un estado.',
        ]);

        $ticket = Ticket::findOrFail($this->respondingId);
        $ticket->response    = $this->responseText;
        $ticket->status      = $this->newStatus;
        $ticket->assigned_to = auth('wellcore')->user()->name ?? 'Admin';

        if (in_array($this->newStatus, ['resolved', 'closed'])) {
            $ticket->resolved_at = now();
        } else {
            $ticket->resolved_at = null;
        }

        $ticket->save();

        $this->closeRespond();
    }

    public function updateStatus(string $ticketId, string $status): void
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->status = $status;

        if (in_array($status, ['resolved', 'closed'])) {
            $ticket->resolved_at = now();
        }

        $ticket->save();
    }

    public function render()
    {
        $query = Ticket::query()->orderBy($this->sortBy, $this->sortDir);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('client_name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('ticket_type', 'like', "%{$s}%");
            });
        }

        $tickets = $query->paginate(20);

        $stats = [
            'open'        => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'resolved'    => Ticket::where('status', 'resolved')->count(),
            'closed'      => Ticket::where('status', 'closed')->count(),
            'total'       => Ticket::count(),
        ];

        return view('livewire.admin.ticket-manager', [
            'tickets' => $tickets,
            'stats'   => $stats,
        ]);
    }
}
