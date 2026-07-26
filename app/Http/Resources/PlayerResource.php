<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'team' => [
                'id' => $this->team?->id,
                'name' => $this->team?->name,
            ],
            'stats' => [
                'aim' => $this->aim,
                'skill' => $this->skill,
                'movement' => $this->movement,
                'rating' => $this->overallRating,
                'primary_stat' => $this->primaryStat,
                'kd_ratio' => $this->kdRatio,
                'kills' => $this->kills,
                'deaths' => $this->deaths,
                'matches_played' => $this->matches_played,
            ],
            'favorite_map' => $this->favoriteMap?->display_name,
            'favorite_weapons' => $this->favorite_weapons,
            'weapon_stats' => $this->whenLoaded('weaponStats', function () {
                return $this->weaponStats->map(fn($stat) => [
                    'weapon' => $stat->weapon_type,
                    'uses' => $stat->uses_count,
                    'kills' => $stat->kills_with_weapon,
                    'efficiency' => $stat->efficiency,
                ]);
            }),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}