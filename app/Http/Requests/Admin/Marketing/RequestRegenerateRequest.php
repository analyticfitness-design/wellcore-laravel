<?php
declare(strict_types=1);
namespace App\Http\Requests\Admin\Marketing;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

final class RequestRegenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, [UserRole::Admin, UserRole::Superadmin], strict: true);
    }

    public function rules(): array
    {
        return ['reason' => ['nullable','string','max:500']];
    }
}
