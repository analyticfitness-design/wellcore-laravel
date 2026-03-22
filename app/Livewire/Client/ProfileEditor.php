<?php

namespace App\Livewire\Client;

use App\Models\Client;
use App\Models\ClientProfile;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.client')]
class ProfileEditor extends Component
{
    // Client fields
    #[Validate('required|string|max:255')]
    public string $name = '';

    // No #[Validate] on email — uniqueness requires Rule::unique()->ignore() in save()
    public string $email = '';

    #[Validate('nullable|string|max:100')]
    public string $city = '';

    #[Validate('nullable|string|max:1000')]
    public string $bio = '';

    #[Validate('nullable|date')]
    public string $birthDate = '';

    // ClientProfile fields
    #[Validate('nullable|numeric|min:30|max:300')]
    public string $peso = '';

    #[Validate('nullable|numeric|min:100|max:250')]
    public string $altura = '';

    #[Validate('nullable|string|max:500')]
    public string $objetivo = '';

    #[Validate('nullable|string|max:20')]
    public string $whatsapp = '';

    #[Validate('nullable|in:principiante,intermedio,avanzado')]
    public string $nivel = '';

    #[Validate('nullable|in:gym,casa,ambos')]
    public string $lugarEntreno = '';

    public array $diasDisponibles = [];

    #[Validate('nullable|string|max:1000')]
    public string $restricciones = '';

    public bool $showSuccess = false;

    public function mount(): void
    {
        /** @var Client $client */
        $client = auth('wellcore')->user();

        $this->name = $client->name ?? '';
        $this->email = $client->email ?? '';
        $this->city = $client->city ?? '';
        $this->bio = $client->bio ?? '';
        $this->birthDate = $client->birth_date?->format('Y-m-d') ?? '';

        $profile = $client->profile;

        if ($profile) {
            $this->peso = (string) ($profile->peso ?? '');
            $this->altura = (string) ($profile->altura ?? '');
            $this->objetivo = $profile->objetivo ?? '';
            $this->whatsapp = $profile->whatsapp ?? '';
            $this->nivel = $profile->nivel ?? '';
            $this->lugarEntreno = $profile->lugar_entreno ?? '';
            $this->diasDisponibles = $profile->dias_disponibles ?? [];
            $this->restricciones = $profile->restricciones ?? '';
        }
    }

    public function save(): void
    {
        /** @var Client $client */
        $client = auth('wellcore')->user();

        $this->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($client->id)],
            'city'          => 'nullable|string|max:100',
            'bio'           => 'nullable|string|max:1000',
            'birthDate'     => 'nullable|date',
            'peso'          => 'nullable|numeric|min:30|max:300',
            'altura'        => 'nullable|numeric|min:100|max:250',
            'objetivo'      => 'nullable|string|max:500',
            'whatsapp'      => 'nullable|string|max:20',
            'nivel'         => 'nullable|in:principiante,intermedio,avanzado',
            'lugarEntreno'  => 'nullable|in:gym,casa,ambos',
            'restricciones' => 'nullable|string|max:1000',
        ]);

        $client->update([
            'name' => $this->name,
            'email' => $this->email,
            'city' => $this->city ?: null,
            'bio' => $this->bio ?: null,
            'birth_date' => $this->birthDate ?: null,
        ]);

        ClientProfile::updateOrCreate(
            ['client_id' => $client->id],
            [
                'peso' => $this->peso !== '' ? $this->peso : null,
                'altura' => $this->altura !== '' ? $this->altura : null,
                'objetivo' => $this->objetivo ?: null,
                'whatsapp' => $this->whatsapp ?: null,
                'nivel' => $this->nivel ?: null,
                'lugar_entreno' => $this->lugarEntreno ?: null,
                'dias_disponibles' => $this->diasDisponibles,
                'restricciones' => $this->restricciones ?: null,
            ]
        );

        $this->showSuccess = true;

        $this->dispatch('profile-saved');
    }

    public function render()
    {
        return view('livewire.client.profile-editor');
    }
}
