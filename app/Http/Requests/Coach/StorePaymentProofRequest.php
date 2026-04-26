<?php

namespace App\Http\Requests\Coach;

use App\Enums\PaymentProofMethod;
use App\Enums\PlanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware (auth:wellcore + coach.contract) enforces access
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email:rfc', 'max:255'],
            'plan' => ['required', Rule::enum(PlanType::class)->except([PlanType::Trial])],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::enum(PaymentProofMethod::class)],
            'coach_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'El comprobante es obligatorio.',
            'file.mimes' => 'El archivo debe ser JPG, PNG o PDF.',
            'file.max' => 'El archivo no puede superar los 10 MB.',
            'client_name.required' => 'El nombre del cliente es obligatorio.',
            'client_email.required' => 'El email del cliente es obligatorio.',
            'client_email.email' => 'Ingresa un email válido.',
            'plan.required' => 'Selecciona un plan.',
            'plan.enum' => 'El plan seleccionado no es válido.',
            'amount.numeric' => 'El monto debe ser un número.',
            'amount.min' => 'El monto no puede ser negativo.',
            'payment_method.enum' => 'El método de pago no es válido.',
            'coach_note.max' => 'La nota no puede superar los 1000 caracteres.',
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $file = $this->file('file');
            if (! $file || ! $file->isValid()) {
                return;
            }

            $realPath = $file->getRealPath();
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMime = $finfo ? finfo_file($finfo, $realPath) : null;
            if ($finfo) {
                finfo_close($finfo);
            }

            $allowed = ['image/jpeg', 'image/png', 'application/pdf'];

            if (! $realMime || ! in_array($realMime, $allowed, true)) {
                $validator->errors()->add(
                    'file',
                    'El archivo no es una imagen o PDF válido. Asegúrate de subir un JPG, PNG o PDF real.'
                );
            }
        });
    }
}
