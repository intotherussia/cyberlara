<?php

namespace App\Domains\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerWeaponStat extends Model
{
    protected $fillable = [
        'player_id', 'weapon_type', 'uses_count', 'kills_with_weapon', 'efficiency',
    ];

    protected $casts = [
        'uses_count' => 'integer',
        'kills_with_weapon' => 'integer',
        'efficiency' => 'decimal:2',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}