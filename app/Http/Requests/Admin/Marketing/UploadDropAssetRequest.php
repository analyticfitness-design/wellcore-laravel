<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Marketing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UploadDropAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // policy is enforced by the controller via Gate
    }

    public function rules(): array
    {
        return [
            'file'                  => ['required', 'file', 'max:51200', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4'],
            'role'                  => ['nullable', Rule::in(['story_main', 'story_slide', 'reel_thumbnail', 'reel_scene', 'launch_sequence', 'brand_cover', 'other'])],
            'linked_to.type'        => ['nullable', Rule::in(['story', 'reel', 'slide', 'drop'])],
            'linked_to.day'         => ['nullable', Rule::in(['LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB', 'DOM'])],
            'linked_to.reel_key'    => ['nullable', Rule::in(['reel_1', 'reel_2'])],
            'linked_to.slide_index' => ['nullable', 'integer', 'min:0', 'max:2'],
            'caption'               => ['nullable', 'string', 'max:200'],
            'order'                 => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes'                 => ['nullable', 'string', 'max:400'],
        ];
    }

    public function linkedTo(): ?array
    {
        $linked = $this->input('linked_to');
        if (!is_array($linked) || empty($linked)) {
            return null;
        }
        return array_filter($linked, fn ($v) => $v !== null && $v !== '');
    }
}
