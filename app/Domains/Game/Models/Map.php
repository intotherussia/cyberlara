<?php

namespace App\Domains\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Map extends Model
{
    protected $fillable = [
        'name', 'display_name', 'description', 'image_url', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }
}