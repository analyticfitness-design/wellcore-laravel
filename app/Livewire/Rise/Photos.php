<?php

namespace App\Livewire\Rise;

use App\Models\ProgressPhoto;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.rise', ['title' => 'Fotos de Progreso'])]
class Photos extends Component
{
    use WithFileUploads;

    public array $photosByDate = [];
    public ?string $firstDate = null;
    public ?string $latestDate = null;

    // Upload form state
    public string $uploadDate = '';
    public $photoFrente = null;
    public $photoPerfil = null;
    public $photoEspalda = null;
    public bool $uploadSuccess = false;

    public function mount(): void
    {
        $this->uploadDate = now()->format('Y-m-d');
        $this->loadPhotos();
    }

    private function loadPhotos(): void
    {
        $client = auth('wellcore')->user();

        $photos = ProgressPhoto::where('client_id', $client->id)
            ->orderBy('photo_date', 'desc')
            ->get();

        $grouped = $photos->groupBy(fn ($photo) => $photo->photo_date->format('Y-m-d'));

        $this->photosByDate = [];
        foreach ($grouped as $date => $datePhotos) {
            $this->photosByDate[] = [
                'date'       => $date,
                'formatted'  => \Carbon\Carbon::parse($date)->translatedFormat('d M Y'),
                'frente'     => $datePhotos->firstWhere('tipo', 'frente')?->filename,
                'frente_id'  => $datePhotos->firstWhere('tipo', 'frente')?->id,
                'perfil'     => $datePhotos->firstWhere('tipo', 'perfil')?->filename,
                'perfil_id'  => $datePhotos->firstWhere('tipo', 'perfil')?->id,
                'espalda'    => $datePhotos->firstWhere('tipo', 'espalda')?->filename,
                'espalda_id' => $datePhotos->firstWhere('tipo', 'espalda')?->id,
            ];
        }

        if ($photos->count() > 0) {
            $this->firstDate = $photos->last()->photo_date->format('Y-m-d');
            $this->latestDate = $photos->first()->photo_date->format('Y-m-d');
        }
    }

    public function uploadPhotos(): void
    {
        $this->uploadSuccess = false;

        $this->validate([
            'uploadDate'   => 'required|date',
            'photoFrente'  => 'nullable|image|max:5120',
            'photoPerfil'  => 'nullable|image|max:5120',
            'photoEspalda' => 'nullable|image|max:5120',
        ]);

        if (! $this->photoFrente && ! $this->photoPerfil && ! $this->photoEspalda) {
            $this->addError('upload', 'Selecciona al menos una foto antes de guardar.');
            return;
        }

        $client = auth('wellcore')->user();
        $uploadDir = public_path('uploads/photos');

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tiposToUpload = [
            'frente'  => $this->photoFrente,
            'perfil'  => $this->photoPerfil,
            'espalda' => $this->photoEspalda,
        ];

        foreach ($tiposToUpload as $tipo => $photo) {
            if ($photo === null) {
                continue;
            }

            $extension = $photo->getClientOriginalExtension() ?: 'jpg';
            $filename = "{$client->id}_{$this->uploadDate}_{$tipo}_" . time() . ".{$extension}";

            // Livewire's TemporaryUploadedFile::getPathname() returns a tmpfile on /tmp
            // (different filesystem from /code), so Symfony's move() via rename() fails
            // with EXDEV. copy() works across filesystems; use it instead.
            $destPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
            copy($photo->getPathname(), $destPath);

            ProgressPhoto::where('client_id', $client->id)
                ->where('photo_date', $this->uploadDate)
                ->where('tipo', $tipo)
                ->delete();

            ProgressPhoto::create([
                'client_id'  => $client->id,
                'photo_date' => $this->uploadDate,
                'tipo'       => $tipo,
                'filename'   => $filename,
            ]);
        }

        $this->photoFrente  = null;
        $this->photoPerfil  = null;
        $this->photoEspalda = null;
        $this->uploadSuccess = true;

        $this->loadPhotos();
    }

    public function deletePhoto(int $photoId): void
    {
        $client = auth('wellcore')->user();

        $photo = ProgressPhoto::where('id', $photoId)
            ->where('client_id', $client->id)
            ->first();

        if (! $photo) {
            return;
        }

        $filePath = public_path('uploads/photos/' . $photo->filename);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $photo->delete();
        $this->loadPhotos();
    }

    public function render()
    {
        return view('livewire.rise.photos');
    }
}
