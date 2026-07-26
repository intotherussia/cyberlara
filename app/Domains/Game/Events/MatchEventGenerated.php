<?php

namespace App\Domains\Game\Events;

use App\Domains\Game\Models\MatchEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchEventGenerated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MatchEvent $event
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("match.{$this->event->match_id}");
    }

    public function broadcastAs(): string
    {
        return 'match.event';
    }

    public function broadcastWith(): array
    {
        $match = $this->event->match;

        $kills = [];
        foreach ($this->event->kills as $kill) {
            $kills[] = [
                'killer' => $kill->killer?->name ?? '???',
                'killer_team_id' => $kill->killer?->team_id ?? null,
                'killer_team_name' => $kill->killer?->team?->name ?? '???',
                'victim' => $kill->victim?->name ?? '???',
                'victim_team_id' => $kill->victim?->team_id ?? null,
                'victim_team_name' => $kill->victim?->team?->name ?? '???',
                'weapon' => $kill->weapon_used ?? '?',
            ];
        }

        return [
            'event_number' => $this->event->event_number,
            'location' => $this->event->location?->name ?? 'Неизвестно',
            'kills' => $kills,
            'kills_count' => count($kills),
            'score' => [
                'team1' => $match->score_team1 ?? 0,
                'team2' => $match->score_team2 ?? 0,
            ],
            'total_events' => $match->total_events ?? 0,
            'progress' => $match ? $match->progress() : 0,
            'timestamp' => now()->toISOString(),
        ];
    }
}
