<?php

namespace App\Livewire\Rise;

use App\Models\ProgressPhoto;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rise', ['title' => 'Fotos de Progreso'])]
class Photos extends Component
{
    public array $photosByDate = [];
    public ?string $firstDate = null;
    public ?string $latestDate = null;

    public function mount(): void
    {
        $client = auth('wellcore')->user();

        $photos = ProgressPhoto::where('client_id', $client->id)
            ->orderBy('photo_date', 'desc')
            ->get();

        // Group by photo_date
        $grouped = $photos->groupBy(fn ($photo) => $photo->photo_date->format('Y-m-d'));

        $this->photosByDate = [];
        foreach ($grouped as $date => $datePhotos) {
            $this->photosByDate[] = [
                'date' => $date,
                'formatted' => \Carbon\Carbon::parse($date)->translatedFormat('d M Y'),
                'frente' => $datePhotos->firstWhere('tipo', 'frente')?->filename,
                'perfil' => $datePhotos->firstWhere('tipo', 'perfil')?->filename,
                'espalda' => $datePhotos->firstWhere('tipo', 'espalda')?->filename,
            ];
        }

        if ($photos->count() > 0) {
            $this->firstDate = $photos->last()->photo_date->format('Y-m-d');
            $this->latestDate = $photos->first()->photo_date->format('Y-m-d');
        }
    }

    public function render()
    {
        return view('livewire.rise.photos');
    }
}
