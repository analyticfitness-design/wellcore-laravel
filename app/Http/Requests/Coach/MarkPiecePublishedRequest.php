<?php
declare(strict_types=1);
namespace App\Http\Requests\Coach;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

final class MarkPiecePublishedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Coach;
    }

    public function rules(): array
    {
        return [
            'url'   => ['nullable','url','max:500'],
            'notes' => ['nullable','string','max:1000'],
        ];
    }
}
