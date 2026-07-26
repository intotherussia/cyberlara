<?php

namespace App\Domains\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerMatchStat extends Model
{
    protected $fillable = [
        'player_id', 'match_id', 'team_id',
        'kills', 'deaths',
        'aim_before', 'skill_before', 'movement_before',
        'aim_after', 'skill_after', 'movement_after',
        'weapons_used',
    ];

    protected $casts = [
        'kills' => 'integer',
        'deaths' => 'integer',
        'aim_before' => 'decimal:2',
        'skill_before' => 'decimal:2',
        'movement_before' => 'decimal:2',
        'aim_after' => 'decimal:2',
        'skill_after' => 'decimal:2',
        'movement_after' => 'decimal:2',
        'weapons_used' => AsArrayObject::class,
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchModel::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
