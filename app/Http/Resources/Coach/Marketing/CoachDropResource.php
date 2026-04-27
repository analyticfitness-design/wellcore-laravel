<?php

declare(strict_types=1);

namespace App\Http\Resources\Coach\Marketing;

use App\Models\CoachContentDrop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CoachContentDrop */
final class CoachDropResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'iso_year' => $this->iso_year,
            'iso_week' => $this->iso_week,
            'week_starts_on' => $this->week_starts_on?->toDateString(),
            'status' => $this->status->value,
            'content' => $this->content,
            'schema_version' => $this->schema_version,
            'attribution' => config('marketing.attribution.line'),
            'ready_at' => $this->ready_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'pieces' => PieceStateResource::collection($this->whenLoaded('pieceStates')),
        ];
    }
}
