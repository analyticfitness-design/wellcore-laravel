<?php

namespace App\Http\Requests\Coach;

use App\Enums\PlanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'plan' => ['required', Rule::enum(PlanType::class)->except([PlanType::Trial])],
            'subject' => ['required', 'string', 'max:255'],
            'intro_message' => ['nullable', 'string', 'max:2000'],
            'cta_label' => ['nullable', 'string', 'max:100'],
        ];
    }
}
