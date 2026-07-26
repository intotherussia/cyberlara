<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tag' => $this->tag,
            'description' => $this->description,
            'owner' => [
                'id' => $this->owner?->id,
                'name' => $this->owner?->name,
            ],
            'stats' => [
                'wins' => $this->wins,
                'losses' => $this->losses,
                'total_matches' => $this->total_matches,
                'win_rate' => $this->winRate,
            ],
            'players' => PlayerResource::collection($this->whenLoaded('players')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}