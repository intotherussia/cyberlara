<?php

namespace App\Domains\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name', 'owner_id', 'tag', 'description',
        'wins', 'losses', 'total_matches',
    ];

    protected $casts = [
        'wins' => 'integer',
        'losses' => 'integer',
        'total_matches' => 'integer',
    ];

    public float $winRate {
        get => $this->total_matches > 0
            ? round(($this->wins / $this->total_matches) * 100, 2)
            : 0;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'team1_id')
            ->orWhere('team2_id', $this->id);
    }

    public function wonMatches(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'winner_team_id');
    }
}
