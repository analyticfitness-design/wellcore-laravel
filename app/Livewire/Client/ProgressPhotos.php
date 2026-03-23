<?php

namespace App\Livewire\Client;

use App\Models\ProgressPhoto;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.client')]
class ProgressPhotos extends Component
{
    use WithFileUploads;

    /**
     * Stored as a plain array (not an Eloquent Collection) so Livewire's
     * snapshot serialization remains small. Each upload or selectDate action
     * would otherwise serialize up to 60 Eloquent models over the wire.
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    public array $photos = [];

    #[Validate('nullable|date')]
    public string $uploadDate = '';

    #[Validate('nullable|image|max:5120')]
    public $photoFrente = null;

    #[Validate('nullable|image|max:5120')]
    public $photoLado = null;

    #[Validate('nullable|image|max:5120')]
    public $photoEspalda = null;

    public ?string $selectedDate = null;

    public bool $showSuccess = false;

    public function mount(): void
    {
        $this->uploadDate = now()->format('Y-m-d');
        $this->loadPhotos();
    }

    public function loadPhotos(): void
    {
        $clientId = auth('wellcore')->id();

        // Convert to a plain array grouped by date string.
        // This keeps the Livewire serialization payload minimal compared to
        // storing a Collection of Eloquent models as a public property.
        $this->photos = ProgressPhoto::where('client_id', $clientId)
            ->orderByDesc('photo_date')
            ->limit(60) // 20 semanas × 3 ángulos
            ->get(['id', 'photo_date', 'tipo', 'filename'])
            ->groupBy(fn ($photo) => $photo->photo_date->format('Y-m-d'))
            ->map(fn ($group) => $group->map(fn ($p) => [
                'id'         => $p->id,
                'photo_date' => $p->photo_date->format('Y-m-d'),
                'tipo'       => $p->tipo,
                'filename'   => $p->filename,
            ])->toArray())
            ->toArray();
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $this->selectedDate === $date ? null : $date;
    }

    public function uploadPhotos(): void
    {
        $this->validate([
            'uploadDate' => 'required|date',
        ]);

        if (! $this->photoFrente && ! $this->photoLado && ! $this->photoEspalda) {
            $this->addError('upload', 'Selecciona al menos una foto para subir.');
            return;
        }

        $clientId = auth('wellcore')->id();

        $uploads = [
            'frente' => $this->photoFrente,
            'lado' => $this->photoLado,
            'espalda' => $this->photoEspalda,
        ];

        foreach ($uploads as $tipo => $photo) {
            if (! $photo) {
                continue;
            }

            $relativePath = sprintf(
                'progress/%d/%s_%s.%s',
                $clientId,
                $this->uploadDate,
                $tipo,
                $photo->getClientOriginalExtension()
            );

            // Store inside the 'public' disk so Storage::disk('public')->url() resolves correctly
            $photo->storeAs(
                dirname($relativePath),
                basename($relativePath),
                'public'
            );

            ProgressPhoto::create([
                'client_id'  => $clientId,
                'photo_date' => $this->uploadDate,
                'tipo'       => $tipo,
                'filename'   => $relativePath, // path relative to public disk root
            ]);
        }

        $this->reset(['photoFrente', 'photoLado', 'photoEspalda']);
        $this->showSuccess = true;
        $this->loadPhotos();

        $this->dispatch('photos-uploaded');
    }

    public function render()
    {
        return view('livewire.client.progress-photos');
    }
}
