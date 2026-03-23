<?php

namespace App\Livewire\Client;

use App\Models\VideoCheckin;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.client')]
class VideoCheckinUpload extends Component
{
    use WithFileUploads;

    public $mediaFile = null;
    public string $exerciseName = '';
    public string $notes = '';
    public string $mediaType = 'video';
    public bool $showSuccess = false;
    public ?int $expandedCheckin = null;

    public function updatedMediaFile(): void
    {
        if (!$this->mediaFile) {
            return;
        }

        $ext = strtolower($this->mediaFile->getClientOriginalExtension());
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $this->mediaType = 'image';
        } else {
            $this->mediaType = 'video';
        }
    }

    public function submitCheckin(): void
    {
        $maxSize = $this->mediaType === 'video' ? 102400 : 10240;

        $this->validate([
            'mediaFile' => "required|file|mimes:mp4,mov,webm,jpg,jpeg,png|max:{$maxSize}",
            'exerciseName' => 'required|string|max:200',
            'notes' => 'nullable|string|max:2000',
        ], [
            'mediaFile.required' => 'Selecciona un archivo de video o imagen.',
            'mediaFile.mimes' => 'Formato permitido: MP4, MOV, WebM, JPG, PNG.',
            'mediaFile.max' => $this->mediaType === 'video'
                ? 'El video no puede superar 100MB.'
                : 'La imagen no puede superar 10MB.',
            'exerciseName.required' => 'Escribe el nombre del ejercicio.',
        ]);

        $clientId = auth('wellcore')->id();

        // Guard: enforce monthly upload limit before storing the file.
        // Count resolved once and reused to avoid issuing two identical COUNT queries.
        $maxPerMonth  = 4;
        $monthlyCount = $this->getMonthlyCount($clientId);
        if ($monthlyCount >= $maxPerMonth) {
            $this->addError('mediaFile', "Has alcanzado el límite de {$maxPerMonth} video check-ins este mes.");
            return;
        }

        $path = $this->mediaFile->store('checkins/' . $clientId, 'public');

        VideoCheckin::create([
            'client_id' => $clientId,
            'coach_id' => $this->getCoachId($clientId),
            'media_type' => $this->mediaType,
            'media_url' => $path,
            'exercise_name' => trim($this->exerciseName),
            'notes' => trim($this->notes) ?: null,
            'status' => 'pending',
            'ai_used' => false,
            'plan_uses_this_month' => $monthlyCount + 1,  // reuse cached count — no second query
            'created_at' => now(),
        ]);

        $this->reset(['mediaFile', 'exerciseName', 'notes', 'mediaType']);
        $this->mediaType = 'video';
        $this->showSuccess = true;
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedCheckin = $this->expandedCheckin === $id ? null : $id;
    }

    public function dismissSuccess(): void
    {
        $this->showSuccess = false;
    }

    private function getCoachId(string $clientId): ?string
    {
        return \App\Models\AssignedPlan::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->value('assigned_by');
    }

    private function getMonthlyCount(string $clientId): int
    {
        return VideoCheckin::where('client_id', $clientId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        $checkins = VideoCheckin::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('livewire.client.video-checkin-upload', [
            'checkins' => $checkins,
        ]);
    }
}
