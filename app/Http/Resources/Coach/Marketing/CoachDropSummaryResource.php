<?php

declare(strict_types=1);

namespace App\Http\Resources\Coach\Marketing;

use App\Enums\Marketing\PieceState;
use App\Models\CoachContentDrop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CoachContentDrop */
final class CoachDropSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'iso_year' => $this->iso_year,
            'iso_week' => $this->iso_week,
            'week_starts_on' => $this->week_starts_on?->toDateString(),
            'status' => $this->status->value,
            'brief_title' => data_get($this->content, 'brief.title'),
            'pieces_completed' => $this->pieceStates->where('state', PieceState::Published)->count(),
            'pieces_total' => $this->pieceStates->count(),
        ];
    }
}
