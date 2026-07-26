<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_number' => $this->event_number,
            'location' => $this->location?->name,
            'occurred_at' => $this->occurred_at?->toISOString(),
            'kills' => $this->kills->map(fn($kill) => [
                'killer' => [
                    'id' => $kill->killer?->id,
                    'name' => $kill->killer?->name,
                ],
                'victim' => [
                    'id' => $kill->victim?->id,
                    'name' => $kill->victim?->name,
                ],
                'weapon' => $kill->weapon_used,
            ]),
            'kills_count' => $this->kills->count(),
        ];
    }
}