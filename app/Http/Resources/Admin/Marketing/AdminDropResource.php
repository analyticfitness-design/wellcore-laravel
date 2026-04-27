<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Marketing;

use App\Models\CoachContentDrop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CoachContentDrop */
final class AdminDropResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coach_id' => $this->coach_id,
            'coach' => [
                'id' => $this->coach?->id,
                'name' => $this->coach?->name,
                'role' => $this->coach?->role?->value,
            ],
            'iso_year' => $this->iso_year,
            'iso_week' => $this->iso_week,
            'status' => $this->status->value,
            'content' => $this->content,
            'original_content' => $this->original_content,
            'intake_snapshot' => $this->intake_snapshot,
            'admin_edits_diff' => $this->admin_edits_diff,
            'generated_by_session_id' => $this->generated_by_session_id,
            'generated_at' => $this->generated_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'ready_at' => $this->ready_at?->toIso8601String(),
        ];
    }
}
