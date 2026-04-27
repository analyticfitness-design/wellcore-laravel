<?php

declare(strict_types=1);

namespace App\Http\Resources\Coach\Marketing;

use App\Models\CoachContentPieceState;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CoachContentPieceState */
final class PieceStateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'piece_type' => $this->piece_type->value,
            'piece_key' => $this->piece_key,
            'state' => $this->state->value,
            'published_url' => $this->published_url,
            'state_changed_at' => $this->state_changed_at?->toIso8601String(),
        ];
    }
}
