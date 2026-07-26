<?php

namespace App\Domains\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchEvent extends Model
{
    protected $table = 'match_events';

    protected $fillable = [
        'match_id', 'location_id',
        'team1_player_ids', 'team2_player_ids',
        'event_number', 'occurred_at',
    ];

    protected $casts = [
        'team1_player_ids' => AsArrayObject::class,
        'team2_player_ids' => AsArrayObject::class,
        'event_number' => 'integer',
        'occurred_at' => 'datetime',
    ];

    public int $totalPlayers {
        get => count($this->team1_player_ids) + count($this->team2_player_ids);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchModel::class, 'match_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function kills(): HasMany
    {
        return $this->hasMany(MatchEventKill::class);
    }

    public function getPlayersFromTeam(int $teamId): array
    {
        $match = $this->match;
        if ($match->team1_id === $teamId) {
            return (array) $this->team1_player_ids;
        }
        if ($match->team2_id === $teamId) {
            return (array) $this->team2_player_ids;
        }
        return [];
    }

    public function getKillCount(): int
    {
        return $this->kills()->count();
    }

    public function getKillsByPlayer(int $playerId): int
    {
        return $this->kills()->where('killer_player_id', $playerId)->count();
    }

    public function getDeathsByPlayer(int $playerId): int
    {
        return $this->kills()->where('victim_player_id', $playerId)->count();
    }
}
