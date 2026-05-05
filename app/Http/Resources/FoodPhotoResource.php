<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'meal_name'      => $this->meal_name,
            'meal_index'     => $this->meal_index,
            'photo_date'     => $this->photo_date->toDateString(),
            'photo_url'      => $this->photo_url,
            'coach_seen'     => $this->coach_seen,
            'coach_reaction' => $this->coach_reaction,
            'coach_note'     => $this->coach_note,
            'xp_awarded'     => $this->xp_awarded,
            'uploaded_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
