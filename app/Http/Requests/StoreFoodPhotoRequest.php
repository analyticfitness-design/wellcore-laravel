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
            'photo'      => 'required|file|image|mimes:jpg,jpeg,png,webp|max:10240',
            'meal_name'  => 'required|string|max:255',
            'meal_index' => 'required|integer|min:0|max:20',
            'photo_date' => "sometimes|date|after_or_equal:{$yesterday}|before_or_equal:{$tomorrow}",
        ];
    }

    public function photoDate(): string
    {
        return $this->input('photo_date', Carbon::now('America/Bogota')->toDateString());
    }
}
