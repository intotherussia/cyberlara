<?php

namespace App\Domains\Game\Events;

use App\Domains\Game\Models\MatchModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchFinished implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MatchModel $match
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("match.{$this->match->id}");
    }

    public function broadcastAs(): string
    {
        return 'match.finished';
    }

    public function broadcastWith(): array
    {
        return [
            'winner' => $this->match->winner?->name,
            'winner_id' => $this->match->winner_team_id,
            'score' => [
                'team1' => $this->match->score_team1,
                'team2' => $this->match->score_team2,
            ],
            'total_events' => $this->match->total_events,
            'team1_name' => $this->match->team1?->name,
            'team2_name' => $this->match->team2?->name,
            'finished_at' => $this->match->finished_at?->toISOString(),
            'stats' => [
                'team1_players' => $this->match->team1?->players->map(fn($p) => [
                    'name' => $p->name,
                    'kills' => $this->match->kills()->where('killer_player_id', $p->id)->count(),
                    'deaths' => $this->match->kills()->where('victim_player_id', $p->id)->count(),
                ]),
                'team2_players' => $this->match->team2?->players->map(fn($p) => [
                    'name' => $p->name,
                    'kills' => $this->match->kills()->where('killer_player_id', $p->id)->count(),
                    'deaths' => $this->match->kills()->where('victim_player_id', $p->id)->count(),
                ]),
            ],
        ];
    }
}
