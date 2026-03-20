<?php

namespace App\Livewire\Coach;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\CoachNote;
use App\Models\Ticket;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Notas'])]
class CoachNotesPage extends Component
{
    // Tab state
    public string $activeTab = 'notes';

    // Notes filters
    public string $search = '';
    public string $noteTypeFilter = 'all';
    public string $clientFilter = 'all';

    // Notes CRUD
    public bool $showNoteModal = false;
    public ?int $editingNoteId = null;
    public string $noteClientId = '';
    public string $noteType = 'general';
    public string $noteText = '';

    // Delete confirmation
    public ?int $confirmDeleteId = null;

    // Tickets filters
    public string $ticketStatusFilter = 'all';
    public string $ticketPriorityFilter = 'all';

    // Ticket create
    public bool $showTicketModal = false;
    public string $ticketType = '';
    public string $ticketDescription = '';
    public string $ticketClientName = '';
    public string $ticketPriority = 'normal';

    // Success messages
    public string $successMessage = '';

    // Expanded ticket
    public ?string $expandedTicketId = null;

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->successMessage = '';
    }

    // ─── Notes CRUD ───────────────────────────────────────────

    public function openCreateNote(): void
    {
        $this->resetNoteForm();
        $this->showNoteModal = true;
    }

    public function openEditNote(int $noteId): void
    {
        $note = CoachNote::where('id', $noteId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if (! $note) {
            return;
        }

        $this->editingNoteId = $note->id;
        $this->noteClientId = (string) $note->client_id;
        $this->noteType = $note->note_type ?? 'general';
        $this->noteText = $note->note;
        $this->showNoteModal = true;
    }

    public function closeNoteModal(): void
    {
        $this->showNoteModal = false;
        $this->resetNoteForm();
    }

    public function saveNote(): void
    {
        $this->validate([
            'noteClientId' => 'required|integer|exists:clients,id',
            'noteType' => 'required|in:general,seguimiento,alerta,logro',
            'noteText' => 'required|string|min:3|max:5000',
        ], [
            'noteClientId.required' => 'Selecciona un cliente.',
            'noteType.required' => 'Selecciona el tipo de nota.',
            'noteText.required' => 'La nota es obligatoria.',
            'noteText.min' => 'La nota debe tener al menos 3 caracteres.',
        ]);

        $coachId = auth('wellcore')->id();

        // Verify this client belongs to the coach
        $clientIds = $this->getCoachClientIds($coachId);
        if (! $clientIds->contains((int) $this->noteClientId)) {
            return;
        }

        if ($this->editingNoteId) {
            $note = CoachNote::where('id', $this->editingNoteId)
                ->where('coach_id', $coachId)
                ->first();

            if ($note) {
                $note->update([
                    'client_id' => (int) $this->noteClientId,
                    'note_type' => $this->noteType,
                    'note' => trim($this->noteText),
                ]);
                $this->successMessage = 'Nota actualizada correctamente.';
            }
        } else {
            CoachNote::create([
                'coach_id' => $coachId,
                'client_id' => (int) $this->noteClientId,
                'note_type' => $this->noteType,
                'note' => trim($this->noteText),
            ]);
            $this->successMessage = 'Nota creada correctamente.';
        }

        $this->closeNoteModal();
    }

    public function confirmDelete(int $noteId): void
    {
        $this->confirmDeleteId = $noteId;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    public function deleteNote(): void
    {
        if (! $this->confirmDeleteId) {
            return;
        }

        $note = CoachNote::where('id', $this->confirmDeleteId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if ($note) {
            $note->delete();
            $this->successMessage = 'Nota eliminada.';
        }

        $this->confirmDeleteId = null;
    }

    // ─── Tickets ──────────────────────────────────────────────

    public function openCreateTicket(): void
    {
        $this->resetTicketForm();
        $this->showTicketModal = true;
    }

    public function closeTicketModal(): void
    {
        $this->showTicketModal = false;
        $this->resetTicketForm();
    }

    public function toggleTicketExpand(string $id): void
    {
        $this->expandedTicketId = ($this->expandedTicketId === $id) ? null : $id;
    }

    public function createTicket(): void
    {
        $this->validate([
            'ticketType' => 'required|in:soporte,tecnico,facturacion,otro',
            'ticketDescription' => 'required|string|min:10|max:3000',
            'ticketPriority' => 'required|in:low,normal,high,urgent',
        ], [
            'ticketType.required' => 'Selecciona el tipo de ticket.',
            'ticketDescription.required' => 'La descripcion es obligatoria.',
            'ticketDescription.min' => 'La descripcion debe tener al menos 10 caracteres.',
            'ticketPriority.required' => 'Selecciona la prioridad.',
        ]);

        $coach = auth('wellcore')->user();

        Ticket::create([
            'id' => uniqid('tkt_', true),
            'coach_id' => $coach->id,
            'coach_name' => $coach->name ?? 'Coach',
            'client_name' => $this->ticketClientName ?: null,
            'ticket_type' => $this->ticketType,
            'description' => trim($this->ticketDescription),
            'priority' => $this->ticketPriority,
            'status' => 'open',
            'deadline' => now()->addHours(48),
        ]);

        $this->closeTicketModal();
        $this->successMessage = 'Ticket creado correctamente.';
    }

    public function dismissSuccess(): void
    {
        $this->successMessage = '';
    }

    // ─── Helpers ──────────────────────────────────────────────

    private function getCoachClientIds(int $coachId)
    {
        return AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();
    }

    private function resetNoteForm(): void
    {
        $this->editingNoteId = null;
        $this->noteClientId = '';
        $this->noteType = 'general';
        $this->noteText = '';
    }

    private function resetTicketForm(): void
    {
        $this->ticketType = '';
        $this->ticketDescription = '';
        $this->ticketClientName = '';
        $this->ticketPriority = 'normal';
    }

    public function render()
    {
        $coachId = auth('wellcore')->id();
        $clientIds = $this->getCoachClientIds($coachId);

        // Coach's clients for dropdowns
        $clients = Client::whereIn('id', $clientIds)
            ->where('status', 'activo')
            ->orderBy('name')
            ->get(['id', 'name']);

        // ─── Notes ────────────────────────────────────────────
        $notesQuery = CoachNote::where('coach_id', $coachId)
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            $notesQuery->where(function ($q) use ($searchTerm) {
                $q->where('note', 'like', $searchTerm)
                    ->orWhereHas('client', function ($cq) use ($searchTerm) {
                        $cq->where('name', 'like', $searchTerm);
                    });
            });
        }

        if ($this->noteTypeFilter !== 'all') {
            $notesQuery->where('note_type', $this->noteTypeFilter);
        }

        if ($this->clientFilter !== 'all') {
            $notesQuery->where('client_id', (int) $this->clientFilter);
        }

        $notes = $notesQuery->get()->map(function ($note) {
            $client = Client::find($note->client_id);

            return [
                'id' => $note->id,
                'client_id' => $note->client_id,
                'client_name' => $client->name ?? 'Cliente',
                'client_initial' => substr($client->name ?? 'C', 0, 1),
                'note_type' => $note->note_type ?? 'general',
                'note' => $note->note,
                'created_at' => Carbon::parse($note->created_at)->format('d M Y, H:i'),
                'created_at_ago' => Carbon::parse($note->created_at)->diffForHumans(),
            ];
        });

        $noteStats = [
            'total' => CoachNote::where('coach_id', $coachId)->count(),
            'general' => CoachNote::where('coach_id', $coachId)->where('note_type', 'general')->count(),
            'seguimiento' => CoachNote::where('coach_id', $coachId)->where('note_type', 'seguimiento')->count(),
            'alerta' => CoachNote::where('coach_id', $coachId)->where('note_type', 'alerta')->count(),
            'logro' => CoachNote::where('coach_id', $coachId)->where('note_type', 'logro')->count(),
        ];

        // ─── Tickets ──────────────────────────────────────────
        $ticketsQuery = Ticket::where('coach_id', $coachId)
            ->orderByDesc('created_at');

        if ($this->ticketStatusFilter !== 'all') {
            $ticketsQuery->where('status', $this->ticketStatusFilter);
        }

        if ($this->ticketPriorityFilter !== 'all') {
            $ticketsQuery->where('priority', $this->ticketPriorityFilter);
        }

        $tickets = $ticketsQuery->get()->map(function ($ticket) {
            $statusValue = $ticket->status instanceof TicketStatus
                ? $ticket->status->value
                : ($ticket->status ?? 'open');
            $statusLabel = $ticket->status instanceof TicketStatus
                ? $ticket->status->label()
                : ucfirst(str_replace('_', ' ', $statusValue));

            $priorityValue = $ticket->priority instanceof TicketPriority
                ? $ticket->priority->value
                : ($ticket->priority ?? 'normal');
            $priorityLabel = $ticket->priority instanceof TicketPriority
                ? $ticket->priority->label()
                : ucfirst($priorityValue);

            return [
                'id' => $ticket->id,
                'ticket_type' => $ticket->ticket_type,
                'description' => $ticket->description,
                'client_name' => $ticket->client_name,
                'priority_value' => $priorityValue,
                'priority_label' => $priorityLabel,
                'status_value' => $statusValue,
                'status_label' => $statusLabel,
                'response' => $ticket->response,
                'deadline' => $ticket->deadline ? Carbon::parse($ticket->deadline)->format('d M Y') : null,
                'created_at' => Carbon::parse($ticket->created_at)->format('d M Y, H:i'),
                'created_at_ago' => Carbon::parse($ticket->created_at)->diffForHumans(),
            ];
        });

        $ticketStats = [
            'total' => Ticket::where('coach_id', $coachId)->count(),
            'open' => Ticket::where('coach_id', $coachId)->where('status', 'open')->count(),
            'in_progress' => Ticket::where('coach_id', $coachId)->where('status', 'in_progress')->count(),
            'resolved' => Ticket::where('coach_id', $coachId)->whereIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('livewire.coach.coach-notes-page', [
            'clients' => $clients,
            'notes' => $notes,
            'noteStats' => $noteStats,
            'tickets' => $tickets,
            'ticketStats' => $ticketStats,
        ]);
    }
}
