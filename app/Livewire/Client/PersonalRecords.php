<?php

namespace App\Livewire\Client;

use App\Models\PersonalRecord;
use Livewire\Component;

class PersonalRecords extends Component
{
    public string $category = 'all';
    public string $search = '';

    // Form fields
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $exercise = '';
    public string $formCategory = 'fuerza';
    public ?float $weight = null;
    public ?int $reps = null;
    public ?int $durationSec = null;
    public ?float $distanceKm = null;
    public string $notes = '';
    public string $achievedAt = '';

    // Delete confirmation
    public ?int $deletingId = null;

    protected function rules(): array
    {
        return [
            'exercise' => 'required|string|max:100',
            'formCategory' => 'required|in:fuerza,cardio,calistenia,flexibilidad',
            'weight' => 'nullable|numeric|min:0|max:9999',
            'reps' => 'nullable|integer|min:0|max:9999',
            'durationSec' => 'nullable|integer|min:0|max:86400',
            'distanceKm' => 'nullable|numeric|min:0|max:9999',
            'notes' => 'nullable|string|max:500',
            'achievedAt' => 'required|date|before_or_equal:today',
        ];
    }

    public function openForm(?int $id = null): void
    {
        if ($id) {
            $pr = PersonalRecord::where('client_id', auth('wellcore')->id())->findOrFail($id);
            $this->editingId = $pr->id;
            $this->exercise = $pr->exercise;
            $this->formCategory = $pr->category;
            $this->weight = $pr->weight;
            $this->reps = $pr->reps;
            $this->durationSec = $pr->duration_sec;
            $this->distanceKm = $pr->distance_km;
            $this->notes = $pr->notes ?? '';
            $this->achievedAt = $pr->achieved_at->format('Y-m-d');
        } else {
            $this->resetForm();
            $this->achievedAt = now()->format('Y-m-d');
        }
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $clientId = auth('wellcore')->id();
        $data = [
            'client_id' => $clientId,
            'exercise' => $this->exercise,
            'category' => $this->formCategory,
            'weight' => $this->weight,
            'reps' => $this->reps,
            'duration_sec' => $this->durationSec,
            'distance_km' => $this->distanceKm,
            'notes' => $this->notes ?: null,
            'achieved_at' => $this->achievedAt,
        ];

        if ($this->editingId) {
            $pr = PersonalRecord::where('client_id', $clientId)->findOrFail($this->editingId);
            $pr->update($data);
        } else {
            // Mark previous records for this exercise as not current
            PersonalRecord::where('client_id', $clientId)
                ->where('exercise', $this->exercise)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $data['is_current'] = true;
            PersonalRecord::create($data);
        }

        $this->showForm = false;
        $this->resetForm();
        $this->dispatch('pr-saved');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            PersonalRecord::where('client_id', auth('wellcore')->id())
                ->where('id', $this->deletingId)
                ->delete();
            $this->deletingId = null;
        }
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->exercise = '';
        $this->formCategory = 'fuerza';
        $this->weight = null;
        $this->reps = null;
        $this->durationSec = null;
        $this->distanceKm = null;
        $this->notes = '';
        $this->achievedAt = '';
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        $query = PersonalRecord::where('client_id', $clientId)
            ->orderByDesc('achieved_at')
            ->orderByDesc('id');

        if ($this->category !== 'all') {
            $query->where('category', $this->category);
        }

        if (strlen($this->search) > 1) {
            $query->where('exercise', 'like', '%' . $this->search . '%');
        }

        $records = $query->get();

        // Group current PRs by exercise for trophy display
        $currentPrs = PersonalRecord::where('client_id', $clientId)
            ->where('is_current', true)
            ->get()
            ->keyBy('exercise');

        // Stats
        $totalPrs = PersonalRecord::where('client_id', $clientId)->where('is_current', true)->count();
        $totalExercises = PersonalRecord::where('client_id', $clientId)->distinct('exercise')->count('exercise');
        $thisMonth = PersonalRecord::where('client_id', $clientId)
            ->where('achieved_at', '>=', now()->startOfMonth())
            ->count();

        return view('livewire.client.personal-records', [
            'records' => $records,
            'currentPrs' => $currentPrs,
            'totalPrs' => $totalPrs,
            'totalExercises' => $totalExercises,
            'thisMonth' => $thisMonth,
        ]);
    }
}
