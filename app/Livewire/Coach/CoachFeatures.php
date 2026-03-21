<?php

namespace App\Livewire\Coach;

use App\Models\AccountabilityPod;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\CoachAudio;
use App\Models\CoachAvailability;
use App\Models\PodMember;
use App\Models\PodMessage;
use App\Models\VideoCheckin;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Herramientas Coach'])]
class CoachFeatures extends Component
{
    // ─── Tab state ──────────────────────────────────────────
    public string $activeTab = 'pods';

    // ─── Pods ───────────────────────────────────────────────
    public bool $showCreatePodModal = false;
    public string $podName = '';
    public string $podDescription = '';
    public int $podMaxMembers = 8;

    public ?int $viewingPodId = null;
    public string $podMessageText = '';
    public bool $showAddMemberModal = false;
    public string $memberSearch = '';

    public ?int $editingPodId = null;
    public bool $showEditPodModal = false;
    public string $editPodName = '';
    public string $editPodDescription = '';
    public int $editPodMaxMembers = 8;
    public bool $editPodIsActive = true;

    // ─── Availability ───────────────────────────────────────
    public bool $showSlotModal = false;
    public ?int $editingSlotId = null;
    public int $slotDay = 1;
    public string $slotStart = '09:00';
    public string $slotEnd = '10:00';
    public ?int $deletingSlotId = null;

    // ─── Audio ──────────────────────────────────────────────
    public bool $showAudioModal = false;
    public ?int $editingAudioId = null;
    public string $audioTitle = '';
    public string $audioUrl = '';
    public int $audioDuration = 0;
    public string $audioCategory = 'general';
    public ?int $deletingAudioId = null;

    // ─── Video Check-ins ────────────────────────────────────
    public string $videoStatusFilter = 'all';
    public ?int $expandedCheckinId = null;
    public string $coachResponse = '';

    // ─── Messages ───────────────────────────────────────────
    public string $successMessage = '';

    // ─── Tab switching ──────────────────────────────────────
    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->successMessage = '';
        $this->viewingPodId = null;
    }

    public function dismissSuccess(): void
    {
        $this->successMessage = '';
    }

    // ═══════════════════════════════════════════════════════
    //  PODS
    // ═══════════════════════════════════════════════════════

    public function openCreatePod(): void
    {
        $this->resetPodForm();
        $this->showCreatePodModal = true;
    }

    public function closeCreatePod(): void
    {
        $this->showCreatePodModal = false;
        $this->resetPodForm();
    }

    public function savePod(): void
    {
        $this->validate([
            'podName' => 'required|string|min:3|max:100',
            'podDescription' => 'nullable|string|max:500',
            'podMaxMembers' => 'required|integer|min:2|max:50',
        ], [
            'podName.required' => 'El nombre del pod es obligatorio.',
            'podName.min' => 'El nombre debe tener al menos 3 caracteres.',
            'podMaxMembers.min' => 'El minimo de miembros es 2.',
        ]);

        AccountabilityPod::create([
            'coach_id' => auth('wellcore')->id(),
            'name' => trim($this->podName),
            'description' => trim($this->podDescription) ?: null,
            'max_members' => $this->podMaxMembers,
            'is_active' => true,
        ]);

        $this->closeCreatePod();
        $this->successMessage = 'Pod creado correctamente.';
    }

    public function viewPod(int $podId): void
    {
        $pod = AccountabilityPod::where('id', $podId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if ($pod) {
            $this->viewingPodId = $podId;
            $this->podMessageText = '';
        }
    }

    public function closePodView(): void
    {
        $this->viewingPodId = null;
        $this->podMessageText = '';
        $this->showAddMemberModal = false;
        $this->memberSearch = '';
    }

    public function sendPodMessage(): void
    {
        if (! $this->viewingPodId || trim($this->podMessageText) === '') {
            return;
        }

        $pod = AccountabilityPod::where('id', $this->viewingPodId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if (! $pod) {
            return;
        }

        PodMessage::create([
            'pod_id' => $this->viewingPodId,
            'client_id' => auth('wellcore')->id(),
            'message' => trim($this->podMessageText),
        ]);

        $this->podMessageText = '';
    }

    public function openAddMember(): void
    {
        $this->showAddMemberModal = true;
        $this->memberSearch = '';
    }

    public function closeAddMember(): void
    {
        $this->showAddMemberModal = false;
        $this->memberSearch = '';
    }

    public function addMemberToPod(int $clientId): void
    {
        if (! $this->viewingPodId) {
            return;
        }

        $pod = AccountabilityPod::where('id', $this->viewingPodId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if (! $pod) {
            return;
        }

        // Check max members
        $currentCount = PodMember::where('pod_id', $pod->id)->count();
        if ($currentCount >= $pod->max_members) {
            $this->successMessage = 'El pod ha alcanzado el maximo de miembros.';
            return;
        }

        // Check if already a member
        $exists = PodMember::where('pod_id', $pod->id)
            ->where('client_id', $clientId)
            ->exists();

        if ($exists) {
            return;
        }

        PodMember::create([
            'pod_id' => $pod->id,
            'client_id' => $clientId,
        ]);

        $this->successMessage = 'Miembro agregado al pod.';
        $this->showAddMemberModal = false;
    }

    public function removeMember(int $memberId): void
    {
        if (! $this->viewingPodId) {
            return;
        }

        $pod = AccountabilityPod::where('id', $this->viewingPodId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if (! $pod) {
            return;
        }

        PodMember::where('id', $memberId)
            ->where('pod_id', $pod->id)
            ->delete();

        $this->successMessage = 'Miembro removido del pod.';
    }

    public function openEditPod(int $podId): void
    {
        $pod = AccountabilityPod::where('id', $podId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if (! $pod) {
            return;
        }

        $this->editingPodId = $pod->id;
        $this->editPodName = $pod->name;
        $this->editPodDescription = $pod->description ?? '';
        $this->editPodMaxMembers = $pod->max_members;
        $this->editPodIsActive = $pod->is_active;
        $this->showEditPodModal = true;
    }

    public function closeEditPod(): void
    {
        $this->showEditPodModal = false;
        $this->editingPodId = null;
    }

    public function updatePod(): void
    {
        if (! $this->editingPodId) {
            return;
        }

        $this->validate([
            'editPodName' => 'required|string|min:3|max:100',
            'editPodDescription' => 'nullable|string|max:500',
            'editPodMaxMembers' => 'required|integer|min:2|max:50',
        ]);

        $pod = AccountabilityPod::where('id', $this->editingPodId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if ($pod) {
            $pod->update([
                'name' => trim($this->editPodName),
                'description' => trim($this->editPodDescription) ?: null,
                'max_members' => $this->editPodMaxMembers,
                'is_active' => $this->editPodIsActive,
            ]);
            $this->successMessage = 'Pod actualizado correctamente.';
        }

        $this->closeEditPod();
    }

    private function resetPodForm(): void
    {
        $this->podName = '';
        $this->podDescription = '';
        $this->podMaxMembers = 8;
    }

    // ═══════════════════════════════════════════════════════
    //  AVAILABILITY
    // ═══════════════════════════════════════════════════════

    public function openAddSlot(): void
    {
        $this->editingSlotId = null;
        $this->slotDay = 1;
        $this->slotStart = '09:00';
        $this->slotEnd = '10:00';
        $this->showSlotModal = true;
    }

    public function openEditSlot(int $slotId): void
    {
        $slot = CoachAvailability::where('id', $slotId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if (! $slot) {
            return;
        }

        $this->editingSlotId = $slot->id;
        $this->slotDay = $slot->day_of_week;
        $this->slotStart = substr($slot->time_start, 0, 5);
        $this->slotEnd = substr($slot->time_end, 0, 5);
        $this->showSlotModal = true;
    }

    public function closeSlotModal(): void
    {
        $this->showSlotModal = false;
        $this->editingSlotId = null;
    }

    public function saveSlot(): void
    {
        $this->validate([
            'slotDay' => 'required|integer|min:1|max:7',
            'slotStart' => 'required|date_format:H:i',
            'slotEnd' => 'required|date_format:H:i|after:slotStart',
        ], [
            'slotDay.required' => 'Selecciona un dia.',
            'slotStart.required' => 'La hora de inicio es obligatoria.',
            'slotEnd.required' => 'La hora de fin es obligatoria.',
            'slotEnd.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        $coachId = auth('wellcore')->id();
        $data = [
            'coach_id' => $coachId,
            'day_of_week' => $this->slotDay,
            'time_start' => $this->slotStart,
            'time_end' => $this->slotEnd,
            'is_active' => true,
        ];

        if ($this->editingSlotId) {
            $slot = CoachAvailability::where('id', $this->editingSlotId)
                ->where('coach_id', $coachId)
                ->first();
            if ($slot) {
                $slot->update($data);
                $this->successMessage = 'Horario actualizado.';
            }
        } else {
            CoachAvailability::create($data);
            $this->successMessage = 'Horario agregado.';
        }

        $this->closeSlotModal();
    }

    public function toggleSlotActive(int $slotId): void
    {
        $slot = CoachAvailability::where('id', $slotId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if ($slot) {
            $slot->update(['is_active' => ! $slot->is_active]);
        }
    }

    public function confirmDeleteSlot(int $slotId): void
    {
        $this->deletingSlotId = $slotId;
    }

    public function cancelDeleteSlot(): void
    {
        $this->deletingSlotId = null;
    }

    public function deleteSlot(): void
    {
        if (! $this->deletingSlotId) {
            return;
        }

        CoachAvailability::where('id', $this->deletingSlotId)
            ->where('coach_id', auth('wellcore')->id())
            ->delete();

        $this->deletingSlotId = null;
        $this->successMessage = 'Horario eliminado.';
    }

    // ═══════════════════════════════════════════════════════
    //  AUDIO
    // ═══════════════════════════════════════════════════════

    public function openCreateAudio(): void
    {
        $this->resetAudioForm();
        $this->showAudioModal = true;
    }

    public function openEditAudio(int $audioId): void
    {
        $audio = CoachAudio::where('id', $audioId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if (! $audio) {
            return;
        }

        $this->editingAudioId = $audio->id;
        $this->audioTitle = $audio->title;
        $this->audioUrl = $audio->audio_url;
        $this->audioDuration = $audio->duration_sec;
        $this->audioCategory = $audio->category ?? 'general';
        $this->showAudioModal = true;
    }

    public function closeAudioModal(): void
    {
        $this->showAudioModal = false;
        $this->resetAudioForm();
    }

    public function saveAudio(): void
    {
        $this->validate([
            'audioTitle' => 'required|string|min:3|max:200',
            'audioUrl' => 'required|url|max:500',
            'audioDuration' => 'required|integer|min:0',
            'audioCategory' => 'required|string|max:80',
        ], [
            'audioTitle.required' => 'El titulo es obligatorio.',
            'audioUrl.required' => 'La URL del audio es obligatoria.',
            'audioUrl.url' => 'La URL no es valida.',
            'audioCategory.required' => 'La categoria es obligatoria.',
        ]);

        $coachId = auth('wellcore')->id();
        $data = [
            'coach_id' => $coachId,
            'title' => trim($this->audioTitle),
            'audio_url' => trim($this->audioUrl),
            'duration_sec' => $this->audioDuration,
            'category' => $this->audioCategory,
            'is_active' => true,
        ];

        if ($this->editingAudioId) {
            $audio = CoachAudio::where('id', $this->editingAudioId)
                ->where('coach_id', $coachId)
                ->first();
            if ($audio) {
                $audio->update($data);
                $this->successMessage = 'Audio actualizado.';
            }
        } else {
            $maxSort = CoachAudio::where('coach_id', $coachId)->max('sort_order') ?? 0;
            $data['sort_order'] = $maxSort + 1;
            CoachAudio::create($data);
            $this->successMessage = 'Audio creado correctamente.';
        }

        $this->closeAudioModal();
    }

    public function confirmDeleteAudio(int $audioId): void
    {
        $this->deletingAudioId = $audioId;
    }

    public function cancelDeleteAudio(): void
    {
        $this->deletingAudioId = null;
    }

    public function deleteAudio(): void
    {
        if (! $this->deletingAudioId) {
            return;
        }

        CoachAudio::where('id', $this->deletingAudioId)
            ->where('coach_id', auth('wellcore')->id())
            ->delete();

        $this->deletingAudioId = null;
        $this->successMessage = 'Audio eliminado.';
    }

    public function toggleAudioActive(int $audioId): void
    {
        $audio = CoachAudio::where('id', $audioId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if ($audio) {
            $audio->update(['is_active' => ! $audio->is_active]);
        }
    }

    private function resetAudioForm(): void
    {
        $this->editingAudioId = null;
        $this->audioTitle = '';
        $this->audioUrl = '';
        $this->audioDuration = 0;
        $this->audioCategory = 'general';
    }

    // ═══════════════════════════════════════════════════════
    //  VIDEO CHECK-INS
    // ═══════════════════════════════════════════════════════

    public function toggleCheckin(int $checkinId): void
    {
        $this->expandedCheckinId = ($this->expandedCheckinId === $checkinId) ? null : $checkinId;

        if ($this->expandedCheckinId === $checkinId) {
            $checkin = VideoCheckin::find($checkinId);
            $this->coachResponse = $checkin->coach_response ?? '';
        } else {
            $this->coachResponse = '';
        }
    }

    public function submitReview(int $checkinId): void
    {
        if (trim($this->coachResponse) === '') {
            return;
        }

        $checkin = VideoCheckin::where('id', $checkinId)
            ->where('coach_id', auth('wellcore')->id())
            ->first();

        if (! $checkin) {
            return;
        }

        $checkin->update([
            'coach_response' => trim($this->coachResponse),
            'status' => 'coach_reviewed',
            'responded_at' => now(),
        ]);

        $this->coachResponse = '';
        $this->expandedCheckinId = null;
        $this->successMessage = 'Video check-in revisado correctamente.';
    }

    // ═══════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════

    private function getCoachClientIds(int|string $coachId)
    {
        return AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();
    }

    private function dayName(int $day): string
    {
        return match ($day) {
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
            7 => 'Domingo',
            default => 'Dia ' . $day,
        };
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '--:--';
        }
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;

        return sprintf('%d:%02d', $m, $s);
    }

    // ═══════════════════════════════════════════════════════
    //  RENDER
    // ═══════════════════════════════════════════════════════

    public function render()
    {
        $coachId = auth('wellcore')->id();
        $clientIds = $this->getCoachClientIds($coachId);

        // ─── Pods ────────────────────────────────────────────
        $pods = AccountabilityPod::where('coach_id', $coachId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($pod) {
                $members = PodMember::where('pod_id', $pod->id)->get();
                $memberData = $members->map(function ($m) {
                    $client = Client::find($m->client_id);

                    return [
                        'id' => $m->id,
                        'client_id' => $m->client_id,
                        'name' => $client->name ?? 'Cliente',
                        'initial' => substr($client->name ?? 'C', 0, 1),
                        'joined_at' => $m->joined_at ? Carbon::parse($m->joined_at)->format('d M Y') : null,
                    ];
                });

                $lastMessage = PodMessage::where('pod_id', $pod->id)
                    ->orderByDesc('created_at')
                    ->first();

                return [
                    'id' => $pod->id,
                    'name' => $pod->name,
                    'description' => $pod->description,
                    'max_members' => $pod->max_members,
                    'is_active' => $pod->is_active,
                    'member_count' => $members->count(),
                    'members' => $memberData,
                    'last_activity' => $lastMessage
                        ? Carbon::parse($lastMessage->created_at)->diffForHumans()
                        : 'Sin actividad',
                    'created_at' => Carbon::parse($pod->created_at)->format('d M Y'),
                ];
            });

        // Pod messages (when viewing a pod)
        $podMessages = [];
        $viewingPod = null;
        if ($this->viewingPodId) {
            $viewingPod = $pods->firstWhere('id', $this->viewingPodId);
            $podMessages = PodMessage::where('pod_id', $this->viewingPodId)
                ->orderBy('created_at')
                ->limit(100)
                ->get()
                ->map(function ($msg) use ($coachId) {
                    $isCoach = ($msg->client_id == $coachId);
                    $client = $isCoach ? null : Client::find($msg->client_id);

                    return [
                        'id' => $msg->id,
                        'message' => $msg->message,
                        'sender_name' => $isCoach ? 'Coach (Tu)' : ($client->name ?? 'Cliente'),
                        'sender_initial' => $isCoach ? 'C' : substr($client->name ?? 'C', 0, 1),
                        'is_coach' => $isCoach,
                        'created_at' => Carbon::parse($msg->created_at)->format('H:i'),
                        'created_at_full' => Carbon::parse($msg->created_at)->format('d M Y, H:i'),
                    ];
                });
        }

        // Available clients for adding to pods
        $availableClients = collect();
        if ($this->showAddMemberModal && $this->viewingPodId) {
            $existingMemberIds = PodMember::where('pod_id', $this->viewingPodId)
                ->pluck('client_id');

            $query = Client::whereIn('id', $clientIds)
                ->where('status', 'activo')
                ->whereNotIn('id', $existingMemberIds)
                ->orderBy('name');

            if ($this->memberSearch !== '') {
                $query->where('name', 'like', '%' . $this->memberSearch . '%');
            }

            $availableClients = $query->get(['id', 'name'])->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'initial' => substr($c->name ?? 'C', 0, 1),
            ]);
        }

        // ─── Availability ─────────────────────────────────────
        $slots = CoachAvailability::where('coach_id', $coachId)
            ->orderBy('day_of_week')
            ->orderBy('time_start')
            ->get()
            ->map(fn ($slot) => [
                'id' => $slot->id,
                'day_of_week' => $slot->day_of_week,
                'day_name' => $this->dayName($slot->day_of_week),
                'time_start' => substr($slot->time_start, 0, 5),
                'time_end' => substr($slot->time_end, 0, 5),
                'is_active' => $slot->is_active,
            ]);

        // Group slots by day for the weekly grid
        $weeklySlots = [];
        for ($d = 1; $d <= 7; $d++) {
            $weeklySlots[$d] = [
                'day' => $d,
                'name' => $this->dayName($d),
                'short' => mb_substr($this->dayName($d), 0, 3),
                'slots' => $slots->where('day_of_week', $d)->values()->toArray(),
            ];
        }

        // ─── Audio ─────────────────────────────────────────────
        $audios = CoachAudio::where('coach_id', $coachId)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'audio_url' => $a->audio_url,
                'duration_sec' => $a->duration_sec,
                'duration_fmt' => $this->formatDuration($a->duration_sec),
                'category' => $a->category,
                'is_active' => $a->is_active,
                'created_at' => $a->created_at ? Carbon::parse($a->created_at)->format('d M Y') : null,
            ]);

        $audioCategories = $audios->pluck('category')->unique()->values()->toArray();

        // ─── Video Check-ins ────────────────────────────────────
        $checkinsQuery = VideoCheckin::where('coach_id', $coachId)
            ->orderByDesc('created_at');

        if ($this->videoStatusFilter !== 'all') {
            $checkinsQuery->where('status', $this->videoStatusFilter);
        }

        $checkins = $checkinsQuery->limit(50)->get()->map(function ($vc) {
            $client = Client::find($vc->client_id);

            return [
                'id' => $vc->id,
                'client_id' => $vc->client_id,
                'client_name' => $client->name ?? 'Cliente',
                'client_initial' => substr($client->name ?? 'C', 0, 1),
                'media_type' => $vc->media_type,
                'media_url' => $vc->media_url,
                'exercise_name' => $vc->exercise_name,
                'notes' => $vc->notes,
                'coach_response' => $vc->coach_response,
                'ai_response' => $vc->ai_response,
                'ai_used' => $vc->ai_used,
                'status' => $vc->status,
                'responded_at' => $vc->responded_at ? Carbon::parse($vc->responded_at)->format('d M Y, H:i') : null,
                'created_at' => Carbon::parse($vc->created_at)->format('d M Y, H:i'),
                'created_at_ago' => Carbon::parse($vc->created_at)->diffForHumans(),
            ];
        });

        $checkinStats = [
            'total' => VideoCheckin::where('coach_id', $coachId)->count(),
            'pending' => VideoCheckin::where('coach_id', $coachId)->where('status', 'pending')->count(),
            'reviewed' => VideoCheckin::where('coach_id', $coachId)->where('status', 'coach_reviewed')->count(),
            'ai_reviewed' => VideoCheckin::where('coach_id', $coachId)->where('status', 'ai_reviewed')->count(),
        ];

        return view('livewire.coach.coach-features', [
            'pods' => $pods,
            'podMessages' => $podMessages,
            'viewingPod' => $viewingPod,
            'availableClients' => $availableClients,
            'weeklySlots' => $weeklySlots,
            'slots' => $slots,
            'audios' => $audios,
            'audioCategories' => $audioCategories,
            'checkins' => $checkins,
            'checkinStats' => $checkinStats,
        ]);
    }
}
