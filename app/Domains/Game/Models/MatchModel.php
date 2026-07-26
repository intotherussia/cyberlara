<?php

namespace App\Domains\Game\Models;

use App\Domains\Game\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MatchModel extends Model
{

    protected $table = 'matches';

    protected $fillable = [
        'team1_id', 'team2_id', 'tournament_id',
        'status', 'map1_id', 'map2_id', 'current_map_index',
        'score_team1', 'score_team2', 'winner_team_id',
        'total_events', 'current_event',
        'started_at', 'finished_at',
    ];

    protected $casts = [
        'status' => MatchStatus::class,
        'current_map_index' => 'integer',
        'score_team1' => 'integer',
        'score_team2' => 'integer',
        'total_events' => 'integer',
        'current_event' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public bool $isFriendly {
        get => $this->tournament_id === null;
    }

    public array $mapsList {
        get => [$this->map1_id, $this->map2_id];
    }

    public bool $hasWinner {
        get => $this->winner_team_id !== null;
    }

    public function team1(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function map1(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'map1_id');
    }

    public function map2(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'map2_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id')->orderBy('event_number');
    }

    public function kills(): HasManyThrough
    {
        return $this->hasManyThrough(
            MatchEventKill::class,
            MatchEvent::class,
            'match_id',       // foreign key on MatchEvent
            'match_event_id', // foreign key on MatchEventKill
            'id',             // local key on MatchModel
            'id'              // local key on MatchEvent
        );
    }

    public function currentMap(): ?Map
    {
        if ($this->current_map_index === 0) {
            return $this->map1;
        }
        return $this->map2;
    }

    public function isFinished(): bool
    {
        return $this->status === MatchStatus::FINISHED;
    }

    public function isInProgress(): bool
    {
        return $this->status === MatchStatus::IN_PROGRESS;
    }

    public function getScore(Team $team): int
    {
        if ($team->id === $this->team1_id) {
            return $this->score_team1;
        }
        if ($team->id === $this->team2_id) {
            return $this->score_team2;
        }
        return 0;
    }

    public function getOpponent(Team $team): ?Team
    {
        if ($team->id === $this->team1_id) {
            return $this->team2;
        }
        if ($team->id === $this->team2_id) {
            return $this->team1;
        }
        return null;
    }

    public function getTeamPlayers(int $teamId): array
    {
        if ($this->team1_id === $teamId) {
            return $this->team1->players->pluck('id')->toArray();
        }
        if ($this->team2_id === $teamId) {
            return $this->team2->players->pluck('id')->toArray();
        }
        return [];
    }

    public function progress(): float
    {
        if ($this->total_events === 0) {
            return 0;
        }
        return round(($this->current_event / $this->total_events) * 100, 2);
    }

    public function isCurrentMapFinished(): bool
    {
        $eventsPerMap = $this->eventsPerMap();
        $eventsOnCurrentMap = $this->current_event - ($this->current_map_index * $eventsPerMap);
        return $eventsOnCurrentMap >= $eventsPerMap;
    }

    /**
     * Количество событий на одну карту
     */
    public function eventsPerMap(): int
    {
        return (int) ceil($this->total_events / 2);
    }

    /**
     * Переключиться на следующую карту
     */
    public function switchToNextMap(): bool
    {
        if ($this->current_map_index === 0) {
            $this->current_map_index = 1;
            $this->save();
            return true;
        }
        return false;
    }
}
