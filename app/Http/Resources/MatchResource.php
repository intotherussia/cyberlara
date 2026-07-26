<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team1' => [
                'id' => $this->team1?->id,
                'name' => $this->team1?->name,
            ],
            'team2' => [
                'id' => $this->team2?->id,
                'name' => $this->team2?->name,
            ],
            'score' => [
                'team1' => $this->score_team1,
                'team2' => $this->score_team2,
            ],
            'winner' => $this->winner?->name,
            'winner_id' => $this->winner_team_id,
            'maps' => [
                $this->map1?->display_name,
                $this->map2?->display_name,
            ],
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'is_friendly' => $this->isFriendly,
            'total_events' => $this->total_events,
            'current_event' => $this->current_event,
            'progress' => $this->progress(),
            'started_at' => $this->started_at?->toISOString(),
            'finished_at' => $this->finished_at?->toISOString(),
            'events' => MatchEventResource::collection($this->whenLoaded('events')),
        ];
    }
}