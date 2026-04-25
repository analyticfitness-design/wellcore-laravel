<?php

namespace App\Http\Requests\Coach;

use App\Enums\PlanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La Policy del controller se encarga de la autorización
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'plan' => ['required', Rule::enum(PlanType::class)->except([PlanType::Trial])],
            'subject' => ['required', 'string', 'max:255'],
            'intro_message' => ['nullable', 'string', 'max:2000'],
            'cta_label' => ['nullable', 'string', 'max:100'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El email del prospecto es obligatorio.',
            'email.email' => 'Ingresa un email válido.',
            'plan.required' => 'Selecciona un plan.',
            'plan.enum' => 'El plan seleccionado no es válido.',
            'subject.required' => 'El asunto del email es obligatorio.',
            'intro_message.max' => 'El mensaje no puede superar los 2000 caracteres.',
            'expires_in_days.min' => 'La invitación debe durar al menos 1 día.',
            'expires_in_days.max' => 'La invitación no puede durar más de 30 días.',
        ];
    }
}
