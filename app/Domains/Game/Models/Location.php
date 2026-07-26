<?php

namespace App\Domains\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'map_id', 'name', 'priority_weapon', 'bonus',
    ];

    protected $casts = [
        'bonus' => 'decimal:2',
    ];

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class);
    }
}