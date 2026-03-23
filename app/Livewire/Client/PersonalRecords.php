<?php

namespace App\Livewire\Client;

use App\Models\PersonalRecord;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
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
            $oldExercise = $pr->exercise;
            $pr->update($data);

            // If the exercise name changed, recalculate is_current for the old exercise too.
            $exercisesToRecalc = array_unique([$oldExercise, $this->exercise]);
            foreach ($exercisesToRecalc as $exerciseName) {
                $this->recalculateCurrentPr($clientId, $exerciseName);
            }
        } else {
            // Mark previous records for this exercise as not current
            PersonalRecord::where('client_id', $clientId)
                ->where('exercise', $this->exercise)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $data['is_current'] = true;
            PersonalRecord::create($data);
        }

        // Bust the global stats cache so the unfiltered view reflects the new record.
        Cache::forget("pr:stats:{$clientId}");

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
            $clientId = auth('wellcore')->id();
            PersonalRecord::where('client_id', $clientId)
                ->where('id', $this->deletingId)
                ->delete();
            // Bust the global stats cache after deletion.
            Cache::forget("pr:stats:{$clientId}");
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

    private function recalculateCurrentPr(int $clientId, string $exerciseName): void
    {
        // Determine the best PR for this exercise.
        // Priority: highest weight → highest reps → longest duration_sec → longest distance_km.
        // This covers all category types (fuerza, calistenia, cardio, flexibilidad).
        $bestPr = PersonalRecord::where('client_id', $clientId)
            ->where('exercise', $exerciseName)
            ->orderByRaw('COALESCE(weight, 0) DESC')
            ->orderByRaw('COALESCE(reps, 0) DESC')
            ->orderByRaw('COALESCE(duration_sec, 0) DESC')
            ->orderByRaw('COALESCE(distance_km, 0) DESC')
            ->orderByDesc('achieved_at')
            ->first();

        // Reset all records for this exercise, then mark the winner.
        PersonalRecord::where('client_id', $clientId)
            ->where('exercise', $exerciseName)
            ->update(['is_current' => false]);

        if ($bestPr) {
            $bestPr->update(['is_current' => true]);
        }
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
        $hasFilters = $this->category !== 'all' || strlen($this->search) > 0;

        // Single base query — fetch up to 500 rows ordered once.
        // Filters applied here so the collection is already scoped.
        $query = PersonalRecord::where('client_id', $clientId)
            ->orderByDesc('achieved_at')
            ->orderByDesc('id');

        if ($this->category !== 'all') {
            $query->where('category', $this->category);
        }

        if (strlen($this->search) > 0) {
            $query->where('exercise', 'like', '%' . $this->search . '%');
        }

        // ONE query — replaces the previous $records + $currentPrs queries
        // when filters are active.
        $records = $query->limit(500)->get();

        // Group current PRs by exercise for trophy display — derived in PHP,
        // no extra query.
        $currentPrs = $records->where('is_current', true)->keyBy('exercise');

        if ($hasFilters) {
            // Derive stats from the already-loaded (filtered) collection.
            // These numbers reflect the current filter scope, which is the
            // most useful value to show while the user is searching.
            $totalPrs       = $records->where('is_current', true)->count();
            $totalExercises = $records->where('is_current', true)->unique('exercise')->count();
            $thisMonth      = $records->where('achieved_at', '>=', now()->startOfMonth())->count();
        } else {
            // No active filters: serve global aggregate stats from a single
            // cached query (TTL 60 s).  Cache is invalidated on save/delete
            // via the 'pr-saved' event pathway; we bust it manually there.
            // Store as array, never as an Eloquent model — PHP serialization of models
            // breaks across deployments that add new columns (incomplete object error).
            $stats = Cache::remember("pr:stats:{$clientId}", 60, function () use ($clientId) {
                $row = PersonalRecord::where('client_id', $clientId)
                    ->selectRaw(
                        'COUNT(DISTINCT exercise) as total_exercises,
                         SUM(CASE WHEN is_current = 1 THEN 1 ELSE 0 END) as total_prs,
                         SUM(CASE WHEN achieved_at >= ? THEN 1 ELSE 0 END) as this_month',
                        [now()->startOfMonth()]
                    )
                    ->first();

                return $row ? $row->toArray() : ['total_exercises' => 0, 'total_prs' => 0, 'this_month' => 0];
            });

            $totalPrs       = (int) ($stats['total_prs'] ?? 0);
            $totalExercises = (int) ($stats['total_exercises'] ?? 0);
            $thisMonth      = (int) ($stats['this_month'] ?? 0);
        }

        return view('livewire.client.personal-records', [
            'records'        => $records,
            'currentPrs'     => $currentPrs,
            'totalPrs'       => $totalPrs,
            'totalExercises' => $totalExercises,
            'thisMonth'      => $thisMonth,
        ]);
    }
}
