<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Serializes a Medal with its per-client pivot state.
 *
 * Expects the pivot to be pre-attached by the controller (either via eager
 * load or by hydrating `$medal->pivot` manually). When there is no pivot
 * yet, the medal is reported as locked with no progress.
 */
class MedalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pivot = $this->pivot ?? null;
        $achieved = $pivot && $pivot->achieved_at !== null;

        return [
            'id' => (string) $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'requirement' => $this->requirement,
            'targetValue' => $this->target_value,
            'xp' => $this->xp,
            'category' => $this->category,
            'tier' => $this->tier,
            'iconLabel' => $this->icon_label,
            'stripeColors' => $this->stripe_colors,
            'achieved' => $achieved,
            'achievedAt' => $achieved ? $this->formatTimestamp($pivot->achieved_at) : null,
            'progress' => $this->when(
                ! $achieved,
                fn () => [
                    'current' => (int) ($pivot->current_progress ?? 0),
                    'target' => (int) $this->target_value,
                ],
            ),
        ];
    }

    private function formatTimestamp(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        return (string) $value;
    }
}
