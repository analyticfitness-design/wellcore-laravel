<?php

namespace App\Livewire\Client;

use App\Models\ProgressPhoto;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.client')]
class ProgressPhotos extends Component
{
    use WithFileUploads;

    public Collection $photos;

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

        $this->photos = ProgressPhoto::where('client_id', $clientId)
            ->orderByDesc('photo_date')
            ->get()
            ->groupBy(fn ($photo) => $photo->photo_date->format('Y-m-d'));
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

            $filename = sprintf(
                'progress/%d/%s_%s.%s',
                $clientId,
                $this->uploadDate,
                $tipo,
                $photo->getClientOriginalExtension()
            );

            $photo->storeAs('public', $filename);

            ProgressPhoto::create([
                'client_id' => $clientId,
                'photo_date' => $this->uploadDate,
                'tipo' => $tipo,
                'filename' => $filename,
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
