<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreFoodPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('wellcore') !== null;
    }

    public function rules(): array
    {
        $tomorrow = Carbon::now('America/Bogota')->addDay()->toDateString();
        $yesterday = Carbon::now('America/Bogota')->subDay()->toDateString();

        return [
            // image rule omitido a proposito: iOS HEIC no es detectado como image en GD,
            // pero Intervention v4 (Imagick si esta) o el flujo del service lo maneja.
            // Aceptamos via mimes incluyendo heic/heif para iPhone default.
            'photo'      => 'required|file|mimetypes:image/jpeg,image/jpg,image/png,image/webp,image/heic,image/heif|max:15360',
            'meal_name'  => 'required|string|max:255',
            'meal_index' => 'required|integer|min:0|max:20',
            'photo_date' => "sometimes|date|after_or_equal:{$yesterday}|before_or_equal:{$tomorrow}",
            'client_note' => 'nullable|string|max:1000',
        ];
    }

    public function photoDate(): string
    {
        return $this->input('photo_date', Carbon::now('America/Bogota')->toDateString());
    }
}
