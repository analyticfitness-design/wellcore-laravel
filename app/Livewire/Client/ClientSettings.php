<?php

namespace App\Livewire\Client;

use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.client')]
class ClientSettings extends Component
{
    // Tab: Perfil
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:30')]
    public string $phone = '';

    // Tab: Seguridad
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $confirmPassword = '';

    // Flash states
    public bool $profileSaved = false;
    public bool $passwordSaved = false;
    public string $passwordError = '';

    public function mount(): void
    {
        /** @var Client $client */
        $client = auth('wellcore')->user();

        $this->name  = $client->name ?? '';
        $this->email = $client->email ?? '';
        // phone may not exist on clients table — use a try/catch to be safe
        $this->phone = $client->phone ?? '';
    }

    public function updateProfile(): void
    {
        $this->validateOnly('name');
        $this->validateOnly('email');
        $this->validateOnly('phone');

        /** @var Client $client */
        $client = auth('wellcore')->user();

        $data = [
            'name'  => $this->name,
            'email' => $this->email,
        ];

        // Only update phone if the column exists on the model (fillable)
        if (in_array('phone', $client->getFillable(), true)) {
            $data['phone'] = $this->phone ?: null;
        }

        $client->update($data);

        $this->profileSaved = true;
        $this->dispatch('profile-updated');
    }

    public function changePassword(): void
    {
        $this->passwordError = '';
        $this->passwordSaved = false;

        /** @var Client $client */
        $client = auth('wellcore')->user();

        if (empty($this->currentPassword)) {
            $this->passwordError = 'Ingresa tu contrasena actual.';
            return;
        }

        if (!password_verify($this->currentPassword, $client->password_hash)) {
            $this->passwordError = 'La contrasena actual es incorrecta.';
            return;
        }

        if (strlen($this->newPassword) < 8) {
            $this->passwordError = 'La nueva contrasena debe tener al menos 8 caracteres.';
            return;
        }

        if ($this->newPassword !== $this->confirmPassword) {
            $this->passwordError = 'Las contrasenas no coinciden.';
            return;
        }

        $client->update([
            'password_hash' => bcrypt($this->newPassword),
        ]);

        $this->currentPassword = '';
        $this->newPassword     = '';
        $this->confirmPassword = '';
        $this->passwordSaved   = true;
        $this->dispatch('password-changed');
    }

    public function render()
    {
        return view('livewire.client.client-settings');
    }
}
