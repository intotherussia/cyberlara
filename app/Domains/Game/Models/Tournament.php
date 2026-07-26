<?php

namespace App\Domains\Game\Models;

use App\Domains\Game\Enums\TournamentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    protected $fillable = [
        'name', 'type', 'rules', 'is_active', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'rules' => AsArrayObject::class,
        'type' => TournamentType::class,
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function matches(): HasMany
    {
        return $this->hasMany(MatchModel::class);
    }
}
