<?php

namespace App\Domains\Game\Models;

use App\Domains\Game\Enums\WeaponType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'name', 'team_id', 'aim', 'skill', 'movement',
        'favorite_map_id', 'favorite_weapons',
        'kills', 'deaths', 'matches_played',
    ];

    protected $casts = [
        'aim' => 'decimal:2',
        'skill' => 'decimal:2',
        'movement' => 'decimal:2',
        'favorite_weapons' => AsArrayObject::class,
        'kills' => 'integer',
        'deaths' => 'integer',
        'matches_played' => 'integer',
    ];

    public string $overallRating {
        get => round(($this->aim + $this->skill + $this->movement) / 3, 2);
    }

    public string $primaryStat {
        get {
            $stats = ['aim' => $this->aim, 'skill' => $this->skill, 'movement' => $this->movement];
            return array_search(max($stats), $stats) ?: 'aim';
        }
    }

    public float $kdRatio {
        get => $this->deaths > 0 ? round($this->kills / $this->deaths, 2) : $this->kills;
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function favoriteMap(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'favorite_map_id');
    }

    public function weaponStats(): HasMany
    {
        return $this->hasMany(PlayerWeaponStat::class);
    }

    public function matchStats(): HasMany
    {
        return $this->hasMany(PlayerMatchStat::class);
    }

    public function getEffectiveStat(WeaponType $weapon, ?Map $map = null): float
    {
        $stat = match($weapon) {
            WeaponType::RAIL => $this->aim,
            WeaponType::ROCKET => ($this->aim + $this->movement) / 2,
            WeaponType::SHAFT => $this->skill,
            WeaponType::MG_PLASMA => $this->movement,
        };

        $favorites = (array) $this->favorite_weapons;
        if (in_array($weapon->value, $favorites)) {
            $stat *= 1.2;
        }

        if ($map && $this->favorite_map_id === $map->id) {
            $stat *= 1.15;
        }

        return round($stat, 2);
    }

    public function applyProgression(string $stat, float $increment = 0.01): void
    {
        $this->increment($stat, $increment);
        
        $current = (float) $this->$stat;
        if ($current > 5.0) {
            $this->$stat = 5.0;
            $this->save();
        }
    }
}