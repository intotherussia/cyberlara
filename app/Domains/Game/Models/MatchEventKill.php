<?php

namespace App\Domains\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchEventKill extends Model
{
    protected $fillable = [
        'match_event_id', 'killer_player_id', 'victim_player_id', 'weapon_used',
    ];

    public function matchEvent(): BelongsTo
    {
        return $this->belongsTo(MatchEvent::class);
    }

    public function killer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'killer_player_id');
    }

    public function victim(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'victim_player_id');
    }
}