<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Marketing;

use App\Models\CoachContentDrop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CoachContentDrop */
final class QueueRowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coach' => [
                'id' => $this->coach?->id,
                'name' => $this->coach?->name,
            ],
            'iso_year' => $this->iso_year,
            'iso_week' => $this->iso_week,
            'status' => $this->status->value,
            'last_action_at' => ($this->reviewed_at ?? $this->generated_at ?? $this->created_at)?->toIso8601String(),
        ];
    }
}
