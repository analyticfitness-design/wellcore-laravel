<?php

namespace App\Livewire\Public;

use App\Models\CoachApplication as CoachApplicationModel;
use Illuminate\Support\Str;
use Livewire\Component;

class CoachApplication extends Component
{
    // Form fields matching the DB schema
    public string $name = '';
    public string $email = '';
    public string $whatsapp = '';
    public string $city = '';
    public string $bio = '';
    public string $experience = '';  // 1-2, 3-5, 5-10, 10+
    public string $plan = '';  // training, nutrition, both
    public string $current_clients = '';  // 0, 1-5, 6-15, 16+
    public array $specializations = [];
    public string $referral = '';
    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'city' => 'required|string|max:100',
            'bio' => 'required|string|min:50|max:2000',
            'experience' => 'required|in:1-2,3-5,5-10,10+',
            'plan' => 'required|in:training,nutrition,both',
            'current_clients' => 'required|in:0,1-5,6-15,16+',
            'specializations' => 'required|array|min:1',
            'referral' => 'nullable|string|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo valido.',
            'whatsapp.required' => 'El numero de WhatsApp es obligatorio.',
            'city.required' => 'La ciudad es obligatoria.',
            'bio.required' => 'La biografia es obligatoria.',
            'bio.min' => 'La biografia debe tener al menos 50 caracteres.',
            'bio.max' => 'La biografia no puede superar los 2000 caracteres.',
            'experience.required' => 'Selecciona tu experiencia.',
            'plan.required' => 'Selecciona el tipo de coaching.',
            'current_clients.required' => 'Selecciona cuantos clientes manejas.',
            'specializations.required' => 'Selecciona al menos una especializacion.',
            'specializations.min' => 'Selecciona al menos una especializacion.',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        CoachApplicationModel::create([
            'id' => Str::ulid(),
            'name' => $this->name,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'city' => $this->city,
            'bio' => $this->bio,
            'experience' => $this->experience,
            'plan' => $this->plan,
            'current_clients' => $this->current_clients,
            'specializations' => $this->specializations,
            'referral' => $this->referral,
            'ip_hash' => hash('sha256', request()->ip()),
            'status' => 'pending',
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.public.coach-application')
            ->layout('components.layouts.public');
    }
}
